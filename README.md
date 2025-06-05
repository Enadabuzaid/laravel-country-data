# Laravel Country Data

A clean and reusable **Laravel package** for accessing comprehensive country information — including localized names, flags, dial codes, currencies, timezones, and more — all stored in a `config/countries.php` file for maximum performance.

> 🇯🇴 Built with multilingual support, region filters, and helper methods — ideal for dropdowns, admin panels, and international applications.

---

## ✅ Features

- 📦 Publishable country list as `config/countries.php`
- 🌐 Multilingual support (English and Arabic)
- 🌍 Filters for **Arab**, **Gulf**, **Muslim-majority**, **Continents**, and more
- 📞 Dial codes with emoji flags and SVG URLs
- 🔍 Search countries by name, code, or filter
- 🧩 Helper formatters for frontend select menus
- ⚡ Laravel-native config access (uses `config()` and `config:cache`)

---

## 🚀 Installation

Add the package locally:
```bash
composer require enad/country-data
```

Publish the config file:
```bash
php artisan vendor:publish --tag=country-data
```

This will copy `countries.php` into your Laravel app's config folder:
```php
config('countries');
```

---

## 📘 Documentation

### 🔄 Available Static Methods

```php
use CountryData; // via Facade
```

| Method | Description |
|--------|-------------|
| `getArabCountries()` | Returns all Arab countries |
| `getGulfCountries()` | Returns Gulf Cooperation Council countries |
| `getByFilter('muslim-majority')` | Returns countries by custom filter |
| `getByCode('SA')` | Gets full country data by code |
| `searchByName('Jordan')` | Finds country by name in English or Arabic |
| `getName('JO', 'ar')` | Returns the name of a country in a specific locale |
| `getFlag('JO')` | Returns the flag emoji |
| `getDialCodes(true)` | Returns list of dial codes, optionally with flags |
| `getSelectOptions('ar')` | Returns array for select dropdowns (label + value) |

### 📞 Example: Get Dial Codes with Flags
```php
CountryData::getDialCodes(true);
```
Returns:
```php
[
  ['code' => 'JO', 'dial' => '+962', 'flag' => '🇯🇴', 'flag_url' => 'https://flagcdn.com/jo.svg'],
  ...
]
```

### 🌍 Example: Get Arab Countries
```php
CountryData::getArabCountries();
```

### 🔍 Example: Search by Arabic Name
```php
CountryData::searchByName('السعودية', 'ar');
```

### 🌐 Example: Select Options for Vue/React
```php
CountryData::getSelectOptions('en');
// => [ ['label' => 'Jordan', 'value' => 'JO'], ... ]
```

---

## 🧠 Extensible Filters

Available `filters` on countries include:
- `arab`
- `gulf`
- `muslim-majority`
- `middle-east`
- `asia`, `africa`, `europe`, `north-america`, etc.

---

## 📂 File Structure
```
config/
└── countries.php       # Published config data
src/
├── CountryData.php     # All logic methods
├── Facades/CountryData.php
└── CountryDataServiceProvider.php
```

---

## 🧪 Coming Soon
- `getByContinent('Asia')`
- `getSelectOptionsWithFlags()`
- `country-data:export-json` command
- Optional database support

---

## 📄 License
MIT — built by [@enadabuzaid](https://github.com/enadabuzaid)
