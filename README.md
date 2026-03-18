# Laravel Country Data

A clean and reusable **Laravel package** for accessing comprehensive country information — including localized names, flags, dial codes, currencies, timezones, and more — all stored in a `config/countries.php` file for maximum performance and cacheability.

Built with multilingual support, region filters, and helper methods — ideal for dropdowns, admin panels, and international applications.

---

## Features

- Publishable country list as `config/countries.php`
- Multilingual support (English and Arabic)
- Region filters: **Arab**, **Gulf**, **Muslim-majority**, **Continents**, and more
- Dial codes with emoji flags and SVG flag URLs
- Search countries by name, code, or filter
- Helper formatters for frontend select menus
- Laravel-native config access — works with `config:cache`
- Artisan commands for setup and frontend component publishing
- Frontend components for **Blade**, **Vue 3**, and **React**

---

## Requirements

- PHP `^8.2`
- Laravel `^11.0` or `^12.0`

---

## Installation

Install the package via Composer:

```bash
composer require enadabuzaid/country-data
```

Publish the config files:

```bash
php artisan vendor:publish --tag=country-data
```

This copies two files into your app's `config/` directory:

| File | Purpose |
|------|---------|
| `config/countries.php` | The full country dataset |
| `config/country-data.php` | Package settings (source, frontend) |

---

## Quick Setup

### Step 1 — Choose your country dataset

Run the interactive setup command to choose which country list to use:

```bash
php artisan country-data:configure
```

You will be prompted to choose:

```
Which country list do you want to use?
  [0] All Countries
  [1] Arab Countries Only
  [2] Gulf Countries Only
  [3] European Countries Only
```

This updates `config/country-data.php` and copies the correct dataset into `config/countries.php`.

You can also set this manually in `config/country-data.php`:

```php
return [
    'source' => 'all', // Options: 'all', 'arab', 'gulf', 'europe'
];
```

### Step 2 — (Optional) Publish a frontend component

```bash
php artisan country-data:publish-component
```

You will be prompted to choose:

```
Which frontend type to publish?
  [0] blade
  [1] vue
  [2] react
```

Published locations:

| Type | Destination |
|------|-------------|
| Blade | `resources/views/components/country-select.blade.php` |
| Vue | `resources/js/components/custom/CountrySelect.vue` |
| React | `resources/js/components/custom/CountrySelect.jsx` |

---

## Usage

### Via Facade

```php
use CountryData;

CountryData::getArabCountries();
CountryData::getByCode('SA');
```

### Via Helper (config)

```php
$countries = config('countries');
```

---

## Available Methods

### `getArabCountries(): array`

Returns all countries tagged with the `arab` filter.

```php
CountryData::getArabCountries();
```

---

### `getGulfCountries(): array`

Returns the 7 Gulf Cooperation Council countries (SA, AE, KW, QA, OM, BH, IQ).

```php
CountryData::getGulfCountries();
```

---

### `getByFilter(string $filter): array`

Returns countries matching a given filter tag.

```php
CountryData::getByFilter('muslim-majority');
CountryData::getByFilter('middle-east');
CountryData::getByFilter('asia');
```

Available filter values:

| Filter | Description |
|--------|-------------|
| `arab` | Arab League countries |
| `gulf` | Gulf Cooperation Council countries |
| `muslim-majority` | Countries with a Muslim-majority population |
| `middle-east` | Middle Eastern countries |
| `asia` | Asian countries |
| `africa` | African countries |
| `europe` | European countries |
| `north-america` | North American countries |

---

### `getByCode(string $code): ?array`

Returns full country data by ISO 2-letter code. Returns `null` if not found.

```php
CountryData::getByCode('JO');
// Returns full array for Jordan
```

---

### `searchByName(string $name, string $locale = 'en'): ?array`

Finds a country by its common or official name. Case-insensitive. Returns `null` if not found.

```php
CountryData::searchByName('Jordan');
CountryData::searchByName('الأردن', 'ar');
CountryData::searchByName('المملكة العربية السعودية', 'ar');
```

---

### `getName(string $code, string $locale = 'en'): ?string`

Returns the common name of a country in the specified locale.

```php
CountryData::getName('SA');       // 'Saudi Arabia'
CountryData::getName('SA', 'ar'); // 'السعودية'
```

---

### `getFlag(string $code): ?string`

Returns the flag emoji for a country.

```php
CountryData::getFlag('JO'); // '🇯🇴'
CountryData::getFlag('AE'); // '🇦🇪'
```

---

### `getDialCodes(bool $withFlag = false): array`

Returns an array of dial codes. Pass `true` to include the flag emoji and SVG URL.

```php
CountryData::getDialCodes();
// [['code' => 'JO', 'dial' => '+962'], ...]

CountryData::getDialCodes(true);
// [['code' => 'JO', 'dial' => '+962', 'flag' => '🇯🇴', 'flag_url' => 'https://flagcdn.com/jo.svg'], ...]
```

---

### `getSelectOptions(string $locale = 'en'): array`

Returns a formatted array suitable for `<select>` dropdowns or frontend components.

```php
CountryData::getSelectOptions();
// [['label' => 'Jordan', 'value' => 'JO'], ...]

CountryData::getSelectOptions('ar');
// [['label' => 'الأردن', 'value' => 'JO'], ...]
```

---

## Country Data Structure

Each country in `config/countries.php` has the following shape:

```php
[
    'code'        => 'JO',
    'iso2'        => 'JO',
    'iso3'        => 'JOR',
    'numericCode' => '400',
    'cca2'        => 'JO',
    'ccn3'        => '400',
    'cioc'        => 'JOR',
    'flag'        => '🇯🇴',
    'emoji'       => '🇯🇴',

    'names' => [
        'common'   => ['en' => 'Jordan',  'ar' => 'الأردن'],
        'official' => ['en' => 'Hashemite Kingdom of Jordan', 'ar' => 'المملكة الأردنية الهاشمية'],
    ],

    'flags' => [
        'png' => 'https://flagcdn.com/w320/jo.png',
        'svg' => 'https://flagcdn.com/jo.svg',
    ],

    'coatOfArms' => [
        'png' => '...',
        'svg' => '...',
    ],

    'maps' => [
        'googleMaps'     => 'https://goo.gl/maps/...',
        'openStreetMaps' => 'https://www.openstreetmap.org/...',
    ],

    'currency' => [
        'code'   => 'JOD',
        'name'   => ['en' => 'Jordanian dinar',  'ar' => 'دينار أردني'],
        'symbol' => ['en' => 'JD', 'ar' => 'د.أ'],
    ],

    'dial'      => '+962',
    'capital'   => 'Amman',
    'region'    => 'Asia',
    'continent' => 'Asia',
    'subregion' => 'Western Asia',

    'languages' => ['ara'],
    'timezones' => ['Asia/Amman'],
    'tld'       => ['.jo'],
    'borders'   => ['IRQ', 'ISR', 'PSE', 'SAU', 'SYR'],

    'geo' => [
        'latitude'  => 31.0,
        'longitude' => 36.0,
    ],

    'population' => 10203140,
    'area'       => 89342.0,

    'filters' => ['arab', 'muslim-majority', 'middle-east', 'asia'],
]
```

---

## Frontend Components

### Blade

After publishing with `php artisan country-data:publish-component`, use the component in your views:

```blade
<x-country-select
    id="country"
    name="country"
    label="Select Country"
    :countries="CountryData::getSelectOptions('en')"
    :preferred="['JO', 'SA', 'AE']"
    :rtl="false"
    :withFlag="true"
/>
```

For a dial code + phone number input:

```blade
<x-phone-code-select
    id="phone"
    label="Phone Number"
    :countries="CountryData::getDialCodes(true)"
    codeName="dial_code"
    inputName="phone"
    :withFlag="true"
/>
```

---

### Vue 3

```vue
<script setup>
import CountrySelect from '@/components/custom/CountrySelect.vue'

const countries = // fetch from your Laravel API or pass via props
</script>

<template>
  <CountrySelect
    :countries="countries"
    v-model="selectedCountry"
    label="Select Country"
    placeholder="Search..."
    :preferred="['JO', 'SA', 'AE']"
    :withFlag="true"
    :rtl="false"
  />
</template>
```

For the phone input:

```vue
<PhoneCodeSelect
  :countries="countries"
  label="Phone Number"
  :withFlag="true"
  @input="val => form.phone = val"
/>
```

The `@input` event emits `{ dial: '+962', number: '791234567' }`.

---

### React

```jsx
import CountrySelect from '@/components/custom/CountrySelect'

function MyForm() {
  return (
    <CountrySelect
      countries={countries}
      value={selectedCountry}
      onChange={(country) => setSelectedCountry(country)}
      label="Select Country"
      placeholder="Search..."
      preferred={['JO', 'SA', 'AE']}
      withFlag={true}
      rtl={false}
    />
  )
}
```

For the phone input:

```jsx
<PhoneCodeSelect
  countries={countries}
  label="Phone Number"
  withFlag={true}
  onChange={({ dial, number }) => setPhone({ dial, number })}
/>
```

---

## Artisan Commands Reference

| Command | Description |
|---------|-------------|
| `php artisan country-data:configure` | Interactively select a country data source |
| `php artisan country-data:publish-component` | Publish a frontend component (Blade, Vue, React) |

---

## Config Reference

### `config/country-data.php`

```php
return [
    // Which dataset to load: 'all', 'arab', 'gulf', 'europe'
    'source' => 'all',

    'frontend' => [
        // Which frontend type is in use: 'none', 'blade', 'vue', 'react'
        'component' => 'none',

        // Whether to publish frontend component files
        'publish_components' => true,
    ],
];
```

---

## Performance

This package uses Laravel's config system, which means:

- All country data is loaded once per request
- You can cache it with `php artisan config:cache` for zero-overhead lookups
- No database queries are made

---

## File Structure

```
config/
├── countries.php               # Published country dataset (active)
├── country-data.php            # Package settings
└── source/
    ├── countries-all.php       # All countries (44+)
    ├── countries-arab.php      # Arab countries only
    ├── countries-gulf.php      # Gulf countries only (7)
    └── countries-europe.php    # European countries (coming soon)

src/
├── CountryData.php             # Core class with all static methods
├── CountryDataServiceProvider.php
├── Facades/
│   └── CountryData.php         # Laravel Facade
├── Commands/
│   ├── ConfigureCountryData.php
│   └── PublishFrontendComponent.php
└── resources/
    ├── views/components/
    │   ├── country-select.blade.php
    │   └── phone-code-select.blade.php
    └── js/
        ├── vue/
        │   ├── CountrySelect.vue
        │   └── PhoneCodeSelect.vue
        └── react/
            ├── CountrySelect.jsx
            └── PhoneCodeSelect.jsx
```

---

## License

MIT — built by [@enadabuzaid](https://github.com/enadabuzaid)
