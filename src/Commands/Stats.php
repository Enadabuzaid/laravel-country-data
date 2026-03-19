<?php

namespace Enadstack\CountryData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;

class Stats extends Command
{
    protected $signature   = 'country-data:stats';
    protected $description = 'Display seeded geography data statistics and cache status';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Geography Data Statistics');
        $this->newLine();

        // ── Seeded data ───────────────────────────────────────────────────────

        if (! Schema::hasTable('countries')) {
            $this->components->error('Tables not found. Run: php artisan country-data:setup --migrate --seed');
            return self::FAILURE;
        }

        $totalCountries  = Country::count();
        $activeCountries = Country::active()->count();
        $totalCities     = City::count();
        $activeCities    = City::active()->count();
        $capitalCities   = City::active()->where('is_capital', true)->count();
        $totalAreas      = Area::count();
        $activeAreas     = Area::active()->count();

        $this->components->twoColumnDetail('<fg=cyan>Countries (active / total)</>', "{$activeCountries} / {$totalCountries}");
        $this->components->twoColumnDetail('<fg=cyan>Cities (active / total)</>', "{$activeCities} / {$totalCities}");
        $this->components->twoColumnDetail('<fg=cyan>Capital cities</>', (string) $capitalCities);
        $this->components->twoColumnDetail('<fg=cyan>Areas (active / total)</>', "{$activeAreas} / {$totalAreas}");

        $this->newLine();

        // ── Breakdown by continent ─────────────────────────────────────────────

        $this->line('  <fg=yellow>Countries by continent:</>');

        $byContinent = Country::active()
            ->selectRaw('continent, COUNT(*) as total')
            ->groupBy('continent')
            ->orderBy('continent')
            ->pluck('total', 'continent');

        foreach ($byContinent as $continent => $count) {
            $this->components->twoColumnDetail("    {$continent}", (string) $count);
        }

        $this->newLine();

        // ── Areas by type ─────────────────────────────────────────────────────

        $this->line('  <fg=yellow>Areas by type:</>');

        $byType = Area::active()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderBy('type')
            ->pluck('total', 'type');

        if ($byType->isEmpty()) {
            $this->line('    <fg=gray>None seeded</>');
        } else {
            foreach ($byType as $type => $count) {
                $this->components->twoColumnDetail("    {$type}", (string) $count);
            }
        }

        $this->newLine();

        // ── Cities with most areas ─────────────────────────────────────────────

        $topCities = City::active()
            ->withCount(['areas' => fn ($q) => $q->where('is_active', true)])
            ->get(['name_en', 'country_code'])
            ->filter(fn ($city) => $city->areas_count > 0)
            ->sortByDesc('areas_count')
            ->take(5);

        if ($topCities->isNotEmpty()) {
            $this->line('  <fg=yellow>Top cities by area count:</>');
            foreach ($topCities as $city) {
                $this->components->twoColumnDetail(
                    "    {$city->name_en} ({$city->country_code})",
                    "{$city->areas_count} areas"
                );
            }
            $this->newLine();
        }

        // ── Cache status ──────────────────────────────────────────────────────

        $prefix  = config('country-data.cache.prefix', 'geography');
        $metaKey = "{$prefix}.__keys";
        $keys    = Cache::get($metaKey, []);
        $enabled = config('country-data.cache.enabled', true);
        $ttl     = config('country-data.cache.ttl', 86400);

        $this->line('  <fg=yellow>Cache:</>');
        $this->components->twoColumnDetail('    Enabled', $enabled ? '<fg=green>yes</>' : '<fg=red>no</>');
        $this->components->twoColumnDetail('    Driver', config('cache.default', 'file'));
        $this->components->twoColumnDetail('    TTL', "{$ttl}s (" . gmdate('H:i:s', $ttl) . ')');
        $this->components->twoColumnDetail('    Tracked keys', (string) count($keys));

        $this->newLine();

        return self::SUCCESS;
    }
}
