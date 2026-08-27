<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('areas', function (Blueprint $table) use ($driver) {
            /**
             * Self-referencing parent. Two levels only:
             *   parent_id NULL  – a root (district / zone / standalone area)
             *   parent_id SET   – a child (neighborhood / street) of a district
             *
             * nullOnDelete, not cascade: removing a district should orphan its
             * neighborhoods up to city level, not delete them. Removing the city
             * still drops the whole subtree via areas.city_id.
             *
             * SQLite gets a plain column: it cannot later DROP a column that is
             * named in a foreign-key definition, which breaks migrate:rollback
             * outright. The cascade that actually protects data (areas.city_id)
             * is declared at table-create time and is unaffected.
             */
            if ($driver === 'sqlite') {
                $table->unsignedBigInteger('parent_id')->nullable()->after('city_id');
            } else {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('city_id')
                    ->constrained('areas')
                    ->nullOnDelete();
            }

            $table->index(['city_id', 'parent_id']);
        });

        $this->pruneBadAmmanRows();
    }

    public function down(): void
    {
        if (! Schema::hasTable('areas') || ! Schema::hasColumn('areas', 'parent_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('areas', fn (Blueprint $table) => $table->dropForeign(['parent_id']));
        }

        /**
         * Each step is its own statement on purpose: SQLite refuses to drop a
         * column while an index still names it, so the index must be gone
         * before the column drop is issued.
         */
        $indexes = collect(Schema::getIndexes('areas'))->pluck('name')->all();

        if (in_array('areas_city_id_parent_id_index', $indexes, true)) {
            Schema::table('areas', fn (Blueprint $table) => $table->dropIndex(['city_id', 'parent_id']));
        }

        Schema::table('areas', fn (Blueprint $table) => $table->dropColumn('parent_id'));
    }

    /**
     * Rows shipped in <= 2.0.2 that are wrong and can never be removed by the
     * seeder (updateOrInsert only inserts or updates, it never deletes):
     *
     *   Al-Luweibdeh – duplicate of Jabal Al-Webdeh (same place, جبل اللويبدة)
     *   Abu Ali      – not an Amman locality; replaced by Abu Alanda
     */
    private function pruneBadAmmanRows(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        if (! $ammanId) {
            return;
        }

        DB::table('areas')
            ->where('city_id', $ammanId)
            ->whereIn('name_en', ['Al-Luweibdeh', 'Abu Ali'])
            ->delete();
    }
};
