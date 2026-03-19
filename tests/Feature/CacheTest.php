<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Facades\Geography;
use Enadstack\CountryData\Services\GeographyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);

        // Start with clean cache for every test
        Geography::flush();
    }

    // ── Cache hits ────────────────────────────────────────────────────────────

    public function test_countries_are_cached_after_first_call(): void
    {
        Geography::countries(); // prime cache

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::countries(); // should hit cache, no DB query

        $this->assertSame(0, $queryCount, 'Second call should be served from cache with 0 DB queries');
    }

    public function test_cities_are_cached_per_country(): void
    {
        Geography::cities('JO'); // prime

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::cities('JO'); // cache hit

        $this->assertSame(0, $queryCount);
    }

    public function test_areas_are_cached_per_city(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Geography::areas($ammanId); // prime

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::areas($ammanId); // cache hit

        $this->assertSame(0, $queryCount);
    }

    public function test_different_filters_cached_separately(): void
    {
        $arab = Geography::countries('arab');
        $gulf = Geography::countries('gulf');

        $this->assertNotEquals($arab->count(), $gulf->count());

        // Both should now be in cache
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::countries('arab');
        Geography::countries('gulf');

        $this->assertSame(0, $queryCount);
    }

    // ── Cache keys stored ─────────────────────────────────────────────────────

    public function test_cache_keys_are_tracked(): void
    {
        Geography::countries();
        Geography::cities('JO');

        $prefix  = config('country-data.cache.prefix', 'geography');
        $tracked = Cache::get("{$prefix}.__keys", []);

        $this->assertNotEmpty($tracked);
    }

    // ── flush() ───────────────────────────────────────────────────────────────

    public function test_flush_clears_all_cached_entries(): void
    {
        // Prime several cache entries
        Geography::countries();
        Geography::cities('JO');
        Geography::capital('JO');

        $prefix  = config('country-data.cache.prefix', 'geography');
        $tracked = Cache::get("{$prefix}.__keys", []);
        $this->assertNotEmpty($tracked);

        Geography::flush();

        // All tracked keys should now be gone
        foreach ($tracked as $key) {
            $this->assertNull(Cache::get($key), "Key '{$key}' should be null after flush");
        }

        // Meta key itself cleared
        $this->assertEmpty(Cache::get("{$prefix}.__keys", []));
    }

    public function test_flush_via_artisan_command(): void
    {
        Geography::countries(); // prime

        $this->artisan('country-data:cache-clear')
            ->assertSuccessful()
            ->expectsOutput('Geography cache cleared.');

        $prefix = config('country-data.cache.prefix', 'geography');
        $this->assertEmpty(Cache::get("{$prefix}.__keys", []));
    }

    public function test_after_flush_next_call_hits_database(): void
    {
        Geography::countries(); // prime
        Geography::flush();     // clear

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::countries(); // must hit DB again

        $this->assertGreaterThan(0, $queryCount);
    }

    // ── Disabled cache ────────────────────────────────────────────────────────

    public function test_cache_can_be_disabled_via_config(): void
    {
        config(['country-data.cache.enabled' => false]);

        Geography::countries(); // should not write to cache

        $prefix  = config('country-data.cache.prefix', 'geography');
        $tracked = Cache::get("{$prefix}.__keys", []);

        $this->assertEmpty($tracked);

        config(['country-data.cache.enabled' => true]); // restore
    }

    public function test_disabled_cache_still_returns_correct_data(): void
    {
        config(['country-data.cache.enabled' => false]);

        $countries = Geography::countries();
        $this->assertCount(22, $countries);

        config(['country-data.cache.enabled' => true]);
    }

    // ── select helpers also cached ────────────────────────────────────────────

    public function test_countries_for_select_is_cached(): void
    {
        Geography::countriesForSelect('ar', 'arab'); // prime

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        Geography::countriesForSelect('ar', 'arab');

        $this->assertSame(0, $queryCount);
    }
}
