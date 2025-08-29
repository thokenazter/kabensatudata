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
        Schema::table('family_members', function (Blueprint $table) {
            // Cek apakah kolom education sudah ada
            if (!Schema::hasColumn('family_members', 'education')) {
                $table->string('education')->nullable()->after('religion');
            } else {
                // Jika education sudah ada tetapi ingin dimodifikasi menjadi enum
                // Uncomment baris di bawah ini jika perlu
                // $table->dropColumn('education');
                // $table->enum('education', [
                //     'Tidak Pernah Sekolah',
                //     'Tidak Tamat SD/MI',
                //     'Tamat SD/MI',
                //     'Tamat SMP/MTs',
                //     'Tamat SMA/MA/SMK',
                //     'Tamat D1/D2/D3',
                //     'Tamat D4/S1',
                //     'Tamat S2/S3'
                // ])->nullable()->after('religion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            //
        });
    }
};
