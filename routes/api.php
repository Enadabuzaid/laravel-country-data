<?php

use Illuminate\Support\Facades\Route;
use Enadstack\CountryData\Http\Controllers\GeographyController;

/*
|--------------------------------------------------------------------------
| Geography API Routes
|--------------------------------------------------------------------------
| These routes are registered only when config('country-data.api.enabled')
| is true. The prefix and middleware are applied by the service provider.
|
| Enable in .env:
|   COUNTRY_DATA_API=true
|   COUNTRY_DATA_API_PREFIX=api/geography  (default)
*/

// Countries
Route::get('countries',                   [GeographyController::class, 'countries']);
Route::get('countries/{code}',            [GeographyController::class, 'country']);
Route::get('countries/{code}/cities',     [GeographyController::class, 'countryCities']);

// Cities
Route::get('cities/{id}',                 [GeographyController::class, 'city']);
Route::get('cities/{id}/areas',           [GeographyController::class, 'cityAreas']);

// Currency
Route::get('currencies',                              [GeographyController::class, 'currencies']);
Route::get('currencies/{code}/countries',             [GeographyController::class, 'currencyCountries']);

// Continents
Route::get('continents',                              [GeographyController::class, 'continents']);
Route::get('continents/{continent}/countries',        [GeographyController::class, 'continentCountries']);

// Geospatial
Route::get('near/cities',                 [GeographyController::class, 'citiesNear']);

// Search
Route::get('search/cities',               [GeographyController::class, 'searchCities']);
Route::get('search/areas',                [GeographyController::class, 'searchAreas']);
