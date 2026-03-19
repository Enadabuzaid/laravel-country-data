<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Enadstack\CountryData\Services\GeographyService;

/**
 * Master seeder — runs in order: Countries → Cities → Areas.
 *
 * Usage from your app:
 *   php artisan db:seed --class="Enadstack\CountryData\Database\Seeders\GeographySeeder"
 *
 * Or via the package artisan command:
 *   php artisan country-data:setup
 */
class GeographySeeder extends Seeder
{
    public function run(GeographyService $geography): void
    {
        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            AreaSeeder::class,
        ]);

        // Flush stale cache so fresh data is returned immediately
        $geography->flush();
    }
}
