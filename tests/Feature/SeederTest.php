<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\CountrySeeder;
use Enadstack\CountryData\Database\Seeders\CitySeeder;
use Enadstack\CountryData\Database\Seeders\AreaSeeder;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Illuminate\Support\Facades\DB;

class SeederTest extends TestCase
{
    // ── CountrySeeder ─────────────────────────────────────────────────────────

    public function test_country_seeder_inserts_all_22_arab_countries(): void
    {
        $this->seed(CountrySeeder::class);

        $this->assertDatabaseCount('countries', 22);
    }

    public function test_country_seeder_jordan_data_is_correct(): void
    {
        $this->seed(CountrySeeder::class);

        $jordan = DB::table('countries')->where('code', 'JO')->first();

        $this->assertNotNull($jordan);
        $this->assertSame('JO',      $jordan->iso2);
        $this->assertSame('JOR',     $jordan->iso3);
        $this->assertSame('Jordan',  $jordan->name_en);
        $this->assertSame('الأردن', $jordan->name_ar);
        $this->assertSame('+962',    $jordan->dial);
        $this->assertSame('Amman',   $jordan->capital);
        $this->assertSame('Asia',    $jordan->continent);
        $this->assertSame('JOD',     $jordan->currency_code);
        $this->assertSame('🇯🇴',    $jordan->flag);
        $this->assertTrue((bool) $jordan->is_active);
    }

    public function test_country_seeder_is_idempotent(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CountrySeeder::class); // run again

        $this->assertDatabaseCount('countries', 22); // no duplicates
    }

    // ── CitySeeder ────────────────────────────────────────────────────────────

    public function test_city_seeder_inserts_all_cities(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);

        $this->assertDatabaseCount('cities', 136);
    }

    public function test_city_seeder_jordan_has_12_cities(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);

        $count = DB::table('cities')->where('country_code', 'JO')->count();

        $this->assertSame(12, $count);
    }

    public function test_city_seeder_amman_is_capital(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);

        $amman = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->first();

        $this->assertNotNull($amman);
        $this->assertTrue((bool) $amman->is_capital);
    }

    public function test_city_seeder_other_jordan_cities_are_not_capital(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);

        $capitals = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('is_capital', true)
            ->count();

        $this->assertSame(1, $capitals);
    }

    public function test_city_has_foreign_key_to_country(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);

        $jordanId = DB::table('countries')->where('code', 'JO')->value('id');

        $amman = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->first();

        $this->assertSame((int) $jordanId, (int) $amman->country_id);
    }

    public function test_city_seeder_is_idempotent(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);
        $this->seed(CitySeeder::class); // run again

        $this->assertDatabaseCount('cities', 136);
    }

    // ── AreaSeeder ────────────────────────────────────────────────────────────

    public function test_area_seeder_inserts_all_areas(): void
    {
        $this->seed(GeographySeeder::class);

        $this->assertDatabaseCount('areas', 101);
    }

    public function test_area_seeder_amman_has_many_areas(): void
    {
        $this->seed(GeographySeeder::class);

        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        $count = DB::table('areas')->where('city_id', $ammanId)->count();

        $this->assertGreaterThanOrEqual(20, $count);
    }

    public function test_area_seeder_has_correct_types(): void
    {
        $this->seed(GeographySeeder::class);

        $validTypes = ['governorate', 'district', 'neighborhood', 'zone'];

        $invalidCount = DB::table('areas')
            ->whereNotIn('type', $validTypes)
            ->count();

        $this->assertSame(0, $invalidCount);
    }

    public function test_area_has_foreign_key_to_city(): void
    {
        $this->seed(GeographySeeder::class);

        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        $abdoun = DB::table('areas')
            ->where('city_id', $ammanId)
            ->where('name_en', 'Abdoun')
            ->first();

        $this->assertNotNull($abdoun);
        $this->assertSame((int) $ammanId, (int) $abdoun->city_id);
    }

    public function test_area_seeder_is_idempotent(): void
    {
        $this->seed(GeographySeeder::class);
        $this->seed(AreaSeeder::class); // run again

        $this->assertDatabaseCount('areas', 101);
    }
}
