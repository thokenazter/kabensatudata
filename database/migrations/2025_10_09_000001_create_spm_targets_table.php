<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spm_targets', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->foreignId('village_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('spm_indicator_code');
            $table->string('spm_indicator_name');
            $table->integer('denominator_dinkes');
            $table->decimal('target_percentage', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['year', 'village_id', 'spm_indicator_code'], 'spm_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spm_targets');
    }
};

