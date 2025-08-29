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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->bigInteger('nik')->nullable();
            $table->string('relationship')->comment('Hubungan dengan kepala keluarga');
            $table->string('birth_place')->nullable()->comment('Tempat Lahir');
            $table->date('birth_date')->nullable()->comment('Tanggal Lahir');
            $table->integer('age')->nullable()->comment('Umur (terhitung otomatis)');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);

            // Khusus perempuan
            $table->boolean('is_pregnant')->nullable()->comment('Sedang hamil? (perempuan 10-54 tahun)');

            // Informasi umum
            $table->string('religion')->nullable()->comment('Agama');
            $table->string('occupation')->nullable()->comment('Pekerjaan (usia > 10 tahun)');
            $table->boolean('has_jkn')->nullable()->comment('Memiliki kartu JKN');

            // Pertanyaan kesehatan umum
            $table->boolean('is_smoker')->nullable()->comment('Merokok? (usia > 15 tahun)');
            $table->boolean('use_toilet')->nullable()->comment('BAB di jamban? (usia > 15 tahun)');
            $table->boolean('use_water')->nullable()->comment('Menggunakan Air Bersih (usia > 15 tahun)');
            $table->boolean('has_tuberculosis')->nullable()->comment('Pernah didiagnosis TBC paru?');
            $table->boolean('takes_tb_medication_regularly')->nullable()->comment('Minum obat TBC teratur?');
            $table->boolean('has_chronic_cough')->nullable()->comment('Pernah batuk berdahak > 2 minggu?');
            $table->boolean('has_hypertension')->nullable()->comment('Didiagnosis darah tinggi? (usia > 15 tahun)');
            $table->boolean('takes_hypertension_medication_regularly')->nullable()->comment('Minum obat darah tinggi teratur?');

            // Pertanyaan KB dan kesehatan reproduksi
            $table->boolean('uses_contraception')->nullable()->comment('Menggunakan alat kontrasepsi/KB?');
            $table->boolean('gave_birth_in_health_facility')->nullable()->comment('Melahirkan di fasilitas kesehatan?');
            $table->boolean('exclusive_breastfeeding')->nullable()->comment('Diberi ASI eksklusif 0-6 bulan?');
            $table->boolean('complete_immunization')->nullable()->comment('Imunisasi lengkap 0-11 bulan?');
            $table->boolean('growth_monitoring')->nullable()->comment('Pemantauan pertumbuhan balita bulan terakhir?');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
