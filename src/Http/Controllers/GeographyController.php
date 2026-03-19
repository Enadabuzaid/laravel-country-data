<?php

namespace Enadstack\CountryData\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Services\GeographyService;

class GeographyController extends Controller
{
    public function __construct(private GeographyService $geo) {}

    // ── Countries ─────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/countries
     *
     * Query params:
     *   filter   – tag filter: arab | gulf | africa …
     *   locale   – en (default) | ar
     */
    public function countries(Request $request): JsonResponse
    {
        $locale    = $request->query('locale', 'en');
        $filter    = $request->query('filter');
        $countries = $this->geo->countries($filter);

        return response()->json([
            'data' => $countries->map(fn (Country $c) => $this->countryArray($c, $locale)),
            'meta' => ['total' => $countries->count()],
        ]);
    }

    /**
     * GET /api/geography/countries/{code}
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function country(Request $request, string $code): JsonResponse
    {
        $country = $this->geo->country($code);

        if (! $country) {
            return $this->notFound('Country');
        }

        return response()->json([
            'data' => $this->countryArray($country, $request->query('locale', 'en')),
        ]);
    }

    /**
     * GET /api/geography/countries/{code}/cities
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function countryCities(Request $request, string $code): JsonResponse
    {
        if (! $this->geo->country($code)) {
            return $this->notFound('Country');
        }

        $locale = $request->query('locale', 'en');
        $cities = $this->geo->cities($code);

        return response()->json([
            'data' => $cities->map(fn (City $c) => $this->cityArray($c, $locale)),
            'meta' => ['total' => $cities->count(), 'country_code' => strtoupper($code)],
        ]);
    }

    // ── Cities ────────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/cities/{id}
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function city(Request $request, int $id): JsonResponse
    {
        $city = City::active()->find($id);

        if (! $city) {
            return $this->notFound('City');
        }

        return response()->json([
            'data' => $this->cityArray($city, $request->query('locale', 'en')),
        ]);
    }

    /**
     * GET /api/geography/cities/{id}/areas
     *
     * Query params:
     *   type   – governorate | district | neighborhood | zone
     *   locale – en (default) | ar
     */
    public function cityAreas(Request $request, int $id): JsonResponse
    {
        $city = City::active()->find($id);

        if (! $city) {
            return $this->notFound('City');
        }

        $type   = $request->query('type');
        $locale = $request->query('locale', 'en');
        $areas  = $this->geo->areas($city, $type);

        return response()->json([
            'data' => $areas->map(fn (Area $a) => $this->areaArray($a, $locale)),
            'meta' => [
                'total'   => $areas->count(),
                'city_id' => $id,
                'type'    => $type,
            ],
        ]);
    }

    // ── Currency ──────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/currencies
     *
     * Returns all unique currencies used across active countries.
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function currencies(Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'en');

        $currencies = $this->geo->countries()
            ->filter(fn (Country $c) => $c->currency_code)
            ->map(fn (Country $c) => $this->geo->currencyOf($c->code, $locale)?->toArray())
            ->filter()
            ->keyBy('code')
            ->values();

        return response()->json([
            'data' => $currencies,
            'meta' => ['total' => $currencies->count()],
        ]);
    }

    /**
     * GET /api/geography/currencies/{code}/countries
     *
     * Returns all countries that use the given currency code.
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function currencyCountries(Request $request, string $code): JsonResponse
    {
        $locale    = $request->query('locale', 'en');
        $countries = $this->geo->countriesByCurrency($code);

        return response()->json([
            'data' => $countries->map(fn (Country $c) => $this->countryArray($c, $locale)),
            'meta' => ['total' => $countries->count(), 'currency_code' => strtoupper($code)],
        ]);
    }

    // ── Continents ────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/continents
     *
     * Returns a list of all distinct continents.
     */
    public function continents(): JsonResponse
    {
        $continents = $this->geo->continents();

        return response()->json([
            'data' => $continents->values(),
            'meta' => ['total' => $continents->count()],
        ]);
    }

    /**
     * GET /api/geography/continents/{continent}/countries
     *
     * Returns all countries on the given continent.
     *
     * Query params:
     *   locale – en (default) | ar
     */
    public function continentCountries(Request $request, string $continent): JsonResponse
    {
        $locale    = $request->query('locale', 'en');
        $countries = $this->geo->countriesByContinent($continent);

        return response()->json([
            'data' => $countries->map(fn (Country $c) => $this->countryArray($c, $locale)),
            'meta' => ['total' => $countries->count(), 'continent' => $continent],
        ]);
    }

    // ── Geospatial ────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/near/cities
     *
     * Query params (all required except country):
     *   lat     – latitude  (float)
     *   lng     – longitude (float)
     *   radius  – radius in km, default 100
     *   country – optional ISO-2 country code to scope results
     *   locale  – en (default) | ar
     *
     * Example: /api/geography/near/cities?lat=31.9566&lng=35.9457&radius=50&country=JO
     */
    public function citiesNear(Request $request): JsonResponse
    {
        $request->validate([
            'lat'    => ['required', 'numeric', 'between:-90,90'],
            'lng'    => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:20000'],
        ]);

        $lat     = (float) $request->query('lat');
        $lng     = (float) $request->query('lng');
        $radius  = (float) ($request->query('radius', 100));
        $country = $request->query('country');
        $locale  = $request->query('locale', 'en');

        $cities = $this->geo->citiesNear($lat, $lng, $radius, $country);

        return response()->json([
            'data' => $cities->map(fn (City $c) => $this->cityArray($c, $locale)),
            'meta' => [
                'total'    => $cities->count(),
                'lat'      => $lat,
                'lng'      => $lng,
                'radius_km'=> $radius,
                'country'  => $country ? strtoupper($country) : null,
            ],
        ]);
    }

    // ── Search ────────────────────────────────────────────────────────────────

    /**
     * GET /api/geography/search/cities
     *
     * Query params:
     *   q       – search term (min 2 chars)
     *   country – optional ISO-2 country code
     *   locale  – en (default) | ar
     */
    public function searchCities(Request $request): JsonResponse
    {
        $q = (string) $request->query('q', '');

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => [], 'meta' => ['total' => 0, 'q' => $q]]);
        }

        $locale  = $request->query('locale', 'en');
        $country = $request->query('country');
        $results = $this->geo->searchCities($q, $country);

        return response()->json([
            'data' => $results->map(fn (City $c) => $this->cityArray($c, $locale)),
            'meta' => ['total' => $results->count(), 'q' => $q],
        ]);
    }

    /**
     * GET /api/geography/search/areas
     *
     * Query params:
     *   q       – search term (min 2 chars)
     *   city_id – city id (required)
     *   locale  – en (default) | ar
     */
    public function searchAreas(Request $request): JsonResponse
    {
        $q      = (string) $request->query('q', '');
        $cityId = (int) $request->query('city_id', 0);

        if (! $cityId) {
            return response()->json(['message' => 'The city_id parameter is required.'], 422);
        }

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => [], 'meta' => ['total' => 0, 'q' => $q]]);
        }

        $city = City::active()->find($cityId);

        if (! $city) {
            return $this->notFound('City');
        }

        $locale  = $request->query('locale', 'en');
        $results = $this->geo->searchAreas($q, $city);

        return response()->json([
            'data' => $results->map(fn (Area $a) => $this->areaArray($a, $locale)),
            'meta' => ['total' => $results->count(), 'q' => $q, 'city_id' => $cityId],
        ]);
    }

    // ── Private formatters ────────────────────────────────────────────────────

    private function countryArray(Country $c, string $locale): array
    {
        return [
            'code'         => $c->code,
            'name'         => $locale === 'ar' ? ($c->name_ar ?? $c->name_en) : $c->name_en,
            'name_en'      => $c->name_en,
            'name_ar'      => $c->name_ar,
            'flag'         => $c->flag,
            'dial'         => $c->dial,
            'capital'      => $c->capital,
            'region'       => $c->region,
            'continent'    => $c->continent,
            'subregion'    => $c->subregion,
            'currency'     => [
                'code'   => $c->currency_code,
                'name'   => $locale === 'ar' ? ($c->currency_name_ar ?? $c->currency_name_en) : $c->currency_name_en,
                'symbol' => $locale === 'ar' ? ($c->currency_symbol_ar ?? $c->currency_symbol_en) : $c->currency_symbol_en,
            ],
            'geo'          => ['latitude' => $c->latitude, 'longitude' => $c->longitude],
            'population'   => $c->population,
            'area_km2'     => $c->area,
            'filters'      => $c->filters,
            'timezones'    => $c->timezones,
        ];
    }

    private function cityArray(City $c, string $locale): array
    {
        return [
            'id'           => $c->id,
            'country_code' => $c->country_code,
            'name'         => $locale === 'ar' ? ($c->name_ar ?? $c->name_en) : $c->name_en,
            'name_en'      => $c->name_en,
            'name_ar'      => $c->name_ar,
            'is_capital'   => $c->is_capital,
            'geo'          => ['latitude' => $c->latitude, 'longitude' => $c->longitude],
            'population'   => $c->population,
            'timezone'     => $c->timezone,
            'distance_km'  => isset($c->distance) ? $c->distance : null,
        ];
    }

    private function areaArray(Area $a, string $locale): array
    {
        return [
            'id'          => $a->id,
            'city_id'     => $a->city_id,
            'name'        => $locale === 'ar' ? ($a->name_ar ?? $a->name_en) : $a->name_en,
            'name_en'     => $a->name_en,
            'name_ar'     => $a->name_ar,
            'type'        => $a->type,
            'geo'         => ['latitude' => $a->latitude, 'longitude' => $a->longitude],
            'distance_km' => isset($a->distance) ? $a->distance : null,
        ];
    }

    private function notFound(string $resource): JsonResponse
    {
        return response()->json(['message' => "{$resource} not found."], 404);
    }
}
