<?php

namespace Enadstack\CountryData\Tests\Unit;

use Enadstack\CountryData\Enums\AreaType;
use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Exceptions\CountryNotFoundException;
use Enadstack\CountryData\Exceptions\GeographyNotFoundException;
use Enadstack\CountryData\Exceptions\RegionNotFoundException;
use Enadstack\CountryData\Support\Lookup;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    // ── Region ──────────────────────────────────────────────────────────────

    public function test_region_has_a_case_for_every_filter(): void
    {
        $regions = require __DIR__ . '/../../resources/regions.php';

        $this->assertSame(array_keys($regions), array_column(Region::cases(), 'value'));

        foreach (['arab', 'gulf', 'middle-east', 'africa', 'asia', 'muslim-majority', 'europe', 'eu',
                  'north-america', 'south-america', 'oceania', 'levant', 'maghreb', 'gcc', 'schengen', 'g20'] as $tag) {
            $this->assertNotNull(Region::tryFrom($tag), $tag);
        }
    }

    public function test_region_resolves_any_spelling(): void
    {
        foreach (['north-america', 'northAmerica', 'NorthAmerica', 'NORTH_AMERICA', 'North America'] as $name) {
            $this->assertSame(Region::NorthAmerica, Region::fromName($name), $name);
        }

        $this->assertSame(Region::GCC, Region::fromName('gcc'));
        $this->assertSame(Region::G20, Region::fromName('G20'));
        $this->assertNull(Region::tryFromName('atlantis'));
    }

    public function test_unknown_region_throws_with_a_suggestion(): void
    {
        try {
            Region::fromName('eurpe');
            $this->fail('Expected RegionNotFoundException');
        } catch (RegionNotFoundException $e) {
            $this->assertSame('eurpe', $e->name);
            $this->assertSame('europe', $e->suggestion);
            $this->assertStringContainsString('Did you mean europe?', $e->getMessage());
            $this->assertInstanceOf(GeographyNotFoundException::class, $e);
        }
    }

    public function test_region_metadata(): void
    {
        $this->assertSame('Gulf Cooperation Council', Region::GCC->label());
        $this->assertTrue(Region::Europe->isGeographic());
        $this->assertFalse(Region::EU->isGeographic());
        $this->assertStringContainsString('M49', Region::Europe->description());
        $this->assertSame('northAmerica', Region::NorthAmerica->method());
        $this->assertSame('middleEast', Region::MiddleEast->method());
    }

    // ── CountryCode ─────────────────────────────────────────────────────────

    public function test_country_code_covers_every_country(): void
    {
        $countries = json_decode(file_get_contents(__DIR__ . '/../../data/countries.json'), true);

        $this->assertSame(array_column($countries, 'code'), array_column(CountryCode::cases(), 'value'));
    }

    public function test_country_code_helpers_work_without_a_database(): void
    {
        $this->assertSame('Jordan', CountryCode::JO->name('en'));
        $this->assertSame('الأردن', CountryCode::JO->name('ar'));
        $this->assertSame('ألمانيا', CountryCode::DE->name('ar'));
        $this->assertSame('🇯🇴', CountryCode::JO->flag());
        $this->assertSame('🇩🇪', CountryCode::DE->flag());
        $this->assertSame('JOR', CountryCode::JO->iso3());
    }

    public function test_country_code_resolves_iso2_and_iso3_case_insensitively(): void
    {
        foreach (['JO', 'jo', 'Jo', 'JOR', 'jor', ' jor '] as $code) {
            $this->assertSame(CountryCode::JO, CountryCode::fromAny($code), $code);
        }

        $this->assertNull(CountryCode::tryFromAny('XX'));
        $this->assertNull(CountryCode::tryFromAny('Jordan'));
    }

    public function test_unknown_country_code_throws(): void
    {
        $this->expectException(CountryNotFoundException::class);

        CountryCode::fromAny('ZZZ');
    }

    public function test_keyword_codes_are_valid_cases(): void
    {
        // ISO codes that are PHP keywords still work as enum cases.
        $this->assertSame('DO', CountryCode::DO->value);
        $this->assertSame('AS', CountryCode::AS->value);
        $this->assertSame('IN', CountryCode::IN->value);
    }

    // ── AreaType ────────────────────────────────────────────────────────────

    public function test_area_type_covers_every_type_in_the_data(): void
    {
        $areas = json_decode(file_get_contents(__DIR__ . '/../../data/areas.json'), true);

        foreach (array_unique(array_column($areas, 'type')) as $type) {
            $this->assertNotNull(AreaType::tryFrom($type), $type);
        }

        $this->assertSame('district', AreaType::District->value);
        $this->assertSame('neighborhood', AreaType::Neighborhood->value);
        $this->assertSame('street', AreaType::Street->value);
        $this->assertSame('zone', AreaType::Zone->value);
    }

    // ── Lookup ──────────────────────────────────────────────────────────────

    public function test_lookup_keys_ignore_case_accents_and_punctuation(): void
    {
        $this->assertSame('saudiarabia', Lookup::key('Saudi Arabia'));
        $this->assertSame('saudiarabia', Lookup::key('saudi-arabia'));
        $this->assertSame('saudiarabia', Lookup::key('saudiArabia'));
        $this->assertSame('cotedivoire', Lookup::key("Côte d'Ivoire"));
        $this->assertSame('الأردن', Lookup::key('الأردن'));
    }

    public function test_lookup_method_names(): void
    {
        $this->assertSame('saudiArabia', Lookup::method('Saudi Arabia'));
        $this->assertSame('coteDivoire', Lookup::method("Côte d'Ivoire"));
        $this->assertSame('guineaBissau', Lookup::method('Guinea-Bissau'));
        $this->assertSame('alandIslands', Lookup::method('Åland Islands'));
    }

    public function test_lookup_closest(): void
    {
        $this->assertSame('jordan', Lookup::closest('jordna', ['jordan', 'japan', 'germany']));
        $this->assertNull(Lookup::closest('qwertyuiop', ['jordan', 'japan']));
    }
}
