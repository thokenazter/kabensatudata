<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spm_achievement_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spm_sub_indicator_id');
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedBigInteger('village_id')->nullable();
            $table->unsignedInteger('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('spm_sub_indicator_id')->references('id')->on('spm_sub_indicators')->cascadeOnDelete();
            $table->foreign('village_id')->references('id')->on('villages')->nullOnDelete();
            $table->index(['spm_sub_indicator_id', 'year']);
            $table->unique(['spm_sub_indicator_id', 'year', 'month', 'village_id'], 'spm_ach_overrides_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spm_achievement_overrides');
    }
};

