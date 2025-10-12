<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('nip')->index();
            $table->string('jenis_kelamin', 1)->nullable()->after('nik')->index(); // L/P
            $table->string('pendidikan_terakhir', 50)->nullable()->after('unit')->index(); // S1, D3, Profesi, dll
            $table->string('profesi', 100)->nullable()->after('pendidikan_terakhir')->index(); // Perawat, Bidan, dll
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn(['nik', 'jenis_kelamin', 'pendidikan_terakhir', 'profesi']);
        });
    }
};

