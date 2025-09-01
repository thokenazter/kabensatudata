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
        Schema::table('medicine_usages', function (Blueprint $table) {
            // Make medicine_id nullable to allow free text medicines
            $table->foreignId('medicine_id')->nullable()->change();
            
            // Add fields for free text medicine
            $table->string('medicine_name')->nullable()->after('medicine_id');
            $table->string('medicine_strength')->nullable()->after('medicine_name');
            $table->string('medicine_unit')->nullable()->after('medicine_strength');
            $table->boolean('is_free_text')->default(false)->after('medicine_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine_usages', function (Blueprint $table) {
            $table->dropColumn(['medicine_name', 'medicine_strength', 'medicine_unit', 'is_free_text']);
            $table->foreignId('medicine_id')->nullable(false)->change();
        });
    }
};
