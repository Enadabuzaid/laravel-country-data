<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AreaHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private function seedGeography(): void
    {
        $this->artisan('db:seed', ['--class' => GeographySeeder::class]);
    }

    private function ammanId(): int
    {
        return DB::table('cities')->where('name_en', 'Amman')->value('id');
    }

    // ── Structure ─────────────────────────────────────────────────────────────

    public function test_amman_neighborhoods_all_have_a_parent(): void
    {
        $this->seedGeography();

        $orphans = DB::table('areas')
            ->where('city_id', $this->ammanId())
            ->where('type', 'neighborhood')
            ->whereNull('parent_id')
            ->pluck('name_en')
            ->all();

        $this->assertSame([], $orphans, 'Amman neighborhoods without a district: '.implode(', ', $orphans));
    }

    public function test_streets_hang_off_a_district(): void
    {
        $this->seedGeography();

        $streets = Area::where('city_id', $this->ammanId())->ofType('street')->with('parent')->get();

        $this->assertNotEmpty($streets);

        foreach ($streets as $street) {
            $this->assertNotNull($street->parent_id, "{$street->name_en} has no parent");
            $this->assertSame('district', $street->parent->type);
        }
    }

    public function test_districts_are_always_roots(): void
    {
        $this->seedGeography();

        $this->assertSame(0, Area::ofType('district')->whereNotNull('parent_id')->count());
    }

    public function test_parent_is_always_in_the_same_city(): void
    {
        $this->seedGeography();

        $mismatched = DB::table('areas as a')
            ->join('areas as p', 'a.parent_id', '=', 'p.id')
            ->whereColumn('a.city_id', '!=', 'p.city_id')
            ->count();

        $this->assertSame(0, $mismatched);
    }

    public function test_hierarchy_is_at_most_two_levels(): void
    {
        $this->seedGeography();

        // A grandchild would mean some row's parent itself has a parent.
        $deep = DB::table('areas as a')
            ->join('areas as p', 'a.parent_id', '=', 'p.id')
            ->whereNotNull('p.parent_id')
            ->count();

        $this->assertSame(0, $deep);
    }

    public function test_no_area_is_its_own_parent(): void
    {
        $this->seedGeography();

        $this->assertSame(0, DB::table('areas')->whereColumn('parent_id', 'id')->count());
    }

    public function test_non_amman_areas_stay_flat(): void
    {
        $this->seedGeography();

        $this->assertSame(
            0,
            Area::where('city_id', '!=', $this->ammanId())->whereNotNull('parent_id')->count(),
            'Only Amman is two-level in this release.'
        );
    }

    public function test_zones_remain_at_root(): void
    {
        $this->seedGeography();

        $this->assertSame(0, Area::ofType('zone')->whereNotNull('parent_id')->count());
    }

    // ── The specific rows that were reported missing ──────────────────────────

    public function test_tabarbour_is_seeded_under_a_district(): void
    {
        $this->seedGeography();

        $area = Area::where('city_id', $this->ammanId())->where('name_en', 'Tabarbour')->first();

        $this->assertNotNull($area, 'Tabarbour is missing from Amman');
        $this->assertSame('طبربور', $area->name_ar);
        $this->assertTrue($area->is_active);
        $this->assertNotNull($area->parent_id);
        $this->assertSame('district', $area->parent->type);
    }

    public function test_madina_munawwara_street_is_seeded(): void
    {
        $this->seedGeography();

        $area = Area::where('city_id', $this->ammanId())
            ->where('name_en', 'Al-Madina Al-Munawwara Street')
            ->first();

        $this->assertNotNull($area, 'Al-Madina Al-Munawwara Street is missing from Amman');
        $this->assertSame('شارع المدينة المنورة', $area->name_ar);
        $this->assertSame('street', $area->type);
        $this->assertNotNull($area->parent_id);
    }

    public function test_amman_has_comprehensive_coverage(): void
    {
        $this->seedGeography();

        $count = Area::where('city_id', $this->ammanId())->count();

        $this->assertGreaterThan(150, $count, "Amman only has {$count} areas");
        $this->assertGreaterThanOrEqual(22, Area::where('city_id', $this->ammanId())->ofType('district')->count());
    }

    // ── Seeder behaviour ──────────────────────────────────────────────────────

    public function test_seeding_twice_does_not_duplicate_or_relink(): void
    {
        $this->seedGeography();

        $before = DB::table('areas')->orderBy('id')->pluck('parent_id', 'id')->toArray();

        $this->seedGeography();

        $after = DB::table('areas')->orderBy('id')->pluck('parent_id', 'id')->toArray();

        $this->assertSame($before, $after, 'Re-seeding changed the hierarchy');
    }

    public function test_reseeding_adopts_a_legacy_flat_row_instead_of_duplicating_it(): void
    {
        $this->seedGeography();

        $amman = $this->ammanId();

        // Simulate a pre-2.1 install: the row exists, flat, with no parent.
        DB::table('areas')->where('city_id', $amman)->where('name_en', 'Abdoun')
            ->update(['parent_id' => null]);

        $this->seedGeography();

        $rows = DB::table('areas')->where('city_id', $amman)->where('name_en', 'Abdoun')->get();

        $this->assertCount(1, $rows, 'Legacy flat row was duplicated instead of adopted');
        $this->assertNotNull($rows->first()->parent_id, 'Legacy row was not re-parented');
    }

    public function test_country_filter_still_links_parents(): void
    {
        $this->artisan('country-data:setup', ['--seed' => true, '--countries' => 'JO']);

        $orphans = DB::table('areas')
            ->where('city_id', $this->ammanId())
            ->where('type', 'neighborhood')
            ->whereNull('parent_id')
            ->count();

        $this->assertSame(0, $orphans);
    }

    // ── Model relations ───────────────────────────────────────────────────────

    public function test_parent_and_children_relations_resolve(): void
    {
        $this->seedGeography();

        $district = Area::where('city_id', $this->ammanId())
            ->ofType('district')
            ->has('children')
            ->first();

        $this->assertNotNull($district);
        $this->assertNotEmpty($district->children);

        $child = $district->children->first();
        $this->assertSame($district->id, $child->parent->id);
    }

    public function test_roots_scope_excludes_children(): void
    {
        $this->seedGeography();

        $roots = Area::where('city_id', $this->ammanId())->roots()->get();

        $this->assertNotEmpty($roots);
        $roots->each(fn (Area $a) => $this->assertNull($a->parent_id));
    }
}
