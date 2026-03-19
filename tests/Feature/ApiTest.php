<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\City;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ApiTest extends TestCase
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

    // ── GET /api/geography/countries ──────────────────────────────────────────

    public function test_countries_endpoint_returns_200(): void
    {
        $response = $this->getJson('/api/geography/countries');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total']]);
    }

    public function test_countries_endpoint_returns_22_countries(): void
    {
        $response = $this->getJson('/api/geography/countries');

        $response->assertOk()
            ->assertJsonPath('meta.total', 22);
    }

    public function test_countries_endpoint_supports_filter(): void
    {
        $response = $this->getJson('/api/geography/countries?filter=gulf');

        $response->assertOk();
        $total = $response->json('meta.total');
        $this->assertGreaterThan(0, $total);
        $this->assertLessThan(22, $total);
    }

    public function test_countries_endpoint_supports_locale_ar(): void
    {
        $response = $this->getJson('/api/geography/countries?locale=ar');

        $response->assertOk();
        // name field should be Arabic for AR locale
        $firstName = $response->json('data.0.name');
        $this->assertNotNull($firstName);
    }

    public function test_countries_endpoint_returns_country_structure(): void
    {
        $response = $this->getJson('/api/geography/countries');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['code', 'name', 'name_en', 'name_ar', 'flag', 'dial', 'currency', 'geo'],
                ],
            ]);
    }

    // ── GET /api/geography/countries/{code} ───────────────────────────────────

    public function test_country_endpoint_returns_jordan(): void
    {
        $response = $this->getJson('/api/geography/countries/JO');

        $response->assertOk()
            ->assertJsonPath('data.code', 'JO')
            ->assertJsonPath('data.name_en', 'Jordan');
    }

    public function test_country_endpoint_is_case_insensitive(): void
    {
        $response = $this->getJson('/api/geography/countries/jo');

        $response->assertOk()
            ->assertJsonPath('data.code', 'JO');
    }

    public function test_country_endpoint_returns_404_for_unknown_code(): void
    {
        $this->getJson('/api/geography/countries/XX')
            ->assertNotFound()
            ->assertJsonPath('message', 'Country not found.');
    }

    // ── GET /api/geography/countries/{code}/cities ────────────────────────────

    public function test_country_cities_endpoint_returns_jordan_cities(): void
    {
        $response = $this->getJson('/api/geography/countries/JO/cities');

        $response->assertOk()
            ->assertJsonPath('meta.country_code', 'JO');

        $total = $response->json('meta.total');
        $this->assertGreaterThan(0, $total);
    }

    public function test_country_cities_endpoint_returns_city_structure(): void
    {
        $response = $this->getJson('/api/geography/countries/JO/cities');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'country_code', 'name', 'name_en', 'is_capital', 'geo'],
                ],
            ]);
    }

    public function test_country_cities_endpoint_returns_404_for_unknown_country(): void
    {
        $this->getJson('/api/geography/countries/XX/cities')
            ->assertNotFound();
    }

    // ── GET /api/geography/cities/{id} ────────────────────────────────────────

    public function test_city_endpoint_returns_city(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/cities/{$amman->id}");

        $response->assertOk()
            ->assertJsonPath('data.name_en', 'Amman')
            ->assertJsonPath('data.country_code', 'JO');
    }

    public function test_city_endpoint_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/geography/cities/99999')
            ->assertNotFound()
            ->assertJsonPath('message', 'City not found.');
    }

    // ── GET /api/geography/cities/{id}/areas ──────────────────────────────────

    public function test_city_areas_endpoint_returns_areas(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/cities/{$amman->id}/areas");

        $response->assertOk()
            ->assertJsonPath('meta.city_id', $amman->id);

        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_city_areas_endpoint_supports_type_filter(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/cities/{$amman->id}/areas?type=neighborhood");

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $area) {
            $this->assertSame('neighborhood', $area['type']);
        }
    }

    public function test_city_areas_endpoint_returns_area_structure(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/cities/{$amman->id}/areas");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'city_id', 'name', 'name_en', 'type', 'geo'],
                ],
            ]);
    }

    public function test_city_areas_endpoint_returns_404_for_unknown_city(): void
    {
        $this->getJson('/api/geography/cities/99999/areas')
            ->assertNotFound();
    }

    // ── GET /api/geography/near/cities ────────────────────────────────────────

    public function test_cities_near_endpoint_returns_results(): void
    {
        $response = $this->getJson('/api/geography/near/cities?lat=31.9566&lng=35.9457&radius=100&country=JO');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total', 'lat', 'lng', 'radius_km']]);

        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_cities_near_endpoint_validates_lat_lng(): void
    {
        $this->getJson('/api/geography/near/cities?lat=999&lng=35.9457')
            ->assertUnprocessable();

        $this->getJson('/api/geography/near/cities?lat=31.9&lng=999')
            ->assertUnprocessable();
    }

    public function test_cities_near_endpoint_requires_lat_lng(): void
    {
        $this->getJson('/api/geography/near/cities')
            ->assertUnprocessable();
    }

    public function test_cities_near_results_have_distance(): void
    {
        $response = $this->getJson('/api/geography/near/cities?lat=31.9566&lng=35.9457&radius=200&country=JO');

        $response->assertOk();
        foreach ($response->json('data') as $city) {
            $this->assertArrayHasKey('distance_km', $city);
        }
    }

    // ── GET /api/geography/search/cities ──────────────────────────────────────

    public function test_search_cities_endpoint_finds_amman(): void
    {
        $response = $this->getJson('/api/geography/search/cities?q=Amman');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name_en')->all();
        $this->assertContains('Amman', $names);
    }

    public function test_search_cities_endpoint_returns_empty_for_short_query(): void
    {
        $response = $this->getJson('/api/geography/search/cities?q=A');

        $response->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('data', []);
    }

    public function test_search_cities_endpoint_supports_country_scope(): void
    {
        $response = $this->getJson('/api/geography/search/cities?q=a&country=JO');

        $response->assertOk();
        foreach ($response->json('data') as $city) {
            $this->assertSame('JO', $city['country_code']);
        }
    }

    // ── GET /api/geography/search/areas ───────────────────────────────────────

    public function test_search_areas_endpoint_requires_city_id(): void
    {
        $this->getJson('/api/geography/search/areas?q=down')
            ->assertUnprocessable();
    }

    public function test_search_areas_endpoint_returns_empty_for_short_query(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/search/areas?q=D&city_id={$amman->id}");

        $response->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_search_areas_endpoint_finds_areas(): void
    {
        $amman = City::where('name_en', 'Amman')->first();

        $response = $this->getJson("/api/geography/search/areas?q=down&city_id={$amman->id}");

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['total', 'q', 'city_id']]);
    }

    public function test_search_areas_endpoint_returns_404_for_invalid_city(): void
    {
        $this->getJson('/api/geography/search/areas?q=down&city_id=99999')
            ->assertNotFound();
    }
}
