<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surat_archives', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 50)->index(); // SURAT_TUGAS, SURAT_KELUAR, dll
            $table->string('nomor_surat')->nullable()->index();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->string('perihal')->nullable();
            $table->date('issued_at')->nullable()->index();
            $table->string('file_path'); // relative path on public disk
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_archives');
    }
};

