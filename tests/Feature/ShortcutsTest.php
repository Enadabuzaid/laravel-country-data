<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Enums\AreaType;
use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Exceptions\CityNotFoundException;
use Enadstack\CountryData\Exceptions\CountryNotFoundException;
use Enadstack\CountryData\Exceptions\RegionNotFoundException;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Services\GeographyService;
use Enadstack\CountryData\Shortcuts\Areas;
use Enadstack\CountryData\Shortcuts\Cities;
use Enadstack\CountryData\Shortcuts\Countries;
use Enadstack\CountryData\Support\AreaCollection;
use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShortcutsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
    }

    private function queries(callable $fn): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $fn();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /** @return string[] method names from a shortcut's generated @method tags */
    private function generatedMethods(string $class): array
    {
        preg_match_all('/@method static .*? (\w+)\(\)/', (new \ReflectionClass($class))->getDocComment(), $m);

        return $m[1];
    }

    // ── Countries ───────────────────────────────────────────────────────────

    public function test_countries_by_name(): void
    {
        $jordan = Countries::jordan();

        $this->assertInstanceOf(Country::class, $jordan);
        $this->assertSame('JO', $jordan->code);
        $this->assertSame('SA', Countries::saudiArabia()->code);
        $this->assertSame('US', Countries::unitedStates()->code);
        $this->assertSame('CI', Countries::ivoryCoast()->code);
        $this->assertSame('CI', Countries::of("Republic of Côte d'Ivoire")->code);
        $this->assertSame('GW', Countries::guineaBissau()->code);
    }

    public function test_countries_of_resolves_iso2_iso3_enum_names_and_case_variants(): void
    {
        foreach (['JO', 'jo', 'Jo', 'JOR', 'jor', CountryCode::JO, 'Jordan', 'jordan', 'Hashemite Kingdom of Jordan', 'الأردن'] as $needle) {
            $this->assertSame('JO', Countries::of($needle)->code, is_string($needle) ? $needle : 'enum');
        }

        $this->assertSame('DE', Countries::of('deu')->code);
        $this->assertSame('SA', Countries::of('saudi-arabia')->code);
        $this->assertSame('SA', Countries::of('SAUDI_ARABIA')->code);
        $this->assertSame('JO', Countries::JO()->code);
    }

    public function test_countries_by_region(): void
    {
        $europe = Countries::europe();

        $this->assertInstanceOf(Collection::class, $europe);
        $this->assertContains('DE', $europe->pluck('code'));
        $this->assertNotContains('JO', $europe->pluck('code'));
        $this->assertCount(7, Countries::gulf());
        $this->assertCount(6, Countries::gcc());
        $this->assertEquals(Countries::northAmerica()->pluck('code'), Countries::in(Region::NorthAmerica)->pluck('code'));
        $this->assertEquals(Countries::levant()->pluck('code'), Countries::in('levant')->pluck('code'));
    }

    public function test_countries_all(): void
    {
        $this->assertCount(250, Countries::all());
    }

    public function test_every_generated_countries_method_resolves(): void
    {
        $methods = $this->generatedMethods(Countries::class);

        $this->assertCount(250 + count(Region::cases()), $methods);

        foreach ($methods as $method) {
            $result = Countries::{$method}();
            $this->assertTrue($result instanceof Country || $result instanceof Collection, $method);

            if ($result instanceof Collection) {
                $this->assertNotEmpty($result, $method);
            }
        }
    }

    public function test_cities_and_areas_document_the_same_methods(): void
    {
        $this->assertSame($this->generatedMethods(Countries::class), $this->generatedMethods(Cities::class));
        $this->assertSame($this->generatedMethods(Countries::class), $this->generatedMethods(Areas::class));
    }

    public function test_unknown_country_throws_with_a_suggestion(): void
    {
        try {
            Countries::jordna();
            $this->fail('Expected CountryNotFoundException');
        } catch (CountryNotFoundException $e) {
            $this->assertSame('jordna', $e->name);
            $this->assertSame('Countries::jordan()', $e->suggestion);
            $this->assertStringContainsString('Did you mean Countries::jordan()?', $e->getMessage());
        }

        $this->expectException(CountryNotFoundException::class);
        Countries::of('XX');
    }

    public function test_misspelled_region_throws_region_not_found(): void
    {
        try {
            Countries::eurpoe();
            $this->fail('Expected RegionNotFoundException');
        } catch (RegionNotFoundException $e) {
            $this->assertSame('Countries::europe()', $e->suggestion);
        }
    }

    public function test_gibberish_throws_without_a_suggestion(): void
    {
        try {
            Countries::qwertyuiopasdf();
            $this->fail('Expected CountryNotFoundException');
        } catch (CountryNotFoundException $e) {
            $this->assertNull($e->suggestion);
        }
    }

    public function test_inactive_countries_are_not_resolved(): void
    {
        Country::where('code', 'JO')->update(['is_active' => false]);

        $this->expectException(CountryNotFoundException::class);
        Countries::jordan();
    }

    // ── Cities ──────────────────────────────────────────────────────────────

    public function test_cities_of_a_country(): void
    {
        $cities = Cities::jordan();

        $this->assertCount(12, $cities);
        $this->assertTrue($cities->every(fn ($c) => $c instanceof City && $c->country_code === 'JO'));
        $this->assertEquals($cities->pluck('id'), Cities::of('JOR')->pluck('id'));
        $this->assertEquals($cities->pluck('id'), Cities::of(CountryCode::JO)->pluck('id'));
    }

    public function test_capital_of(): void
    {
        $this->assertSame('Amman', Cities::capitalOf('JO')->name_en);
        $this->assertSame('Amman', Cities::capitalOf('jor')->name_en);
        $this->assertSame('Berlin', Cities::capitalOf(CountryCode::DE)->name_en);
        $this->assertSame('Tokyo', Cities::capitalOf('Japan')->name_en);
        $this->assertNull(Cities::capitalOf('AQ'));
    }

    public function test_city_by_name(): void
    {
        $this->assertSame('Irbid', Cities::named('JO', 'Irbid')->name_en);
        $this->assertSame('Irbid', Cities::named('JO', 'irbid')->name_en);
    }

    public function test_unknown_city_throws_with_a_suggestion(): void
    {
        try {
            Cities::named('JO', 'Irbd');
            $this->fail('Expected CityNotFoundException');
        } catch (CityNotFoundException $e) {
            $this->assertSame('Irbid', $e->suggestion);
        }
    }

    public function test_cities_of_a_region(): void
    {
        $codes = Cities::gulf()->pluck('country_code')->unique()->sort()->values()->all();

        $this->assertSame(['AE', 'BH', 'IQ', 'KW', 'OM', 'QA', 'SA'], $codes);
        $this->assertEquals(Cities::gulf()->pluck('id'), Cities::in(Region::Gulf)->pluck('id'));
    }

    public function test_unknown_country_for_cities_throws(): void
    {
        $this->expectException(CountryNotFoundException::class);
        Cities::narnia();
    }

    // ── Areas ───────────────────────────────────────────────────────────────

    public function test_areas_of_a_country(): void
    {
        $areas = Areas::jordan();

        $this->assertInstanceOf(AreaCollection::class, $areas);
        $this->assertSame(Area::inCountry('JO')->count(), $areas->count());
        $this->assertEquals($areas->pluck('id'), Areas::of('JOR')->pluck('id'));
        $this->assertEquals($areas->pluck('id'), Areas::in('JO')->pluck('id'));
    }

    public function test_areas_in_amman_are_the_207_existing_areas(): void
    {
        $areas = Areas::in('JO', 'Amman');

        $this->assertInstanceOf(AreaCollection::class, $areas);
        $this->assertCount(207, $areas);

        $json = array_filter(
            json_decode(file_get_contents(__DIR__ . '/../../data/areas.json'), true),
            fn ($a) => $a['country_code'] === 'JO' && $a['city_name_en'] === 'Amman'
        );
        $expected = array_column($json, 'name_en');
        sort($expected);
        $actual = $areas->pluck('name_en')->sort()->values()->all();

        $this->assertSame($expected, $actual);
    }

    public function test_amman_hierarchy_is_intact(): void
    {
        $areas = Areas::in('JO', 'Amman');
        $tree  = $areas->tree();

        $json = array_values(array_filter(
            json_decode(file_get_contents(__DIR__ . '/../../data/areas.json'), true),
            fn ($a) => $a['country_code'] === 'JO' && $a['city_name_en'] === 'Amman'
        ));

        // Same roots and the same parent → child links as the source data.
        $expectedRoots = array_column(array_filter($json, fn ($a) => empty($a['parent_en'])), 'name_en');
        sort($expectedRoots);
        $this->assertSame($expectedRoots, $tree->pluck('name_en')->sort()->values()->all());

        $expectedLinks = [];
        foreach ($json as $a) {
            if (! empty($a['parent_en'])) {
                $expectedLinks[] = "{$a['parent_en']} > {$a['name_en']}";
            }
        }
        sort($expectedLinks);

        $actualLinks = [];
        foreach ($tree as $root) {
            foreach ($root->children as $child) {
                $this->assertSame($root->id, $child->parent_id);
                $actualLinks[] = "{$root->name_en} > {$child->name_en}";
            }
        }
        sort($actualLinks);

        $this->assertSame($expectedLinks, $actualLinks);
        $this->assertSame(207, $tree->count() + count($actualLinks));
    }

    public function test_areas_in_tolerates_a_plain_collection_cached_by_v2(): void
    {
        $amman = Cities::named('JO', 'Amman');
        $key   = config('country-data.cache.prefix') . ".areas.{$amman->id}.";

        \Illuminate\Support\Facades\Cache::put($key, new \Illuminate\Database\Eloquent\Collection(Area::where('city_id', $amman->id)->get()->all()), 60);
        $this->app->forgetInstance(GeographyService::class);

        $areas = null;
        $this->assertSame(0, $this->queries(function () use (&$areas) {
            $areas = Areas::in('JO', 'Amman');   // served from the planted cache entry
        }));

        $this->assertInstanceOf(AreaCollection::class, $areas);
        $this->assertCount(207, $areas);
    }

    public function test_tree_does_not_modify_cached_models(): void
    {
        Areas::in('JO', 'Amman')->tree();

        $this->assertTrue(Areas::in('JO', 'Amman')->every(fn ($a) => ! $a->relationLoaded('children')));
    }

    public function test_fluent_area_filters(): void
    {
        $areas = Areas::in('JO', 'Amman');

        $this->assertSame($areas->where('type', 'neighborhood')->count(), $areas->neighborhoods()->count());
        $this->assertSame($areas->where('type', 'district')->count(), $areas->districts()->count());
        $this->assertSame($areas->where('type', 'street')->count(), $areas->ofType(AreaType::Street)->count());
        $this->assertGreaterThan(0, $areas->neighborhoods()->count());
        $this->assertTrue($areas->neighborhoods()->every(fn ($a) => $a->type === 'neighborhood'));
        $this->assertInstanceOf(AreaCollection::class, $areas->neighborhoods());
    }

    public function test_areas_of_a_region(): void
    {
        $this->assertSame(Area::inCountry('JO')->count() + Area::inCountry('SY')->count()
            + Area::inCountry('LB')->count() + Area::inCountry('PS')->count() + Area::inCountry('IL')->count(),
            Areas::levant()->count());
        $this->assertEquals(Areas::levant()->pluck('id'), Areas::inRegion('levant')->pluck('id'));
    }

    public function test_areas_in_unknown_city_throws(): void
    {
        $this->expectException(CityNotFoundException::class);
        Areas::in('JO', 'Atlantis');
    }

    // ── Caching ─────────────────────────────────────────────────────────────

    public function test_results_are_cached(): void
    {
        $calls = [
            fn () => Countries::jordan(),
            fn () => Countries::of('JOR'),
            fn () => Countries::europe(),
            fn () => Countries::all(),
            fn () => Cities::jordan(),
            fn () => Cities::capitalOf('JO'),
            fn () => Cities::gulf(),
            fn () => Areas::jordan(),
            fn () => Areas::in('JO', 'Amman'),
            fn () => Areas::levant(),
        ];

        foreach ($calls as $i => $call) {
            app(GeographyService::class)->flush();

            $this->assertGreaterThan(0, $this->queries($call), "call #{$i} first run");
            $this->assertSame(0, $this->queries($call), "call #{$i} second run");
        }
    }

    public function test_results_survive_a_new_service_instance_through_the_cache_store(): void
    {
        Countries::jordan();
        Areas::in('JO', 'Amman');

        // Drop the per-process memo; the cache store must still answer.
        $this->app->forgetInstance(GeographyService::class);

        $this->assertSame(0, $this->queries(fn () => Countries::jordan()));
        $this->assertSame(0, $this->queries(fn () => Areas::in('JO', 'Amman')->neighborhoods()));
    }

    // ── Locale ──────────────────────────────────────────────────────────────

    public function test_arabic_locale_returns_arabic_names(): void
    {
        $this->app->setLocale('ar');

        $this->assertSame('الأردن', Countries::jordan()->name);
        $this->assertSame('ألمانيا', Countries::of('DE')->name);
        $this->assertSame('الأردن', CountryCode::JO->name());
        $this->assertSame('عمّان', Cities::capitalOf('JO')->name);
        $this->assertSame('برلين', Cities::capitalOf('DE')->name);
        $this->assertMatchesRegularExpression('/\p{Arabic}/u', Areas::in('JO', 'Amman')->first()->name);

        $this->app->setLocale('en');

        $this->assertSame('Jordan', Countries::jordan()->name);
        $this->assertSame('Jordan', CountryCode::JO->name());
    }

    public function test_country_code_model(): void
    {
        $this->assertSame('JO', CountryCode::JO->model()->code);

        Country::where('code', 'JO')->update(['is_active' => false]);
        app(GeographyService::class)->flush();

        $this->assertNull(CountryCode::JO->model());
    }
}
