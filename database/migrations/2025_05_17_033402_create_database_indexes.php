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
        // Indeks untuk tabel family_members
        Schema::table('family_members', function (Blueprint $table) {
            if (!Schema::hasIndex('family_members', 'family_members_family_id_index')) {
                $table->index('family_id');
            }
            if (!Schema::hasIndex('family_members', 'family_members_gender_index')) {
                $table->index('gender');
            }
            if (!Schema::hasIndex('family_members', 'family_members_birth_date_index')) {
                $table->index('birth_date');
            }
            if (!Schema::hasIndex('family_members', 'family_members_education_index')) {
                $table->index('education');
            }
            if (!Schema::hasIndex('family_members', 'family_members_has_tuberculosis_index')) {
                $table->index('has_tuberculosis');
            }
            if (!Schema::hasIndex('family_members', 'family_members_has_hypertension_index')) {
                $table->index('has_hypertension');
            }
            if (!Schema::hasIndex('family_members', 'family_members_has_chronic_cough_index')) {
                $table->index('has_chronic_cough');
            }
            if (!Schema::hasIndex('family_members', 'family_members_has_jkn_index')) {
                $table->index('has_jkn');
            }
        });

        // Indeks untuk tabel families
        Schema::table('families', function (Blueprint $table) {
            if (!Schema::hasIndex('families', 'families_building_id_index')) {
                $table->index('building_id');
            }
            if (!Schema::hasIndex('families', 'families_has_clean_water_index')) {
                $table->index('has_clean_water');
            }
            if (!Schema::hasIndex('families', 'families_is_water_protected_index')) {
                $table->index('is_water_protected');
            }
            if (!Schema::hasIndex('families', 'families_has_toilet_index')) {
                $table->index('has_toilet');
            }
            if (!Schema::hasIndex('families', 'families_is_toilet_sanitary_index')) {
                $table->index('is_toilet_sanitary');
            }
            if (!Schema::hasIndex('families', 'families_has_mental_illness_index')) {
                $table->index('has_mental_illness');
            }
            if (!Schema::hasIndex('families', 'families_has_restrained_member_index')) {
                $table->index('has_restrained_member');
            }
        });

        // Indeks untuk tabel buildings
        Schema::table('buildings', function (Blueprint $table) {
            if (!Schema::hasIndex('buildings', 'buildings_village_id_index')) {
                $table->index('village_id');
            }
            if (!Schema::hasIndex('buildings', 'buildings_building_number_index')) {
                $table->index('building_number');
            }
        });

        // Indeks untuk tabel family_health_indices
        Schema::table('family_health_indices', function (Blueprint $table) {
            if (!Schema::hasIndex('family_health_indices', 'family_health_indices_family_id_index')) {
                $table->index('family_id');
            }
            if (!Schema::hasIndex('family_health_indices', 'family_health_indices_iks_value_index')) {
                $table->index('iks_value');
            }
            if (!Schema::hasIndex('family_health_indices', 'family_health_indices_health_status_index')) {
                $table->index('health_status');
            }
            if (!Schema::hasIndex('family_health_indices', 'family_health_indices_calculated_at_index')) {
                $table->index('calculated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus indeks dari tabel family_members
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropIndex(['family_id']);
            $table->dropIndex(['gender']);
            $table->dropIndex(['birth_date']);
            $table->dropIndex(['education']);
            $table->dropIndex(['has_tuberculosis']);
            $table->dropIndex(['has_hypertension']);
            $table->dropIndex(['has_chronic_cough']);
            $table->dropIndex(['has_jkn']);
        });

        // Hapus indeks dari tabel families
        Schema::table('families', function (Blueprint $table) {
            $table->dropIndex(['building_id']);
            $table->dropIndex(['has_clean_water']);
            $table->dropIndex(['is_water_protected']);
            $table->dropIndex(['has_toilet']);
            $table->dropIndex(['is_toilet_sanitary']);
            $table->dropIndex(['has_mental_illness']);
            $table->dropIndex(['has_restrained_member']);
        });

        // Hapus indeks dari tabel buildings
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropIndex(['village_id']);
            $table->dropIndex(['building_number']);
        });

        // Hapus indeks dari tabel family_health_indices
        Schema::table('family_health_indices', function (Blueprint $table) {
            $table->dropIndex(['family_id']);
            $table->dropIndex(['iks_value']);
            $table->dropIndex(['health_status']);
            $table->dropIndex(['calculated_at']);
        });
    }
};
