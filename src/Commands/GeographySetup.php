<?php

namespace Enadstack\CountryData\Commands;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Services\GeographyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\multiselect;

class GeographySetup extends Command
{
    protected $signature = 'country-data:setup
                            {--migrate       : Run migrations only (skip seed prompt)}
                            {--seed          : Run seeders only (skip migrate)}
                            {--all           : Seed all countries without prompting}
                            {--source=       : Seed countries from a configured source file (all, arab, gulf, europe)}
                            {--countries=    : Comma-separated ISO-2 codes to seed (e.g. JO,SA,AE)}
                            {--fresh         : Drop and re-create geography tables before migrating}';

    protected $description = 'Set up geography tables interactively: migrate, then seed selected countries';

    public function handle(GeographyService $geography): int
    {
        $this->newLine();
        $this->components->info('Geography Setup');
        $this->newLine();

        $migrateOnly = $this->option('migrate');
        $seedOnly = $this->option('seed');
        $seedAll = $this->option('all');
        $sourceOption = $this->option('source');
        $codesOption = $this->option('countries');
        $fresh = $this->option('fresh');

        $runMigrate = ! $seedOnly;
        $runSeed = ! $migrateOnly;

        // ── Step 1: Migrations ────────────────────────────────────────────────

        if ($runMigrate) {
            $shouldMigrate = $migrateOnly || $this->components->confirm('Run migrations?', true);

            if ($shouldMigrate) {
                if ($fresh) {
                    if (! $this->components->confirm(
                        '<fg=red>--fresh will DROP the areas, cities, and countries tables. Continue?</>',
                        false
                    )) {
                        $this->components->warn('Aborted.');

                        return self::FAILURE;
                    }

                    foreach (['areas', 'cities', 'countries'] as $table) {
                        Schema::dropIfExists($table);
                    }

                    /**
                     * Dropping the tables is not enough — the rows in `migrations`
                     * survive, so the migrator would see nothing pending and the
                     * tables would never be recreated. Forget this package's
                     * migrations so the run below actually re-applies them.
                     */
                    $this->forgetPackageMigrations();

                    $this->components->warn('Dropped: areas, cities, countries');
                }

                $this->components->task('Running migrations', function () {
                    $this->call('migrate', [
                        '--path' => 'vendor/enadstack/laravel-country-data/database/migrations',
                        '--force' => true,
                    ]);
                });

                $this->newLine();
            }
        }

        // ── Step 2: Seed ──────────────────────────────────────────────────────

        if (! $runSeed) {
            $this->components->info('Migrations complete. Skipping seeder (--migrate flag used).');

            return self::SUCCESS;
        }

        $shouldSeed = $seedOnly || $this->components->confirm('Seed geography data?', true);

        if (! $shouldSeed) {
            $this->components->info('Skipped seeding. Run <fg=yellow>country-data:setup --seed</> any time to seed later.');

            return self::SUCCESS;
        }

        // ── Step 3: Country selection ─────────────────────────────────────────

        $selectedCodes = $this->resolveCountryCodes($codesOption, $seedAll, $sourceOption);

        if ($selectedCodes === null) {
            // User cancelled the selection
            $this->components->warn('No countries selected. Seeding skipped.');

            return self::SUCCESS;
        }

        // ── Step 4: Run seeders ───────────────────────────────────────────────

        $label = empty($selectedCodes)
            ? 'all countries'
            : implode(', ', $selectedCodes).' ('.count($selectedCodes).')';

        $this->newLine();
        $this->components->info("Seeding: {$label}");
        $this->newLine();

        $seeder = new GeographySeeder;
        $seeder->countryCodes = $selectedCodes;
        $seeder->setCommand($this);
        app()->call([$seeder, 'run']);

        $this->newLine();
        $this->components->info('Geography setup complete.');

        return self::SUCCESS;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Remove this package's rows from the `migrations` table so that a --fresh
     * run re-applies them against the freshly dropped tables.
     */
    private function forgetPackageMigrations(): void
    {
        if (! Schema::hasTable('migrations')) {
            return;
        }

        $names = collect(File::files(__DIR__.'/../../database/migrations'))
            ->map(fn ($file) => str_replace('.php', '', $file->getFilename()))
            ->all();

        if ($names) {
            DB::table('migrations')->whereIn('migration', $names)->delete();
        }
    }

    /**
     * Resolve the final list of country codes to seed.
     *
     * Returns:
     *   []        — seed all countries
     *   ['JO',…]  — seed the listed subset
     *   null      — user cancelled / nothing chosen
     */
    private function resolveCountryCodes(?string $codesOption, bool $seedAll, ?string $sourceOption): ?array
    {
        // --all flag or no filtering needed
        if ($seedAll) {
            return [];
        }

        // --countries=JO,SA,AE passed on the CLI (non-interactive)
        if ($codesOption !== null) {
            $codes = array_filter(array_map('trim', explode(',', $codesOption)));

            return array_values(array_map('strtoupper', $codes));
        }

        if ($sourceOption !== null) {
            return $this->loadCountryCodesFromSource($sourceOption);
        }

        // Interactive multiselect
        $available = $this->loadCountryChoices();

        if (empty($available)) {
            $this->components->warn('Could not load countries.json — seeding all countries.');

            return [];
        }

        $this->newLine();
        $this->line('  <fg=yellow>Select the countries you want to seed.</>');
        $this->line('  <fg=gray>Press <space> to select, <enter> to confirm. Choose "All" to seed everything.</>');
        $this->newLine();

        // Build choice list: "All countries" first, then each country
        $allLabel = 'All countries ('.count($available).')';
        $choices = array_merge([$allLabel], array_values($available));

        $selected = multiselect(
            label  : 'Countries to seed',
            options: $choices,
            hint   : 'Space to toggle, Enter to confirm',
        );

        if (empty($selected)) {
            return null;
        }

        // If "All countries" was chosen, return empty (= seed everything)
        if (in_array($allLabel, $selected, true)) {
            return [];
        }

        // Map display labels back to ISO codes
        $labelToCode = array_flip($available);

        return array_values(array_filter(
            array_map(fn ($label) => $labelToCode[$label] ?? null, $selected)
        ));
    }

    /**
     * @return string[]|null
     */
    private function loadCountryCodesFromSource(string $source): ?array
    {
        $source = strtolower(trim($source));
        $path = __DIR__.'/../../config/source/countries-'.$source.'.php';

        if (! File::exists($path)) {
            $this->components->error("Country source [{$source}] does not exist.");

            return null;
        }

        $countries = require $path;

        if (! is_array($countries)) {
            $this->components->error("Country source [{$source}] is invalid.");

            return null;
        }

        return array_values(array_unique(array_filter(array_map(
            fn (array $country): ?string => isset($country['code'])
                ? strtoupper((string) $country['code'])
                : null,
            $countries,
        ))));
    }

    /**
     * Load country choices from the JSON data file.
     *
     * Returns array keyed by ISO-2 code: ['JO' => 'Jordan (JO)', 'SA' => 'Saudi Arabia (SA)', …]
     */
    private function loadCountryChoices(): array
    {
        $path = __DIR__.'/../../data/countries.json';

        if (! file_exists($path)) {
            return [];
        }

        $countries = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($countries)) {
            return [];
        }

        $choices = [];

        foreach ($countries as $c) {
            $code = strtoupper($c['code']);
            $name = $c['names']['common']['en'] ?? $code;
            $choices[$code] = "{$name} ({$code})";
        }

        asort($choices);

        return $choices;
    }
}
