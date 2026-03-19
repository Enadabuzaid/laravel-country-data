<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Data\CurrencyData;
use Enadstack\CountryData\Facades\Geography;
use Enadstack\CountryData\Models\City;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CurrencyTimezoneTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('country-data.api.enabled', true);
        $app['config']->set('country-data.api.prefix', 'api/geography');
        $app['config']->set('country-data.api.middleware', []);
    }

    // ── currencyOf() ──────────────────────────────────────────────────────────

    public function test_currency_of_returns_currency_data_instance(): void
    {
        $currency = Geography::currencyOf('JO');

        $this->assertInstanceOf(CurrencyData::class, $currency);
    }

    public function test_currency_of_jordan_has_correct_code(): void
    {
        $currency = Geography::currencyOf('JO');

        $this->assertSame('JOD', $currency->code);
    }

    public function test_currency_of_returns_english_name_by_default(): void
    {
        $currency = Geography::currencyOf('JO');

        $this->assertSame('Jordanian Dinar', $currency->nameEn);
        $this->assertSame('Jordanian Dinar', $currency->name());
    }

    public function test_currency_of_returns_arabic_name_with_ar_locale(): void
    {
        $currency = Geography::currencyOf('JO', 'ar');

        $this->assertNotEmpty($currency->nameAr);
        $this->assertSame($currency->nameAr, $currency->name());
    }

    public function test_currency_of_returns_locale_aware_symbol(): void
    {
        $en = Geography::currencyOf('JO', 'en');
        $ar = Geography::currencyOf('JO', 'ar');

        $this->assertSame($en->symbolEn, $en->symbol());
        $this->assertSame($ar->symbolAr, $ar->symbol());
    }

    public function test_currency_in_switches_locale(): void
    {
        $en = Geography::currencyOf('JO', 'en');
        $ar = $en->in('ar');

        $this->assertSame($en->nameEn, $en->name());
        $this->assertSame($en->nameAr, $ar->name());
        // original is unchanged
        $this->assertSame($en->nameEn, $en->name());
    }

    public function test_currency_to_array_has_all_keys(): void
    {
        $arr = Geography::currencyOf('JO')->toArray();

        $this->assertArrayHasKey('code', $arr);
        $this->assertArrayHasKey('name', $arr);
        $this->assertArrayHasKey('name_en', $arr);
        $this->assertArrayHasKey('name_ar', $arr);
        $this->assertArrayHasKey('symbol', $arr);
        $this->assertArrayHasKey('symbol_en', $arr);
        $this->assertArrayHasKey('symbol_ar', $arr);
    }

    public function test_currency_of_returns_null_for_unknown_country(): void
    {
        $this->assertNull(Geography::currencyOf('XX'));
    }

    // ── countriesByCurrency() ─────────────────────────────────────────────────

    public function test_countries_by_currency_returns_collection(): void
    {
        $countries = Geography::countriesByCurrency('SAR');

        $this->assertGreaterThan(0, $countries->count());
        $countries->each(fn ($c) => $this->assertSame('SAR', $c->currency_code));
    }

    public function test_countries_by_currency_returns_empty_for_unknown_code(): void
    {
        $this->assertCount(0, Geography::countriesByCurrency('XYZ'));
    }

    public function test_countries_by_currency_is_case_insensitive(): void
    {
        $upper = Geography::countriesByCurrency('SAR');
        $lower = Geography::countriesByCurrency('sar');

        // cache keys differ but same DB query result
        $this->assertSame($upper->pluck('code')->sort()->values()->all(),
                          $lower->pluck('code')->sort()->values()->all());
    }

    // ── timezonesOf() ─────────────────────────────────────────────────────────

    public function test_timezones_of_jordan_returns_asia_amman(): void
    {
        $tz = Geography::timezonesOf('JO');

        $this->assertIsArray($tz);
        $this->assertContains('Asia/Amman', $tz);
    }

    public function test_timezones_of_returns_empty_array_for_unknown_country(): void
    {
        $this->assertSame([], Geography::timezonesOf('XX'));
    }

    public function test_timezones_of_uae_returns_asia_dubai(): void
    {
        $tz = Geography::timezonesOf('AE');

        $this->assertContains('Asia/Dubai', $tz);
    }

    // ── timezoneForCity() ─────────────────────────────────────────────────────

    public function test_timezone_for_city_by_model(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $tz = Geography::timezoneForCity($amman);

        $this->assertSame('Asia/Amman', $tz);
    }

    public function test_timezone_for_city_by_id(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $tz = Geography::timezoneForCity($amman->id);

        $this->assertSame('Asia/Amman', $tz);
    }

    public function test_timezone_for_city_returns_null_for_unknown_id(): void
    {
        $this->assertNull(Geography::timezoneForCity(99999));
    }

    // ── dialCodeOf() ──────────────────────────────────────────────────────────

    public function test_dial_code_of_jordan_is_plus_962(): void
    {
        $this->assertSame('+962', Geography::dialCodeOf('JO'));
    }

    public function test_dial_code_of_returns_null_for_unknown_country(): void
    {
        $this->assertNull(Geography::dialCodeOf('XX'));
    }

    // ── countryByDialCode() ───────────────────────────────────────────────────

    public function test_country_by_dial_code_with_plus_prefix(): void
    {
        $country = Geography::countryByDialCode('+962');

        $this->assertNotNull($country);
        $this->assertSame('JO', $country->code);
    }

    public function test_country_by_dial_code_without_plus_prefix(): void
    {
        $country = Geography::countryByDialCode('962');

        $this->assertNotNull($country);
        $this->assertSame('JO', $country->code);
    }

    public function test_country_by_dial_code_returns_null_for_unknown(): void
    {
        $this->assertNull(Geography::countryByDialCode('+0'));
    }

    // ── continents() ──────────────────────────────────────────────────────────

    public function test_continents_returns_collection(): void
    {
        $continents = Geography::continents();

        $this->assertGreaterThan(0, $continents->count());
    }

    public function test_continents_contains_asia(): void
    {
        $this->assertContains('Asia', Geography::continents()->all());
    }

    public function test_continents_contains_africa(): void
    {
        $this->assertContains('Africa', Geography::continents()->all());
    }

    public function test_continents_are_unique(): void
    {
        $continents = Geography::continents();

        $this->assertSame($continents->count(), $continents->unique()->count());
    }

    // ── countriesByContinent() ────────────────────────────────────────────────

    public function test_countries_by_continent_returns_only_that_continent(): void
    {
        $countries = Geography::countriesByContinent('Asia');

        $this->assertGreaterThan(0, $countries->count());
        $countries->each(fn ($c) => $this->assertSame('Asia', $c->continent));
    }

    public function test_countries_by_continent_returns_empty_for_unknown(): void
    {
        $this->assertCount(0, Geography::countriesByContinent('Atlantis'));
    }

    // ── groupedByContinent() ──────────────────────────────────────────────────

    public function test_grouped_by_continent_is_keyed_by_continent(): void
    {
        $grouped = Geography::groupedByContinent();

        $this->assertArrayHasKey('Asia', $grouped->toArray());
    }

    public function test_grouped_by_continent_each_group_shares_continent(): void
    {
        $grouped = Geography::groupedByContinent();

        foreach ($grouped as $continent => $countries) {
            $countries->each(fn ($c) => $this->assertSame($continent, $c->continent));
        }
    }

    // ── API: GET /api/geography/currencies ────────────────────────────────────

    public function test_currencies_api_returns_200(): void
    {
        $response = $this->getJson('/api/geography/currencies');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total']]);
    }

    public function test_currencies_api_items_have_required_keys(): void
    {
        $response = $this->getJson('/api/geography/currencies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['*' => ['code', 'name', 'name_en', 'symbol', 'symbol_en']],
            ]);
    }

    public function test_currencies_api_are_unique_by_code(): void
    {
        $response = $this->getJson('/api/geography/currencies');
        $codes    = collect($response->json('data'))->pluck('code');

        $this->assertSame($codes->count(), $codes->unique()->count());
    }

    // ── API: GET /api/geography/currencies/{code}/countries ───────────────────

    public function test_currency_countries_api_returns_countries(): void
    {
        $response = $this->getJson('/api/geography/currencies/SAR/countries');

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_currency_countries_api_returns_empty_for_unknown(): void
    {
        $response = $this->getJson('/api/geography/currencies/XYZ/countries');

        $response->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    // ── API: GET /api/geography/continents ────────────────────────────────────

    public function test_continents_api_returns_200(): void
    {
        $response = $this->getJson('/api/geography/continents');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total']]);
    }

    public function test_continents_api_contains_asia(): void
    {
        $response = $this->getJson('/api/geography/continents');

        $this->assertContains('Asia', $response->json('data'));
    }

    // ── API: GET /api/geography/continents/{name}/countries ───────────────────

    public function test_continent_countries_api_returns_countries(): void
    {
        $response = $this->getJson('/api/geography/continents/Asia/countries');

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_continent_countries_api_returns_empty_for_unknown(): void
    {
        $response = $this->getJson('/api/geography/continents/Atlantis/countries');

        $response->assertOk()
            ->assertJsonPath('meta.total', 0);
    }
}
