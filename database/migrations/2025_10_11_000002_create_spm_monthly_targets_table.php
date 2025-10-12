<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spm_monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->tinyInteger('month'); // 1..12
            $table->foreignId('spm_sub_indicator_id')->constrained('spm_sub_indicators')->cascadeOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->integer('target_absolute');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['year','month','spm_sub_indicator_id','village_id'], 'uniq_monthly_targets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spm_monthly_targets');
    }
};

