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
        Schema::create('medicine_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_used'); // Jumlah obat yang digunakan
            $table->string('instruction_text'); // Instruksi penggunaan (3dd1, 2dd1, dll)
            $table->string('frequency')->nullable(); // Frekuensi (3x sehari, 2x sehari)
            $table->string('dosage')->nullable(); // Dosis (1 tablet, 2 kapsul)
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
            
            // Indexes
            $table->index(['medical_record_id', 'medicine_id']);
            $table->index('medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_usages');
    }
};