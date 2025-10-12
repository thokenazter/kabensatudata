<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_spm_sub_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('spm_sub_indicator_id')->constrained('spm_sub_indicators')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['medical_record_id', 'spm_sub_indicator_id'], 'mr_spm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_spm_sub_indicators');
    }
};

