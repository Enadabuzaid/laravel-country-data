<?php

namespace Enadstack\CountryData;

use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Services\GeographyService;

/**
 * Array-based country lookups.
 *
 * @deprecated 3.0 Use the Countries shortcut, the Geography facade or the
 *             Country model instead; see UPGRADE.md. Kept working throughout 3.x.
 *
 * When the countries table is seeded, every method reads through
 * GeographyService (cached, and reflecting any edits made to the rows).
 * Without a seeded table it falls back to the bundled config('countries'),
 * so apps that never ran the migrations keep working. Both paths return the
 * same array shape as before.
 */
class CountryData
{
    /** @return array<int, array<string, mixed>> */
    protected static function all(): array
    {
        $geo = self::geography();

        if ($geo === null) {
            return config('countries', []);
        }

        return $geo->countries()->sortBy('id')->map(fn (Country $c) => self::toArray($c))->values()->all();
    }

    /** @deprecated 3.0 Use Countries::gulf() or Country::gulf()->get(). */
    public static function getGulfCountries(): array
    {
        return self::getByFilter('gulf');
    }

    /** @deprecated 3.0 Use Countries::arab() or Country::arab()->get(). */
    public static function getArabCountries(): array
    {
        return self::getByFilter('arab');
    }

    /** @deprecated 3.0 Use Countries::in($region) or Country::inRegion($region)->get(). */
    public static function getByFilter(string $filter): array
    {
        if ($geo = self::geography()) {
            return $geo->countries($filter)->sortBy('id')->map(fn (Country $c) => self::toArray($c))->values()->all();
        }

        return array_values(array_filter(self::all(), function ($country) use ($filter) {
            return in_array($filter, $country['filters'] ?? []);
        }));
    }

    /** @deprecated 3.0 Use Countries::of($code) or Geography::country($code). */
    public static function getByCode(string $code): ?array
    {
        if ($geo = self::geography()) {
            $country = $geo->country($code);

            return $country ? self::toArray($country) : null;
        }

        foreach (self::all() as $country) {
            if (strtoupper($country['code']) === strtoupper($code)) {
                return $country;
            }
        }

        return null;
    }

    /** @deprecated 3.0 Use Geography::phoneCountriesForSelect(). */
    public static function getDialCodes(bool $withFlag = false): array
    {
        return array_values(array_map(function ($country) use ($withFlag) {
            $entry = [
                'code' => $country['code'],
                'dial' => $country['dial'],
            ];

            if ($withFlag && isset($country['flag'])) {
                $entry['flag'] = $country['flag'];
                $entry['flag_url'] = $country['flags']['svg'] ?? null;
            }

            return $entry;
        }, self::all()));
    }

    /** @deprecated 3.0 Use Countries::of($name) (any spelling) or Country::search($name). */
    public static function searchByName(string $name, string $locale = 'en'): ?array
    {
        $name = strtolower($name);

        foreach (self::all() as $country) {
            $common = strtolower($country['names']['common'][$locale] ?? '');
            $official = strtolower($country['names']['official'][$locale] ?? '');

            if ($name === $common || $name === $official) {
                return $country;
            }
        }

        return null;
    }

    /** @deprecated 3.0 Use CountryCode::from($code)->name($locale) or $country->name. */
    public static function getName(string $code, string $locale = 'en'): ?string
    {
        $country = self::getByCode($code);
        return $country['names']['common'][$locale] ?? null;
    }

    /** @deprecated 3.0 Use CountryCode::from($code)->flag() or $country->flag. */
    public static function getFlag(string $code): ?string
    {
        $country = self::getByCode($code);
        return $country['flag'] ?? null;
    }

    /** @deprecated 3.0 Use Geography::countriesForSelect($locale). */
    public static function getSelectOptions(string $locale = 'en'): array
    {
        return array_map(function ($country) use ($locale) {
            return [
                'label' => $country['names']['common'][$locale] ?? $country['names']['common']['en'],
                'value' => $country['code']
            ];
        }, self::all());
    }

    // ── Internals ────────────────────────────────────────────────────────────

    /** The service when the countries table is seeded, otherwise null (use config). */
    private static function geography(): ?GeographyService
    {
        $geo = app(GeographyService::class);

        return $geo->isSeeded() ? $geo : null;
    }

    /** A Country row in the array shape of config/countries.php. */
    private static function toArray(Country $c): array
    {
        return [
            'names' => [
                'common'   => ['en' => $c->name_en, 'ar' => $c->name_ar],
                'official' => ['en' => $c->official_name_en, 'ar' => $c->official_name_ar],
            ],
            'code'        => $c->code,
            'iso2'        => $c->iso2,
            'iso3'        => $c->iso3,
            'numericCode' => $c->numeric_code,
            'cioc'        => $c->cioc,
            'cca2'        => $c->iso2,
            'ccn3'        => $c->numeric_code,
            'flag'        => $c->flag,
            'emoji'       => $c->emoji,
            'flags'       => ['png' => $c->flag_png, 'svg' => $c->flag_svg],
            'coatOfArms'  => ['png' => $c->coat_of_arms_png, 'svg' => $c->coat_of_arms_svg],
            'maps'        => ['googleMaps' => $c->google_maps_url, 'openStreetMaps' => $c->openstreet_maps_url],
            'currency'    => [
                'code'   => $c->currency_code,
                'name'   => ['en' => $c->currency_name_en, 'ar' => $c->currency_name_ar],
                'symbol' => ['en' => $c->currency_symbol_en, 'ar' => $c->currency_symbol_ar],
            ],
            'dial'       => $c->dial,
            'capital'    => $c->capital,
            'region'     => $c->region,
            'continent'  => $c->continent,
            'subregion'  => $c->subregion,
            'languages'  => $c->languages ?? [],
            'timezones'  => $c->timezones ?? [],
            'tld'        => $c->tld ?? [],
            'borders'    => $c->borders ?? [],
            'geo'        => ['latitude' => $c->latitude, 'longitude' => $c->longitude],
            'population' => $c->population,
            'area'       => $c->area,
            'filters'    => $c->filters ?? [],
        ];
    }
}
