<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Models\Area;

class SetupCommandTest extends TestCase
{
    // ── --migrate only ────────────────────────────────────────────────────────

    public function test_migrate_flag_creates_tables_without_seeding(): void
    {
        $this->artisan('country-data:setup', ['--migrate' => true])
            ->assertExitCode(0);

        $this->assertSame(0, Country::count());
        $this->assertSame(0, City::count());
    }

    // ── --all flag: seed all countries non-interactively ──────────────────────

    public function test_all_flag_seeds_every_country(): void
    {
        $this->artisan('country-data:setup', ['--seed' => true, '--all' => true])
            ->assertExitCode(0);

        $this->assertSame(22, Country::count());
        $this->assertGreaterThan(0, City::count());
    }

    // ── --countries= option ───────────────────────────────────────────────────

    public function test_countries_option_seeds_only_specified_codes(): void
    {
        $this->artisan('country-data:setup', [
            '--seed'      => true,
            '--countries' => 'JO,SA',
        ])->assertExitCode(0);

        $this->assertSame(2, Country::count());

        $codes = Country::pluck('code')->sort()->values()->all();
        $this->assertSame(['JO', 'SA'], $codes);
    }

    public function test_countries_option_seeds_cities_only_for_selected(): void
    {
        $this->artisan('country-data:setup', [
            '--seed'      => true,
            '--countries' => 'JO',
        ])->assertExitCode(0);

        $this->assertSame(1, Country::count());

        City::all()->each(fn ($city) => $this->assertSame('JO', $city->country_code));
    }

    public function test_countries_option_is_case_insensitive(): void
    {
        $this->artisan('country-data:setup', [
            '--seed'      => true,
            '--countries' => 'jo,ae',
        ])->assertExitCode(0);

        $this->assertSame(2, Country::count());
        $this->assertContains('JO', Country::pluck('code')->all());
        $this->assertContains('AE', Country::pluck('code')->all());
    }

    public function test_countries_option_accepts_single_country(): void
    {
        $this->artisan('country-data:setup', [
            '--seed'      => true,
            '--countries' => 'AE',
        ])->assertExitCode(0);

        $this->assertSame(1, Country::count());
        $this->assertSame('AE', Country::first()->code);
    }

    public function test_areas_are_scoped_to_selected_countries(): void
    {
        $this->artisan('country-data:setup', [
            '--seed'      => true,
            '--countries' => 'JO',
        ])->assertExitCode(0);

        $this->assertGreaterThan(0, Area::count());

        // All areas should belong to a JO city
        $joCityIds = City::where('country_code', 'JO')->pluck('id')->all();
        Area::all()->each(fn ($area) => $this->assertContains($area->city_id, $joCityIds));
    }

    // ── interactive: seed declined ────────────────────────────────────────────

    public function test_declining_seed_skips_seeding(): void
    {
        $this->artisan('country-data:setup', ['--migrate' => true])
            ->assertExitCode(0);

        // Tables exist but should be empty (no seed ran)
        $this->assertSame(0, Country::count());
    }

    // ── idempotency ───────────────────────────────────────────────────────────

    public function test_running_command_twice_does_not_duplicate_data(): void
    {
        $options = ['--seed' => true, '--countries' => 'JO'];

        $this->artisan('country-data:setup', $options)->assertExitCode(0);
        $firstCount = Country::count();

        $this->artisan('country-data:setup', $options)->assertExitCode(0);

        $this->assertSame($firstCount, Country::count());
    }

    // ── partial then full seed ────────────────────────────────────────────────

    public function test_seeding_additional_countries_accumulates(): void
    {
        $this->artisan('country-data:setup', ['--seed' => true, '--countries' => 'JO'])
            ->assertExitCode(0);

        $this->assertSame(1, Country::count());

        $this->artisan('country-data:setup', ['--seed' => true, '--countries' => 'SA'])
            ->assertExitCode(0);

        $this->assertSame(2, Country::count());
    }
}
