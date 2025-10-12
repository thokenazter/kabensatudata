<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spm_sub_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spm_indicator_id')->constrained('spm_indicators')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('definition')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spm_sub_indicators');
    }
};

