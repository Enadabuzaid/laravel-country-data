<?php

/**
 * Download the upstream datasets that scripts/build-data.php consumes and pin
 * them under resources/source/. Run this only to refresh the data; the build
 * itself is fully offline and reproducible from the pinned copies.
 *
 *   php scripts/fetch-sources.php
 *   php scripts/build-data.php
 *
 * Every source is fetched at an exact commit / release so a rerun with the same
 * pins produces the same files. Bump a pin deliberately, then review the diff.
 */

const MLEDOZE_SHA = 'c2ac0049c14edcf2436c7aa1b2493222a020b462';   // mledoze/countries (ODbL-1.0)
const CLDR_SHA    = '13e9fe520d5efe4298b1f988adea284a3396d8f5';   // unicode-org/cldr-json (Unicode-3.0)
const TZDB_TAG    = '2026e';                                      // eggert/tz (public domain)

require __DIR__ . '/lib/functions.php';

$root = dirname(__DIR__);
$out  = $root . '/resources/source';

if (! is_dir($out)) {
    mkdir($out, 0755, true);
}

$userAgent = 'laravel-country-data-build/1.0 (https://github.com/Enadabuzaid/laravel-country-data)';

function fetch(string $url, string $userAgent, array $headers = []): string
{
    $ctx = stream_context_create(['http' => [
        'header'        => array_merge(["User-Agent: {$userAgent}"], $headers),
        'timeout'       => 120,
        'ignore_errors' => false,
    ]]);

    for ($attempt = 1; $attempt <= 4; $attempt++) {
        $body = @file_get_contents($url, false, $ctx);

        if ($body !== false && $body !== '') {
            return $body;
        }

        sleep($attempt * 2);
    }

    fwrite(STDERR, "Failed to fetch {$url}\n");
    exit(1);
}

function sparql(string $query, string $userAgent): array
{
    $url  = 'https://query.wikidata.org/sparql?format=json&query=' . rawurlencode($query);
    $json = json_decode(fetch($url, $userAgent, ['Accept: application/sparql-results+json']), true);

    return $json['results']['bindings'] ?? [];
}

function put(string $path, string $contents): void
{
    file_put_contents($path, $contents);
    echo '  wrote ' . basename($path) . ' (' . number_format(strlen($contents)) . " bytes)\n";
}

echo "Fetching sources…\n";

// ── mledoze/countries ────────────────────────────────────────────────────────
put("{$out}/mledoze-countries.json", fetch(
    'https://raw.githubusercontent.com/mledoze/countries/' . MLEDOZE_SHA . '/countries.json',
    $userAgent
));

// ── CLDR (Arabic locale) ─────────────────────────────────────────────────────
$cldr = 'https://raw.githubusercontent.com/unicode-org/cldr-json/' . CLDR_SHA . '/cldr-json';
put("{$out}/cldr-ar-territories.json", fetch("{$cldr}/cldr-localenames-full/main/ar/territories.json", $userAgent));
put("{$out}/cldr-ar-currencies.json", fetch("{$cldr}/cldr-numbers-full/main/ar/currencies.json", $userAgent));

// ── IANA time zone database: zone.tab (country → zones with coordinates) ─────
put("{$out}/tzdb-zone.tab", fetch('https://raw.githubusercontent.com/eggert/tz/' . TZDB_TAG . '/zone.tab', $userAgent));

// ── SIL ISO 639-3 code table (maps 639-3 → 639-1) ────────────────────────────
put("{$out}/iso-639-3.tab", fetch('https://iso639-3.sil.org/sites/iso639-3/files/downloads/iso-639-3.tab', $userAgent));

// ── UN M49 standard country or area codes ────────────────────────────────────
$html = fetch('https://unstats.un.org/unsd/methodology/m49/overview/', 'Mozilla/5.0 ' . $userAgent);
$dom  = new DOMDocument();
@$dom->loadHTML($html);
$table = $dom->getElementById('downloadTableEN');

if (! $table) {
    fwrite(STDERR, "UN M49 table not found in the overview page\n");
    exit(1);
}

$m49 = [];
foreach ($table->getElementsByTagName('tr') as $i => $tr) {
    $cells = [];
    foreach ($tr->childNodes as $cell) {
        if ($cell->nodeType === XML_ELEMENT_NODE) {
            $cells[] = trim($cell->textContent);
        }
    }

    if ($i === 0 || count($cells) < 12 || $cells[10] === '') {
        continue;
    }

    $m49[$cells[10]] = [
        'm49'                => $cells[9],
        'name'               => $cells[8],
        'region'             => $cells[3] ?: null,
        'subregion'          => $cells[5] ?: null,
        'intermediateRegion' => $cells[7] ?: null,
    ];
}
ksort($m49);
put("{$out}/un-m49.json", json_encode($m49, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

// ── Wikidata (CC0): population, OSM relation, coat of arms, capitals ────────
$countryRows = sparql(<<<'SPARQL'
SELECT ?iso ?c ?osm ?coa ?pop ?popDate ?rank WHERE {
  ?c wdt:P297 ?iso .
  OPTIONAL { ?c wdt:P402 ?osm . }
  OPTIONAL { ?c wdt:P94 ?coa . }
  OPTIONAL {
    ?c p:P1082 ?ps . ?ps ps:P1082 ?pop ; wikibase:rank ?rank .
    OPTIONAL { ?ps pq:P585 ?popDate . }
    FILTER(?rank != wikibase:DeprecatedRank)
  }
}
SPARQL, $userAgent);

$capitalRows = sparql(<<<'SPARQL'
SELECT ?iso ?c ?cap ?en ?ar ?coord ?pop WHERE {
  ?c wdt:P297 ?iso ; wdt:P36 ?cap .
  OPTIONAL { ?cap rdfs:label ?en FILTER(LANG(?en) = "en") }
  OPTIONAL { ?cap rdfs:label ?ar FILTER(LANG(?ar) = "ar") }
  OPTIONAL { ?cap wdt:P625 ?coord . }
  OPTIONAL { ?cap wdt:P1082 ?pop . }
}
SPARQL, $userAgent);

$qid = fn (?array $v) => $v ? basename($v['value']) : null;

// Reduce to one record per (iso, entity). Population: the most recent
// preferred-rank figure, falling back to the most recent normal-rank one.
$countries = [];
foreach ($countryRows as $r) {
    $iso = $r['iso']['value'];
    $id  = $qid($r['c']);
    $rec = &$countries[$iso][$id];
    $rec ??= ['qid' => $id, 'osmRelation' => null, 'coatOfArms' => null, 'population' => null, 'populationDate' => null, '_rank' => -1];

    $rec['osmRelation'] ??= $r['osm']['value'] ?? null;
    $rec['coatOfArms']  ??= isset($r['coa']) ? str_replace('http://', 'https://', $r['coa']['value']) : null;

    if (isset($r['pop'])) {
        $rank = str_ends_with($r['rank']['value'], 'PreferredRank') ? 1 : 0;
        $date = substr($r['popDate']['value'] ?? '0000', 0, 10);

        if ([$rank, $date] > [$rec['_rank'], (string) $rec['populationDate']]) {
            $rec['_rank']          = $rank;
            $rec['population']     = (int) $r['pop']['value'];
            $rec['populationDate'] = $date;
        }
    }
    unset($rec);
}

foreach ($countries as $iso => &$entities) {
    foreach ($entities as &$e) {
        unset($e['_rank']);
    }
    unset($e);
    ksort($entities);
    $entities = array_values($entities);
}
unset($entities);
ksort($countries);

$capitals = [];
foreach ($capitalRows as $r) {
    $iso = $r['iso']['value'];
    $key = $qid($r['c']) . '|' . $qid($r['cap']);
    $rec = &$capitals[$iso][$key];
    $rec ??= ['countryQid' => $qid($r['c']), 'qid' => $qid($r['cap']), 'en' => null, 'ar' => null, 'latitude' => null, 'longitude' => null, 'population' => null];

    $rec['en'] ??= $r['en']['value'] ?? null;
    $rec['ar'] ??= $r['ar']['value'] ?? null;

    if ($rec['latitude'] === null && isset($r['coord']) && preg_match('/Point\(([-\d.eE]+) ([-\d.eE]+)\)/', $r['coord']['value'], $m)) {
        $rec['longitude'] = round((float) $m[1], 6);
        $rec['latitude']  = round((float) $m[2], 6);
    }

    if (isset($r['pop'])) {
        $rec['population'] = max((int) $rec['population'], (int) $r['pop']['value']);
    }
    unset($rec);
}

/**
 * Second pass for capitals whose mledoze name matches none of the country's
 * P36 values (renamed capitals, territories whose P36 is missing). Look the
 * name up by label, then keep the match nearest the country's centroid
 * (within 1000 km), preferring the most populous.
 */
$mledoze   = json_decode(file_get_contents("{$out}/mledoze-countries.json"), true);
$unmatched = [];
$centroid  = [];

foreach ($mledoze as $m) {
    $name = $m['capital'][0] ?? null;
    $iso  = $m['cca2'];

    if ($name === null) {
        continue;
    }

    $found = false;
    foreach ($capitals[$iso] ?? [] as $c) {
        $found = $found || namesMatch($name, (string) $c['en']);
    }

    if (! $found) {
        $unmatched[$iso] = $name;
        $centroid[$iso]  = $m['latlng'] ?? null;
    }
}

if ($unmatched) {
    $values = implode(' ', array_map(
        fn ($iso, $name) => '("' . $iso . '" "' . addcslashes($name, '"\\') . '"@en)',
        array_keys($unmatched),
        $unmatched
    ));

    $rows = sparql(<<<SPARQL
SELECT ?iso ?city ?en ?ar ?coord ?pop WHERE {
  VALUES (?iso ?name) { {$values} }
  { ?city rdfs:label ?name } UNION { ?city skos:altLabel ?name }
  ?city wdt:P625 ?coord .
  OPTIONAL { ?city rdfs:label ?en FILTER(LANG(?en) = "en") }
  OPTIONAL { ?city rdfs:label ?ar FILTER(LANG(?ar) = "ar") }
  OPTIONAL { ?city wdt:P1082 ?pop . }
}
SPARQL, $userAgent);

    $best = [];
    foreach ($rows as $r) {
        $iso = $r['iso']['value'];

        if (! preg_match('/Point\(([-\d.eE]+) ([-\d.eE]+)\)/', $r['coord']['value'], $m) || ! $centroid[$iso]) {
            continue;
        }

        [$lat, $lng] = [(float) $m[2], (float) $m[1]];

        if (haversineKm($lat, $lng, $centroid[$iso][0], $centroid[$iso][1]) > 1000) {
            continue;
        }

        $cand = [
            'countryQid' => null,
            'qid'        => $qid($r['city']),
            'en'         => $unmatched[$iso],
            'ar'         => $r['ar']['value'] ?? null,
            'latitude'   => round($lat, 6),
            'longitude'  => round($lng, 6),
            'population' => isset($r['pop']) ? (int) $r['pop']['value'] : null,
            'matchedBy'  => 'label',
        ];

        $cur  = $best[$iso] ?? null;
        $rank = fn ($c) => [(int) $c['population'], -(int) substr($c['qid'], 1)];

        if ($cur === null || $cur['qid'] === $cand['qid'] || $rank($cand) > $rank($cur)) {
            if ($cur && $cur['qid'] === $cand['qid']) {
                $cand['ar']         ??= $cur['ar'];
                $cand['population'] = max((int) $cur['population'], (int) $cand['population']) ?: null;
            }
            $best[$iso] = $cand;
        }
    }

    foreach ($best as $iso => $cand) {
        $capitals[$iso]['label|' . $cand['qid']] = $cand;
    }

    echo '  capitals resolved by label: ' . count($best) . ' of ' . count($unmatched) . "\n";
}

foreach ($capitals as $iso => &$list) {
    ksort($list);
    $list = array_values($list);
}
unset($list);
ksort($capitals);

put("{$out}/wikidata-countries.json", json_encode($countries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
put("{$out}/wikidata-capitals.json", json_encode($capitals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

// ── Manifest ─────────────────────────────────────────────────────────────────
put("{$out}/manifest.json", json_encode([
    'fetchedAt' => gmdate('Y-m-d\TH:i:s\Z'),
    'sources'   => [
        'mledoze-countries.json'   => ['url' => 'https://github.com/mledoze/countries', 'ref' => MLEDOZE_SHA, 'license' => 'ODbL-1.0'],
        'cldr-ar-territories.json' => ['url' => 'https://github.com/unicode-org/cldr-json', 'ref' => CLDR_SHA, 'license' => 'Unicode-3.0'],
        'cldr-ar-currencies.json'  => ['url' => 'https://github.com/unicode-org/cldr-json', 'ref' => CLDR_SHA, 'license' => 'Unicode-3.0'],
        'tzdb-zone.tab'            => ['url' => 'https://github.com/eggert/tz', 'ref' => TZDB_TAG, 'license' => 'public-domain'],
        'iso-639-3.tab'            => ['url' => 'https://iso639-3.sil.org/code_tables/download_tables', 'ref' => 'latest', 'license' => 'SIL ISO 639-3 terms of use'],
        'un-m49.json'              => ['url' => 'https://unstats.un.org/unsd/methodology/m49/overview/', 'ref' => 'latest', 'license' => 'UN Statistics Division, free use'],
        'wikidata-countries.json'  => ['url' => 'https://query.wikidata.org/', 'ref' => 'query at fetchedAt', 'license' => 'CC0-1.0'],
        'wikidata-capitals.json'   => ['url' => 'https://query.wikidata.org/', 'ref' => 'query at fetchedAt', 'license' => 'CC0-1.0'],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "Done. Now run: php scripts/build-data.php\n";
