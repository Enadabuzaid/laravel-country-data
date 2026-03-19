<?php

namespace Enadstack\CountryData;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Enadstack\CountryData\Commands\CacheClear;
use Enadstack\CountryData\Commands\Stats;
use Enadstack\CountryData\Commands\ConfigureCountryData;
use Enadstack\CountryData\Commands\PublishFrontendComponent;
use Enadstack\CountryData\Commands\GeographySetup;
use Enadstack\CountryData\Services\GeographyService;

class CountryDataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ── Views ─────────────────────────────────────────────────────────────
        $this->loadViewsFrom(__DIR__ . '/../src/resources/views', 'country-data');

        // ── Migrations ────────────────────────────────────────────────────────
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // ── API routes (optional — only when enabled in config) ──────────────
        if (config('country-data.api.enabled', false)) {
            Route::prefix(config('country-data.api.prefix', 'api/geography'))
                ->middleware(config('country-data.api.middleware', ['api']))
                ->group(__DIR__ . '/../routes/api.php');
        }

        // ── Livewire component (optional — only when Livewire is installed) ───
        if (class_exists(\Livewire\LivewireManager::class)
            && config('country-data.livewire.register', true)
        ) {
            \Livewire\Livewire::component(
                'geography-select',
                \Enadstack\CountryData\Livewire\GeographySelect::class
            );
        }

        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../config/countries.php'    => config_path('countries.php'),
                __DIR__ . '/../config/country-data.php' => config_path('country-data.php'),
            ], 'country-data');

            // Migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'country-data-migrations');

            // Livewire view (publish to customise)
            $this->publishes([
                __DIR__ . '/../src/resources/views/livewire' => resource_path('views/vendor/country-data/livewire'),
            ], 'country-data-livewire');

            // Commands
            $this->commands([
                ConfigureCountryData::class,
                PublishFrontendComponent::class,
                GeographySetup::class,
                CacheClear::class,
                Stats::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/countries.php', 'countries');
        $this->mergeConfigFrom(__DIR__ . '/../config/country-data.php', 'country-data');

        $this->app->singleton('country-data', fn () => new CountryData());

        $this->app->singleton(GeographyService::class);
    }
}
