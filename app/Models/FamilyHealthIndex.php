<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyHealthIndex extends Model
{
    protected $fillable = [
        'family_id',
        'iks_value',
        'health_status',
        'relevant_indicators',
        'fulfilled_indicators',
        'kb_status',
        'birth_facility_status',
        'immunization_status',
        'exclusive_breastfeeding_status',
        'growth_monitoring_status',
        'tb_treatment_status',
        'hypertension_treatment_status',
        'mental_treatment_status',
        'no_smoking_status',
        'jkn_membership_status',
        'clean_water_status',
        'sanitary_toilet_status',
        'kb_detail',
        'birth_facility_detail',
        'immunization_detail',
        'exclusive_breastfeeding_detail',
        'growth_monitoring_detail',
        'tb_treatment_detail',
        'hypertension_treatment_detail',
        'mental_treatment_detail',
        'no_smoking_detail',
        'jkn_membership_detail',
        'clean_water_detail',
        'sanitary_toilet_detail',
        'calculated_at'
    ];

    protected $casts = [
        'iks_value' => 'float',
        'kb_status' => 'boolean',
        'birth_facility_status' => 'boolean',
        'immunization_status' => 'boolean',
        'exclusive_breastfeeding_status' => 'boolean',
        'growth_monitoring_status' => 'boolean',
        'tb_treatment_status' => 'boolean',
        'hypertension_treatment_status' => 'boolean',
        'mental_treatment_status' => 'boolean',
        'no_smoking_status' => 'boolean',
        'jkn_membership_status' => 'boolean',
        'clean_water_status' => 'boolean',
        'sanitary_toilet_status' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
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
