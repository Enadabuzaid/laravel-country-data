<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Facades\Geography;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Area;

class GeographyFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    // ── countries() ───────────────────────────────────────────────────────────

    public function test_countries_returns_all_22(): void
    {
        $this->assertCount(22, Geography::countries());
    }

    public function test_countries_with_filter_returns_subset(): void
    {
        $arab = Geography::countries('arab');
        $gulf = Geography::countries('gulf');

        $this->assertCount(22, $arab);
        $this->assertGreaterThan(0, $gulf->count());
        $this->assertLessThan(22, $gulf->count());
    }

    public function test_countries_returns_country_models(): void
    {
        $first = Geography::countries()->first();

        $this->assertInstanceOf(Country::class, $first);
    }

    // ── country() ────────────────────────────────────────────────────────────

    public function test_country_returns_correct_model(): void
    {
        $jordan = Geography::country('JO');

        $this->assertInstanceOf(Country::class, $jordan);
        $this->assertSame('JO', $jordan->code);
        $this->assertSame('Jordan', $jordan->name_en);
    }

    public function test_country_returns_null_for_unknown_code(): void
    {
        $this->assertNull(Geography::country('XX'));
    }

    public function test_country_is_case_insensitive(): void
    {
        $this->assertNotNull(Geography::country('jo'));
        $this->assertNotNull(Geography::country('JO'));
    }

    // ── capital() ────────────────────────────────────────────────────────────

    public function test_capital_returns_correct_city(): void
    {
        $capital = Geography::capital('JO');

        $this->assertInstanceOf(City::class, $capital);
        $this->assertSame('Amman', $capital->name_en);
        $this->assertTrue($capital->is_capital);
    }

    public function test_capital_returns_null_for_unknown_country(): void
    {
        $this->assertNull(Geography::capital('XX'));
    }

    // ── cities() ─────────────────────────────────────────────────────────────

    public function test_cities_returns_all_cities_for_country(): void
    {
        $cities = Geography::cities('JO');

        $this->assertCount(12, $cities);
    }

    public function test_cities_returns_city_models(): void
    {
        $first = Geography::cities('JO')->first();

        $this->assertInstanceOf(City::class, $first);
    }

    public function test_cities_are_sorted_alphabetically(): void
    {
        $names = Geography::cities('JO')->pluck('name_en')->toArray();
        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    public function test_cities_returns_empty_for_unknown_country(): void
    {
        $this->assertCount(0, Geography::cities('XX'));
    }

    // ── city() ───────────────────────────────────────────────────────────────

    public function test_city_returns_correct_model(): void
    {
        $amman = Geography::city('JO', 'Amman');

        $this->assertInstanceOf(City::class, $amman);
        $this->assertSame('JO', $amman->country_code);
        $this->assertSame('Amman', $amman->name_en);
    }

    public function test_city_returns_null_for_unknown(): void
    {
        $this->assertNull(Geography::city('JO', 'NonExistentCity'));
    }

    // ── areas() ──────────────────────────────────────────────────────────────

    public function test_areas_returns_areas_for_city(): void
    {
        $amman = Geography::city('JO', 'Amman');

        $areas = Geography::areas($amman);

        $this->assertNotEmpty($areas);
        $this->assertInstanceOf(Area::class, $areas->first());
    }

    public function test_areas_accepts_city_id(): void
    {
        $ammanId = City::where('name_en', 'Amman')->value('id');

        $areas = Geography::areas($ammanId);

        $this->assertNotEmpty($areas);
    }

    public function test_areas_filtered_by_type(): void
    {
        $amman = Geography::city('JO', 'Amman');

        $neighborhoods = Geography::areas($amman, 'neighborhood');

        foreach ($neighborhoods as $area) {
            $this->assertSame('neighborhood', $area->type);
        }
    }

    // ── areasByType() ─────────────────────────────────────────────────────────

    public function test_areas_by_type_groups_correctly(): void
    {
        $amman  = Geography::city('JO', 'Amman');
        $groups = Geography::areasByType($amman);

        $this->assertIsIterable($groups);

        foreach ($groups as $type => $items) {
            foreach ($items as $item) {
                $this->assertSame($type, $item->type);
            }
        }
    }

    // ── searchCities() ────────────────────────────────────────────────────────

    public function test_search_cities_by_partial_name(): void
    {
        $results = Geography::searchCities('Amm');

        $this->assertNotEmpty($results);
        $this->assertTrue(
            $results->contains(fn ($c) => $c->name_en === 'Amman')
        );
    }

    public function test_search_cities_scoped_to_country(): void
    {
        $results = Geography::searchCities('Amm', 'JO');

        foreach ($results as $city) {
            $this->assertSame('JO', $city->country_code);
        }
    }

    public function test_search_cities_by_arabic_name(): void
    {
        $results = Geography::searchCities('عمّ');

        $this->assertNotEmpty($results);
    }

    public function test_search_cities_returns_empty_for_no_match(): void
    {
        $this->assertCount(0, Geography::searchCities('ZZZNoMatch'));
    }

    // ── searchAreas() ─────────────────────────────────────────────────────────

    public function test_search_areas_by_partial_name(): void
    {
        $amman   = Geography::city('JO', 'Amman');
        $results = Geography::searchAreas('Abdo', $amman);

        $this->assertNotEmpty($results);
        $this->assertTrue(
            $results->contains(fn ($a) => $a->name_en === 'Abdoun')
        );
    }

    public function test_search_areas_by_arabic_name(): void
    {
        $amman   = Geography::city('JO', 'Amman');
        $results = Geography::searchAreas('عبدون', $amman);

        $this->assertNotEmpty($results);
    }

    // ── select helpers ────────────────────────────────────────────────────────

    public function test_countries_for_select_has_correct_keys(): void
    {
        $options = Geography::countriesForSelect();

        $first = $options->first();

        $this->assertArrayHasKey('value', $first);
        $this->assertArrayHasKey('label', $first);
        $this->assertArrayHasKey('flag', $first);
        $this->assertArrayHasKey('dial', $first);
    }

    public function test_countries_for_select_arabic_locale(): void
    {
        $options = Geography::countriesForSelect('ar')
            ->firstWhere('value', 'JO');

        $this->assertSame('الأردن', $options['label']);
    }

    public function test_cities_for_select_has_correct_keys(): void
    {
        $options = Geography::citiesForSelect('JO');

        $first = $options->first();

        $this->assertArrayHasKey('value', $first);
        $this->assertArrayHasKey('label', $first);
    }

    public function test_areas_for_select_has_correct_keys(): void
    {
        $amman   = Geography::city('JO', 'Amman');
        $options = Geography::areasForSelect($amman);

        $first = $options->first();

        $this->assertArrayHasKey('value', $first);
        $this->assertArrayHasKey('label', $first);
        $this->assertArrayHasKey('type', $first);
    }
}
