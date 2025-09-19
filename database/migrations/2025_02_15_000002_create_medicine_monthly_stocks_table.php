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
        Schema::create('medicine_monthly_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->integer('opening_stock');
            $table->integer('usage_quantity')->default(0);
            $table->integer('adjustment_quantity')->default(0);
            $table->integer('closing_stock');
            $table->timestamps();

            $table->unique(['medicine_id', 'period_start']);
            $table->index('period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_monthly_stocks');
    }
};
