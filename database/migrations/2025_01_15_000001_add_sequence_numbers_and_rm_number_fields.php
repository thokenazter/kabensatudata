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
        // Add sequence_number to villages table
        Schema::table('villages', function (Blueprint $table) {
            $table->integer('sequence_number')->unique()->nullable()->after('code')->comment('Nomor urut desa untuk RM');
        });

        // Add sequence_number_in_building to families table
        Schema::table('families', function (Blueprint $table) {
            $table->integer('sequence_number_in_building')->nullable()->after('family_number')->comment('Nomor urut keluarga dalam bangunan');
            
            // Add unique constraint for building_id and sequence_number_in_building
            $table->unique(['building_id', 'sequence_number_in_building'], 'families_building_sequence_unique');
        });

        // Add rm_number and sequence_number_in_family to family_members table
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('rm_number')->unique()->nullable()->after('nik')->comment('Nomor Rekam Medis');
            $table->integer('sequence_number_in_family')->nullable()->after('rm_number')->comment('Nomor urut anggota dalam keluarga');
            
            // Add unique constraint for family_id and sequence_number_in_family
            $table->unique(['family_id', 'sequence_number_in_family'], 'family_members_family_sequence_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn('sequence_number');
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropUnique('families_building_sequence_unique');
            $table->dropColumn('sequence_number_in_building');
        });

        Schema::table('family_members', function (Blueprint $table) {
            $table->dropUnique('family_members_family_sequence_unique');
            $table->dropColumn(['rm_number', 'sequence_number_in_family']);
        });
    }
};