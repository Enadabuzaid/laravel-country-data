<?php

namespace Enadstack\CountryData\Facades;

use Illuminate\Support\Facades\Facade;
use Enadstack\CountryData\Services\GeographyService;

/**
 * @method static \Illuminate\Support\Collection countries(?string $filter = null)
 * @method static \Enadstack\CountryData\Models\Country|null country(string $code)
 * @method static \Illuminate\Support\Collection capitals()
 * @method static \Illuminate\Support\Collection cities(string $countryCode)
 * @method static \Enadstack\CountryData\Models\City|null city(string $countryCode, string $nameEn)
 * @method static \Enadstack\CountryData\Models\City|null capital(string $countryCode)
 * @method static \Illuminate\Support\Collection areas(\Enadstack\CountryData\Models\City|int $city, ?string $type = null)
 * @method static \Illuminate\Support\Collection areasByType(\Enadstack\CountryData\Models\City|int $city)
 * @method static \Illuminate\Support\Collection searchCities(string $query, ?string $countryCode = null)
 * @method static \Illuminate\Support\Collection searchAreas(string $query, \Enadstack\CountryData\Models\City|int $city)
 * @method static \Illuminate\Support\Collection countriesForSelect(string $locale = 'en', ?string $filter = null)
 * @method static \Illuminate\Support\Collection citiesForSelect(string $countryCode, string $locale = 'en')
 * @method static \Illuminate\Support\Collection areasForSelect(\Enadstack\CountryData\Models\City|int $city, string $locale = 'en', ?string $type = null)
 * @method static \Enadstack\CountryData\Data\CurrencyData|null currencyOf(string $countryCode, string $locale = 'en')
 * @method static \Illuminate\Support\Collection countriesByCurrency(string $currencyCode)
 * @method static array timezonesOf(string $countryCode)
 * @method static string|null timezoneForCity(\Enadstack\CountryData\Models\City|int $city)
 * @method static string|null dialCodeOf(string $countryCode)
 * @method static \Enadstack\CountryData\Models\Country|null countryByDialCode(string $dialCode)
 * @method static \Illuminate\Support\Collection continents()
 * @method static \Illuminate\Support\Collection countriesByContinent(string $continent)
 * @method static \Illuminate\Support\Collection groupedByContinent()
 * @method static void flush()
 * @method static float|null distanceBetween(string $code1, string $code2)
 * @method static \Illuminate\Support\Collection citiesNear(float $lat, float $lng, float $radiusKm = 100, ?string $countryCode = null)
 * @method static \Illuminate\Support\Collection sortCitiesByDistance(float $lat, float $lng, string $countryCode)
 * @method static \Illuminate\Support\Collection areasNear(float $lat, float $lng, \Enadstack\CountryData\Models\City|int $city, float $radiusKm = 10)
 *
 * @see \Enadstack\CountryData\Services\GeographyService
 */
class Geography extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GeographyService::class;
    }
}
