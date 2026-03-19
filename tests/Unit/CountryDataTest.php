<?php

namespace Enadstack\CountryData\Tests\Unit;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Facades\CountryData;

/**
 * Tests for the config-based CountryData class (no database required).
 * Uses the real countries config merged by the service provider.
 */
class CountryDataTest extends TestCase
{
    // ── getByCode ─────────────────────────────────────────────────────────────

    public function test_get_by_code_returns_country(): void
    {
        $country = CountryData::getByCode('JO');

        $this->assertNotNull($country);
        $this->assertSame('JO', $country['code']);
        $this->assertSame('Jordan', $country['names']['common']['en']);
        $this->assertSame('الأردن', $country['names']['common']['ar']);
    }

    public function test_get_by_code_is_case_insensitive(): void
    {
        $this->assertNotNull(CountryData::getByCode('jo'));
        $this->assertNotNull(CountryData::getByCode('JO'));
        $this->assertNotNull(CountryData::getByCode('Jo'));
    }

    public function test_get_by_code_returns_null_for_unknown_code(): void
    {
        $this->assertNull(CountryData::getByCode('XX'));
    }

    // ── getArabCountries ──────────────────────────────────────────────────────

    public function test_get_arab_countries_returns_only_arab_tagged(): void
    {
        $countries = CountryData::getArabCountries();

        $this->assertNotEmpty($countries);

        foreach ($countries as $c) {
            $this->assertContains('arab', $c['filters'], "Country {$c['code']} should have 'arab' filter");
        }
    }

    public function test_get_arab_countries_returns_all_22(): void
    {
        $this->assertCount(22, CountryData::getArabCountries());
    }

    // ── getGulfCountries ──────────────────────────────────────────────────────

    public function test_get_gulf_countries_returns_only_gulf_tagged(): void
    {
        $countries = CountryData::getGulfCountries();

        $this->assertNotEmpty($countries);

        foreach ($countries as $c) {
            $this->assertContains('gulf', $c['filters'], "Country {$c['code']} should have 'gulf' filter");
        }
    }

    public function test_get_gulf_countries_contains_expected_members(): void
    {
        $codes = array_column(CountryData::getGulfCountries(), 'code');

        // GCC core 6 members (IQ is tagged gulf in JSON data but may vary in PHP config)
        foreach (['SA', 'AE', 'KW', 'QA', 'OM', 'BH'] as $expected) {
            $this->assertContains($expected, $codes, "Gulf countries should include {$expected}");
        }
    }

    // ── getByFilter ───────────────────────────────────────────────────────────

    public function test_get_by_filter_returns_matching_countries(): void
    {
        $african = CountryData::getByFilter('africa');

        $this->assertNotEmpty($african);

        foreach ($african as $c) {
            $this->assertContains('africa', $c['filters']);
        }
    }

    public function test_get_by_filter_returns_empty_for_unknown_filter(): void
    {
        $this->assertEmpty(CountryData::getByFilter('__nonexistent__'));
    }

    // ── getName ───────────────────────────────────────────────────────────────

    public function test_get_name_returns_english_name(): void
    {
        $this->assertSame('Jordan', CountryData::getName('JO', 'en'));
    }

    public function test_get_name_returns_arabic_name(): void
    {
        $this->assertSame('الأردن', CountryData::getName('JO', 'ar'));
    }

    public function test_get_name_returns_null_for_unknown_code(): void
    {
        $this->assertNull(CountryData::getName('XX'));
    }

    // ── getFlag ───────────────────────────────────────────────────────────────

    public function test_get_flag_returns_emoji(): void
    {
        $flag = CountryData::getFlag('JO');

        $this->assertNotNull($flag);
        $this->assertSame('🇯🇴', $flag);
    }

    public function test_get_flag_returns_null_for_unknown_code(): void
    {
        $this->assertNull(CountryData::getFlag('XX'));
    }

    // ── getDialCodes ──────────────────────────────────────────────────────────

    public function test_get_dial_codes_returns_array_with_code_and_dial(): void
    {
        $dials = CountryData::getDialCodes();

        $this->assertNotEmpty($dials);
        $this->assertArrayHasKey('code', $dials[0]);
        $this->assertArrayHasKey('dial', $dials[0]);
    }

    public function test_get_dial_codes_with_flag_includes_flag(): void
    {
        $dials = CountryData::getDialCodes(withFlag: true);

        $this->assertNotEmpty($dials);
        $this->assertArrayHasKey('flag', $dials[0]);
    }

    public function test_get_dial_codes_without_flag_excludes_flag(): void
    {
        $dials = CountryData::getDialCodes(withFlag: false);

        $this->assertArrayNotHasKey('flag', $dials[0]);
    }

    public function test_jordan_dial_code_is_correct(): void
    {
        $dials = collect(CountryData::getDialCodes())->keyBy('code');

        $this->assertSame('+962', $dials['JO']['dial']);
    }

    // ── searchByName ──────────────────────────────────────────────────────────

    public function test_search_by_name_finds_exact_english_name(): void
    {
        $result = CountryData::searchByName('Jordan', 'en');

        $this->assertNotNull($result);
        $this->assertSame('JO', $result['code']);
    }

    public function test_search_by_name_is_case_insensitive(): void
    {
        $this->assertNotNull(CountryData::searchByName('jordan', 'en'));
        $this->assertNotNull(CountryData::searchByName('JORDAN', 'en'));
    }

    public function test_search_by_name_finds_arabic_name(): void
    {
        $result = CountryData::searchByName('الأردن', 'ar');

        $this->assertNotNull($result);
        $this->assertSame('JO', $result['code']);
    }

    public function test_search_by_name_returns_null_for_unknown(): void
    {
        $this->assertNull(CountryData::searchByName('Neverland', 'en'));
    }

    // ── getSelectOptions ──────────────────────────────────────────────────────

    public function test_get_select_options_has_label_and_value(): void
    {
        $options = CountryData::getSelectOptions('en');

        $this->assertNotEmpty($options);
        $this->assertArrayHasKey('label', $options[0]);
        $this->assertArrayHasKey('value', $options[0]);
    }

    public function test_get_select_options_arabic_returns_ar_labels(): void
    {
        $options = collect(CountryData::getSelectOptions('ar'))
            ->firstWhere('value', 'JO');

        $this->assertSame('الأردن', $options['label']);
    }
}
