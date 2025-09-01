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
        Schema::table('medical_records', function (Blueprint $table) {
            // Add patient identity fields for denormalization
            $table->string('patient_name')->nullable()->after('family_member_id')->comment('Nama pasien (denormalisasi dari FamilyMember)');
            $table->text('patient_address')->nullable()->after('patient_name')->comment('Alamat pasien (dari Village melalui Family->Building->Village)');
            $table->enum('patient_gender', ['Laki-laki', 'Perempuan'])->nullable()->after('patient_address')->comment('Jenis kelamin pasien');
            $table->string('patient_nik', 16)->nullable()->after('patient_gender')->comment('NIK pasien');
            $table->string('patient_rm_number')->nullable()->after('patient_nik')->comment('Nomor RM pasien');
            $table->date('patient_birth_date')->nullable()->after('patient_rm_number')->comment('Tanggal lahir pasien');
            $table->integer('patient_age')->nullable()->after('patient_birth_date')->comment('Umur pasien saat record dibuat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'patient_name',
                'patient_address', 
                'patient_gender',
                'patient_nik',
                'patient_rm_number',
                'patient_birth_date',
                'patient_age'
            ]);
        });
    }
};
