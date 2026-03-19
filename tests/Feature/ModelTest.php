<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Area;

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    // ── Country model ─────────────────────────────────────────────────────────

    public function test_country_active_scope_returns_only_active(): void
    {
        Country::where('code', 'SA')->update(['is_active' => false]);

        $codes = Country::active()->pluck('code');

        $this->assertNotContains('SA', $codes);
        $this->assertContains('JO', $codes);
    }

    public function test_country_by_filter_scope(): void
    {
        $gulf = Country::active()->byFilter('gulf')->get();

        $this->assertNotEmpty($gulf);

        foreach ($gulf as $c) {
            $this->assertContains('gulf', $c->filters);
        }
    }

    public function test_country_has_many_cities(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $jordan->cities);
        $this->assertCount(12, $jordan->cities);
    }

    public function test_country_capital_city_accessor(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $capital = $jordan->capitalCity;

        $this->assertNotNull($capital);
        $this->assertSame('Amman', $capital->name_en);
        $this->assertTrue($capital->is_capital);
    }

    public function test_country_json_casts_are_arrays(): void
    {
        $jordan = Country::where('code', 'JO')->first();

        $this->assertIsArray($jordan->filters);
        $this->assertIsArray($jordan->languages);
        $this->assertIsArray($jordan->timezones);
        $this->assertIsArray($jordan->borders);
    }

    // ── City model ────────────────────────────────────────────────────────────

    public function test_city_belongs_to_country(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $this->assertInstanceOf(Country::class, $amman->country);
        $this->assertSame('JO', $amman->country->code);
    }

    public function test_city_has_many_areas(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $amman->areas);
        $this->assertGreaterThanOrEqual(20, $amman->areas->count());
    }

    public function test_city_by_country_scope(): void
    {
        $jordanCities = City::byCountry('JO')->get();

        $this->assertCount(12, $jordanCities);

        foreach ($jordanCities as $city) {
            $this->assertSame('JO', $city->country_code);
        }
    }

    public function test_city_capitals_scope(): void
    {
        $capitals = City::byCountry('JO')->capitals()->get();

        $this->assertCount(1, $capitals);
        $this->assertSame('Amman', $capitals->first()->name_en);
    }

    public function test_city_active_scope(): void
    {
        City::where('name_en', 'Irbid')->update(['is_active' => false]);

        $active = City::byCountry('JO')->active()->pluck('name_en');

        $this->assertNotContains('Irbid', $active);
        $this->assertContains('Amman', $active);
    }

    // ── Area model ────────────────────────────────────────────────────────────

    public function test_area_belongs_to_city(): void
    {
        $amman   = City::where('name_en', 'Amman')->first();
        $abdoun  = Area::where('city_id', $amman->id)->where('name_en', 'Abdoun')->first();

        $this->assertInstanceOf(City::class, $abdoun->city);
        $this->assertSame('Amman', $abdoun->city->name_en);
    }

    public function test_area_of_type_scope(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $neighborhoods = $amman->areas()->ofType('neighborhood')->get();

        $this->assertNotEmpty($neighborhoods);

        foreach ($neighborhoods as $area) {
            $this->assertSame('neighborhood', $area->type);
        }
    }

    public function test_area_type_constants_are_defined(): void
    {
        $this->assertSame('governorate',  Area::TYPE_GOVERNORATE);
        $this->assertSame('district',     Area::TYPE_DISTRICT);
        $this->assertSame('neighborhood', Area::TYPE_NEIGHBORHOOD);
        $this->assertSame('zone',         Area::TYPE_ZONE);
    }

    public function test_area_active_scope(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        Area::where('city_id', $amman->id)
            ->where('name_en', 'Abdoun')
            ->update(['is_active' => false]);

        $active = $amman->areas()->active()->pluck('name_en');

        $this->assertNotContains('Abdoun', $active);
    }
}
