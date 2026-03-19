<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\Country;

class HasManyThroughTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    public function test_country_has_areas_through_cities(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $this->assertNotNull($jordan);
        $this->assertGreaterThan(0, $jordan->areas->count());
    }

    public function test_country_areas_are_area_models(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $jordan->areas->each(fn ($area) => $this->assertInstanceOf(Area::class, $area));
    }

    public function test_country_areas_belong_to_cities_in_that_country(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $cityIds = $jordan->cities()->pluck('id')->all();

        foreach ($jordan->areas as $area) {
            $this->assertContains($area->city_id, $cityIds);
        }
    }

    public function test_areas_are_accessible_via_relationship(): void
    {
        $saudi = Country::where('code', 'SA')->first();

        $this->assertGreaterThan(0, $saudi->areas->count());
    }

    public function test_country_with_no_areas_returns_empty_collection(): void
    {
        // Kuwait has cities but we seed no areas for it in data/areas.json
        $kuwait = Country::where('code', 'KW')->first();

        $this->assertNotNull($kuwait);
        $this->assertCount(0, $kuwait->areas);
    }

    public function test_areas_count_matches_sum_of_city_areas(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $sumViaCity = $jordan->cities->sum(fn ($city) => $city->areas->count());

        $this->assertSame($sumViaCity, $jordan->areas->count());
    }

    public function test_can_filter_areas_by_type_through_relationship(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $neighborhoods = $jordan->areas()->where('type', Area::TYPE_NEIGHBORHOOD)->get();
        $districts     = $jordan->areas()->where('type', Area::TYPE_DISTRICT)->get();

        // All returned areas should be of the requested type
        $neighborhoods->each(fn ($a) => $this->assertSame(Area::TYPE_NEIGHBORHOOD, $a->type));
        $districts->each(fn ($a) => $this->assertSame(Area::TYPE_DISTRICT, $a->type));
    }

    public function test_each_area_has_city_id(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        foreach ($jordan->areas as $area) {
            $this->assertNotNull($area->city_id);
        }
    }
}
