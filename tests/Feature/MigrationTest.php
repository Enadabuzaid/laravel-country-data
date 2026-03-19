<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    // ── countries ─────────────────────────────────────────────────────────────

    public function test_countries_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('countries'));
    }

    public function test_countries_table_has_required_columns(): void
    {
        $required = [
            'id', 'code', 'iso2', 'iso3',
            'name_en', 'name_ar',
            'flag', 'dial', 'capital',
            'region', 'continent',
            'currency_code',
            'latitude', 'longitude',
            'population', 'area',
            'languages', 'timezones', 'borders', 'filters',
            'is_active',
            'created_at', 'updated_at',
        ];

        foreach ($required as $column) {
            $this->assertTrue(
                Schema::hasColumn('countries', $column),
                "Column '{$column}' missing from countries table"
            );
        }
    }

    // ── cities ────────────────────────────────────────────────────────────────

    public function test_cities_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('cities'));
    }

    public function test_cities_table_has_required_columns(): void
    {
        $required = [
            'id', 'country_id', 'country_code',
            'name_en', 'name_ar',
            'is_capital', 'latitude', 'longitude',
            'population', 'timezone',
            'is_active', 'created_at', 'updated_at',
        ];

        foreach ($required as $column) {
            $this->assertTrue(
                Schema::hasColumn('cities', $column),
                "Column '{$column}' missing from cities table"
            );
        }
    }

    // ── areas ─────────────────────────────────────────────────────────────────

    public function test_areas_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('areas'));
    }

    public function test_areas_table_has_required_columns(): void
    {
        $required = [
            'id', 'city_id',
            'name_en', 'name_ar',
            'type', 'latitude', 'longitude',
            'is_active', 'created_at', 'updated_at',
        ];

        foreach ($required as $column) {
            $this->assertTrue(
                Schema::hasColumn('areas', $column),
                "Column '{$column}' missing from areas table"
            );
        }
    }
}
