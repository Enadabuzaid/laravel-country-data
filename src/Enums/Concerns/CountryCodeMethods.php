<?php

namespace Enadstack\CountryData\Enums\Concerns;

use Enadstack\CountryData\Exceptions\CountryNotFoundException;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Services\GeographyService;

/**
 * Behaviour of the generated CountryCode enum. Names and ISO3 codes live in
 * CountryCode::DATA, so name(), flag() and iso3() work without a database.
 */
trait CountryCodeMethods
{
    /** The Country row (cached), or null when it isn't seeded or is inactive. */
    public function model(): ?Country
    {
        return app(GeographyService::class)->country($this->value);
    }

    /** Common name in the given locale (default: the app locale). Only 'ar' and 'en' ship. */
    public function name(?string $locale = null): string
    {
        $locale ??= function_exists('app') && app()->bound('translator') ? app()->getLocale() : 'en';

        return self::DATA[$this->value][$locale === 'ar' ? 2 : 1];
    }

    /** Flag emoji, e.g. 🇯🇴. */
    public function flag(): string
    {
        return implode('', array_map(
            fn (string $ch) => mb_chr(0x1F1E6 + ord($ch) - ord('A')),
            str_split($this->value)
        ));
    }

    /** ISO 3166-1 alpha-3, e.g. JOR. */
    public function iso3(): string
    {
        return self::DATA[$this->value][0];
    }

    /** Resolve an ISO-2 or ISO-3 code, case-insensitively. */
    public static function tryFromAny(string $code): ?self
    {
        $code = strtoupper(trim($code));

        if (strlen($code) === 2) {
            return self::tryFrom($code);
        }

        if (strlen($code) === 3) {
            foreach (self::DATA as $iso2 => [$iso3]) {
                if ($iso3 === $code) {
                    return self::from($iso2);
                }
            }
        }

        return null;
    }

    /** @throws CountryNotFoundException */
    public static function fromAny(string $code): self
    {
        return self::tryFromAny($code) ?? throw CountryNotFoundException::named($code);
    }
}
