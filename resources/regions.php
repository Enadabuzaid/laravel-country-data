<?php

/**
 * Region / filter definitions — the single place that decides which countries
 * carry which `filters` tag. Read by scripts/build-data.php; the Region enum is
 * generated from this file too.
 *
 * Every tag is one of two kinds:
 *
 *   geographic — derived from the UN M49 standard (resources/source/un-m49.json),
 *                i.e. where a country *is*.
 *   political  — an explicit ISO-2 membership list, i.e. what a country *belongs
 *                to*. These change over time; the date each list was checked is
 *                noted next to it. Update the list and rebuild when they change.
 *
 * How tags are applied (see build-data.php):
 *   - The 22 curated countries keep their existing filters exactly, in order.
 *     Tags listed in `'curated' => false` below are *appended* when a curated
 *     country qualifies (e.g. JO gains `levant`, SA gains `gcc` and `g20`).
 *     Tags with `'curated' => true` are never recomputed for them.
 *   - Every other country gets every tag it qualifies for.
 *
 * Countries missing from M49 (XK Kosovo, TW Taiwan) fall back to the region M49
 * uses for the surrounding area — see M49_FALLBACK in build-data.php.
 */

return [

    // ── Political / cultural groupings ──────────────────────────────────────

    'arab' => [
        'label'       => 'Arab League',
        'kind'        => 'political',
        'curated'     => true,
        'description' => 'The 22 member states of the Arab League.',
        'codes'       => ['JO', 'SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'IQ', 'SY', 'LB', 'PS', 'EG', 'LY', 'TN', 'DZ', 'MA', 'SD', 'YE', 'MR', 'SO', 'DJ', 'KM'],
    ],

    'gulf' => [
        'label'       => 'Arabian Gulf',
        'kind'        => 'political',
        'curated'     => true,
        'description' => 'Arab states on the Arabian Gulf: the six GCC members plus Iraq. '
                       . 'Kept as shipped since v2.0. For strict GCC membership use `gcc`.',
        'codes'       => ['SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'IQ'],
    ],

    'gcc' => [
        'label'       => 'Gulf Cooperation Council',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The six member states of the Gulf Cooperation Council (checked 2026-09). '
                       . 'Unlike `gulf`, it excludes Iraq.',
        'codes'       => ['SA', 'AE', 'KW', 'QA', 'OM', 'BH'],
    ],

    'levant' => [
        'label'       => 'Levant',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The Levant in its common narrow sense (Bilad al-Sham): Jordan, Lebanon, '
                       . 'Palestine, Syria and Israel.',
        'codes'       => ['JO', 'LB', 'PS', 'SY', 'IL'],
    ],

    'maghreb' => [
        'label'       => 'Maghreb',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The five member states of the Arab Maghreb Union: Algeria, Libya, '
                       . 'Mauritania, Morocco and Tunisia.',
        'codes'       => ['DZ', 'LY', 'MR', 'MA', 'TN'],
    ],

    'middle-east' => [
        'label'       => 'Middle East',
        'kind'        => 'political',
        'curated'     => true,
        'description' => 'The curated Arab Middle East set (Arab states of Western Asia plus Egypt, as '
                       . 'shipped since v2.0) extended with Iran, Israel, Turkey and Cyprus.',
        'codes'       => ['JO', 'SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'IQ', 'SY', 'LB', 'PS', 'EG', 'YE', 'IR', 'IL', 'TR', 'CY'],
    ],

    'muslim-majority' => [
        'label'       => 'Muslim-majority',
        'kind'        => 'political',
        'curated'     => true,
        'description' => 'Countries and territories where Muslims are more than 50% of the population, '
                       . 'per Pew Research Center, "The Future of the Global Muslim Population" (2011). '
                       . 'The 22 curated countries keep their shipped tags (Lebanon is not tagged).',
        'codes'       => [
            // curated (as shipped)
            'JO', 'SA', 'AE', 'KW', 'QA', 'OM', 'BH', 'IQ', 'SY', 'PS', 'EG', 'LY', 'TN', 'DZ', 'MA', 'SD', 'YE', 'MR', 'SO', 'DJ', 'KM',
            // added
            'AF', 'AL', 'AZ', 'BD', 'BN', 'BF', 'TD', 'GM', 'GN', 'ID', 'IR', 'KZ', 'XK', 'KG', 'MY', 'MV',
            'ML', 'YT', 'NE', 'PK', 'SN', 'SL', 'TJ', 'TR', 'TM', 'UZ', 'EH',
        ],
    ],

    // ── Geographic (UN M49) ─────────────────────────────────────────────────

    'africa' => [
        'label'       => 'Africa',
        'kind'        => 'geographic',
        'curated'     => true,
        'description' => 'UN M49 region 002 "Africa".',
        'm49'         => ['region' => ['Africa']],
    ],

    'asia' => [
        'label'       => 'Asia',
        'kind'        => 'geographic',
        'curated'     => true,
        'description' => 'UN M49 region 142 "Asia" (includes Western Asia, so Cyprus and Turkey are here).',
        'm49'         => ['region' => ['Asia']],
    ],

    'europe' => [
        'label'       => 'Europe',
        'kind'        => 'geographic',
        'curated'     => false,
        'description' => 'UN M49 region 150 "Europe" (includes Russia; Cyprus and Turkey are in Asia per M49).',
        'm49'         => ['region' => ['Europe']],
    ],

    'north-america' => [
        'label'       => 'North America',
        'kind'        => 'geographic',
        'curated'     => false,
        'description' => 'The North American continent: UN M49 sub-region 021 "Northern America" plus the '
                       . 'intermediate regions 013 "Central America" and 029 "Caribbean".',
        'm49'         => ['subregion' => ['Northern America'], 'intermediateRegion' => ['Central America', 'Caribbean']],
    ],

    'south-america' => [
        'label'       => 'South America',
        'kind'        => 'geographic',
        'curated'     => false,
        'description' => 'UN M49 intermediate region 005 "South America".',
        'm49'         => ['intermediateRegion' => ['South America']],
    ],

    'oceania' => [
        'label'       => 'Oceania',
        'kind'        => 'geographic',
        'curated'     => false,
        'description' => 'UN M49 region 009 "Oceania".',
        'm49'         => ['region' => ['Oceania']],
    ],

    // ── Political unions ────────────────────────────────────────────────────

    'eu' => [
        'label'       => 'European Union',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The 27 member states of the European Union (checked 2026-09).',
        'codes'       => ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE',
                          'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'],
    ],

    'schengen' => [
        'label'       => 'Schengen Area',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The 29 full members of the Schengen Area, including Bulgaria and Romania since '
                       . '2025-01-01 (checked 2026-09). Cyprus and Ireland are EU members outside Schengen.',
        'codes'       => ['AT', 'BE', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IT', 'LV',
                          'LI', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH'],
    ],

    'g20' => [
        'label'       => 'G20',
        'kind'        => 'political',
        'curated'     => false,
        'description' => 'The 19 sovereign-state members of the G20 (checked 2026-09). The EU and the African '
                       . 'Union are members too but are not countries, so they have no rows here.',
        'codes'       => ['AR', 'AU', 'BR', 'CA', 'CN', 'FR', 'DE', 'IN', 'ID', 'IT', 'JP', 'KR', 'MX', 'RU',
                          'SA', 'ZA', 'TR', 'GB', 'US'],
    ],
];
