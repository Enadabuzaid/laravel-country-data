<?php

namespace Enadstack\CountryData\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Enadstack\CountryData\CountryDataServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [CountryDataServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'CountryData' => \Enadstack\CountryData\Facades\CountryData::class,
            'Geography'   => \Enadstack\CountryData\Facades\Geography::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
