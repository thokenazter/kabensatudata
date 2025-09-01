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
        // Add indexes using try-catch to handle "already exists" errors
        try {
            \Illuminate\Support\Facades\DB::statement('
                CREATE INDEX buildings_latitude_index ON buildings (latitude)
            ');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
        
        try {
            \Illuminate\Support\Facades\DB::statement('
                CREATE INDEX buildings_longitude_index ON buildings (longitude)
            ');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
        
        try {
            \Illuminate\Support\Facades\DB::statement('
                CREATE INDEX buildings_coordinates_index ON buildings (latitude, longitude)
            ');
        } catch (\Exception $e) {
            // Index might already exist, ignore error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes with try-catch to handle "doesn't exist" errors
        try {
            \Illuminate\Support\Facades\DB::statement('DROP INDEX buildings_coordinates_index ON buildings');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
        
        try {
            \Illuminate\Support\Facades\DB::statement('DROP INDEX buildings_longitude_index ON buildings');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
        
        try {
            \Illuminate\Support\Facades\DB::statement('DROP INDEX buildings_latitude_index ON buildings');
        } catch (\Exception $e) {
            // Index might not exist, ignore error
        }
    }
};
