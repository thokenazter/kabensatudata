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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');
            $table->date('visit_date');
            $table->string('chief_complaint')->nullable();
            $table->text('anamnesis')->nullable();
            $table->integer('systolic')->nullable(); // Tekanan darah sistolik
            $table->integer('diastolic')->nullable(); // Tekanan darah diastolik
            $table->decimal('weight', 5, 2)->nullable(); // Berat badan (kg)
            $table->decimal('height', 5, 2)->nullable(); // Tinggi badan (cm)
            $table->integer('heart_rate')->nullable(); // Detak jantung
            $table->decimal('body_temperature', 4, 1)->nullable(); // Suhu tubuh (°C)
            $table->integer('respiratory_rate')->nullable(); // Laju pernapasan
            $table->string('diagnosis_code')->nullable(); // Kode diagnosis (ICD)
            $table->string('diagnosis_name')->nullable(); // Nama diagnosis
            $table->text('therapy')->nullable(); // Terapi
            $table->text('medication')->nullable(); // Obat
            $table->text('procedure')->nullable(); // Tindakan
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
