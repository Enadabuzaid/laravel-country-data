<?php

namespace Enadstack\CountryData\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Invariants of the shipped data files (no database, no Laravel).
 */
class WorldDataTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    /**
     * SHA-256 of the curated inputs as they shipped in v2.2.0. The build copies
     * these verbatim, so a changed hash means curated data was edited.
     */
    private const CURATED_SHA256 = [
        'resources/curated/countries.json' => '80cdd41315fc7779eda6c0d57d25d0cb930841131f4bc87cebb79bb64253d684',
        'resources/curated/cities.json'    => '33ed3821fa98f8af917e233268b7dcf20731752090797efe42ac26ba2c95449c',
        'data/areas.json'                  => '98ab2378bcb0baf24d7b7601526b8bd1c328b247c1c349610a1446b5e0acd463',
    ];

    /**
     * Uninhabited territories that genuinely have no currency / dial code /
     * capital. Listed exactly, so a regression anywhere else fails loudly.
     */
    private const NO_CURRENCY = ['AQ'];
    private const NO_DIAL     = ['AQ', 'HM'];
    private const NO_CAPITAL  = ['AQ', 'BV', 'HM', 'MO', 'UM'];

    private static array $cache = [];

    private static function json(string $path): array
    {
        return self::$cache[$path] ??= json_decode(file_get_contents(self::ROOT . "/{$path}"), true, flags: JSON_THROW_ON_ERROR);
    }

    private static function countries(): array
    {
        return self::json('data/countries.json');
    }

    private static function byCode(): array
    {
        return array_column(self::countries(), null, 'code');
    }

    private static function codesWith(string $filter): array
    {
        $codes = array_column(array_filter(self::countries(), fn ($c) => in_array($filter, $c['filters'], true)), 'code');
        sort($codes);

        return $codes;
    }

    // ── Coverage & identity ──────────────────────────────────────────────────

    public function test_covers_the_whole_world(): void
    {
        $this->assertGreaterThanOrEqual(245, count(self::countries()));
    }

    public function test_iso_codes_are_unique_and_well_formed(): void
    {
        $countries = self::countries();

        foreach (['code', 'iso2', 'iso3'] as $key) {
            $values = array_column($countries, $key);
            $this->assertSame(count($values), count(array_unique($values)), "{$key} is not unique");
        }

        foreach ($countries as $c) {
            $this->assertSame($c['code'], $c['iso2']);
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $c['iso2']);
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $c['iso3']);
        }
    }

    public function test_every_country_has_a_dial_code_except_uninhabited_territories(): void
    {
        $missing = [];

        foreach (self::countries() as $c) {
            if (blank($c['dial'])) {
                $missing[] = $c['code'];
                continue;
            }
            $this->assertMatchesRegularExpression('/^\+\d{1,4}$/', $c['dial'], "{$c['code']} dial");
        }

        $this->assertSame(self::NO_DIAL, $missing);
    }

    public function test_every_country_has_a_currency_except_antarctica(): void
    {
        $missing = [];

        foreach (self::countries() as $c) {
            if (blank($c['currency']['code'] ?? null)) {
                $missing[] = $c['code'];
                continue;
            }
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $c['currency']['code']);
            $this->assertNotEmpty($c['currency']['name']['en'], "{$c['code']} currency name");
        }

        $this->assertSame(self::NO_CURRENCY, $missing);
    }

    public function test_every_country_has_a_flag(): void
    {
        foreach (self::countries() as $c) {
            $this->assertNotEmpty($c['flag'], "{$c['code']} flag");
            $this->assertStringStartsWith('https://', $c['flags']['svg']);
            $this->assertStringStartsWith('https://', $c['flags']['png']);
        }
    }

    public function test_every_country_has_english_and_arabic_names(): void
    {
        foreach (self::countries() as $c) {
            $this->assertNotEmpty($c['names']['common']['en'], "{$c['code']} en");
            $this->assertNotEmpty($c['names']['common']['ar'], "{$c['code']} ar");
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $c['names']['common']['ar'], "{$c['code']} ar is not Arabic");
        }
    }

    public function test_every_country_has_the_documented_json_shape(): void
    {
        $keys = array_keys(self::byCode()['JO']);

        foreach (self::countries() as $c) {
            $this->assertSame($keys, array_keys($c), "{$c['code']} key order/shape");
        }
    }

    // ── Filters ─────────────────────────────────────────────────────────────

    public function test_europe_filter(): void
    {
        $europe = self::codesWith('europe');

        foreach (['DE', 'FR', 'ES', 'IT', 'PL', 'GB', 'RU'] as $code) {
            $this->assertContains($code, $europe);
        }
        foreach (['JO', 'TR', 'CY', 'US'] as $code) {
            $this->assertNotContains($code, $europe);
        }
    }

    public function test_gulf_filter_still_has_exactly_seven_countries(): void
    {
        $this->assertSame(['AE', 'BH', 'IQ', 'KW', 'OM', 'QA', 'SA'], self::codesWith('gulf'));
    }

    public function test_gcc_is_the_six_member_states(): void
    {
        $this->assertSame(['AE', 'BH', 'KW', 'OM', 'QA', 'SA'], self::codesWith('gcc'));
    }

    public function test_arab_filter_is_the_22_arab_league_members(): void
    {
        $this->assertCount(22, self::codesWith('arab'));
    }

    public function test_political_filters_have_their_documented_sizes(): void
    {
        $this->assertCount(27, self::codesWith('eu'));
        $this->assertCount(29, self::codesWith('schengen'));
        $this->assertCount(19, self::codesWith('g20'));
        $this->assertSame(['IL', 'JO', 'LB', 'PS', 'SY'], self::codesWith('levant'));
        $this->assertSame(['DZ', 'LY', 'MA', 'MR', 'TN'], self::codesWith('maghreb'));
    }

    public function test_continental_filters(): void
    {
        $this->assertContains('US', self::codesWith('north-america'));
        $this->assertContains('MX', self::codesWith('north-america'));
        $this->assertContains('JM', self::codesWith('north-america'));
        $this->assertContains('BR', self::codesWith('south-america'));
        $this->assertNotContains('BR', self::codesWith('north-america'));
        $this->assertContains('AU', self::codesWith('oceania'));
        $this->assertContains('NG', self::codesWith('africa'));
        $this->assertContains('JP', self::codesWith('asia'));
    }

    public function test_every_filter_matches_the_region_definitions(): void
    {
        $regions = require self::ROOT . '/resources/regions.php';
        $used    = array_unique(array_merge(...array_column(self::countries(), 'filters')));

        sort($used);
        $defined = array_keys($regions);
        sort($defined);

        $this->assertSame($defined, $used);

        // Political lists are the literal membership, curated rows included.
        foreach ($regions as $tag => $def) {
            if (isset($def['codes'])) {
                $codes = $def['codes'];
                sort($codes);
                $this->assertSame($codes, self::codesWith($tag), "{$tag} membership");
            }
        }
    }

    // ── Curated data is preserved ───────────────────────────────────────────

    public function test_curated_inputs_are_unchanged_since_v2_2_0(): void
    {
        foreach (self::CURATED_SHA256 as $path => $hash) {
            $this->assertSame($hash, hash_file('sha256', self::ROOT . "/{$path}"), "{$path} was edited");
        }
    }

    public function test_curated_countries_are_identical_apart_from_appended_filters(): void
    {
        $curated = self::json('resources/curated/countries.json');
        $shipped = self::byCode();

        $this->assertCount(22, $curated);

        foreach ($curated as $c) {
            $out = $shipped[$c['code']];

            // Existing tags kept in order; only new region tags may follow.
            $this->assertSame($c['filters'], array_slice($out['filters'], 0, count($c['filters'])));

            unset($c['filters'], $out['filters']);
            $this->assertSame($c, $out, "{$c['code']} curated fields changed");
        }
    }

    public function test_curated_countries_come_first_in_their_original_order(): void
    {
        $this->assertSame(
            array_column(self::json('resources/curated/countries.json'), 'code'),
            array_slice(array_column(self::countries(), 'code'), 0, 22)
        );
    }

    public function test_curated_cities_are_kept_verbatim(): void
    {
        $curatedText = file_get_contents(self::ROOT . '/resources/curated/cities.json');
        $shippedText = file_get_contents(self::ROOT . '/data/cities.json');

        $this->assertStringStartsWith(rtrim(substr($curatedText, 0, strrpos($curatedText, ']'))), $shippedText);
        $this->assertSame(self::json('resources/curated/cities.json'), array_slice(self::json('data/cities.json'), 0, 136));
    }

    public function test_amman_keeps_its_207_areas(): void
    {
        $amman = array_filter(self::json('data/areas.json'), fn ($a) => $a['country_code'] === 'JO' && $a['city_name_en'] === 'Amman');

        $this->assertCount(207, $amman);
        $this->assertCount(276, self::json('data/areas.json'));
    }

    // ── Cities ──────────────────────────────────────────────────────────────

    public function test_every_country_with_a_capital_has_a_capital_city(): void
    {
        $capitals = [];
        foreach (self::json('data/cities.json') as $city) {
            if ($city['is_capital']) {
                $capitals[$city['country_code']] = true;
            }
        }

        $missing = [];
        foreach (self::countries() as $c) {
            if (! isset($capitals[$c['code']])) {
                $missing[] = $c['code'];
            }
        }
        sort($missing);

        $this->assertSame(self::NO_CAPITAL, $missing);
    }

    public function test_generated_capitals_have_coordinates_and_a_timezone(): void
    {
        foreach (array_slice(self::json('data/cities.json'), 136) as $city) {
            $this->assertIsFloat($city['latitude'], "{$city['country_code']} {$city['name_en']}");
            $this->assertIsFloat($city['longitude']);
            $this->assertNotEmpty($city['timezone']);
            $this->assertContains($city['timezone'], \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC));
        }
    }

    public function test_every_city_belongs_to_a_known_country(): void
    {
        $codes = self::byCode();

        foreach (self::json('data/cities.json') as $city) {
            $this->assertArrayHasKey($city['country_code'], $codes);
        }
    }

    // ── One source of truth ─────────────────────────────────────────────────

    public function test_config_countries_matches_data_json(): void
    {
        $config = require self::ROOT . '/config/countries.php';

        $this->assertSame(array_column(self::countries(), 'code'), array_column($config, 'code'));

        foreach ($config as $i => $c) {
            // cca2 / ccn3 are kept in config only, for pre-2.3 readers.
            $this->assertSame($c['iso2'], $c['cca2']);
            unset($c['cca2'], $c['ccn3']);
            $this->assertEquals(self::countries()[$i], $c);
        }
    }

    public static function sources(): array
    {
        $regions = require self::ROOT . '/resources/regions.php';

        return ['all' => ['all']] + array_combine(array_keys($regions), array_map(fn ($t) => [$t], array_keys($regions)));
    }

    #[DataProvider('sources')]
    public function test_each_config_source_is_its_filter_subset(string $source): void
    {
        $file = self::ROOT . "/config/source/countries-{$source}.php";
        $this->assertFileExists($file);

        $expected = $source === 'all' ? array_column(self::countries(), 'code') : self::codesWith($source);
        $actual   = array_column(require $file, 'code');

        if ($source !== 'all') {
            sort($actual);
        }

        $this->assertSame($expected, $actual);
    }

    public function test_generated_files_are_in_sync_with_their_sources(): void
    {
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(self::ROOT . '/scripts/build-data.php') . ' --check 2>&1', $output, $exit);

        $this->assertSame(0, $exit, "Run `php scripts/build-data.php`:\n" . implode("\n", $output));
    }
}
