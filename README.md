# Laravel Country Data

A Laravel geography package covering **every country in the world** (250 ISO 3166-1 countries
and territories), their capitals and cities, and neighborhood-level areas. It stores them
in your database, caches every read and ships in English and Arabic.

```php
Countries::jordan();                       // Country model
Countries::europe();                       // every European country
Cities::capitalOf('JO');                   // Amman
Areas::in('JO', 'Amman')->neighborhoods(); // Abdoun, Sweifieh, …
CountryCode::SA->name('ar');               // المملكة العربية السعودية
```

It also includes validation rules, a React phone input, a Livewire cascading select,
Blade/Vue/React components and an optional REST API.

> **Upgrading from v2?** Read [UPGRADE.md](UPGRADE.md). Some defaults changed.

---

## Contents

- [Requirements](#requirements) · [Installation](#installation) · [Data coverage](#data-coverage)
- [Shortcuts cheat sheet](#shortcuts-cheat-sheet) · [Enums](#enums) · [Models & scopes](#models--scopes)
- [Geography facade](#geography-facade) · [Validation rules](#validation-rules)
- [Frontend components](#frontend-components) · [REST API](#rest-api)
- [Caching](#caching) · [Artisan commands](#artisan-commands) · [Configuration](#configuration)
- [Where the data comes from](#where-the-data-comes-from) · [Testing](#testing)

---

## Requirements

- PHP `^8.2`
- Laravel 11, 12 or 13 (Laravel 13 needs PHP 8.3+)

## Installation

```bash
composer require enadstack/laravel-country-data

php artisan vendor:publish --tag=country-data     # optional: config files
php artisan country-data:setup                    # migrate + choose what to seed
```

`country-data:setup` asks whether to migrate, whether to seed and which countries to
seed. For CI or scripts:

```bash
php artisan country-data:setup --migrate                  # migrations only
php artisan country-data:setup --seed --all               # all 250 countries
php artisan country-data:setup --seed --source=arab       # one region (any filter below)
php artisan country-data:setup --seed --countries=JO,SA   # specific ISO-2 codes
php artisan country-data:setup --fresh --all              # drop, migrate, seed (asks first)
```

Seeders are idempotent (`updateOrInsert`), so they are safe to re-run, and they flush
the cache when they finish.

---

## Data coverage

| | Count |
|---|---|
| Countries and territories | **250** (all of ISO 3166-1, plus Kosovo) |
| Cities | **359**: 136 hand-curated in the 22 Arab League countries, plus the capital of every other country that has one |
| Areas | **276**: Amman is modelled in full (207 areas: 27 districts, each with its neighborhoods and major streets); a few more cities in SA, AE, EG, LB and IQ are partially covered |

Every country has English and Arabic common and official names, ISO-2/ISO-3/numeric
codes, a flag, currency (with Arabic name and symbol), dial code, capital, UN M49
region, languages, IANA timezones, TLDs, borders, coordinates, population and area.
The only gaps are uninhabited territories: Antarctica has no currency or dial code,
Heard & McDonald Islands has no dial code, and five territories have no capital. See
[`resources/build-report.md`](resources/build-report.md) for the details.

### Regions

Every country carries `filters` tags. Each tag is also a `Region` enum case, a model
scope, a shortcut and a seed source.

| Filter | Meaning | Countries | Cities | Areas |
|---|---|---:|---:|---:|
| `arab` | Arab League members | 22 | 136 | 276 |
| `gulf` | Arabian Gulf: the GCC plus Iraq (as shipped since v2) | 7 | 52 | 19 |
| `gcc` | Gulf Cooperation Council members (no Iraq) | 6 | 44 | 14 |
| `levant` | Jordan, Lebanon, Palestine, Syria, Israel | 5 | 32 | 251 |
| `maghreb` | Arab Maghreb Union: DZ, LY, MR, MA, TN | 5 | 23 | 0 |
| `middle-east` | Arab Middle East, plus Iran, Israel, Turkey, Cyprus | 17 | 102 | 276 |
| `muslim-majority` | Over 50% Muslim (Pew Research, 2011) | 48 | 157 | 272 |
| `africa` | UN M49 region Africa | 60 | 98 | 6 |
| `asia` | UN M49 region Asia | 51 | 126 | 270 |
| `europe` | UN M49 region Europe | 52 | 52 | 0 |
| `north-america` | UN M49 Northern America, Central America and Caribbean | 41 | 41 | 0 |
| `south-america` | UN M49 South America | 16 | 15 | 0 |
| `oceania` | UN M49 region Oceania | 29 | 27 | 0 |
| `eu` | European Union member states | 27 | 27 | 0 |
| `schengen` | Schengen Area full members | 29 | 29 | 0 |
| `g20` | G20 member states (the EU and AU are not countries) | 19 | 33 | 6 |

The exact definition of each filter is in [`resources/regions.php`](resources/regions.php).
Geographic filters follow UN M49. Political ones are explicit membership lists, each
with the date it was last checked.

---

## Shortcuts cheat sheet

```php
use Enadstack\CountryData\Shortcuts\{Countries, Cities, Areas};
```

Every call is cached through `GeographyService`, so a repeated call runs no queries.
Names are matched loosely: `saudiArabia()`, `of('saudi-arabia')`, `of('Saudi Arabia')`,
`of('SAU')`, `of('sa')` and `of('السعودية')` all find the same country.

### Countries

```php
Countries::jordan();                    // Country (by common/official name, EN or AR)
Countries::unitedStates();
Countries::of('JO');                    // ISO-2, any case
Countries::of('JOR');                   // ISO-3
Countries::of(CountryCode::JO);         // enum
Countries::europe();                    // Collection: any region name works
Countries::northAmerica();
Countries::in(Region::Levant);          // same, explicit
Countries::all();                       // every active country
```

### Cities

```php
Cities::jordan();                       // cities of Jordan
Cities::of('SAU');                      // same, by any country reference
Cities::capitalOf('JO');                // City (null for Antarctica & co.)
Cities::named('JO', 'Irbid');           // one city, by English name
Cities::gulf();                         // cities in every country of a region
```

### Areas

```php
Areas::jordan();                        // all areas in Jordan
Areas::in('JO', 'Amman');               // AreaCollection for one city
Areas::in('JO', 'Amman')->neighborhoods();
Areas::in('JO', 'Amman')->districts();
Areas::in('JO', 'Amman')->ofType(AreaType::Street);
Areas::in('JO', 'Amman')->tree();       // districts, each with ->children
Areas::levant();                        // areas in every country of a region
```

### Errors

Unknown names throw an exception with a suggestion:

```php
Countries::jordna();
// CountryNotFoundException: Country [jordna] not found. Did you mean Countries::jordan()?

Countries::eurpoe();
// RegionNotFoundException: Region [eurpoe] not found. Did you mean Countries::europe()?

Cities::named('JO', 'Irbd');
// CityNotFoundException: City [Irbd] not found. Did you mean Irbid?
```

All three extend `GeographyNotFoundException` (an `InvalidArgumentException`) and expose
`$e->name` and `$e->suggestion`.

Every country and region is listed as an `@method` tag on the shortcut classes, so your
IDE autocompletes `Countries::` with all 266 names.

---

## Enums

```php
use Enadstack\CountryData\Enums\{CountryCode, Region, AreaType};

CountryCode::JO->model();          // Country row (cached)
CountryCode::JO->name();           // app locale: 'Jordan' or 'الأردن'
CountryCode::JO->name('ar');       // 'الأردن'
CountryCode::JO->flag();           // '🇯🇴'
CountryCode::JO->iso3();           // 'JOR'
CountryCode::fromAny('jor');       // CountryCode::JO (ISO-2 or ISO-3, any case)
CountryCode::tryFromAny('xx');     // null

Region::Europe->countries();       // Collection (cached)
Region::GCC->label();              // 'Gulf Cooperation Council'
Region::Europe->description();     // how membership is defined
Region::fromName('north-america'); // Region::NorthAmerica (any spelling)

AreaType::Neighborhood;            // district | neighborhood | street | zone | governorate
```

`name()`, `flag()` and `iso3()` work without a database.

---

## Models & scopes

`Country`, `City` and `Area` are ordinary Eloquent models in `Enadstack\CountryData\Models`.

### Country

```php
Country::europe()->get();                  // one named scope per region
Country::gulf()->active()->get();
Country::inRegion(Region::G20)->get();     // or ::inRegion('g20')
Country::code('JOR')->first();             // ISO-2 or ISO-3, any case
Country::search('أرد')->get();             // EN + AR, common + official names

$jordan->cities;                           // HasMany
$jordan->areas;                            // HasManyThrough cities
$jordan->capitalCity;                      // HasOne: eager-load with ::with('capitalCity')
$jordan->capital_city;                     // same (kept for v2 code)
$jordan->name;                             // name_ar when the app locale is 'ar'
```

Columns: `code`, `iso2`, `iso3`, `numeric_code`, `cioc`, `name_en`, `name_ar`,
`official_name_en`, `official_name_ar`, `flag`, `emoji`, `flag_png`, `flag_svg`,
`coat_of_arms_png`, `coat_of_arms_svg`, `google_maps_url`, `openstreet_maps_url`,
`currency_code`, `currency_name_en/ar`, `currency_symbol_en/ar`, `dial`, `capital`,
`region`, `continent`, `subregion`, `languages`, `timezones`, `tld`, `borders`,
`filters`, `latitude`, `longitude`, `population`, `area`, `is_active`.

### City

```php
City::byCountry('JO')->get();
City::capitals()->get();
$city->country;  $city->areas;  $city->name;
```

### Area

Areas form a **two-level tree**. A root has `parent_id = null`: a district, a zone, or
any area in a city that has not been broken down yet. A child (a neighborhood or
street) points at its district.

```php
Area::inCity('Amman', 'JO')->get();        // or a City model / id
Area::inCountry('JO')->get();              // ISO-2, ISO-3, CountryCode or Country
Area::ofType(AreaType::Street)->get();     // or ::ofType('street')
Area::neighborhoods()->get();
Area::districts()->get();
Area::roots()->get();                      // parent_id IS NULL
Area::nested()->get();                     // parent_id IS NOT NULL

$area->parent;   $area->children;   $area->city;
```

Every Area query returns an `AreaCollection`, which filters and shapes results without
another query: `->neighborhoods()`, `->districts()`, `->streets()`, `->zones()`,
`->ofType()`, `->roots()` and `->tree()`.

> Amman's districts and localities come from OpenStreetMap (Amman Governorate,
> retrieved 2026-08-27). Each neighborhood is assigned to the district whose centre is
> nearest, with well-known assignments pinned explicitly. A handful near district
> boundaries may need correcting. `street` is used for major named streets, which in
> Jordanian addresses locate a place as readily as a neighborhood does.

---

## Geography facade

The facade over `GeographyService`, which the shortcuts use too. Every read is cached.

```php
use Enadstack\CountryData\Facades\Geography;

// Countries
Geography::countries();                       // all active, ordered by name
Geography::countries('gulf');                 // by filter
Geography::country('JO');                     // by ISO-2
Geography::resolveCountry('Jordan');          // by anything (ISO-2/3, name, enum)
Geography::capitals();
Geography::countriesForSelect('ar', 'arab');  // [['value' => 'JO', 'label' => 'الأردن', 'flag' => '🇯🇴', 'dial' => '+962'], …]

// Cities
Geography::cities('JO');
Geography::city('JO', 'Amman');
Geography::capital('JO');
Geography::searchCities('am', 'JO');          // not cached (dynamic input)
Geography::citiesForSelect('JO', 'ar');

// Areas
Geography::areas($amman, 'neighborhood');
Geography::areaTree($amman);                  // roots with children eager-loaded
Geography::areaRoots($amman, 'district');
Geography::areaChildren($districtId);
Geography::areasForSelect($amman, 'en', 'neighborhood');
Geography::areasForSelectGrouped($amman, 'ar'); // for <optgroup> selects
Geography::searchAreas('down', $amman);

// Currency, timezones, dial codes, continents
Geography::currencyOf('JO')->in('ar')->symbol(); // CurrencyData value object → 'د.أ'
Geography::countriesByCurrency('EUR');
Geography::timezonesOf('US');                    // ['America/Adak', …]
Geography::timezoneForCity($amman);
Geography::dialCodeOf('JO');                     // '+962'
Geography::countryByDialCode('962');
Geography::continents();
Geography::countriesByContinent('Europe');
Geography::groupedByContinent();

// Geospatial (Haversine in PHP, so it works on every DB driver)
Geography::distanceBetween('JO', 'SA');          // km between country centres
Geography::citiesNear(31.95, 35.94, radiusKm: 100, countryCode: 'JO'); // each has ->distance
Geography::sortCitiesByDistance(31.95, 35.94, 'JO');
Geography::areasNear(31.95, 35.94, $amman, radiusKm: 5);
```

**Dial codes.** NANP territories carry their full prefix (`+1264` Anguilla, `+1876`
Jamaica), so pasting an international number selects the right country. The US, Canada,
Puerto Rico and the Dominican Republic share `+1`.

---

## Validation rules

```php
use Enadstack\CountryData\Rules\{ValidCountryCode, ValidCityForCountry, ValidAreaForCity, ValidPhoneNumber};

$request->validate([
    'country_code' => ['required', new ValidCountryCode()],               // active ISO-2
    'country_code' => ['required', new ValidCountryCode(filter: 'gcc')],  // within a filter
    'city_id'      => ['required', new ValidCityForCountry($request->country_code)],
    'area_id'      => ['required', new ValidAreaForCity($request->city_id)],
    'area_id'      => ['required', new ValidAreaForCity($request->city_id, type: 'neighborhood')],

    // Phone: normalise first ("0772 432 330" → "772432330")
    'phone_country_code' => ['nullable', 'required_with:phone', new ValidCountryCode],
    'phone'              => ['nullable', new ValidPhoneNumber('phone_country_code')],
]);

$request->merge(['phone' => ValidPhoneNumber::normalize($request->input('phone'))]);
```

`ValidPhoneNumber` accepts digits only, needs at least 6 of them, and checks that the dial
code plus the number fits E.164 (15 digits).

---

## Frontend components

### React PhoneInput

A searchable dial-code picker (flag + `+962`) joined to a national-number input. Pasting
`+962 772 432 330` selects the country automatically.

```php
return Inertia::render('users/create', [
    'phoneCountries' => Geography::phoneCountriesForSelect(app()->getLocale()),
]);
```

```ts
// vite.config.ts → resolve.alias
'@country-data': path.resolve(__dirname, 'vendor/enadstack/laravel-country-data/src/resources/js'),
```

```css
/* app.css (Tailwind v4) */
@source '../../vendor/enadstack/laravel-country-data/src/resources/js/react';
```

```tsx
import { PhoneInput } from '@country-data/react/PhoneInput';

<PhoneInput
    countries={phoneCountries}
    countryCode={data.phone_country_code}
    phone={data.phone}
    defaultCountry="JO"
    invalid={!!errors.phone}
    onChange={({ countryCode, phone }) => setData((d) => ({ ...d, phone_country_code: countryCode, phone }))}
/>
```

| Prop | Type | Description |
|---|---|---|
| `countries` | `PhoneCountryOption[]` | Output of `Geography::phoneCountriesForSelect()` |
| `countryCode` / `phone` | `string \| null` | Controlled values (ISO-2 / national digits) |
| `onChange` | `({ countryCode, dial, phone, e164 }) => void` | Fires when the country or number changes |
| `defaultCountry` | `string` | Used while `countryCode` is empty (or set `country-data.phone.default_country`) |
| `countryCodeName` / `phoneName` | `string` | Input names for native posts |
| `invalid`, `disabled`, `required`, `placeholder`, `searchPlaceholder`, `emptyText`, `id`, `className` | | Presentation |

It depends only on React and Tailwind classes that use shadcn/ui theme tokens.

### Blade, Vue and React selects

```bash
php artisan country-data:publish-component --component=country-select --type=blade   # or vue / react
php artisan country-data:publish-component --component=phone-input    --type=react   # or blade / vue
```

### Livewire cascading select

Livewire 3 only. The component registers itself when Livewire is installed.

```blade
<livewire:geography-select locale="ar" filter="gcc" :show-areas="true" :required="true"
    country-field="country_code" city-field="city_id" area-field="area_id" />
```

| Prop | Default | Description |
|---|---|---|
| `locale` | `'en'` | `en` or `ar` (RTL-aware) |
| `filter` | `null` | Any region filter |
| `showAreas` | `true` | Show the third (area) dropdown |
| `required` | `false` | Mark every select required |
| `countryField` / `cityField` / `areaField` | `country_code` / `city_id` / `area_id` | Input names |

Emits `country-selected`, `city-selected` and `area-selected`. Customise the view with
`php artisan vendor:publish --tag=country-data-livewire`.

---

## REST API

Disabled by default. Enable it with `COUNTRY_DATA_API=true` (prefix:
`COUNTRY_DATA_API_PREFIX`, default `api/geography`).

| Method | URL | Description |
|---|---|---|
| GET | `/countries` | All countries (`?filter=gcc&locale=ar`) |
| GET | `/countries/{code}` | One country |
| GET | `/countries/{code}/cities` | Its cities |
| GET | `/cities/{id}` | One city |
| GET | `/cities/{id}/areas` | Its areas (`?type=neighborhood`) |
| GET | `/cities/{id}/areas/tree` | Districts with their children |
| GET | `/areas/{id}/children` | Children of one district |
| GET | `/currencies` · `/currencies/{code}/countries` | Currencies and who uses them |
| GET | `/continents` · `/continents/{name}/countries` | Continents |
| GET | `/near/cities` | `?lat=&lng=&radius=&country=` |
| GET | `/search/cities` · `/search/areas` | `?q=amman&country=JO` · `?q=down&city_id=3` |

Responses look like `{ "data": [...], "meta": { "total": 250 } }`.

---

## Caching

Every read in `GeographyService` (and so in every shortcut, `CountryCode::model()` and
`Region::countries()`) is cached in your default cache store. Results are also memoised
for the rest of the process, so a repeated call runs no queries.

```php
// config/country-data.php
'cache' => [
    'enabled' => env('COUNTRY_DATA_CACHE', true),
    'ttl'     => env('COUNTRY_DATA_CACHE_TTL', 86400),
    'prefix'  => env('COUNTRY_DATA_CACHE_PREFIX', 'geography'),
],
```

Flush it with `Geography::flush()` or `php artisan country-data:cache-clear`; seeding
flushes it automatically. If your app restricts `cache.serializable_classes`, lookups
fall back to per-process memoisation, so they still work correctly.

---

## Artisan commands

| Command | Description |
|---|---|
| `country-data:setup` | Interactive migrate + selective seed (see [Installation](#installation)) |
| `country-data:cache-clear` | Flush the geography cache |
| `country-data:stats` | Seeded counts per continent / area type, cache status |
| `country-data:configure` | Publish a config-based dataset (`all` or any filter) as `config/countries.php` |
| `country-data:publish-component` | Publish a Blade/Vue/React component |

---

## Configuration

```php
// config/country-data.php
return [
    'source'   => 'all',       // dataset for config/countries.php: 'all' or any filter
    'cache'    => [/* see Caching */],
    'api'      => ['enabled' => env('COUNTRY_DATA_API', false), 'prefix' => 'api/geography', 'middleware' => ['api']],
    'livewire' => ['register' => true, 'locale' => 'en', 'show_areas' => true],
    'frontend' => ['component' => 'none', 'publish_components' => true],
    'phone'    => ['default_country' => env('COUNTRY_DATA_DEFAULT_PHONE_COUNTRY')],
];
```

### Deprecated: `CountryData`

The v1 array API still works throughout 3.x but is deprecated. When the countries table
is seeded, it reads through `GeographyService`; otherwise it reads `config/countries.php`.

```php
CountryData::getByCode('JO');        // → Countries::of('JO')
CountryData::getGulfCountries();     // → Countries::gulf()
CountryData::getName('SA', 'ar');    // → CountryCode::SA->name('ar')
```

See [UPGRADE.md](UPGRADE.md) for the full mapping.

---

## Where the data comes from

- **The 22 Arab League countries, their 136 cities and all areas** are hand-curated.
  They live in `resources/curated/` and are never overwritten.
- **Everything else** is generated by `scripts/build-data.php` from pinned copies of open
  datasets in `resources/source/`:
  - [mledoze/countries](https://github.com/mledoze/countries) (ODbL)
  - [Unicode CLDR](https://github.com/unicode-org/cldr-json) `ar`: Arabic names
  - [UN M49](https://unstats.un.org/unsd/methodology/m49/): regions
  - the [IANA tz database](https://www.iana.org/time-zones): timezones
  - [Wikidata](https://www.wikidata.org) (CC0): population and capitals

  The same build generates `data/*.json`, `config/countries.php`, `config/source/*`,
  the `Region` and `CountryCode` enums, the region scopes and the shortcut docblocks.

```bash
composer data:fetch    # refresh resources/source (network); review the diff
composer data:build    # regenerate everything, offline and deterministically
php scripts/build-data.php --check   # fails if anything generated is stale (also a test)
```

Licences are listed in [`data/ATTRIBUTION.md`](data/ATTRIBUTION.md). The package code is
MIT. The generated data files contain ODbL-licensed data from mledoze/countries.

---

## Testing

```bash
composer test            # all tests (SQLite in-memory, no external DB)
composer test:unit
composer test:feature
```

CI runs the suite on PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12 / 13.

## License

MIT for the code. The data files are covered by the licences in
[`data/ATTRIBUTION.md`](data/ATTRIBUTION.md). Built by [@enadabuzaid](https://github.com/enadabuzaid).
