<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('spm_targets', function (Blueprint $table) {
                if (!Schema::hasColumn('spm_targets', 'spm_sub_indicator_id')) {
                    $table->unsignedBigInteger('spm_sub_indicator_id')->nullable()->after('village_id');
                    try { $table->index('spm_sub_indicator_id'); } catch (\Throwable $e) {}
                }
            });
        } else {
            Schema::table('spm_targets', function (Blueprint $table) {
                try { $table->dropUnique('spm_target_unique'); } catch (\Throwable $e) {}
                if (Schema::hasColumn('spm_targets', 'spm_indicator_code')) { $table->dropColumn('spm_indicator_code'); }
                if (Schema::hasColumn('spm_targets', 'spm_indicator_name')) { $table->dropColumn('spm_indicator_name'); }
                if (!Schema::hasColumn('spm_targets', 'spm_sub_indicator_id')) {
                    // Tambahkan nullable dulu untuk memudahkan migrasi data lama, lalu bisa diubah NOT NULL setelah backfill
                    $table->unsignedBigInteger('spm_sub_indicator_id')->nullable()->after('village_id');
                    try { $table->foreign('spm_sub_indicator_id')->references('id')->on('spm_sub_indicators')->onDelete('cascade'); } catch (\Throwable $e) {}
                }
                try { $table->unique(['year', 'village_id', 'spm_sub_indicator_id'], 'spm_target_sub_indicator_unique'); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        Schema::table('spm_targets', function (Blueprint $table) {
            if (Schema::hasColumn('spm_targets', 'spm_sub_indicator_id')) {
                $table->dropForeign(['spm_sub_indicator_id']);
                $table->dropColumn('spm_sub_indicator_id');
            }
            // Cannot reliably recreate the old columns here
        });
    }
};
