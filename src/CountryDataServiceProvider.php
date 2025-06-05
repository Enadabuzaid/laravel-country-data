<?php

namespace Enad\CountryData;

use Illuminate\Support\ServiceProvider;

class CountryDataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/countries.php' => config_path('countries.php'),
                __DIR__ . '/../config/country-data.php' => config_path('country-data.php'),
            ], 'country-data');


            $this->commands([
                \Enad\CountryData\Commands\ConfigureCountryData::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/countries.php',
            'countries'
        );

        $this->app->singleton('country-data', function () {
            return new \Enad\CountryData\CountryData();
        });
    }
}