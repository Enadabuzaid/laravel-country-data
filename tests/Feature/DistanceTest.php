<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Facades\Geography;
use Enadstack\CountryData\Models\City;

class DistanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    // ── distanceBetween() ─────────────────────────────────────────────────────

    public function test_distance_between_two_countries_returns_float(): void
    {
        $distance = Geography::distanceBetween('JO', 'SA');

        $this->assertIsFloat($distance);
        $this->assertGreaterThan(0, $distance);
    }

    public function test_distance_between_same_country_is_zero_or_near_zero(): void
    {
        $distance = Geography::distanceBetween('JO', 'JO');

        $this->assertSame(0.0, $distance);
    }

    public function test_distance_between_is_symmetric(): void
    {
        $joSa = Geography::distanceBetween('JO', 'SA');
        $saJo = Geography::distanceBetween('SA', 'JO');

        $this->assertSame($joSa, $saJo);
    }

    public function test_distance_between_returns_null_for_unknown_country(): void
    {
        $this->assertNull(Geography::distanceBetween('XX', 'JO'));
        $this->assertNull(Geography::distanceBetween('JO', 'XX'));
    }

    public function test_distance_between_jo_and_ae_is_reasonable(): void
    {
        // Jordan to UAE should be roughly 1500–2500 km
        $distance = Geography::distanceBetween('JO', 'AE');

        $this->assertNotNull($distance);
        $this->assertGreaterThan(1000, $distance);
        $this->assertLessThan(3000, $distance);
    }

    // ── citiesNear() ──────────────────────────────────────────────────────────

    public function test_cities_near_returns_collection(): void
    {
        // Amman coords
        $cities = Geography::citiesNear(31.9566, 35.9457, 100, 'JO');

        $this->assertGreaterThan(0, $cities->count());
    }

    public function test_cities_near_result_have_distance_attribute(): void
    {
        $cities = Geography::citiesNear(31.9566, 35.9457, 100, 'JO');

        foreach ($cities as $city) {
            $this->assertNotNull($city->distance);
            $this->assertIsFloat($city->distance);
        }
    }

    public function test_cities_near_are_sorted_by_distance_ascending(): void
    {
        $cities    = Geography::citiesNear(31.9566, 35.9457, 500, 'JO');
        $distances = $cities->pluck('distance')->all();

        $sorted = $distances;
        sort($sorted);

        $this->assertSame($sorted, $distances);
    }

    public function test_cities_near_respects_radius(): void
    {
        $within50  = Geography::citiesNear(31.9566, 35.9457, 50,  'JO');
        $within500 = Geography::citiesNear(31.9566, 35.9457, 500, 'JO');

        $this->assertLessThanOrEqual($within500->count(), $within50->count() + 1);

        foreach ($within50 as $city) {
            $this->assertLessThanOrEqual(50, $city->distance);
        }
    }

    public function test_cities_near_respects_country_scope(): void
    {
        // Centre of Saudi Arabia with large radius but scoped to JO
        $cities = Geography::citiesNear(24.0, 45.0, 5000, 'JO');

        foreach ($cities as $city) {
            $this->assertSame('JO', $city->country_code);
        }
    }

    public function test_cities_near_without_country_can_cross_borders(): void
    {
        // Amman area, no country filter — should find JO + maybe nearby countries
        $cities = Geography::citiesNear(31.9566, 35.9457, 200);

        $codes = $cities->pluck('country_code')->unique()->all();

        $this->assertContains('JO', $codes);
    }

    // ── sortCitiesByDistance() ────────────────────────────────────────────────

    public function test_sort_cities_by_distance_returns_all_cities_of_country(): void
    {
        $cities   = Geography::sortCitiesByDistance(31.9566, 35.9457, 'JO');
        $allCities = Geography::cities('JO');

        $this->assertCount($allCities->count(), $cities);
    }

    public function test_sort_cities_by_distance_are_sorted(): void
    {
        $cities    = Geography::sortCitiesByDistance(31.9566, 35.9457, 'JO');
        $distances = $cities->pluck('distance')->all();

        $sorted = $distances;
        sort($sorted);

        $this->assertSame($sorted, $distances);
    }

    public function test_sort_cities_by_distance_sets_distance_attribute(): void
    {
        $cities = Geography::sortCitiesByDistance(31.9566, 35.9457, 'JO');

        foreach ($cities as $city) {
            $this->assertNotNull($city->distance);
            $this->assertIsFloat($city->distance);
        }
    }

    public function test_closest_city_to_amman_coords_is_amman(): void
    {
        $cities = Geography::sortCitiesByDistance(31.9566, 35.9457, 'JO');
        $closest = $cities->first();

        $this->assertSame('Amman', $closest->name_en);
    }

    // ── areasNear() ───────────────────────────────────────────────────────────

    public function test_areas_near_returns_collection(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $this->assertNotNull($amman);

        $areas = Geography::areasNear(31.9566, 35.9457, $amman, 20);

        $this->assertGreaterThan(0, $areas->count());
    }

    public function test_areas_near_respect_radius(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $within5  = Geography::areasNear(31.9566, 35.9457, $amman, 5);
        $within50 = Geography::areasNear(31.9566, 35.9457, $amman, 50);

        $this->assertLessThanOrEqual($within50->count(), $within5->count() + 1);

        foreach ($within5 as $area) {
            $this->assertLessThanOrEqual(5, $area->distance);
        }
    }

    public function test_areas_near_have_distance_attribute(): void
    {
        $amman = City::where('name_en', 'Amman')->first();
        $areas = Geography::areasNear(31.9566, 35.9457, $amman, 50);

        foreach ($areas as $area) {
            $this->assertNotNull($area->distance);
            $this->assertIsFloat($area->distance);
        }
    }

    public function test_areas_near_sorted_by_distance(): void
    {
        $amman = City::where('name_en', 'Amman')->first();
        $areas = Geography::areasNear(31.9566, 35.9457, $amman, 50);

        $distances = $areas->pluck('distance')->all();
        $sorted    = $distances;
        sort($sorted);

        $this->assertSame($sorted, $distances);
    }

    public function test_areas_near_accepts_city_id(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $byModel = Geography::areasNear(31.9566, 35.9457, $amman, 50);
        $byId    = Geography::areasNear(31.9566, 35.9457, $amman->id, 50);

        $this->assertSame($byModel->count(), $byId->count());
    }
}
