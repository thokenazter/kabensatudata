<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (!Schema::hasColumn('family_members', 'has_diabetes_mellitus')) {
                $table->boolean('has_diabetes_mellitus')->default(false)->after('takes_hypertension_medication_regularly');
            }
            if (!Schema::hasColumn('family_members', 'takes_dm_medication_regularly')) {
                $table->boolean('takes_dm_medication_regularly')->default(false)->after('has_diabetes_mellitus');
            }
            if (!Schema::hasColumn('family_members', 'has_mental_disorder')) {
                $table->boolean('has_mental_disorder')->default(false)->after('takes_dm_medication_regularly');
            }
            if (!Schema::hasColumn('family_members', 'takes_mental_disorder_medication_regularly')) {
                $table->boolean('takes_mental_disorder_medication_regularly')->default(false)->after('has_mental_disorder');
            }
            if (!Schema::hasColumn('family_members', 'is_at_risk_for_hiv')) {
                $table->boolean('is_at_risk_for_hiv')->default(false)->after('takes_mental_disorder_medication_regularly');
            }

            if (!Schema::hasColumn('family_members', 'last_anc_visit_date')) {
                $table->date('last_anc_visit_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_maternity_service_date')) {
                $table->date('last_maternity_service_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_neonatal_visit_date')) {
                $table->date('last_neonatal_visit_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_child_growth_monitoring_date')) {
                $table->date('last_child_growth_monitoring_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_school_screening_date')) {
                $table->date('last_school_screening_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_productive_age_screening_date')) {
                $table->date('last_productive_age_screening_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_elderly_screening_date')) {
                $table->date('last_elderly_screening_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_hiv_screening_date')) {
                $table->date('last_hiv_screening_date')->nullable();
            }
            if (!Schema::hasColumn('family_members', 'last_tb_screening_date')) {
                $table->date('last_tb_screening_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $cols = [
                'has_diabetes_mellitus',
                'takes_dm_medication_regularly',
                'has_mental_disorder',
                'takes_mental_disorder_medication_regularly',
                'is_at_risk_for_hiv',
                'last_anc_visit_date',
                'last_maternity_service_date',
                'last_neonatal_visit_date',
                'last_child_growth_monitoring_date',
                'last_school_screening_date',
                'last_productive_age_screening_date',
                'last_elderly_screening_date',
                'last_hiv_screening_date',
                'last_tb_screening_date',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('family_members', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

