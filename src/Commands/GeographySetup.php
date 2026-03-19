<?php

namespace Enadstack\CountryData\Commands;

use Illuminate\Console\Command;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;

class GeographySetup extends Command
{
    protected $signature = 'country-data:setup
                            {--migrate : Run migrations only}
                            {--seed    : Run seeders only}
                            {--fresh   : Drop and re-create all geography tables before seeding}';

    protected $description = 'Set up country-data geography tables: migrate + seed (countries, cities, areas)';

    public function handle(): int
    {
        $migrateOnly = $this->option('migrate');
        $seedOnly    = $this->option('seed');
        $fresh       = $this->option('fresh');

        $runMigrate = ! $seedOnly;
        $runSeed    = ! $migrateOnly;

        // ── Migrate ──────────────────────────────────────────────────────────
        if ($runMigrate) {
            $this->info('Running geography migrations…');

            if ($fresh) {
                if (! $this->confirm('--fresh will DROP the areas, cities and countries tables. Continue?')) {
                    $this->line('Aborted.');
                    return self::FAILURE;
                }

                foreach (['areas', 'cities', 'countries'] as $table) {
                    \Illuminate\Support\Facades\Schema::dropIfExists($table);
                }

                $this->warn('Dropped: areas, cities, countries');
            }

            $this->call('migrate', [
                '--path'  => 'vendor/enadstack/laravel-country-data/database/migrations',
                '--force' => true,
            ]);
        }

        // ── Seed ─────────────────────────────────────────────────────────────
        if ($runSeed) {
            $this->info('Seeding geography data…');

            $this->call('db:seed', [
                '--class' => GeographySeeder::class,
                '--force' => true,
            ]);
        }

        $this->info('Done! Geography tables are ready.');

        return self::SUCCESS;
    }
}
