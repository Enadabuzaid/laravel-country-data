<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\CountryData;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Services\GeographyService;
use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

/**
 * CountryData is deprecated in 3.0 but must keep working throughout 3.x:
 * through GeographyService when the DB is seeded, from config otherwise.
 */
class CountryDataDeprecationTest extends TestCase
{
    public function test_every_public_method_is_marked_deprecated(): void
    {
        $class = new \ReflectionClass(CountryData::class);

        $this->assertStringContainsString('@deprecated', $class->getDocComment());

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->assertStringContainsString('@deprecated', (string) $method->getDocComment(), $method->getName());
        }
    }

    public function test_falls_back_to_config_when_the_table_is_empty(): void
    {
        $this->assertFalse(app(GeographyService::class)->isSeeded());

        $this->assertSame('Jordan', CountryData::getName('JO'));
        $this->assertCount(250, CountryData::getSelectOptions());
        $this->assertCount(7, CountryData::getGulfCountries());
    }

    public function test_falls_back_to_config_when_there_are_no_tables(): void
    {
        foreach (['areas', 'cities', 'countries'] as $table) {
            Schema::dropIfExists($table);
        }

        $this->assertSame('الأردن', CountryData::getName('JO', 'ar'));
        $this->assertSame('🇯🇴', CountryData::getFlag('JO'));
        $this->assertCount(22, CountryData::getArabCountries());
    }

    public function test_delegates_to_the_database_when_seeded(): void
    {
        $this->seed(GeographySeeder::class);
        Country::where('code', 'JO')->update(['name_en' => 'Jordan (from DB)']);
        app(GeographyService::class)->flush();

        $this->assertTrue(app(GeographyService::class)->isSeeded());
        $this->assertSame('Jordan (from DB)', CountryData::getName('JO'));
        $this->assertSame('Jordan (from DB)', CountryData::getByCode('jo')['names']['common']['en']);
        $this->assertSame('Jordan (from DB)', CountryData::searchByName('jordan (from db)')['names']['common']['en']);
        $this->assertContains('Jordan (from DB)', array_column(CountryData::getSelectOptions(), 'label'));
    }

    public function test_database_results_respect_is_active(): void
    {
        $this->seed(GeographySeeder::class);
        Country::where('code', 'SA')->update(['is_active' => false]);
        app(GeographyService::class)->flush();

        $this->assertNull(CountryData::getByCode('SA'));
        $this->assertNotContains('SA', array_column(CountryData::getGulfCountries(), 'code'));
    }

    public function test_both_paths_return_the_same_shape_and_values(): void
    {
        $fromConfig = CountryData::getByCode('JO');

        $this->seed(GeographySeeder::class);
        app(GeographyService::class)->flush();
        $fromDb = CountryData::getByCode('JO');

        $this->assertSame(array_keys($fromConfig), array_keys($fromDb));
        $this->assertEquals($fromConfig, $fromDb);
    }

    public function test_both_paths_keep_the_bundled_order(): void
    {
        $fromConfig = array_column(CountryData::getSelectOptions(), 'value');

        $this->seed(GeographySeeder::class);
        app(GeographyService::class)->flush();

        $this->assertSame($fromConfig, array_column(CountryData::getSelectOptions(), 'value'));
        $this->assertSame(
            array_column(CountryData::getDialCodes(), 'code'),
            $fromConfig
        );
    }
}
