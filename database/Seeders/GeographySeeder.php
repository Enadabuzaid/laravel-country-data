<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Enadstack\CountryData\Services\GeographyService;

/**
 * Master seeder — runs in order: Countries → Cities → Areas.
 *
 * Accepts an optional country-code filter:
 *   $seeder = new GeographySeeder();
 *   $seeder->countryCodes = ['JO', 'SA'];
 *   $seeder->setCommand($command)->run($geography);
 *
 * Via artisan (all countries):
 *   php artisan db:seed --class="Enadstack\CountryData\Database\Seeders\GeographySeeder"
 *
 * Via the package command (interactive, with country selection):
 *   php artisan country-data:setup
 */
class GeographySeeder extends Seeder
{
    /**
     * Optional country code filter (ISO-2 uppercase).
     * Empty array = seed all countries.
     *
     * @var string[]
     */
    public array $countryCodes = [];

    public function run(GeographyService $geography): void
    {
        // Run child seeders directly so we can pass the filter
        foreach ([CountrySeeder::class, CitySeeder::class, AreaSeeder::class] as $class) {
            /** @var CountrySeeder|CitySeeder|AreaSeeder $seeder */
            $seeder = new $class();
            $seeder->countryCodes = $this->countryCodes;
            $seeder->setCommand($this->command);
            app()->call([$seeder, 'run']);
        }

        // Flush stale cache so fresh data is returned immediately
        $geography->flush();
    }
}
