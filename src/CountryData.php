<?php

namespace Enad\CountryData;

class CountryData
{
    protected static function all(): array
    {
        return config('countries',  []);
    }

    public static function getGulfCountries(): array
    {
        return self::getByFilter('gulf');
    }

    public static function getArabCountries(): array
    {
        return self::getByFilter('arab');
    }

    public static function getByFilter(string $filter): array
    {
        return array_values(array_filter(self::all(), function ($country) use ($filter) {
            return in_array($filter, $country['filters'] ?? []);
        }));
    }

    public static function getByCode(string $code): ?array
    {
        foreach (self::all() as $country) {
            if (strtoupper($country['code']) === strtoupper($code)) {
                return $country;
            }
        }

        return null;
    }

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

    public static function getName(string $code, string $locale = 'en'): ?string
    {
        $country = self::getByCode($code);
        return $country['names']['common'][$locale] ?? null;
    }

    public static function getFlag(string $code): ?string
    {
        $country = self::getByCode($code);
        return $country['flag'] ?? null;
    }

    public static function getSelectOptions(string $locale = 'en'): array
    {
        return array_map(function ($country) use ($locale) {
            return [
                'label' => $country['names']['common'][$locale] ?? $country['names']['common']['en'],
                'value' => $country['code']
            ];
        }, self::all());
    }
}