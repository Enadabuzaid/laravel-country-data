# Upgrade guide

## From 2.x to 3.0

3.0 ships the whole world (250 countries instead of 22), static shortcuts, enums and
scopes. It also deprecates the config-based `CountryData` class. No public method was
removed or renamed. The changes below are mostly about **how much data** your app sees.

### 1. Flush the cache after deploying

```bash
php artisan country-data:cache-clear
```

Some cached values change type in 3.0: area results are now an `AreaCollection`. The
shortcuts tolerate old entries, but a flush guarantees fresh data.

### 2. "All countries" now means 250, not 22

**Affects:** `country-data:setup --all`, `Geography::countries()`, `Countries::all()`,
`GET /countries`, `<livewire:geography-select />` and `ValidCountryCode()` without a filter.

Existing rows are kept. Re-running the seeder adds the new countries and one capital city
each, and appends the new region tags to the 22 existing countries' `filters` (JO gains
`levant`, SA gains `gcc` and `g20`, and so on).

If your app should keep working with Arab countries only:

```bash
php artisan country-data:setup --seed --source=arab      # seed only the 22
```

```php
Geography::countries('arab');                             // or Countries::arab()
new ValidCountryCode(filter: 'arab');
```

```blade
<livewire:geography-select filter="arab" />
```

If the other countries are already seeded, deactivate them rather than deleting rows
that your records may reference:

```php
Country::whereJsonDoesntContain('filters', 'arab')->update(['is_active' => false]);
Geography::flush();
```

### 3. `config/countries.php` is generated and now has 250 entries

`config('countries')` and `CountryData` read this file when the database isn't seeded.
It now contains every country, and its values match `data/countries.json` (the single
source of truth):

| | 2.x config | 3.0 |
|---|---|---|
| Entries | 22 | 250 |
| SA `names.common.ar` | السعودية | المملكة العربية السعودية |
| AE `names.common.ar` | الإمارات | الإمارات العربية المتحدة |
| OM `names.common.ar` / official | عمان / سلطنة عمان | عُمان / سلطنة عُمان |
| PS currency (ar) | شيكل جديد | شيكل إسرائيلي جديد |
| `getGulfCountries()` | 6 (no Iraq) | 7 (Iraq included, same as the DB always had) |

A few currency symbols, borders and coordinates were aligned in the same way. The
database values did not change.

To keep a 22-country config, publish the Arab dataset:

```bash
php artisan country-data:configure      # choose "Arab Countries Only"
```

For strict GCC membership (without Iraq) use the new `gcc` filter:
`Countries::gcc()`, `Country::gcc()`, `Geography::countries('gcc')`.

### 4. `CountryData` is deprecated and reads the database when it is seeded

The methods still work throughout 3.x. What changed is where they read from: when the
`countries` table has rows, `CountryData` reads it through `GeographyService` (cached,
active rows only). Only without a seeded table does it read `config/countries.php`.
**If you customised a published `config/countries.php` and also seeded the database,
`CountryData` now returns the database values.** Edit the rows instead, then run
`Geography::flush()`.

| 2.x | 3.0 |
|---|---|
| `CountryData::getByCode('JO')` | `Countries::of('JO')` or `Geography::country('JO')` |
| `CountryData::getArabCountries()` | `Countries::arab()` or `Country::arab()->get()` |
| `CountryData::getGulfCountries()` | `Countries::gulf()` (or `Countries::gcc()`) |
| `CountryData::getByFilter($f)` | `Countries::in($f)` or `Country::inRegion($f)->get()` |
| `CountryData::searchByName('Jordan')` | `Countries::of('Jordan')` or `Country::search('Jordan')` |
| `CountryData::getName('SA', 'ar')` | `CountryCode::SA->name('ar')` or `$country->name` |
| `CountryData::getFlag('JO')` | `CountryCode::JO->flag()` or `$country->flag` |
| `CountryData::getDialCodes()` | `Geography::phoneCountriesForSelect()` |
| `CountryData::getSelectOptions('ar')` | `Geography::countriesForSelect('ar')` |

The new API returns models and collections instead of arrays.

### 5. Smaller changes you might notice

- **`Country::$capital_city`** now reads through the new `capitalCity()` relation. It
  runs at most one query per model and can be eager-loaded:
  `Country::with('capitalCity')->get()`.
- **`Country` no longer casts `is_capital`**. It was never a column on `countries`, so
  the value was always `null`.
- **Area queries return `Enadstack\CountryData\Support\AreaCollection`**, a subclass of
  Eloquent's `Collection`, so existing code keeps working. `Area::ofType()` also
  accepts the `AreaType` enum.
- **New named scopes on `Country`**: `europe()`, `gulf()`, `gcc()`, `levant()`,
  `maghreb()`, `middleEast()`, `muslimMajority()`, `africa()`, `asia()`,
  `northAmerica()`, `southAmerica()`, `oceania()`, `eu()`, `schengen()`, `g20()`,
  `arab()`, plus `inRegion()`, `code()` and `search()`. If you extended `Country`
  with local scopes of the same names, yours take precedence.
- **The `version` field was removed from `composer.json`**. Versions come from git tags.
- **Data licence.** The generated data files contain data from mledoze/countries under
  the ODbL 1.0; see `data/ATTRIBUTION.md`. The code remains MIT.
