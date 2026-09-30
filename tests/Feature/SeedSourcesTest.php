<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SeedSourcesTest extends TestCase
{
    public static function sources(): array
    {
        $out = [];

        foreach (glob(__DIR__ . '/../../config/source/countries-*.php') as $file) {
            $source = substr(basename($file, '.php'), strlen('countries-'));
            $out[$source] = [$source];
        }

        return $out;
    }

    public function test_every_documented_source_has_a_file(): void
    {
        foreach (['all', 'arab', 'gulf', 'europe', 'gcc', 'levant', 'maghreb', 'eu', 'schengen', 'g20',
                  'asia', 'africa', 'north-america', 'south-america', 'oceania'] as $source) {
            $this->assertArrayHasKey($source, self::sources());
        }
    }

    #[DataProvider('sources')]
    public function test_setup_seeds_exactly_the_source_countries(string $source): void
    {
        $expected = array_column(require __DIR__ . "/../../config/source/countries-{$source}.php", 'code');
        sort($expected);

        $this->artisan('country-data:setup', ['--seed' => true, '--source' => $source])
            ->assertExitCode(0);

        $this->assertNotEmpty($expected);
        $this->assertSame($expected, Country::orderBy('code')->pluck('code')->all());

        City::all()->each(fn (City $city) => $this->assertContains($city->country_code, $expected));
    }

    public function test_europe_source_is_no_longer_empty(): void
    {
        $this->artisan('country-data:setup', ['--seed' => true, '--source' => 'europe'])
            ->assertExitCode(0);

        $this->assertTrue(Country::where('code', 'DE')->exists());
        $this->assertTrue(City::where('country_code', 'DE')->where('is_capital', true)->exists());
        $this->assertFalse(Country::where('code', 'JO')->exists());
    }
}
