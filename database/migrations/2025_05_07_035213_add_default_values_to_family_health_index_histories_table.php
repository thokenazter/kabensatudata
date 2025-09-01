<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('family_health_index_histories', function (Blueprint $table) {
            // Ubah kolom-kolom boolean untuk menerima nilai default false
            $table->boolean('kb_relevant')->default(false)->change();
            $table->boolean('kb_status')->default(false)->change();

            $table->boolean('birth_facility_relevant')->default(false)->change();
            $table->boolean('birth_facility_status')->default(false)->change();

            $table->boolean('immunization_relevant')->default(false)->change();
            $table->boolean('immunization_status')->default(false)->change();

            $table->boolean('exclusive_breastfeeding_relevant')->default(false)->change();
            $table->boolean('exclusive_breastfeeding_status')->default(false)->change();

            $table->boolean('growth_monitoring_relevant')->default(false)->change();
            $table->boolean('growth_monitoring_status')->default(false)->change();

            $table->boolean('tb_treatment_relevant')->default(false)->change();
            $table->boolean('tb_treatment_status')->default(false)->change();

            $table->boolean('hypertension_treatment_relevant')->default(false)->change();
            $table->boolean('hypertension_treatment_status')->default(false)->change();

            $table->boolean('mental_treatment_relevant')->default(false)->change();
            $table->boolean('mental_treatment_status')->default(false)->change();

            $table->boolean('no_smoking_relevant')->default(false)->change();
            $table->boolean('no_smoking_status')->default(false)->change();

            $table->boolean('jkn_membership_relevant')->default(false)->change();
            $table->boolean('jkn_membership_status')->default(false)->change();

            $table->boolean('clean_water_relevant')->default(false)->change();
            $table->boolean('clean_water_status')->default(false)->change();

            $table->boolean('sanitary_toilet_relevant')->default(false)->change();
            $table->boolean('sanitary_toilet_status')->default(false)->change();
        });

        // Untuk kolom TEXT, kita perlu menggunakan raw SQL karena MySQL tidak mendukung default value untuk TEXT
        // Kita perlu mengupdate nilai null yang sudah ada menjadi nilai default
        DB::statement("UPDATE family_health_index_histories SET kb_detail = 'Tidak ada data' WHERE kb_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET birth_facility_detail = 'Tidak ada data' WHERE birth_facility_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET immunization_detail = 'Tidak ada data' WHERE immunization_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET exclusive_breastfeeding_detail = 'Tidak ada data' WHERE exclusive_breastfeeding_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET growth_monitoring_detail = 'Tidak ada data' WHERE growth_monitoring_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET tb_treatment_detail = 'Tidak ada data' WHERE tb_treatment_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET hypertension_treatment_detail = 'Tidak ada data' WHERE hypertension_treatment_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET mental_treatment_detail = 'Tidak ada data' WHERE mental_treatment_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET no_smoking_detail = 'Tidak ada data' WHERE no_smoking_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET jkn_membership_detail = 'Tidak ada data' WHERE jkn_membership_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET clean_water_detail = 'Tidak ada data' WHERE clean_water_detail IS NULL");
        DB::statement("UPDATE family_health_index_histories SET sanitary_toilet_detail = 'Tidak ada data' WHERE sanitary_toilet_detail IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_health_index_histories', function (Blueprint $table) {
            // Kembalikan kolom-kolom boolean ke pengaturan sebelumnya
            $table->boolean('kb_relevant')->default(null)->change();
            $table->boolean('kb_status')->default(null)->change();

            $table->boolean('birth_facility_relevant')->default(null)->change();
            $table->boolean('birth_facility_status')->default(null)->change();

            $table->boolean('immunization_relevant')->default(null)->change();
            $table->boolean('immunization_status')->default(null)->change();

            $table->boolean('exclusive_breastfeeding_relevant')->default(null)->change();
            $table->boolean('exclusive_breastfeeding_status')->default(null)->change();

            $table->boolean('growth_monitoring_relevant')->default(null)->change();
            $table->boolean('growth_monitoring_status')->default(null)->change();

            $table->boolean('tb_treatment_relevant')->default(null)->change();
            $table->boolean('tb_treatment_status')->default(null)->change();

            $table->boolean('hypertension_treatment_relevant')->default(null)->change();
            $table->boolean('hypertension_treatment_status')->default(null)->change();

            $table->boolean('mental_treatment_relevant')->default(null)->change();
            $table->boolean('mental_treatment_status')->default(null)->change();

            $table->boolean('no_smoking_relevant')->default(null)->change();
            $table->boolean('no_smoking_status')->default(null)->change();

            $table->boolean('jkn_membership_relevant')->default(null)->change();
            $table->boolean('jkn_membership_status')->default(null)->change();

            $table->boolean('clean_water_relevant')->default(null)->change();
            $table->boolean('clean_water_status')->default(null)->change();

            $table->boolean('sanitary_toilet_relevant')->default(null)->change();
            $table->boolean('sanitary_toilet_status')->default(null)->change();
        });
    }
};
