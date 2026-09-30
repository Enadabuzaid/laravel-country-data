<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Country Data Source
    |--------------------------------------------------------------------------
    | 'all', or any region filter: 'arab' | 'gulf' | 'gcc' | 'levant' |
    | 'maghreb' | 'middle-east' | 'muslim-majority' | 'africa' | 'asia' |
    | 'europe' | 'north-america' | 'south-america' | 'oceania' | 'eu' |
    | 'schengen' | 'g20'. Each maps to config/source/countries-{source}.php.
    */
    'source' => 'all',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Geography data is static — caching it avoids repeated DB queries.
    | Works with every Laravel cache driver (file, redis, database, array…).
    |
    | 'ttl'    – seconds to keep items cached (default: 24 hours)
    | 'prefix' – key prefix, change if you have multiple apps sharing a cache
    */
    'cache' => [
        'enabled' => env('COUNTRY_DATA_CACHE', true),
        'ttl'     => env('COUNTRY_DATA_CACHE_TTL', 86400),   // 24 h
        'prefix'  => env('COUNTRY_DATA_CACHE_PREFIX', 'geography'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Components
    |--------------------------------------------------------------------------
    */
    'frontend' => [
        // Options: 'none' | 'blade' | 'vue' | 'react' | 'livewire'
        'component' => 'none',

        'publish_components' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional REST API
    |--------------------------------------------------------------------------
    | When enabled the package registers read-only JSON endpoints automatically.
    |
    | Endpoints (all GET, relative to prefix):
    |   /countries                     – list all (or filtered) countries
    |   /countries/{code}              – single country
    |   /countries/{code}/cities       – cities for a country
    |   /cities/{id}                   – single city
    |   /cities/{id}/areas             – areas for a city  (?type=neighborhood)
    |   /near/cities                   – cities near a coordinate  (?lat&lng&radius&country)
    |   /search/cities                 – search cities  (?q&country)
    |   /search/areas                  – search areas   (?q&city_id)
    */
    'api' => [
        'enabled'    => env('COUNTRY_DATA_API', false),
        'prefix'     => env('COUNTRY_DATA_API_PREFIX', 'api/geography'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Component
    |--------------------------------------------------------------------------
    | Only relevant when livewire/livewire ^3.0 is installed.
    */
    'livewire' => [
        // Register the <livewire:geography-select /> component automatically
        'register' => true,

        // Default locale for labels: 'en' | 'ar'
        'locale'   => 'en',

        // Show the areas dropdown by default
        'show_areas' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Phone Input
    |--------------------------------------------------------------------------
    | Default country (ISO-2) preselected by the PhoneInput component when a
    | record has no country code yet, e.g. 'JO'. Null = no preselection.
    */
    'phone' => [
        'default_country' => env('COUNTRY_DATA_DEFAULT_PHONE_COUNTRY'),
    ],
];
