<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Enums\AreaType;
use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Exceptions\RegionNotFoundException;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Support\AreaCollection;
use Enadstack\CountryData\Support\Lookup;
use Enadstack\CountryData\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ScopesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    private function codes($query): array
    {
        return $query->orderBy('code')->pluck('code')->all();
    }

    // ── Country ─────────────────────────────────────────────────────────────

    public function test_in_region_accepts_the_enum_and_any_spelling(): void
    {
        $expected = $this->codes(Country::byFilter('north-america'));

        $this->assertNotEmpty($expected);
        $this->assertSame($expected, $this->codes(Country::inRegion(Region::NorthAmerica)));
        $this->assertSame($expected, $this->codes(Country::inRegion('north-america')));
        $this->assertSame($expected, $this->codes(Country::inRegion('northAmerica')));
    }

    public function test_in_region_rejects_unknown_names(): void
    {
        $this->expectException(RegionNotFoundException::class);

        Country::inRegion('narnia')->get();
    }

    public static function regions(): array
    {
        return array_combine(
            array_column(Region::cases(), 'value'),
            array_map(fn (Region $r) => [$r], Region::cases())
        );
    }

    #[DataProvider('regions')]
    public function test_every_region_has_a_named_scope(Region $region): void
    {
        $method = $region->method();
        $codes  = $this->codes(Country::{$method}());

        $this->assertNotEmpty($codes, "Country::{$method}()");
        $this->assertSame($this->codes(Country::byFilter($region->value)), $codes);
    }

    public function test_named_region_scopes(): void
    {
        $this->assertContains('DE', $this->codes(Country::europe()));
        $this->assertNotContains('JO', $this->codes(Country::europe()));
        $this->assertSame(['AE', 'BH', 'IQ', 'KW', 'OM', 'QA', 'SA'], $this->codes(Country::gulf()));
        $this->assertSame(['AE', 'BH', 'KW', 'OM', 'QA', 'SA'], $this->codes(Country::gcc()));
        $this->assertSame(['IL', 'JO', 'LB', 'PS', 'SY'], $this->codes(Country::levant()));
        $this->assertCount(22, Country::arab()->get());
        $this->assertCount(19, Country::g20()->get());
    }

    public function test_region_scopes_chain_with_other_scopes(): void
    {
        Country::where('code', 'DE')->update(['is_active' => false]);

        $this->assertNotContains('DE', $this->codes(Country::europe()->active()));
        $this->assertSame(['IL'], $this->codes(Country::levant()->inRegion(Region::MiddleEast)->where('code', 'IL')));
    }

    public function test_code_scope_accepts_iso2_iso3_and_the_enum_in_any_case(): void
    {
        foreach (['JO', 'jo', 'JOR', 'jor', CountryCode::JO] as $code) {
            $this->assertSame('JO', Country::code($code)->value('code'), is_string($code) ? $code : 'enum');
        }

        $this->assertNull(Country::code('XX')->first());
        $this->assertNull(Country::code('Jordan')->first());
    }

    public function test_search_scope_matches_english_and_arabic_names(): void
    {
        $this->assertContains('JO', $this->codes(Country::search('Jord')));
        $this->assertContains('JO', $this->codes(Country::search('الأردن')));
        $this->assertContains('JO', $this->codes(Country::search('Hashemite')));
        $this->assertContains('SA', $this->codes(Country::search('السعودية')));
        $this->assertContains('DE', $this->codes(Country::search('ألمانيا')));
        $this->assertSame([], $this->codes(Country::search('Atlantis')));
    }

    // ── Area ────────────────────────────────────────────────────────────────

    public function test_of_type_accepts_the_enum_or_a_string(): void
    {
        $this->assertSame(
            Area::ofType('neighborhood')->count(),
            Area::ofType(AreaType::Neighborhood)->count()
        );
        $this->assertGreaterThan(0, Area::ofType(AreaType::Street)->count());
    }

    public function test_neighborhoods_and_districts_scopes(): void
    {
        $this->assertSame(Area::ofType(AreaType::Neighborhood)->count(), Area::neighborhoods()->count());
        $this->assertSame(Area::ofType(AreaType::District)->count(), Area::districts()->count());
        $this->assertTrue(Area::neighborhoods()->get()->every(fn ($a) => $a->type === 'neighborhood'));
    }

    public function test_in_city_accepts_a_model_an_id_or_a_name(): void
    {
        $amman = City::where('country_code', 'JO')->where('name_en', 'Amman')->first();

        $this->assertSame(207, Area::inCity($amman)->count());
        $this->assertSame(207, Area::inCity($amman->id)->count());
        $this->assertSame(207, Area::inCity('Amman')->count());
        $this->assertSame(207, Area::inCity('Amman', 'JO')->count());
        $this->assertSame(207, Area::inCity('Amman', CountryCode::JO)->count());
        $this->assertSame(0, Area::inCity('Amman', 'SA')->count());
    }

    public function test_in_country_accepts_codes_the_enum_and_a_model(): void
    {
        $expected = Area::whereIn('city_id', City::where('country_code', 'JO')->pluck('id'))->count();

        $this->assertGreaterThanOrEqual(207, $expected);

        foreach (['JO', 'jo', 'JOR', CountryCode::JO, Country::where('code', 'JO')->first()] as $country) {
            $this->assertSame($expected, Area::inCountry($country)->count());
        }
    }

    public function test_area_queries_return_an_area_collection(): void
    {
        $this->assertInstanceOf(AreaCollection::class, Area::inCity('Amman', 'JO')->get());
        $this->assertInstanceOf(AreaCollection::class, City::where('name_en', 'Amman')->first()->areas);
    }

    public function test_every_generated_scope_name_is_a_lookup_method(): void
    {
        foreach (Region::cases() as $region) {
            $this->assertTrue(method_exists(Country::class, 'scope' . ucfirst(Lookup::method($region->value))));
        }
    }
}
