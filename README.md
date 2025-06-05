# Laravel Country Data

This package provides a simple way to work with a set of country information that can be stored as JSON or imported into your database. The repository currently includes:

- **`data/countries.json`** – a JSON file containing a list of countries with codes and names.
- **`src/Commands/InstallCountryData.php`** – a stub command that can be extended to import the country list into your application.

The package requires PHP 8.2 and is intended for use with Laravel 11 or 12. You can include it in your Laravel project via Composer and register the service provider `Enad\CountryData\CountryDataServiceProvider`.

This project is still in its early stages, so feel free to modify it or extend the command to fit your own application's needs.
