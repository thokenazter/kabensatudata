<?php
// file: database/migrations/xxxx_xx_xx_add_queue_fields_to_medical_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Queue Management Fields
            $table->string('queue_number')->unique()->nullable()->after('id');
            $table->enum('priority_level', ['normal', 'urgent', 'emergency'])->default('normal')->after('queue_number');
            $table->integer('estimated_service_time')->default(15)->after('priority_level'); // dalam menit

            // Role Handler & Time Tracking
            $table->unsignedBigInteger('current_role_handler')->nullable()->after('estimated_service_time');
            $table->timestamp('registration_start_time')->nullable()->after('current_role_handler');
            $table->timestamp('registration_end_time')->nullable()->after('registration_start_time');
            $table->timestamp('nurse_start_time')->nullable()->after('registration_end_time');
            $table->timestamp('nurse_end_time')->nullable()->after('nurse_start_time');
            $table->timestamp('doctor_start_time')->nullable()->after('nurse_end_time');
            $table->timestamp('doctor_end_time')->nullable()->after('doctor_start_time');
            $table->timestamp('pharmacy_start_time')->nullable()->after('doctor_end_time');
            $table->timestamp('pharmacy_end_time')->nullable()->after('pharmacy_start_time');

            // Foreign Key Constraints
            $table->foreign('current_role_handler')->references('id')->on('users')->onDelete('set null');

            // Indexes untuk performance
            $table->index(['queue_number', 'visit_date']);
            $table->index(['workflow_status', 'visit_date']);
            $table->index(['priority_level', 'queue_number']);
            $table->index('current_role_handler');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['current_role_handler']);

            $table->dropIndex(['queue_number', 'visit_date']);
            $table->dropIndex(['workflow_status', 'visit_date']);
            $table->dropIndex(['priority_level', 'queue_number']);
            $table->dropIndex(['current_role_handler']);

            $table->dropColumn([
                'queue_number',
                'priority_level',
                'estimated_service_time',
                'current_role_handler',
                'registration_start_time',
                'registration_end_time',
                'nurse_start_time',
                'nurse_end_time',
                'doctor_start_time',
                'doctor_end_time',
                'pharmacy_start_time',
                'pharmacy_end_time'
            ]);
        });
    }
};
