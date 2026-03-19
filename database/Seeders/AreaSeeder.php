<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/../../data/areas.json';

        if (! file_exists($path)) {
            $this->command->error("areas.json not found at: {$path}");
            return;
        }

        $areas = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse areas.json: ' . json_last_error_msg());
            return;
        }

        // Pre-load city id map: "COUNTRY_CODE|city_name_en" => id
        $cityMap = DB::table('cities')
            ->get(['id', 'country_code', 'name_en'])
            ->mapWithKeys(fn ($row) => [
                strtoupper($row->country_code) . '|' . $row->name_en => $row->id,
            ])
            ->toArray();

        $this->command->info('Seeding areas…');
        $bar = $this->command->getOutput()->createProgressBar(count($areas));
        $bar->start();

        $skipped = 0;

        foreach ($areas as $a) {
            $key = strtoupper($a['country_code']) . '|' . $a['city_name_en'];

            if (! isset($cityMap[$key])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            DB::table('areas')->updateOrInsert(
                [
                    'city_id' => $cityMap[$key],
                    'name_en' => $a['name_en'],
                ],
                [
                    'city_id'    => $cityMap[$key],
                    'name_en'    => $a['name_en'],
                    'name_ar'    => $a['name_ar'] ?? null,
                    'type'       => $a['type'] ?? 'neighborhood',
                    'latitude'   => $a['latitude'] ?? null,
                    'longitude'  => $a['longitude'] ?? null,
                    'is_active'  => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Areas seeded: ' . (count($areas) - $skipped) . ($skipped ? " (skipped {$skipped} — city not found)" : ''));
    }
}
