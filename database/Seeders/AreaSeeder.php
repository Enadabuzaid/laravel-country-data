<?php

namespace Enadstack\CountryData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds data/areas.json into the `areas` table.
 *
 * Areas form a two-level tree. A row is a ROOT when it has no `parent_en`
 * (districts, zones, standalone areas); it is a CHILD when `parent_en` names
 * another row's `name_en` within the same city (neighborhoods, streets).
 *
 * Roots are seeded first so that every child's parent_id is already known when
 * the child pass runs. That ordering also lets children match on
 * (city_id, name_en, parent_id) — necessary because the same neighborhood name
 * legitimately repeats across districts (Al-Rawdah, Al-Ashrafieh, Um Al-Summaq).
 */
class AreaSeeder extends Seeder
{
    /**
     * Optional country code filter (ISO-2 uppercase).
     * Empty array = seed all areas.
     *
     * A filter can never split a parent from its child: both always carry the
     * same country_code and city_name_en, so they are kept or dropped together.
     *
     * @var string[]
     */
    public array $countryCodes = [];

    public function run(): void
    {
        $path = __DIR__ . '/../../data/areas.json';

        if (! file_exists($path)) {
            $this->command->error("areas.json not found at: {$path}");
            return;
        }

        $areas = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse areas.json: ' . json_last_error_msg());
            return;
        }

        // Filter to selected countries when a subset was requested
        if (! empty($this->countryCodes)) {
            $codes = array_map('strtoupper', $this->countryCodes);
            $areas = array_filter($areas, fn ($a) => in_array(strtoupper($a['country_code']), $codes, true));
            $areas = array_values($areas);
        }

        // Pre-load city id map: "COUNTRY_CODE|city_name_en" => id
        $cityMap = DB::table('cities')
            ->get(['id', 'country_code', 'name_en'])
            ->mapWithKeys(fn ($row) => [
                strtoupper($row->country_code) . '|' . $row->name_en => $row->id,
            ])
            ->toArray();

        // Split into roots and children, preserving file order within each group
        $roots    = array_values(array_filter($areas, fn ($a) => empty($a['parent_en'])));
        $children = array_values(array_filter($areas, fn ($a) => ! empty($a['parent_en'])));

        $this->command->info('Seeding areas…');
        $bar = $this->command->getOutput()->createProgressBar(count($areas));
        $bar->start();

        $skipped       = 0;
        $orphaned      = 0;
        $linked        = 0;
        $touchedCities = [];

        DB::transaction(function () use (
            $roots, $children, $cityMap, $bar,
            &$skipped, &$orphaned, &$linked, &$touchedCities
        ) {
            // ── Pass A: roots ────────────────────────────────────────────────
            foreach ($roots as $a) {
                $cityId = $cityMap[strtoupper($a['country_code']) . '|' . $a['city_name_en']] ?? null;

                if (! $cityId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $touchedCities[$cityId] = true;

                // parent_id => null in the match key keeps this pass from
                // clobbering a child row that shares a name with a root.
                // Laravel turns where('parent_id', null) into whereNull().
                DB::table('areas')->updateOrInsert(
                    [
                        'city_id'   => $cityId,
                        'name_en'   => $a['name_en'],
                        'parent_id' => null,
                    ],
                    $this->payload($a, $cityId, null)
                );

                $bar->advance();
            }

            // ── Pass B: children ─────────────────────────────────────────────
            // Rebuild the id map from the roots we just wrote.
            $rootIds = DB::table('areas')
                ->whereIn('city_id', array_keys($touchedCities))
                ->whereNull('parent_id')
                ->get(['id', 'city_id', 'name_en'])
                ->mapWithKeys(fn ($r) => [$r->city_id . '|' . $r->name_en => $r->id])
                ->toArray();

            foreach ($children as $a) {
                $cityId = $cityMap[strtoupper($a['country_code']) . '|' . $a['city_name_en']] ?? null;

                if (! $cityId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $parentId = $rootIds[$cityId . '|' . $a['parent_en']] ?? null;

                if ($parentId === null) {
                    // Parent named but not found — keep the area, flag the gap.
                    $orphaned++;
                } else {
                    $linked++;
                }

                /**
                 * Match an existing row on (city_id, name_en) restricted to either
                 * this parent, or NULL. The NULL arm adopts a legacy flat row from
                 * a pre-2.1 install instead of inserting a duplicate alongside it;
                 * the $parentId arm keeps repeat runs idempotent.
                 */
                $existingId = DB::table('areas')
                    ->where('city_id', $cityId)
                    ->where('name_en', $a['name_en'])
                    ->where(fn ($q) => $parentId === null
                        ? $q->whereNull('parent_id')
                        : $q->whereNull('parent_id')->orWhere('parent_id', $parentId)
                    )
                    ->value('id');

                if ($existingId) {
                    DB::table('areas')
                        ->where('id', $existingId)
                        ->update($this->payload($a, $cityId, $parentId, insert: false));
                } else {
                    DB::table('areas')->insert($this->payload($a, $cityId, $parentId));
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->command->newLine();

        $seeded = count($areas) - $skipped;
        $this->command->info(
            "Areas seeded: {$seeded} ({$linked} linked to a parent)" .
            ($skipped  ? " — skipped {$skipped}, city not found" : '') .
            ($orphaned ? " — {$orphaned} with an unresolved parent_en" : '')
        );
    }

    /**
     * Column payload for one area row.
     */
    private function payload(array $a, int $cityId, ?int $parentId, bool $insert = true): array
    {
        $row = [
            'city_id'    => $cityId,
            'parent_id'  => $parentId,
            'name_en'    => $a['name_en'],
            'name_ar'    => $a['name_ar'] ?? null,
            'type'       => $a['type'] ?? 'neighborhood',
            'latitude'   => $a['latitude'] ?? null,
            'longitude'  => $a['longitude'] ?? null,
            'is_active'  => true,
            'updated_at' => now(),
        ];

        if ($insert) {
            $row['created_at'] = now();
        }

        return $row;
    }
}
