<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyHealthIndexHistory extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'iks_value',
        'health_status',
        'relevant_indicators',
        'fulfilled_indicators',
        'kb_relevant',
        'kb_status',
        'kb_detail',
        'birth_facility_relevant',
        'birth_facility_status',
        'birth_facility_detail',
        'immunization_relevant',
        'immunization_status',
        'immunization_detail',
        'exclusive_breastfeeding_relevant',
        'exclusive_breastfeeding_status',
        'exclusive_breastfeeding_detail',
        'growth_monitoring_relevant',
        'growth_monitoring_status',
        'growth_monitoring_detail',
        'tb_treatment_relevant',
        'tb_treatment_status',
        'tb_treatment_detail',
        'hypertension_treatment_relevant',
        'hypertension_treatment_status',
        'hypertension_treatment_detail',
        'mental_treatment_relevant',
        'mental_treatment_status',
        'mental_treatment_detail',
        'no_smoking_relevant',
        'no_smoking_status',
        'no_smoking_detail',
        'jkn_membership_relevant',
        'jkn_membership_status',
        'jkn_membership_detail',
        'clean_water_relevant',
        'clean_water_status',
        'clean_water_detail',
        'sanitary_toilet_relevant',
        'sanitary_toilet_status',
        'sanitary_toilet_detail',
        'notes',
        'improvement_factors',
        'decline_factors',
        'calculated_at',
    ];

    protected $casts = [
        'iks_value' => 'float',
        'kb_relevant' => 'boolean',
        'kb_status' => 'boolean',
        'birth_facility_relevant' => 'boolean',
        'birth_facility_status' => 'boolean',
        'immunization_relevant' => 'boolean',
        'immunization_status' => 'boolean',
        'exclusive_breastfeeding_relevant' => 'boolean',
        'exclusive_breastfeeding_status' => 'boolean',
        'growth_monitoring_relevant' => 'boolean',
        'growth_monitoring_status' => 'boolean',
        'tb_treatment_relevant' => 'boolean',
        'tb_treatment_status' => 'boolean',
        'hypertension_treatment_relevant' => 'boolean',
        'hypertension_treatment_status' => 'boolean',
        'mental_treatment_relevant' => 'boolean',
        'mental_treatment_status' => 'boolean',
        'no_smoking_relevant' => 'boolean',
        'no_smoking_status' => 'boolean',
        'jkn_membership_relevant' => 'boolean',
        'jkn_membership_status' => 'boolean',
        'clean_water_relevant' => 'boolean',
        'clean_water_status' => 'boolean',
        'sanitary_toilet_relevant' => 'boolean',
        'sanitary_toilet_status' => 'boolean',
        'improvement_factors' => 'json',
        'decline_factors' => 'json',
        'calculated_at' => 'datetime',
    ];

    /**
     * Relasi ke model Family
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Relasi ke User yang melakukan perhitungan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan persentase IKS
     */
    public function getIksPercentageAttribute(): float
    {
        return $this->iks_value * 100;
    }

    /**
     * Mendapatkan status IKS dalam format HTML dengan warna
     */
    public function getStatusHtmlAttribute(): string
    {
        return match ($this->health_status) {
            'Keluarga Sehat' => '<span class="text-green-600 font-medium">Keluarga Sehat</span>',
            'Keluarga Pra-Sehat' => '<span class="text-yellow-600 font-medium">Keluarga Pra-Sehat</span>',
            'Keluarga Tidak Sehat' => '<span class="text-red-600 font-medium">Keluarga Tidak Sehat</span>',
            default => '<span class="text-gray-600">Belum dihitung</span>',
        };
    }

    /**
     * Mendapatkan warna berdasarkan status kesehatan
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->health_status) {
            'Keluarga Sehat' => 'success',
            'Keluarga Pra-Sehat' => 'warning',
            'Keluarga Tidak Sehat' => 'danger',
            default => 'gray',
        };
    }
}
