<?php

namespace Enadstack\CountryData\Services;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Enadstack\CountryData\Data\CurrencyData;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Area;

/**
 * DB-based geography service.
 * Consumed via the Geography facade: Geography::countries('arab')
 *
 * All read methods are transparently cached.
 * Clear with: Geography::flush()  or  php artisan country-data:cache-clear
 */
class GeographyService
{
    // ── Countries ────────────────────────────────────────────────────────────

    /** All active countries, optionally filtered by tag (arab, gulf, etc.) */
    public function countries(?string $filter = null): Collection
    {
        return $this->remember("countries.{$filter}", fn () =>
            Country::active()
                ->when($filter, fn ($q) => $q->byFilter($filter))
                ->orderBy('name_en')
                ->get()
        );
    }

    /** Single country by ISO-2 code (JO, SA, AE…) */
    public function country(string $code): ?Country
    {
        $code = strtoupper($code);

        return $this->remember("country.{$code}", fn () =>
            Country::active()->where('code', $code)->first()
        );
    }

    /** All capital cities with their countries */
    public function capitals(): Collection
    {
        return $this->remember('capitals', fn () =>
            Country::active()
                ->whereHas('cities', fn ($q) => $q->where('is_capital', true))
                ->with(['cities' => fn ($q) => $q->where('is_capital', true)])
                ->get()
        );
    }

    // ── Cities ───────────────────────────────────────────────────────────────

    /** All active cities for a country code */
    public function cities(string $countryCode): Collection
    {
        $code = strtoupper($countryCode);

        return $this->remember("cities.{$code}", fn () =>
            City::active()->byCountry($code)->orderBy('name_en')->get()
        );
    }

    /** Single city by country code + English name */
    public function city(string $countryCode, string $nameEn): ?City
    {
        $code = strtoupper($countryCode);
        $key  = md5($code . $nameEn);

        return $this->remember("city.{$key}", fn () =>
            City::active()->byCountry($code)->where('name_en', $nameEn)->first()
        );
    }

    /** Capital city of a country */
    public function capital(string $countryCode): ?City
    {
        $code = strtoupper($countryCode);

        return $this->remember("capital.{$code}", fn () =>
            City::active()->byCountry($code)->capitals()->first()
        );
    }

    // ── Areas ────────────────────────────────────────────────────────────────

    /**
     * All active areas for a city.
     *
     * @param  City|int   $city  City model or city id
     * @param  string|null $type  governorate | district | neighborhood | zone
     */
    public function areas(City|int $city, ?string $type = null): Collection
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return $this->remember("areas.{$cityId}.{$type}", fn () =>
            Area::active()
                ->where('city_id', $cityId)
                ->when($type, fn ($q) => $q->ofType($type))
                ->orderBy('name_en')
                ->get()
        );
    }

    /** All areas for a city grouped by type */
    public function areasByType(City|int $city): Collection
    {
        return $this->areas($city)->groupBy('type');
    }

    // ── Search (not cached — dynamic input) ──────────────────────────────────

    /** Search cities by partial name (en or ar) within optional country */
    public function searchCities(string $query, ?string $countryCode = null): Collection
    {
        return City::active()
            ->where(fn ($q) => $q
                ->where('name_en', 'like', "%{$query}%")
                ->orWhere('name_ar', 'like', "%{$query}%")
            )
            ->when($countryCode, fn ($q) => $q->byCountry($countryCode))
            ->orderBy('name_en')
            ->get();
    }

    /** Search areas by partial name (en or ar) within a city */
    public function searchAreas(string $query, City|int $city): Collection
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return Area::active()
            ->where('city_id', $cityId)
            ->where(fn ($q) => $q
                ->where('name_en', 'like', "%{$query}%")
                ->orWhere('name_ar', 'like', "%{$query}%")
            )
            ->orderBy('name_en')
            ->get();
    }

    // ── Select helpers ────────────────────────────────────────────────────────

    /** Countries formatted for a select dropdown */
    public function countriesForSelect(string $locale = 'en', ?string $filter = null): Collection
    {
        return $this->remember("select.countries.{$locale}.{$filter}", fn () =>
            $this->countries($filter)->map(fn (Country $c) => [
                'value' => $c->code,
                'label' => $locale === 'ar' ? ($c->name_ar ?? $c->name_en) : $c->name_en,
                'flag'  => $c->flag,
                'dial'  => $c->dial,
            ])
        );
    }

    /** Cities formatted for a select dropdown */
    public function citiesForSelect(string $countryCode, string $locale = 'en'): Collection
    {
        $code = strtoupper($countryCode);

        return $this->remember("select.cities.{$code}.{$locale}", fn () =>
            $this->cities($code)->map(fn (City $c) => [
                'value' => $c->id,
                'label' => $locale === 'ar' ? ($c->name_ar ?? $c->name_en) : $c->name_en,
            ])
        );
    }

    /** Areas formatted for a select dropdown */
    public function areasForSelect(City|int $city, string $locale = 'en', ?string $type = null): Collection
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return $this->remember("select.areas.{$cityId}.{$locale}.{$type}", fn () =>
            $this->areas($cityId, $type)->map(fn (Area $a) => [
                'value' => $a->id,
                'label' => $locale === 'ar' ? ($a->name_ar ?? $a->name_en) : $a->name_en,
                'type'  => $a->type,
            ])
        );
    }

    // ── Currency ─────────────────────────────────────────────────────────────

    /**
     * Currency information for a country.
     *
     * @param  string $locale  'en' (default) or 'ar' — controls name() and symbol()
     */
    public function currencyOf(string $countryCode, string $locale = 'en'): ?CurrencyData
    {
        $country = $this->country($countryCode);

        if (! $country || ! $country->currency_code) {
            return null;
        }

        return new CurrencyData(
            code:      $country->currency_code,
            nameEn:    (string) $country->currency_name_en,
            nameAr:    (string) $country->currency_name_ar,
            symbolEn:  (string) $country->currency_symbol_en,
            symbolAr:  (string) $country->currency_symbol_ar,
            locale:    $locale,
        );
    }

    /**
     * All countries that share a given currency code (e.g. 'USD', 'EUR').
     */
    public function countriesByCurrency(string $currencyCode): Collection
    {
        $code = strtoupper($currencyCode);

        return $this->remember("currency.countries.{$code}", fn () =>
            Country::active()->where('currency_code', $code)->orderBy('name_en')->get()
        );
    }

    // ── Timezones ─────────────────────────────────────────────────────────────

    /**
     * All IANA timezone identifiers for a country (e.g. ['Asia/Amman']).
     * Returns an empty array when the country is not found.
     */
    public function timezonesOf(string $countryCode): array
    {
        $country = $this->country($countryCode);

        return $country ? (array) $country->timezones : [];
    }

    /**
     * Timezone of a specific city (stored per-row in the cities table).
     *
     * @param  City|int $city
     */
    public function timezoneForCity(City|int $city): ?string
    {
        if ($city instanceof City) {
            return $city->timezone ?: null;
        }

        return $this->remember("timezone.city.{$city}", fn () =>
            City::active()->where('id', $city)->value('timezone')
        );
    }

    // ── Dial codes ────────────────────────────────────────────────────────────

    /**
     * International dial code for a country (e.g. '+962' for Jordan).
     * Returns null when the country is not found.
     */
    public function dialCodeOf(string $countryCode): ?string
    {
        return $this->country($countryCode)?->dial;
    }

    /**
     * Find a country by its dial code (e.g. '+962' or '962').
     * Returns the first match when multiple countries share a prefix.
     */
    public function countryByDialCode(string $dialCode): ?Country
    {
        $normalized = ltrim($dialCode, '+');
        $withPlus   = "+{$normalized}";

        return $this->remember("dial.country.{$normalized}", fn () =>
            Country::active()
                ->where('dial', $withPlus)
                ->orWhere('dial', $normalized)
                ->first()
        );
    }

    // ── Continents ────────────────────────────────────────────────────────────

    /**
     * All distinct continent names present in the database.
     *
     * Example: collect(['Asia', 'Africa', 'Europe', …])
     */
    public function continents(): Collection
    {
        return $this->remember('continents', fn () =>
            Country::active()->distinct()->orderBy('continent')->pluck('continent')
        );
    }

    /**
     * All active countries on a given continent.
     */
    public function countriesByContinent(string $continent): Collection
    {
        return $this->remember("continent.{$continent}", fn () =>
            Country::active()
                ->where('continent', $continent)
                ->orderBy('name_en')
                ->get()
        );
    }

    /**
     * All active countries grouped by continent.
     *
     * Returns a Collection keyed by continent name, each value a Collection of Countries.
     */
    public function groupedByContinent(): Collection
    {
        return $this->remember('grouped.continent', fn () =>
            Country::active()->orderBy('continent')->orderBy('name_en')->get()->groupBy('continent')
        );
    }

    // ── Distance / Geospatial ────────────────────────────────────────────────

    /**
     * Straight-line distance in km between two countries' geographic centres.
     * Returns null if either country is missing or has no coordinates.
     */
    public function distanceBetween(string $code1, string $code2): ?float
    {
        $c1 = $this->country($code1);
        $c2 = $this->country($code2);

        if (! $c1 || ! $c2 || ! $c1->latitude || ! $c2->latitude) {
            return null;
        }

        return round($this->haversine($c1->latitude, $c1->longitude, $c2->latitude, $c2->longitude), 2);
    }

    /**
     * Cities within $radiusKm kilometres of a coordinate.
     * Optionally scoped to a single country.
     *
     * Each returned City model has an extra `distance` (float, km) attribute.
     *
     * Uses PHP-level Haversine so it works with all DB drivers incl. SQLite.
     */
    public function citiesNear(float $lat, float $lng, float $radiusKm = 100, ?string $countryCode = null): Collection
    {
        return City::active()
            ->when($countryCode, fn ($q) => $q->byCountry($countryCode))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (City $city) use ($lat, $lng) {
                $city->distance = round($this->haversine($lat, $lng, $city->latitude, $city->longitude), 2);
                return $city;
            })
            ->filter(fn (City $city) => $city->distance <= $radiusKm)
            ->sortBy('distance')
            ->values();
    }

    /**
     * All cities of a country sorted by distance from a coordinate.
     *
     * Each returned City model has an extra `distance` (float, km) attribute.
     */
    public function sortCitiesByDistance(float $lat, float $lng, string $countryCode): Collection
    {
        return City::active()
            ->byCountry($countryCode)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (City $city) use ($lat, $lng) {
                $city->distance = round($this->haversine($lat, $lng, $city->latitude, $city->longitude), 2);
                return $city;
            })
            ->sortBy('distance')
            ->values();
    }

    /**
     * Areas within $radiusKm kilometres of a coordinate, scoped to one city.
     *
     * Each returned Area model has an extra `distance` (float, km) attribute.
     */
    public function areasNear(float $lat, float $lng, City|int $city, float $radiusKm = 10): Collection
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return Area::active()
            ->where('city_id', $cityId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (Area $area) use ($lat, $lng) {
                $area->distance = round($this->haversine($lat, $lng, $area->latitude, $area->longitude), 2);
                return $area;
            })
            ->filter(fn (Area $area) => $area->distance <= $radiusKm)
            ->sortBy('distance')
            ->values();
    }

    // ── Cache management ──────────────────────────────────────────────────────

    /**
     * Flush all geography cache entries.
     * Called automatically after seeding; call manually after data changes.
     */
    public function flush(): void
    {
        $prefix  = $this->prefix();
        $metaKey = "{$prefix}.__keys";

        foreach (Cache::get($metaKey, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget($metaKey);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function remember(string $key, Closure $callback): mixed
    {
        if (! config('country-data.cache.enabled', true)) {
            return $callback();
        }

        $fullKey = $this->prefix() . '.' . $key;
        $ttl     = (int) config('country-data.cache.ttl', 86400);

        $this->trackKey($fullKey, $ttl);

        return Cache::remember($fullKey, $ttl, $callback);
    }

    private function trackKey(string $key, int $ttl): void
    {
        $metaKey = $this->prefix() . '.__keys';
        $keys    = Cache::get($metaKey, []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put($metaKey, $keys, $ttl + 60);
        }
    }

    private function prefix(): string
    {
        return (string) config('country-data.cache.prefix', 'geography');
    }

    /**
     * Haversine formula — great-circle distance in km between two coordinates.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R         = 6371; // Earth radius in km
        $latDelta  = deg2rad($lat2 - $lat1);
        $lngDelta  = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($lngDelta / 2) ** 2;

        return $R * 2 * asin(sqrt($a));
    }
}
