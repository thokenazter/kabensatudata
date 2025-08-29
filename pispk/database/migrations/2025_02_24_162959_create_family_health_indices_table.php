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
        Schema::create('family_health_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->float('iks_value', 5, 4)->default(0);
            $table->string('health_status')->nullable();
            $table->unsignedInteger('relevant_indicators')->default(0);
            $table->unsignedInteger('fulfilled_indicators')->default(0);

            // Indikator 1: KB
            $table->boolean('kb_relevant')->default(false);
            $table->boolean('kb_status')->default(false);
            $table->string('kb_detail')->nullable();

            // Indikator 2: Persalinan di Fasilitas Kesehatan
            $table->boolean('birth_facility_relevant')->default(false);
            $table->boolean('birth_facility_status')->default(false);
            $table->string('birth_facility_detail')->nullable();

            // Indikator 3: Imunisasi
            $table->boolean('immunization_relevant')->default(false);
            $table->boolean('immunization_status')->default(false);
            $table->string('immunization_detail')->nullable();

            // Indikator 4: ASI Eksklusif
            $table->boolean('exclusive_breastfeeding_relevant')->default(false);
            $table->boolean('exclusive_breastfeeding_status')->default(false);
            $table->string('exclusive_breastfeeding_detail')->nullable();

            // Indikator 5: Pemantauan Pertumbuhan
            $table->boolean('growth_monitoring_relevant')->default(false);
            $table->boolean('growth_monitoring_status')->default(false);
            $table->string('growth_monitoring_detail')->nullable();

            // Indikator 6: Pengobatan TB
            $table->boolean('tb_treatment_relevant')->default(false);
            $table->boolean('tb_treatment_status')->default(false);
            $table->string('tb_treatment_detail')->nullable();

            // Indikator 7: Pengobatan Hipertensi
            $table->boolean('hypertension_treatment_relevant')->default(false);
            $table->boolean('hypertension_treatment_status')->default(false);
            $table->string('hypertension_treatment_detail')->nullable();

            // Indikator 8: Pengobatan Gangguan Jiwa
            $table->boolean('mental_treatment_relevant')->default(false);
            $table->boolean('mental_treatment_status')->default(false);
            $table->string('mental_treatment_detail')->nullable();

            // Indikator 9: Tidak Merokok
            $table->boolean('no_smoking_relevant')->default(true);
            $table->boolean('no_smoking_status')->default(false);
            $table->string('no_smoking_detail')->nullable();

            // Indikator 10: JKN
            $table->boolean('jkn_membership_relevant')->default(true);
            $table->boolean('jkn_membership_status')->default(false);
            $table->string('jkn_membership_detail')->nullable();

            // Indikator 11: Air Bersih
            $table->boolean('clean_water_relevant')->default(true);
            $table->boolean('clean_water_status')->default(false);
            $table->string('clean_water_detail')->nullable();

            // Indikator 12: Jamban Sehat
            $table->boolean('sanitary_toilet_relevant')->default(true);
            $table->boolean('sanitary_toilet_status')->default(false);
            $table->string('sanitary_toilet_detail')->nullable();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_health_indices');
    }
};
