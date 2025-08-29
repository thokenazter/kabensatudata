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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->onDelete('cascade');
            $table->string('building_number')->comment('No Urut Bangunan');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Kombinasi village_id dan building_number harus unik
            $table->unique(['village_id', 'building_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
