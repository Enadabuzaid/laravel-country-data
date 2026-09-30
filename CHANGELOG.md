# Changelog

All notable changes to `enadstack/laravel-country-data` are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the
project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
