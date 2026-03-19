<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/../../data/cities.json';

        if (! file_exists($path)) {
            $this->command->error("cities.json not found at: {$path}");
            return;
        }

        $cities = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse cities.json: ' . json_last_error_msg());
            return;
        }

        // Pre-load country id map  (code => id)
        $countryMap = DB::table('countries')
            ->pluck('id', 'code')
            ->toArray();

        $this->command->info('Seeding cities…');
        $bar = $this->command->getOutput()->createProgressBar(count($cities));
        $bar->start();

        $skipped = 0;

        foreach ($cities as $c) {
            $countryCode = strtoupper($c['country_code']);

            if (! isset($countryMap[$countryCode])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            DB::table('cities')->updateOrInsert(
                [
                    'country_code' => $countryCode,
                    'name_en'      => $c['name_en'],
                ],
                [
                    'country_id'  => $countryMap[$countryCode],
                    'country_code'=> $countryCode,
                    'name_en'     => $c['name_en'],
                    'name_ar'     => $c['name_ar'] ?? null,
                    'is_capital'  => (bool) ($c['is_capital'] ?? false),
                    'latitude'    => $c['latitude'] ?? null,
                    'longitude'   => $c['longitude'] ?? null,
                    'population'  => $c['population'] ?? null,
                    'timezone'    => $c['timezone'] ?? null,
                    'is_active'   => true,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Cities seeded: ' . (count($cities) - $skipped) . ($skipped ? " (skipped {$skipped} — country not found)" : ''));
    }
}
