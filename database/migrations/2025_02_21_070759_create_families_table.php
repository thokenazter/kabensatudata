<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->string('family_number')->comment('No Urut Keluarga');
            $table->string('head_name')->comment('Nama Kepala Keluarga');

            // Kesehatan Keluarga
            $table->boolean('has_clean_water')->nullable()->comment('Tersedia sarana air bersih?');
            $table->boolean('is_water_protected')->nullable()->comment('Jenis sumber air terlindungi?');
            $table->boolean('has_toilet')->nullable()->comment('Tersedia jamban keluarga?');
            $table->boolean('is_toilet_sanitary')->nullable()->comment('Jenis jamban saniter?');
            $table->boolean('has_mental_illness')->nullable()->comment('Ada anggota keluarga dengan gangguan jiwa berat?');
            $table->boolean('takes_medication_regularly')->nullable()->comment('Penderita minum obat teratur?');
            $table->boolean('has_restrained_member')->nullable()->comment('Ada anggota keluarga yang dipasung?');

            $table->timestamps();

            // Kombinasi building_id dan family_number harus unik
            $table->unique(['building_id', 'family_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
