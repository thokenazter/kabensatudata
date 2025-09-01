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
        Schema::table('medical_records', function (Blueprint $table) {
            // Check if columns already exist before adding them
            if (!Schema::hasColumn('medical_records', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('medical_records', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('queued_at');
            }
            if (!Schema::hasColumn('medical_records', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('medical_records', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('medical_records', 'queue_position')) {
                $table->integer('queue_position')->nullable()->after('assigned_to');
            }
            
            // Check if patient fields already exist
            if (!Schema::hasColumn('medical_records', 'patient_rm_number')) {
                $table->string('patient_rm_number')->nullable()->after('patient_nik');
            }
            if (!Schema::hasColumn('medical_records', 'patient_birth_date')) {
                $table->date('patient_birth_date')->nullable()->after('patient_rm_number');
            }
            if (!Schema::hasColumn('medical_records', 'patient_age')) {
                $table->integer('patient_age')->nullable()->after('patient_birth_date');
            }
        });
        
        // Add foreign key and indexes if they don't exist
        Schema::table('medical_records', function (Blueprint $table) {
            // Add foreign key if assigned_to column exists and foreign key doesn't exist
            if (Schema::hasColumn('medical_records', 'assigned_to')) {
                try {
                    $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist, ignore
                }
            }
            
            // Add indexes if they don't exist (Laravel will handle duplicates gracefully)
            try {
                $table->index(['workflow_status', 'queued_at']);
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
            try {
                $table->index(['assigned_to', 'workflow_status']);
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['workflow_status', 'queued_at']);
            $table->dropIndex(['assigned_to', 'workflow_status']);
            $table->dropColumn([
                'workflow_status', 'queued_at', 'started_at', 
                'completed_at', 'assigned_to', 'queue_position',
                'patient_rm_number', 'patient_birth_date', 'patient_age'
            ]);
        });
        
        Schema::table('medical_records', function (Blueprint $table) {
            $table->enum('workflow_status', [
                'draft', 'registered', 'nurse_examined', 
                'doctor_examined', 'completed'
            ])->default('draft')->after('created_by');
        });
    }
};
