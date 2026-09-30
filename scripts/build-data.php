<?php

/**
 * Build every shipped data file from one source of truth. Offline and
 * deterministic: it reads only the pinned files in resources/.
 *
 *   php scripts/build-data.php            build and write
 *   php scripts/build-data.php --check    exit 1 if any output would change
 *
 * Inputs
 *   resources/curated/countries.json   the hand-curated countries (always win)
 *   resources/curated/cities.json      the hand-curated cities (kept verbatim)
 *   resources/regions.php              filter / region definitions
 *   resources/source/*                 pinned upstream data (scripts/fetch-sources.php)
 *
 * Outputs
 *   data/countries.json, data/cities.json
 *   config/countries.php, config/source/countries-{all,<filter>}.php
 *   resources/build-report.md
 *
 * Merge rules
 *   - A curated country is copied field-for-field. The only change the build
 *     makes to one is appending newly defined region tags to `filters`.
 *   - A generated country takes names from mledoze, Arabic names from CLDR `ar`
 *     (falling back to mledoze's Arabic translation), never invented. Anything
 *     still missing is listed in the build report.
 *   - Curated cities are kept byte-for-byte; the capital of every generated
 *     country is appended from Wikidata.
 */

$root  = dirname(__DIR__);
$check = in_array('--check', $argv, true);

require __DIR__ . '/lib/DataFormatter.php';
require __DIR__ . '/lib/functions.php';

// ─────────────────────────────────────────────────────────────────────────────
// Small, documented corrections to upstream data. Keep this list short.
// ─────────────────────────────────────────────────────────────────────────────

/** Currency where mledoze has none, per the ISO 4217 country list. */
const CURRENCY_OVERRIDES = [
    'FM' => 'USD',  // Micronesia uses the US dollar
    'BV' => 'NOK',  // Bouvet Island (Norwegian dependency)
    'HM' => 'AUD',  // Heard & McDonald Islands (Australian territory)
];

/**
 * Dial codes mledoze gets wrong for a phone input. Its single-suffix entries
 * are kept as the full prefix (NANP: +1 264 Anguilla), which suits the
 * longest-prefix matching in PhoneInput, except where the suffix is a landline
 * area code that mobiles don't share, or several suffixes share no prefix.
 */
const DIAL_OVERRIDES = [
    'AX' => '+358',  // Åland: mledoze +358 18 is the landline area code only
    'SJ' => '+47',   // Svalbard: mledoze +47 79 is the landline area code only
    'VA' => '+39',   // Vatican City is served by the Italian +39 06 698 range
    'SH' => '+290',  // Saint Helena (+247 Ascension is a separate range)
    'EH' => '+212',  // Western Sahara uses Moroccan numbering
];

/**
 * Capitals whose mledoze spelling differs from Wikidata's label but which are
 * the same place, pinned by Wikidata QID (verified by hand).
 */
const CAPITAL_SAME_PLACE = [
    'AG' => 'Q36262',   // "Saint John's" — the item has no English label
    'GG' => 'Q174262',  // "St. Peter Port" = Saint Peter Port
    'MN' => 'Q23430',   // "Ulan Bator" = Ulaanbaatar
];

/** Capital zones the nearest-reference-point rule gets wrong. */
const CAPITAL_TIMEZONE_OVERRIDES = [
    'KZ' => 'Asia/Almaty',  // Astana is in the Almaty zone, not the nearer Qostanay reference point
];

/** IANA zone for countries absent from tzdb's zone.tab. */
const TIMEZONE_OVERRIDES = [
    'XK' => ['Europe/Belgrade'],
];

/** M49 entry for codes the UN does not list (they mirror the surrounding M49 area). */
const M49_FALLBACK = [
    'XK' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'intermediateRegion' => null],
    'TW' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'intermediateRegion' => null],
];

// ─────────────────────────────────────────────────────────────────────────────
// Load inputs
// ─────────────────────────────────────────────────────────────────────────────

$json = fn (string $path) => json_decode(file_get_contents("{$root}/{$path}"), true, flags: JSON_THROW_ON_ERROR);

$curated       = $json('resources/curated/countries.json');
$curatedCities = $json('resources/curated/cities.json');
$regions       = require "{$root}/resources/regions.php";
$mledoze       = $json('resources/source/mledoze-countries.json');
$cldrNames     = $json('resources/source/cldr-ar-territories.json')['main']['ar']['localeDisplayNames']['territories'];
$cldrCurrency  = $json('resources/source/cldr-ar-currencies.json')['main']['ar']['numbers']['currencies'];
$m49           = $json('resources/source/un-m49.json') + M49_FALLBACK;
$wdCountries   = $json('resources/source/wikidata-countries.json');
$wdCapitals    = $json('resources/source/wikidata-capitals.json');
$zones         = parseZoneTab("{$root}/resources/source/tzdb-zone.tab");
$iso639        = parseIso639("{$root}/resources/source/iso-639-3.tab");

$report = [
    'missingCommonAr'   => [],
    'missingOfficialAr' => [],
    'arFromMledoze'     => [],
    'noCurrency'        => [],
    'noCurrencyAr'      => [],
    'noDial'            => [],
    'noCapital'         => [],
    'capitalNoCoords'   => [],
    'capitalUnresolved' => [],
    'noTimezone'        => [],
    'overrides'         => [],
];

$curatedCodes = array_column($curated, 'code');

// Currency names, looked up across all of mledoze (needed for overrides).
$currencyEn = [];
foreach ($mledoze as $m) {
    foreach ($m['currencies'] ?? [] as $code => $cur) {
        $currencyEn[$code] ??= $cur;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Build countries
// ─────────────────────────────────────────────────────────────────────────────

$generated = [];

foreach ($mledoze as $m) {
    $iso2 = $m['cca2'];

    if (in_array($iso2, $curatedCodes, true)) {
        continue;
    }

    $generated[] = buildCountry($m);
}

usort($generated, fn ($a, $b) => strcmp($a['names']['common']['en'], $b['names']['common']['en']));

$countries = [];

foreach ($curated as $c) {
    $c['filters'] = array_values(array_merge(
        $c['filters'],
        array_diff(filtersFor($c['code'], curatedOnly: true), $c['filters'])
    ));
    $countries[] = $c;
}

foreach ($generated as $c) {
    $countries[] = $c;
}

// ─────────────────────────────────────────────────────────────────────────────
// Build cities: curated verbatim + one capital per generated country
// ─────────────────────────────────────────────────────────────────────────────

$newCities = [];

foreach ($generated as $c) {
    $capital = buildCapital($c);

    if ($capital) {
        $newCities[] = $capital;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Write outputs
// ─────────────────────────────────────────────────────────────────────────────

$outputs = [];

$outputs['data/countries.json'] = DataFormatter::countriesJson($countries);
$outputs['data/cities.json']     = DataFormatter::appendCities(file_get_contents("{$root}/resources/curated/cities.json"), $newCities);
$outputs['config/countries.php'] = DataFormatter::countriesConfig($countries, 'All countries');
$outputs['config/source/countries-all.php'] = $outputs['config/countries.php'];

foreach ($regions as $tag => $def) {
    $subset = array_values(array_filter($countries, fn ($c) => in_array($tag, $c['filters'], true)));
    $outputs["config/source/countries-{$tag}.php"] = DataFormatter::countriesConfig($subset, $def['label']);
}

$outputs['resources/build-report.md'] = renderReport($countries, $newCities, $curatedCities);

$changed = [];

foreach ($outputs as $path => $contents) {
    $full = "{$root}/{$path}";

    if (! file_exists($full) || file_get_contents($full) !== $contents) {
        $changed[] = $path;

        if (! $check) {
            file_put_contents($full, $contents);
        }
    }
}

// Source files for tags that no longer exist would silently keep seeding.
foreach (glob("{$root}/config/source/countries-*.php") as $file) {
    $rel = 'config/source/' . basename($file);

    if (! isset($outputs[$rel])) {
        $changed[] = "{$rel} (stale)";

        if (! $check) {
            unlink($file);
        }
    }
}

printf(
    "%d countries (%d curated, %d generated), %d cities (%d curated, %d capitals added)\n",
    count($countries), count($curated), count($generated),
    count($curatedCities) + count($newCities), count($curatedCities), count($newCities)
);

if ($check) {
    if ($changed) {
        echo "Out of date:\n  " . implode("\n  ", $changed) . "\n";
        exit(1);
    }

    echo "All generated files are up to date.\n";
    exit(0);
}

echo $changed ? "Updated:\n  " . implode("\n  ", $changed) . "\n" : "Nothing changed.\n";

// ═════════════════════════════════════════════════════════════════════════════
// Functions
// ═════════════════════════════════════════════════════════════════════════════

function buildCountry(array $m): array
{
    global $cldrNames, $cldrCurrency, $currencyEn, $report;

    $iso2 = $m['cca2'];
    $lc   = strtolower($iso2);
    $geo  = m49For($iso2);
    $wd   = wikidataCountry($iso2);

    // ── Names ────────────────────────────────────────────────────────────────
    $commonAr = $cldrNames[$iso2] ?? null;
    if ($commonAr === null && isset($m['translations']['ara']['common'])) {
        $commonAr = $m['translations']['ara']['common'];
        $report['arFromMledoze'][] = $iso2;
    }
    if ($commonAr === null) {
        $report['missingCommonAr'][] = $iso2;
    }

    $officialAr = $m['translations']['ara']['official'] ?? null;
    if ($officialAr === null) {
        $report['missingOfficialAr'][] = $iso2;
    }

    // ── Currency ─────────────────────────────────────────────────────────────
    $currencyCode = array_key_first($m['currencies'] ?? []) ?? null;
    if ($currencyCode === null && isset(CURRENCY_OVERRIDES[$iso2])) {
        $currencyCode = CURRENCY_OVERRIDES[$iso2];
        $report['overrides'][] = "{$iso2} currency → {$currencyCode} (ISO 4217)";
    }
    if ($currencyCode === null) {
        $report['noCurrency'][] = $iso2;
    }

    $curEn = $m['currencies'][$currencyCode] ?? $currencyEn[$currencyCode] ?? [];
    $curAr = $cldrCurrency[$currencyCode] ?? [];
    if ($currencyCode !== null && ! isset($curAr['displayName'])) {
        $report['noCurrencyAr'][] = $iso2;
    }

    // ── Dial ─────────────────────────────────────────────────────────────────
    $dial = dialFor($iso2, $m['idd'] ?? []);
    if ($dial === null) {
        $report['noDial'][] = $iso2;
    }

    // ── Timezones ────────────────────────────────────────────────────────────
    $timezones = timezonesFor($iso2);
    if (! $timezones) {
        $report['noTimezone'][] = $iso2;
    }

    $capital = $m['capital'][0] ?? null;
    if ($capital === null) {
        $report['noCapital'][] = $iso2;
    }

    $name = $m['name']['common'];

    return [
        'names' => [
            'common'   => ['en' => $name, 'ar' => $commonAr],
            'official' => ['en' => $m['name']['official'], 'ar' => $officialAr],
        ],
        'code'        => $iso2,
        'iso2'        => $iso2,
        'iso3'        => $m['cca3'],
        'numericCode' => ($m['ccn3'] ?? '') !== '' ? $m['ccn3'] : null,
        'cioc'        => ($m['cioc'] ?? '') !== '' ? $m['cioc'] : null,
        'flag'        => flagEmoji($iso2, $m['flag'] ?? ''),
        'emoji'       => flagEmoji($iso2, $m['flag'] ?? ''),
        'flags'       => [
            'png' => "https://flagcdn.com/w320/{$lc}.png",
            'svg' => "https://flagcdn.com/{$lc}.svg",
        ],
        'coatOfArms' => [
            'png' => $wd['coatOfArms'] ? $wd['coatOfArms'] . '?width=320' : null,
            'svg' => $wd['coatOfArms'] ?? null,
        ],
        'maps' => [
            'googleMaps'     => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($name),
            'openStreetMaps' => $wd['osmRelation'] ? "https://www.openstreetmap.org/relation/{$wd['osmRelation']}" : null,
        ],
        'currency' => [
            'code'   => $currencyCode,
            'name'   => ['en' => $curEn['name'] ?? null, 'ar' => $curAr['displayName'] ?? null],
            // CLDR resolves a missing locale symbol to the ISO code (root locale); so do we.
            'symbol' => ['en' => $curEn['symbol'] ?? null, 'ar' => stripBidi($curAr['symbol'] ?? $curAr['symbol-alt-narrow'] ?? (string) $currencyCode) ?: null],
        ],
        'dial'      => $dial,
        'capital'   => $capital,
        'region'    => $geo['region'],
        'continent' => $geo['continent'],
        'subregion' => $geo['subregion'],
        'languages' => languagesFor($m['languages'] ?? []),
        'timezones' => $timezones,
        'tld'       => array_values($m['tld'] ?? []),
        'borders'   => array_values($m['borders'] ?? []),
        'geo'       => [
            'latitude'  => isset($m['latlng'][0]) ? (float) $m['latlng'][0] : null,
            'longitude' => isset($m['latlng'][1]) ? (float) $m['latlng'][1] : null,
        ],
        'population' => $wd['population'],
        'area'       => $m['area'] ?? null,
        'filters'    => filtersFor($iso2),
    ];
}

/**
 * Tags a country qualifies for, in definition order.
 * $curatedOnly limits the result to tags that may be appended to a curated country.
 */
function filtersFor(string $iso2, bool $curatedOnly = false): array
{
    global $regions;

    $geo  = m49For($iso2);
    $tags = [];

    foreach ($regions as $tag => $def) {
        if ($curatedOnly && $def['curated']) {
            continue;
        }

        if (isset($def['codes'])) {
            $in = in_array($iso2, $def['codes'], true);
        } else {
            $in = false;
            foreach ($def['m49'] as $field => $values) {
                $in = $in || in_array($geo['m49'][$field] ?? null, $values, true);
            }
        }

        if ($in) {
            $tags[] = $tag;
        }
    }

    return $tags;
}

/** region / subregion / continent fields plus the raw M49 record. */
function m49For(string $iso2): array
{
    global $m49;

    $rec = $m49[$iso2] ?? ['region' => null, 'subregion' => null, 'intermediateRegion' => null];

    $continent = match (true) {
        $rec['region'] === null                          => 'Antarctica',
        $rec['intermediateRegion'] === 'South America'   => 'South America',
        $rec['region'] === 'Americas'                    => 'North America',
        default                                          => $rec['region'],
    };

    return [
        'region'    => $rec['region'] ?? 'Antarctic',
        'subregion' => $rec['intermediateRegion'] ?? $rec['subregion'],
        'continent' => $continent,
        'm49'       => $rec,
    ];
}

/** The main Wikidata item for a code: lowest numeric QID when several share it. */
function wikidataCountry(string $iso2): array
{
    global $wdCountries;

    $items = $wdCountries[$iso2] ?? [];
    usort($items, fn ($a, $b) => (int) substr($a['qid'], 1) <=> (int) substr($b['qid'], 1));

    return $items[0] ?? ['qid' => null, 'osmRelation' => null, 'coatOfArms' => null, 'population' => null];
}

function dialFor(string $iso2, array $idd): ?string
{
    global $report;

    if (isset(DIAL_OVERRIDES[$iso2])) {
        $report['overrides'][] = "{$iso2} dial → " . DIAL_OVERRIDES[$iso2];

        return DIAL_OVERRIDES[$iso2];
    }

    $root     = $idd['root'] ?? '';
    $suffixes = $idd['suffixes'] ?? [];

    if ($root === '') {
        return null;
    }

    // One suffix: it is part of the country code (+9 + 62 → +962).
    // Several suffixes: they are area codes under a shared code (+1 US, +7 RU/KZ).
    return count($suffixes) === 1 ? $root . $suffixes[0] : $root;
}

function timezonesFor(string $iso2): array
{
    global $zones, $report;

    if (isset(TIMEZONE_OVERRIDES[$iso2])) {
        $report['overrides'][] = "{$iso2} timezones → " . implode(', ', TIMEZONE_OVERRIDES[$iso2]);

        return TIMEZONE_OVERRIDES[$iso2];
    }

    $list = array_keys($zones[$iso2] ?? []);
    sort($list);

    return $list;
}

function languagesFor(array $languages): array
{
    global $iso639;

    $codes = [];
    foreach (array_keys($languages) as $code) {
        $codes[] = $iso639[$code] ?? $code;
    }

    return array_values(array_unique($codes));
}

function flagEmoji(string $iso2, string $upstream): string
{
    if ($upstream !== '') {
        return $upstream;
    }

    // Regional-indicator pair — the Unicode definition of a flag emoji.
    return implode('', array_map(
        fn ($ch) => mb_chr(0x1F1E6 + ord($ch) - ord('A')),
        str_split(strtoupper($iso2))
    ));
}

function stripBidi(string $s): string
{
    return trim(str_replace(["\u{200E}", "\u{200F}", "\u{061C}"], '', $s));
}

/** The capital of a generated country as a cities.json row (null when none). */
function buildCapital(array $country): ?array
{
    global $wdCapitals, $zones, $report;

    $iso2 = $country['code'];
    $name = $country['capital'];

    if ($name === null) {
        return null;
    }

    $mainQid = wikidataCountry($iso2)['qid'];
    $match   = null;

    // Candidates: the country's own P36 capitals, then label lookups (fetch-sources.php).
    foreach ($wdCapitals[$iso2] ?? [] as $c) {
        $ours = $c['countryQid'] === $mainQid || ($c['matchedBy'] ?? null) === 'label';

        if ($ours && (namesMatch($name, (string) $c['en']) || $c['qid'] === (CAPITAL_SAME_PLACE[$iso2] ?? null))) {
            $match = $c;
            break;
        }
    }

    if ($match === null) {
        $report['capitalUnresolved'][] = "{$iso2} {$name}";
    }

    $lat = $match['latitude'] ?? null;
    $lng = $match['longitude'] ?? null;

    if ($match !== null && $lat === null) {
        $report['capitalNoCoords'][] = "{$iso2} {$name}";
    }

    return [
        'country_code' => $iso2,
        'name_en'      => $name,
        'name_ar'      => $match['ar'] ?? null,
        'is_capital'   => true,
        'latitude'     => $lat !== null ? round($lat, 4) : null,
        'longitude'    => $lng !== null ? round($lng, 4) : null,
        'population'   => $match['population'] ?? null,
        'timezone'     => CAPITAL_TIMEZONE_OVERRIDES[$iso2] ?? nearestZone($iso2, $lat, $lng, $country['timezones']),
    ];
}

/** The country's zone whose tzdb reference point is closest to the city. */
function nearestZone(string $iso2, ?float $lat, ?float $lng, array $fallback): ?string
{
    global $zones;

    $candidates = $zones[$iso2] ?? [];

    if ($lat === null || ! $candidates) {
        return $fallback[0] ?? null;
    }

    $best = null;
    $bestDistance = INF;

    foreach ($candidates as $zone => [$zLat, $zLng]) {
        $d = ($zLat - $lat) ** 2 + (($zLng - $lng) * cos(deg2rad($lat))) ** 2;
        if ($d < $bestDistance) {
            $best = $zone;
            $bestDistance = $d;
        }
    }

    return $best;
}

/** @return array<string, array<string, array{float, float}>> country => zone => [lat, lng] */
function parseZoneTab(string $path): array
{
    $zones = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        [$cc, $coords, $zone] = explode("\t", $line);
        preg_match('/^([+-]\d{4,6})([+-]\d{5,7})$/', $coords, $m);
        $zones[$cc][$zone] = [dms($m[1], 2), dms($m[2], 3)];
    }

    return $zones;
}

function dms(string $v, int $degDigits): float
{
    $sign = $v[0] === '-' ? -1 : 1;
    $v    = substr($v, 1);
    $deg  = (int) substr($v, 0, $degDigits);
    $min  = (int) substr($v, $degDigits, 2);
    $sec  = (int) (substr($v, $degDigits + 2, 2) ?: 0);

    return $sign * ($deg + $min / 60 + $sec / 3600);
}

/** @return array<string, string> ISO 639-3 => ISO 639-1 */
function parseIso639(string $path): array
{
    $map = [];

    foreach (array_slice(file($path, FILE_IGNORE_NEW_LINES), 1) as $line) {
        $cols = explode("\t", $line);
        if (($cols[3] ?? '') !== '') {
            $map[$cols[0]] = $cols[3];
            if ($cols[1] !== '') {
                $map[$cols[1]] = $cols[3];   // ISO 639-2/B codes, e.g. "per"
            }
        }
    }

    return $map;
}

function renderReport(array $countries, array $newCities, array $curatedCities): string
{
    global $report, $regions;

    $name = [];
    foreach ($countries as $c) {
        $name[$c['code']] = $c['names']['common']['en'];
    }

    $list = function (array $codes) use ($name): string {
        if (! $codes) {
            return "_None._\n";
        }
        $out = '';
        foreach ($codes as $code) {
            $out .= '- ' . (isset($name[$code]) ? "{$code} — {$name[$code]}" : $code) . "\n";
        }
        return $out;
    };

    $lines = fn (array $items) => $items ? '- ' . implode("\n- ", $items) . "\n" : "_None._\n";

    $md  = "# Data build report\n\n";
    $md .= "Generated by `scripts/build-data.php`. Do not edit by hand.\n\n";
    $md .= "## Totals\n\n";
    $md .= '- Countries: ' . count($countries) . "\n";
    $md .= '- Cities: ' . (count($curatedCities) + count($newCities)) . ' (' . count($curatedCities) . ' curated + ' . count($newCities) . " generated capitals)\n\n";
    $md .= "## Countries per filter\n\n| Filter | Kind | Countries |\n|---|---|---|\n";
    foreach ($regions as $tag => $def) {
        $n = count(array_filter($countries, fn ($c) => in_array($tag, $c['filters'], true)));
        $md .= "| `{$tag}` | {$def['kind']} | {$n} |\n";
    }
    $md .= "\n## Missing Arabic common name\n\n" . $list($report['missingCommonAr']);
    $md .= "\n## Missing Arabic official name\n\n" . $list($report['missingOfficialAr']);
    $md .= "\n## Arabic common name taken from mledoze (not in CLDR)\n\n" . $list($report['arFromMledoze']);
    $md .= "\n## No currency\n\n" . $list($report['noCurrency']);
    $md .= "\n## No Arabic currency name in CLDR\n\n" . $list($report['noCurrencyAr']);
    $md .= "\n## No dial code\n\n" . $list($report['noDial']);
    $md .= "\n## No timezone\n\n" . $list($report['noTimezone']);
    $md .= "\n## No capital (no city generated)\n\n" . $list($report['noCapital']);
    $md .= "\n## Capital without coordinates\n\n" . $lines($report['capitalNoCoords']);
    $md .= "\n## Capital not found on Wikidata (no coordinates, no Arabic name)\n\n" . $lines($report['capitalUnresolved']);
    $md .= "\n## Overrides applied\n\n" . $lines(array_values(array_unique($report['overrides'])));

    return $md;
}
