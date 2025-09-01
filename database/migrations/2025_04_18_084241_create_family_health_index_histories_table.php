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
        Schema::create('family_health_index_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->float('iks_value');
            $table->string('health_status');
            $table->integer('relevant_indicators');
            $table->integer('fulfilled_indicators');

            // Indikator 1: KB
            $table->boolean('kb_relevant')->default(false);
            $table->boolean('kb_status')->default(false);
            $table->text('kb_detail')->nullable();

            // Indikator 2: Persalinan di Fasilitas Kesehatan
            $table->boolean('birth_facility_relevant')->default(false);
            $table->boolean('birth_facility_status')->default(false);
            $table->text('birth_facility_detail')->nullable();

            // Indikator 3: Imunisasi
            $table->boolean('immunization_relevant')->default(false);
            $table->boolean('immunization_status')->default(false);
            $table->text('immunization_detail')->nullable();

            // Indikator 4: ASI Eksklusif
            $table->boolean('exclusive_breastfeeding_relevant')->default(false);
            $table->boolean('exclusive_breastfeeding_status')->default(false);
            $table->text('exclusive_breastfeeding_detail')->nullable();

            // Indikator 5: Pemantauan Pertumbuhan
            $table->boolean('growth_monitoring_relevant')->default(false);
            $table->boolean('growth_monitoring_status')->default(false);
            $table->text('growth_monitoring_detail')->nullable();

            // Indikator 6: Pengobatan TB
            $table->boolean('tb_treatment_relevant')->default(false);
            $table->boolean('tb_treatment_status')->default(false);
            $table->text('tb_treatment_detail')->nullable();

            // Indikator 7: Pengobatan Hipertensi
            $table->boolean('hypertension_treatment_relevant')->default(false);
            $table->boolean('hypertension_treatment_status')->default(false);
            $table->text('hypertension_treatment_detail')->nullable();

            // Indikator 8: Pengobatan Gangguan Jiwa
            $table->boolean('mental_treatment_relevant')->default(false);
            $table->boolean('mental_treatment_status')->default(false);
            $table->text('mental_treatment_detail')->nullable();

            // Indikator 9: Tidak Merokok
            $table->boolean('no_smoking_relevant')->default(false);
            $table->boolean('no_smoking_status')->default(false);
            $table->text('no_smoking_detail')->nullable();

            // Indikator 10: JKN
            $table->boolean('jkn_membership_relevant')->default(false);
            $table->boolean('jkn_membership_status')->default(false);
            $table->text('jkn_membership_detail')->nullable();

            // Indikator 11: Air Bersih
            $table->boolean('clean_water_relevant')->default(false);
            $table->boolean('clean_water_status')->default(false);
            $table->text('clean_water_detail')->nullable();

            // Indikator 12: Jamban Sehat
            $table->boolean('sanitary_toilet_relevant')->default(false);
            $table->boolean('sanitary_toilet_status')->default(false);
            $table->text('sanitary_toilet_detail')->nullable();

            $table->text('notes')->nullable();
            $table->json('improvement_factors')->nullable();
            $table->json('decline_factors')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_health_index_histories');
    }
};
