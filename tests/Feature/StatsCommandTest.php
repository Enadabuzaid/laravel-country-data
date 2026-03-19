<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;

class StatsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    public function test_stats_command_exits_successfully(): void
    {
        $this->artisan('country-data:stats')
            ->assertExitCode(0);
    }

    public function test_stats_command_outputs_country_count(): void
    {
        $this->artisan('country-data:stats')
            ->expectsOutputToContain('22')
            ->assertExitCode(0);
    }

    public function test_stats_command_outputs_continent_breakdown(): void
    {
        $this->artisan('country-data:stats')
            ->expectsOutputToContain('Asia')
            ->assertExitCode(0);
    }

    public function test_stats_command_outputs_cache_info(): void
    {
        $this->artisan('country-data:stats')
            ->expectsOutputToContain('Cache')
            ->assertExitCode(0);
    }

    public function test_stats_command_fails_gracefully_without_tables(): void
    {
        // Drop tables and verify the command handles it
        \Illuminate\Support\Facades\Schema::drop('areas');
        \Illuminate\Support\Facades\Schema::drop('cities');
        \Illuminate\Support\Facades\Schema::drop('countries');

        $this->artisan('country-data:stats')
            ->assertExitCode(1);
    }
}
