# Laravel Country Data

A full-featured **Laravel geography package** with countries, cities, and areas — backed by a database with caching, geospatial helpers, validation rules, a Livewire cascading dropdown, and an optional REST API.

Built with multilingual support (English & Arabic), region filters, and a clean `Geography` facade — ideal for any application that needs structured location data.

---

## Features

- **DB-backed geography** — countries, cities, and areas as Eloquent models
- **Selective seeding** — choose which countries to seed interactively or via CLI flags
- **Transparent caching** — all reads cached, one-command flush
- **Geography facade** — clean API for countries, cities, areas, search, and select helpers
- **Currency & timezone helpers** — typed `CurrencyData` value object, dial-code lookup, continent grouping
- **Geospatial helpers** — Haversine distance, cities/areas near a coordinate
- **Validation rules** — `ValidCountryCode`, `ValidCityForCountry`, `ValidAreaForCity`
- **Livewire component** — cascading Country → City → Area dropdowns, RTL-aware
- **Optional REST API** — 12 JSON endpoints, opt-in via `.env`
- **Artisan commands** — setup, cache-clear, stats
- Multilingual (EN + AR), region filters, and frontend select helpers

---

## Requirements

- PHP `^8.2`
- Laravel `^11.0` or `^12.0`

---

## Installation

```bash
composer require enadstack/laravel-country-data
```

Publish the config files:

```bash
php artisan vendor:publish --tag=country-data
```

---

## Geography System Setup

### 1. Run the interactive setup command

```bash
php artisan country-data:setup
```

The command will ask three things:

```
 Geography Setup

 ┌ Run migrations? ──────────────────────┐
 │ Yes                                   │
 └───────────────────────────────────────┘

 ┌ Seed geography data? ─────────────────┐
 │ Yes                                   │
 └───────────────────────────────────────┘

 ┌ Countries to seed ────────────────────┐
 │ > ◼ All countries (22)               │
 │   ◻ Bahrain (BH)                     │
 │   ◼ Jordan (JO)                      │
 │   ◼ Saudi Arabia (SA)                │
 │   ...                                │
 └───────────────────────────────────────┘
```

### 2. Non-interactive / CI options

```bash
# Migrate only, seed later
php artisan country-data:setup --migrate

# Seed all countries without prompts
php artisan country-data:setup --seed --all

# Seed specific countries by ISO-2 code
php artisan country-data:setup --seed --countries=JO,SA,AE

# Drop tables + migrate + seed (destructive — confirms before drop)
php artisan country-data:setup --fresh --all
```

### What gets created

| Table | Content |
|---|---|
| `countries` | 22 Arab League countries with currency, dial code, timezone, geo coordinates |
| `cities` | 136 cities across all 22 countries (Jordan: all 12 governorates) |
| `areas` | 101 areas (Jordan fully covered — 32 Amman neighborhoods + all governorates) |

Seeders are **idempotent** — safe to re-run, they use `updateOrInsert`.

---

## Geography Facade

```php
use Enadstack\CountryData\Facades\Geography;
```

### Countries

```php
// All active countries (ordered by name_en)
Geography::countries();

// Filter by tag: arab | gulf | middle-east | africa | asia …
Geography::countries('gulf');

// Single country by ISO-2 code (case-insensitive)
$jordan = Geography::country('JO');
$jordan->code;        // 'JO'
$jordan->name_en;     // 'Jordan'
$jordan->name_ar;     // 'الأردن'
$jordan->flag;        // '🇯🇴'
$jordan->dial;        // '+962'
$jordan->timezones;   // ['Asia/Amman']
$jordan->currency_code; // 'JOD'

// All capital cities
Geography::capitals();

// For select dropdowns
Geography::countriesForSelect(locale: 'en', filter: 'arab');
// [['value' => 'JO', 'label' => 'Jordan', 'flag' => '🇯🇴', 'dial' => '+962'], ...]
```

### Cities

```php
// All cities for a country
Geography::cities('JO');

// Single city by country + English name
Geography::city('JO', 'Amman');

// Capital city of a country
Geography::capital('JO');

// Search cities by partial name (EN or AR), optionally scoped to a country
Geography::searchCities('am');
Geography::searchCities('am', 'JO');

// For select dropdowns
Geography::citiesForSelect('JO', locale: 'ar');
// [['value' => 3, 'label' => 'عمان'], ...]
```

### Areas

```php
// All areas for a city (accepts City model or int ID)
Geography::areas($amman);
Geography::areas(3);

// Filter by type: governorate | district | neighborhood | zone
Geography::areas($amman, 'neighborhood');

// Grouped by type
Geography::areasByType($amman);
// Collection keyed by type: ['neighborhood' => [...], 'district' => [...]]

// Search areas within a city
Geography::searchAreas('down', $amman);

// For select dropdowns
Geography::areasForSelect($amman, locale: 'en', type: 'neighborhood');
// [['value' => 12, 'label' => 'Downtown', 'type' => 'neighborhood'], ...]
```

### Country → Areas (HasManyThrough)

```php
$jordan = Country::where('code', 'JO')->first();

// All areas across all of Jordan's cities
$jordan->areas;

// Filtered
$jordan->areas()->where('type', 'neighborhood')->get();
```

---

## Currency & Timezone Helpers

### Currency

```php
$currency = Geography::currencyOf('JO');
// Returns a CurrencyData value object

$currency->code;        // 'JOD'
$currency->nameEn;      // 'Jordanian Dinar'
$currency->nameAr;      // 'دينار أردني'
$currency->symbolEn;    // 'JD'
$currency->symbolAr;    // 'د.أ'

// Locale-aware display values
$currency->name();      // 'Jordanian Dinar' (en)
$currency->symbol();    // 'JD' (en)

// Switch locale — returns a new immutable copy
$currency->in('ar')->name();    // 'دينار أردني'
$currency->in('ar')->symbol();  // 'د.أ'

// Serialize
$currency->toArray();

// Countries sharing a currency
Geography::countriesByCurrency('USD');
```

### Timezones

```php
// All IANA timezone identifiers for a country
Geography::timezonesOf('JO');       // ['Asia/Amman']
Geography::timezonesOf('AE');       // ['Asia/Dubai']

// Timezone of a specific city (accepts City model or int ID)
Geography::timezoneForCity($amman); // 'Asia/Amman'
Geography::timezoneForCity(3);      // 'Asia/Amman'
```

### Dial Codes

```php
Geography::dialCodeOf('JO');           // '+962'
Geography::countryByDialCode('+962');  // Country model for Jordan
Geography::countryByDialCode('962');   // also works (without +)
```

### Continents

```php
// All distinct continent names
Geography::continents();
// Collection: ['Africa', 'Asia', ...]

// Countries on a continent (flat collection)
Geography::countriesByContinent('Asia');

// All countries grouped by continent
Geography::groupedByContinent();
// Collection keyed by continent name, each value a Collection of Countries
```

---

## Geospatial Helpers

All distance calculations use the **Haversine formula** (PHP-level, works with SQLite and all DB drivers).

```php
// Straight-line distance in km between two country centres
Geography::distanceBetween('JO', 'SA'); // ~1,400.0

// Cities within a radius — each result has a `distance` (float, km) attribute
$cities = Geography::citiesNear(lat: 31.9566, lng: 35.9457, radiusKm: 100, countryCode: 'JO');
$cities->first()->distance; // e.g. 0.42

// All cities of a country sorted by distance from a point
$cities = Geography::sortCitiesByDistance(31.9566, 35.9457, 'JO');
$cities->first()->name_en; // 'Amman'

// Areas near a coordinate within a city
$areas = Geography::areasNear(lat: 31.9566, lng: 35.9457, city: $amman, radiusKm: 10);
$areas->first()->distance; // km from the given point
```

---

## Validation Rules

```php
use Enadstack\CountryData\Rules\ValidCountryCode;
use Enadstack\CountryData\Rules\ValidCityForCountry;
use Enadstack\CountryData\Rules\ValidAreaForCity;

$request->validate([
    // Must be an active ISO-2 country code
    'country_code' => ['required', new ValidCountryCode()],

    // Scoped to a filter (arab, gulf, etc.)
    'country_code' => ['required', new ValidCountryCode(filter: 'arab')],

    // City must exist and belong to the given country
    'city_id' => ['required', new ValidCityForCountry($request->country_code)],

    // Area must exist and belong to the given city
    'area_id' => ['required', new ValidAreaForCity($request->city_id)],

    // Area must also be of a specific type
    'area_id' => ['required', new ValidAreaForCity($request->city_id, type: 'neighborhood')],
]);
```

---

## Livewire Cascading Dropdown

Requires **Livewire 3**. The component is auto-registered when Livewire is installed.

### Basic usage

```blade
<livewire:geography-select />
```

### With options

```blade
<livewire:geography-select
    locale="ar"
    filter="gulf"
    :show-areas="true"
    :required="true"
    country-field="country_code"
    city-field="city_id"
    area-field="area_id"
/>
```

### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `locale` | `string` | `'en'` | Display language (`en` or `ar`) |
| `filter` | `?string` | `null` | Country filter tag (e.g. `arab`, `gulf`) |
| `showAreas` | `bool` | `false` | Show the third area dropdown |
| `required` | `bool` | `false` | Mark all selects as required |
| `countryField` | `string` | `'country_code'` | Hidden input name for country |
| `cityField` | `string` | `'city_id'` | Hidden input name for city |
| `areaField` | `string` | `'area_id'` | Hidden input name for area |

### Events emitted

| Event | Payload |
|---|---|
| `country-selected` | `['code' => 'JO']` |
| `city-selected` | `['id' => 3]` |
| `area-selected` | `['id' => 12]` |

### Publish the view to customise

```bash
php artisan vendor:publish --tag=country-data-livewire
```

Published to: `resources/views/vendor/country-data/livewire/geography-select.blade.php`

---

## REST API

The API is **disabled by default**. Enable it in `.env`:

```env
COUNTRY_DATA_API=true
COUNTRY_DATA_API_PREFIX=api/geography   # default
```

Or in `config/country-data.php`:

```php
'api' => [
    'enabled'    => true,
    'prefix'     => 'api/geography',
    'middleware' => ['api'],
],
```

### Endpoints

#### Countries

| Method | URL | Description |
|---|---|---|
| GET | `/api/geography/countries` | All countries (`?filter=gulf&locale=ar`) |
| GET | `/api/geography/countries/{code}` | Single country by ISO-2 code |
| GET | `/api/geography/countries/{code}/cities` | Cities for a country |

#### Cities & Areas

| Method | URL | Description |
|---|---|---|
| GET | `/api/geography/cities/{id}` | Single city by ID |
| GET | `/api/geography/cities/{id}/areas` | Areas for a city (`?type=neighborhood`) |

#### Currency & Continents

| Method | URL | Description |
|---|---|---|
| GET | `/api/geography/currencies` | All unique currencies (`?locale=ar`) |
| GET | `/api/geography/currencies/{code}/countries` | Countries using a currency |
| GET | `/api/geography/continents` | All distinct continents |
| GET | `/api/geography/continents/{name}/countries` | Countries on a continent |

#### Geospatial

| Method | URL | Description |
|---|---|---|
| GET | `/api/geography/near/cities` | Cities near a coordinate (`?lat=&lng=&radius=&country=`) |

#### Search

| Method | URL | Description |
|---|---|---|
| GET | `/api/geography/search/cities` | Search cities (`?q=amman&country=JO`) |
| GET | `/api/geography/search/areas` | Search areas (`?q=down&city_id=3`) |

### Response shape

```json
{
    "data": [ { "code": "JO", "name": "Jordan", "name_en": "Jordan", "name_ar": "الأردن", ... } ],
    "meta": { "total": 1 }
}
```

---

## Artisan Commands

| Command | Description |
|---|---|
| `php artisan country-data:setup` | Interactive migrate + selective seed |
| `php artisan country-data:setup --migrate` | Run migrations only |
| `php artisan country-data:setup --seed --all` | Seed all countries without prompting |
| `php artisan country-data:setup --seed --countries=JO,SA` | Seed specific countries |
| `php artisan country-data:setup --fresh --all` | Drop tables, migrate, seed all |
| `php artisan country-data:cache-clear` | Flush all geography cache entries |
| `php artisan country-data:stats` | Display seeded counts, cache status |
| `php artisan country-data:configure` | Choose config-based country dataset |
| `php artisan country-data:publish-component` | Publish Blade/Vue/React frontend component |

### `country-data:stats` output

```
 Geography Data Statistics

  Countries (active / total) ............. 22 / 22
  Cities    (active / total) ............. 136 / 136
  Capital cities ........................... 22
  Areas   (active / total) ............... 101 / 101

  Countries by continent:
    Africa .................................. 5
    Asia ................................... 17

  Areas by type:
    neighborhood ........................... 82
    zone ................................... 16
    district ................................ 3

  Top cities by area count:
    Amman (JO) ...................... 32 areas
    Riyadh (SA) ..................... 12 areas

  Cache:
    Enabled ............................. yes
    Driver .............................. file
    TTL ........................ 86400s (24:00:00)
    Tracked keys ......................... 14
```

---

## Cache Configuration

```php
// config/country-data.php
'cache' => [
    'enabled' => true,
    'ttl'     => 86400,      // seconds (24 h)
    'prefix'  => 'geography',
],
```

Flush manually:

```php
Geography::flush();
```

Or via artisan:

```bash
php artisan country-data:cache-clear
```

The cache is also flushed automatically after seeding.

---

## Config Reference

```php
// config/country-data.php
return [
    // Config-based country dataset (used by CountryData facade)
    'source' => 'all', // 'all' | 'arab' | 'gulf' | 'europe'

    'cache' => [
        'enabled' => env('COUNTRY_DATA_CACHE', true),
        'ttl'     => env('COUNTRY_DATA_CACHE_TTL', 86400),
        'prefix'  => 'geography',
    ],

    'api' => [
        'enabled'    => env('COUNTRY_DATA_API', false),
        'prefix'     => env('COUNTRY_DATA_API_PREFIX', 'api/geography'),
        'middleware' => ['api'],
    ],

    'livewire' => [
        'register'    => true,
        'locale'      => 'en',
        'show_areas'  => false,
    ],

    'frontend' => [
        'component'          => 'none', // 'none' | 'blade' | 'vue' | 'react'
        'publish_components' => true,
    ],
];
```

---

## Config-Based Facade (v1 — still available)

For lightweight use without a database, the original `CountryData` facade works from config:

```php
use Enadstack\CountryData\Facades\CountryData;

CountryData::getArabCountries();
CountryData::getGulfCountries();
CountryData::getByCode('JO');
CountryData::getByFilter('muslim-majority');
CountryData::searchByName('Jordan');
CountryData::searchByName('الأردن', 'ar');
CountryData::getName('SA', 'ar');       // 'المملكة العربية السعودية'
CountryData::getFlag('JO');             // '🇯🇴'
CountryData::getDialCodes(withFlag: true);
CountryData::getSelectOptions('ar');
```

---

## File Structure

```
laravel-country-data/
├── config/
│   ├── countries.php           # Config-based country dataset
│   └── country-data.php        # Package settings
│
├── data/
│   ├── countries.json          # 22 Arab countries (source for DB seeder)
│   ├── cities.json             # 136 cities
│   └── areas.json              # 101 areas
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_countries_table.php
│   │   ├── ..._create_cities_table.php
│   │   └── ..._create_areas_table.php
│   └── Seeders/
│       ├── GeographySeeder.php
│       ├── CountrySeeder.php
│       ├── CitySeeder.php
│       └── AreaSeeder.php
│
├── routes/
│   └── api.php
│
└── src/
    ├── Commands/
    │   ├── GeographySetup.php
    │   ├── CacheClear.php
    │   ├── Stats.php
    │   ├── ConfigureCountryData.php
    │   └── PublishFrontendComponent.php
    ├── Data/
    │   └── CurrencyData.php
    ├── Facades/
    │   ├── Geography.php
    │   └── CountryData.php
    ├── Http/Controllers/
    │   └── GeographyController.php
    ├── Livewire/
    │   └── GeographySelect.php
    ├── Models/
    │   ├── Country.php
    │   ├── City.php
    │   └── Area.php
    ├── Rules/
    │   ├── ValidCountryCode.php
    │   ├── ValidCityForCountry.php
    │   └── ValidAreaForCity.php
    ├── Services/
    │   └── GeographyService.php
    └── resources/views/livewire/
        └── geography-select.blade.php
```

---

## Testing

```bash
composer test              # all tests
composer test:unit         # unit tests only
composer test:feature      # feature tests only
```

The test suite runs on an **SQLite in-memory** database — no external DB needed.

---

## License

MIT — built by [@enadabuzaid](https://github.com/enadabuzaid)
