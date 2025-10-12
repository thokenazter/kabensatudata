<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surat_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 50)->index();
            $table->integer('year')->index();
            $table->integer('last_seq')->default(0);
            $table->timestamps();
            $table->unique(['jenis', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_sequences');
    }
};

