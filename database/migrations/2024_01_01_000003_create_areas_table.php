<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();

            $table->string('name_en');
            $table->string('name_ar')->nullable();

            /**
             * Types:
             *   governorate  – administrative governorate (e.g. Amman Governorate)
             *   district     – sub-city district / qadaa (e.g. Wadi Seer District)
             *   neighborhood – residential / commercial area (e.g. Abdoun, Sweifieh)
             *   zone         – industrial / economic zone
             */
            $table->string('type', 30)->default('neighborhood');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['city_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
