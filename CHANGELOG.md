# Changelog

All notable changes to `enadstack/laravel-country-data` are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the
project uses [Semantic Versioning](https://semver.org/).

## [Unreleased] — 3.0.0

### Added — shortcuts and enums
- Static, cached shortcut classes in `Enadstack\CountryData\Shortcuts`:
  - `Countries::jordan()`, `Countries::of('JO' | 'JOR' | CountryCode::JO | 'Jordan' | 'الأردن')`,
    `Countries::europe()` (any region), `Countries::in(Region::Levant)`, `Countries::all()`.
  - `Cities::jordan()`, `Cities::of($country)`, `Cities::capitalOf($country)`,
    `Cities::named($country, 'Irbid')`, `Cities::gulf()`.
  - `Areas::jordan()`, `Areas::in('JO', 'Amman')`, `Areas::levant()`.
  - Unknown names throw `CountryNotFoundException`, `RegionNotFoundException` or
    `CityNotFoundException` (all extend `GeographyNotFoundException`) with a
    "did you mean …?" suggestion.
  - Generated `@method` docblocks for every country and region, for IDE autocompletion.
- Enums in `Enadstack\CountryData\Enums`: `Region` (every filter), `CountryCode`
  (all 250 ISO-2 codes, with `model()`, `name($locale)`, `flag()`, `iso3()`,
  `fromAny('jor')`) and `AreaType`.
- `AreaCollection`, returned by every Area query: `->districts()`, `->neighborhoods()`,
  `->streets()`, `->zones()`, `->ofType()`, `->roots()` and `->tree()`, all in memory.
- Model scopes:
  - `Country`: `inRegion(Region|string)`, one named scope per region
    (`Country::europe()`, `::gulf()`, `::levant()`, …), `code()` (ISO-2 / ISO-3,
    any case) and `search()` (English and Arabic, common and official names).
  - `Area`: `ofType()` accepts `AreaType`, plus `neighborhoods()`, `inCity()` and
    `inCountry()`.
- `GeographyService`: `resolveCountry()`, `countryIndex()`, `countriesIn()`,
  `citiesIn()`, `areasInCountry()`, `areasInRegion()`, `isSeeded()`.

### Deprecated
- `CountryData` and its facade. When the countries table is seeded, every method now
  reads through `GeographyService`; without it, they fall back to the bundled config,
  as before. The methods keep working throughout 3.x. See UPGRADE.md.

### Added — full world data
- All 250 ISO 3166-1 countries and territories (was 22). The 22 curated Arab League
  countries are unchanged; the other 228 are generated.
- A capital city for every generated country that has one: `cities.json` goes from
  136 to 359 rows. The 136 curated cities and all 276 areas (207 in Amman) are unchanged.
- New region filters: `gcc`, `levant`, `maghreb`, `europe`, `north-america`,
  `south-america`, `oceania`, `eu`, `schengen`, `g20`. Each is defined and documented
  in `resources/regions.php`. `asia` / `africa` now cover every country in the
  UN M49 region.
- `config/source/countries-{filter}.php` for every filter, so
  `country-data:setup --source=levant` (or any filter) works. `europe` was empty before.
- `scripts/fetch-sources.php` pins upstream data (mledoze/countries, CLDR `ar`,
  UN M49, IANA tzdb, Wikidata, ISO 639-3) under `resources/source/`.
  `scripts/build-data.php` regenerates `data/*.json`, `config/countries.php` and
  `config/source/*` from it offline (`composer data:build`; `--check` fails when
  generated files are stale).
- `data/ATTRIBUTION.md`: sources and licences. The generated data files are
  derived from mledoze/countries and fall under the ODbL 1.0.

### Changed
- `data/countries.json` is now the single source of truth. `config/countries.php`
  and `config/source/*` are generated from it, which changes a few values seen
  through the config-based `CountryData` class. They now match the database:
  - Arabic names: SA `المملكة العربية السعودية` (was `السعودية`), AE
    `الإمارات العربية المتحدة` (was `الإمارات`), OM `عُمان` / `سلطنة عُمان`
    (was `عمان` / `سلطنة عمان`); PS currency `شيكل إسرائيلي جديد`.
  - Iraq is tagged `gulf` in config too, so `CountryData::getGulfCountries()` returns
    7 countries (it returned 6). The DB always had 7. Use the new `gcc` filter for
    the six GCC member states.
  - Some currency symbols, borders, coordinates and populations were aligned with
    the JSON values in the same way.
- Curated countries gain the new region tags only (appended after their existing
  filters). For example, JO gains `levant`, and SA gains `gcc` and `g20`.

## [2.3.0]

### Fixed
- `Country::$capital_city` no longer runs a query on every access (N+1). It now reads
  through a new `capitalCity()` `HasOne` relation, so `Country::with('capitalCity')`
  loads every capital in one query. The `capital_city` attribute keeps working.
- Removed the `is_capital` cast from `Country`; it is not a column on `countries`.

### Added
- `Country::capitalCity(): HasOne` relation.
- GitHub Actions test matrix: PHP 8.2 / 8.3 / 8.4 × Laravel 11 / 12 / 13
  (Laravel 13 runs on PHP 8.3+ only, as Testbench 11 requires).
- `orchestra/testbench ^11.0` in `require-dev`, so Laravel 13 is actually tested.

### Changed
- `composer.json`: removed the hard-coded `version` (Packagist reads git tags),
  removed `minimum-stability: dev`, corrected the author email.

### Removed
- The empty `src/Commands/InstallCountryData.php` file.

## [2.2.0]
- React `PhoneInput` with searchable dial codes.
- Geography lookups keep working when `cache.serializable_classes` forbids objects.

## [2.1.0]
- Full Amman coverage with a district → neighborhood hierarchy (207 areas).

## [2.0.1]
- Laravel 13 support.

## [2.0.0]
- DB-backed geography (countries, cities, areas), caching, REST API, Livewire and helpers.
