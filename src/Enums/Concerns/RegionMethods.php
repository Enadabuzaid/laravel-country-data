<?php

namespace Enadstack\CountryData\Enums\Concerns;

use Enadstack\CountryData\Exceptions\RegionNotFoundException;
use Enadstack\CountryData\Services\GeographyService;
use Enadstack\CountryData\Support\Lookup;
use Illuminate\Support\Collection;

/**
 * Behaviour of the generated Region enum. Metadata lives in Region::META.
 */
trait RegionMethods
{
    /** Human-readable name, e.g. "Gulf Cooperation Council". */
    public function label(): string
    {
        return self::META[$this->value]['label'];
    }

    /** How membership is defined (M49 region or the political list and its date). */
    public function description(): string
    {
        return self::META[$this->value]['description'];
    }

    /** Derived from UN M49 geography, rather than a political membership list. */
    public function isGeographic(): bool
    {
        return self::META[$this->value]['kind'] === 'geographic';
    }

    /** The static-method / scope spelling: 'north-america' → northAmerica. */
    public function method(): string
    {
        return Lookup::method($this->value);
    }

    /** Active countries in this region (cached). */
    public function countries(): Collection
    {
        return app(GeographyService::class)->countries($this->value);
    }

    /**
     * Resolve any spelling of a region: 'north-america', 'northAmerica',
     * 'NORTH_AMERICA', 'North America', or a case name ('NorthAmerica').
     */
    public static function tryFromName(string $name): ?self
    {
        $key = Lookup::key($name);

        foreach (self::cases() as $case) {
            if (Lookup::key($case->value) === $key || Lookup::key($case->name) === $key) {
                return $case;
            }
        }

        return null;
    }

    /** @throws RegionNotFoundException with a "did you mean …?" suggestion */
    public static function fromName(string $name): self
    {
        return self::tryFromName($name)
            ?? throw RegionNotFoundException::named($name, Lookup::closest($name, array_column(self::cases(), 'value')));
    }
}
