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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama obat
            $table->string('generic_name')->nullable(); // Nama generik
            $table->integer('stock_quantity')->default(0); // Jumlah stok
            $table->string('unit')->default('tablet'); // Satuan (tablet, kapsul, botol, dll)
            $table->integer('minimum_stock')->default(10); // Stok minimum untuk alert
            $table->string('strength')->nullable(); // Kekuatan obat (500mg, 250mg, dll)
            $table->text('description')->nullable(); // Deskripsi obat
            $table->boolean('is_active')->default(true); // Status aktif
            $table->timestamps();
            
            // Indexes
            $table->index(['name', 'is_active']);
            $table->index('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};