<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/../../data/countries.json';

        if (! file_exists($path)) {
            $this->command->error("countries.json not found at: {$path}");
            return;
        }

        $countries = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse countries.json: ' . json_last_error_msg());
            return;
        }

        $this->command->info('Seeding countries…');
        $bar = $this->command->getOutput()->createProgressBar(count($countries));
        $bar->start();

        foreach ($countries as $c) {
            DB::table('countries')->updateOrInsert(
                ['code' => $c['code']],
                [
                    'code'                => $c['code'],
                    'iso2'                => $c['iso2'],
                    'iso3'                => $c['iso3'],
                    'numeric_code'        => $c['numericCode'] ?? null,
                    'cioc'                => $c['cioc'] ?? null,
                    'name_en'             => $c['names']['common']['en'],
                    'name_ar'             => $c['names']['common']['ar'] ?? null,
                    'official_name_en'    => $c['names']['official']['en'] ?? null,
                    'official_name_ar'    => $c['names']['official']['ar'] ?? null,
                    'flag'                => $c['flag'] ?? null,
                    'emoji'               => $c['emoji'] ?? null,
                    'flag_png'            => $c['flags']['png'] ?? null,
                    'flag_svg'            => $c['flags']['svg'] ?? null,
                    'coat_of_arms_png'    => $c['coatOfArms']['png'] ?? null,
                    'coat_of_arms_svg'    => $c['coatOfArms']['svg'] ?? null,
                    'google_maps_url'     => $c['maps']['googleMaps'] ?? null,
                    'openstreet_maps_url' => $c['maps']['openStreetMaps'] ?? null,
                    'currency_code'       => $c['currency']['code'] ?? null,
                    'currency_name_en'    => $c['currency']['name']['en'] ?? null,
                    'currency_name_ar'    => $c['currency']['name']['ar'] ?? null,
                    'currency_symbol_en'  => $c['currency']['symbol']['en'] ?? null,
                    'currency_symbol_ar'  => $c['currency']['symbol']['ar'] ?? null,
                    'dial'                => $c['dial'] ?? null,
                    'capital'             => $c['capital'] ?? null,
                    'region'              => $c['region'] ?? null,
                    'continent'           => $c['continent'] ?? null,
                    'subregion'           => $c['subregion'] ?? null,
                    'languages'           => json_encode($c['languages'] ?? []),
                    'timezones'           => json_encode($c['timezones'] ?? []),
                    'tld'                 => json_encode($c['tld'] ?? []),
                    'borders'             => json_encode($c['borders'] ?? []),
                    'filters'             => json_encode($c['filters'] ?? []),
                    'latitude'            => $c['geo']['latitude'] ?? null,
                    'longitude'           => $c['geo']['longitude'] ?? null,
                    'population'          => $c['population'] ?? null,
                    'area'                => $c['area'] ?? null,
                    'is_active'           => true,
                    'updated_at'          => now(),
                    'created_at'          => now(),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Countries seeded: ' . count($countries));
    }
}
