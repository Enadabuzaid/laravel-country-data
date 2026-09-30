<?php

namespace Enadstack\CountryData\Shortcuts;

use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Exceptions\CountryNotFoundException;
use Enadstack\CountryData\Exceptions\RegionNotFoundException;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Services\GeographyService;
use Enadstack\CountryData\Support\Lookup;

/**
 * Shared resolution for the static shortcut classes. Every lookup goes through
 * GeographyService, so results are cached like the rest of the package.
 *
 * A dynamic static call is resolved region first, then country:
 *   Countries::europe()   → Region::Europe
 *   Countries::jordan()   → the JO row (by name, official name, ISO-2 or ISO-3)
 */
abstract class Shortcut
{
    protected static function geo(): GeographyService
    {
        return app(GeographyService::class);
    }

    /**
     * @throws CountryNotFoundException
     */
    protected static function country(Country|CountryCode|string $country): Country
    {
        return static::geo()->resolveCountry($country)
            ?? throw CountryNotFoundException::named(
                (string) (is_string($country) ? $country : $country->value),
                self::suggest((string) (is_string($country) ? $country : $country->value), withRegions: false)
            );
    }

    /**
     * @throws CountryNotFoundException|RegionNotFoundException
     */
    protected static function resolve(string $name): Region|Country
    {
        if ($region = Region::tryFromName($name)) {
            return $region;
        }

        if ($country = static::geo()->resolveCountry($name)) {
            return $country;
        }

        $suggestion = self::suggest($name, withRegions: true);

        // Report the kind of thing the caller most likely meant.
        $meantRegion = $suggestion !== null
            && Region::tryFromName(substr($suggestion, strrpos($suggestion, ':') + 1, -2)) !== null;

        throw $meantRegion
            ? RegionNotFoundException::named($name, $suggestion)
            : CountryNotFoundException::named($name, $suggestion);
    }

    /** "Countries::jordan()" for the closest known name, or null. */
    private static function suggest(string $name, bool $withRegions): ?string
    {
        $methods = array_values(static::geo()->countryIndex()['methods']);

        if ($withRegions) {
            $methods = array_merge(array_map(fn (Region $r) => $r->method(), Region::cases()), $methods);
        }

        $closest = Lookup::closest($name, $methods);

        return $closest !== null ? class_basename(static::class) . "::{$closest}()" : null;
    }
}
