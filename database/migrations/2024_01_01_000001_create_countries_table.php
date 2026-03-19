<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();        // ISO 3166-1 alpha-2 (JO, SA, AE…)
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();
            $table->string('numeric_code', 10)->nullable();
            $table->string('cioc', 10)->nullable();

            // Names
            $table->string('name_en');
            $table->string('name_ar')->nullable();
            $table->string('official_name_en')->nullable();
            $table->string('official_name_ar')->nullable();

            // Symbols
            $table->string('flag', 10)->nullable();
            $table->string('emoji', 10)->nullable();
            $table->string('flag_png')->nullable();
            $table->string('flag_svg')->nullable();
            $table->string('coat_of_arms_png')->nullable();
            $table->string('coat_of_arms_svg')->nullable();

            // Maps
            $table->string('google_maps_url')->nullable();
            $table->string('openstreet_maps_url')->nullable();

            // Currency
            $table->string('currency_code', 10)->nullable();
            $table->string('currency_name_en')->nullable();
            $table->string('currency_name_ar')->nullable();
            $table->string('currency_symbol_en', 20)->nullable();
            $table->string('currency_symbol_ar', 20)->nullable();

            // Contact / Location info
            $table->string('dial', 15)->nullable();
            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->string('continent')->nullable();
            $table->string('subregion')->nullable();

            // Arrays stored as JSON
            $table->json('languages')->nullable();
            $table->json('timezones')->nullable();
            $table->json('tld')->nullable();
            $table->json('borders')->nullable();
            $table->json('filters')->nullable();

            // Geography
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->bigInteger('population')->nullable();
            $table->decimal('area', 15, 2)->nullable();   // km²

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
