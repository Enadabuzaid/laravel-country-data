<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class CapitalCityRelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    public function test_capital_city_is_a_has_one_relation(): void
    {
        $this->assertInstanceOf(HasOne::class, (new Country)->capitalCity());
    }

    public function test_relation_returns_the_capital(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $this->assertSame('Amman', $jordan->capitalCity->name_en);
    }

    public function test_snake_case_accessor_still_works(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $this->assertInstanceOf(City::class, $jordan->capital_city);
        $this->assertSame('Amman', $jordan->capital_city->name_en);
    }

    public function test_accessor_queries_only_once_per_model(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        DB::enableQueryLog();
        $jordan->capital_city;
        $jordan->capital_city;
        $jordan->capitalCity;

        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_eager_loading_capitals_runs_a_fixed_number_of_queries(): void
    {
        DB::enableQueryLog();

        $countries = Country::with('capitalCity')->get();

        foreach ($countries as $country) {
            $country->capital_city?->name_en;
        }

        // One query for countries, one for all their capitals — independent of the row count.
        $this->assertGreaterThan(1, $countries->count());
        $this->assertCount(2, DB::getQueryLog());
    }

    public function test_country_without_capital_returns_null(): void
    {
        City::where('country_code', 'JO')->update(['is_capital' => false]);

        $this->assertNull(Country::where('code', 'JO')->first()->capital_city);
    }

    public function test_country_does_not_cast_is_capital(): void
    {
        $this->assertArrayNotHasKey('is_capital', (new Country)->getCasts());
    }
}
