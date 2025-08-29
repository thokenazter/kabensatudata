<?php

namespace App\Models;

use App\Services\IksService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Family extends Model
{
    protected $fillable = [
        'building_id',
        'family_number',
        'head_name',
        'has_clean_water',
        'is_water_protected',
        'has_toilet',
        'is_toilet_sanitary',
        'has_mental_illness',
        'takes_medication_regularly',
        'has_restrained_member'
    ];

    protected $casts = [
        'has_clean_water' => 'boolean',
        'is_water_protected' => 'boolean',
        'has_toilet' => 'boolean',
        'is_toilet_sanitary' => 'boolean',
        'has_mental_illness' => 'boolean',
        'takes_medication_regularly' => 'boolean',
        'has_restrained_member' => 'boolean',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    // Tambahkan kode ini ke dalam model Family.php

    // Relasi ke model FamilyHealthIndex
    public function healthIndex()
    {
        return $this->hasOne(FamilyHealthIndex::class)->latest();
    }

    // Method untuk menghitung IKS
    public function calculateIks()
    {
        $iksService = app(IksService::class);
        return $iksService->calculateIks($this);
    }

    // Method untuk menyimpan hasil perhitungan IKS
    public function saveIksResult(array $iksData)
    {
        $indicators = $iksData['indicators'];

        return $this->healthIndex()->updateOrCreate(
            ['family_id' => $this->id],
            [
                'iks_value' => $iksData['iks_value'],
                'health_status' => $iksData['health_status'],
                'relevant_indicators' => $iksData['relevant_count'],
                'fulfilled_indicators' => $iksData['positive_count'],

                // Indikator 1: KB
                'kb_relevant' => $indicators['kb']['relevant'],
                'kb_status' => $indicators['kb']['value'] == 1,
                'kb_detail' => $indicators['kb']['detail'],

                // Indikator 2: Persalinan di Fasilitas Kesehatan
                'birth_facility_relevant' => $indicators['birth_facility']['relevant'],
                'birth_facility_status' => $indicators['birth_facility']['value'] == 1,
                'birth_facility_detail' => $indicators['birth_facility']['detail'],

                // Indikator 3: Imunisasi
                'immunization_relevant' => $indicators['immunization']['relevant'],
                'immunization_status' => $indicators['immunization']['value'] == 1,
                'immunization_detail' => $indicators['immunization']['detail'],

                // Indikator 4: ASI Eksklusif
                'exclusive_breastfeeding_relevant' => $indicators['exclusive_breastfeeding']['relevant'],
                'exclusive_breastfeeding_status' => $indicators['exclusive_breastfeeding']['value'] == 1,
                'exclusive_breastfeeding_detail' => $indicators['exclusive_breastfeeding']['detail'],

                // Indikator 5: Pemantauan Pertumbuhan
                'growth_monitoring_relevant' => $indicators['growth_monitoring']['relevant'],
                'growth_monitoring_status' => $indicators['growth_monitoring']['value'] == 1,
                'growth_monitoring_detail' => $indicators['growth_monitoring']['detail'],

                // Indikator 6: Pengobatan TB
                'tb_treatment_relevant' => $indicators['tb_treatment']['relevant'],
                'tb_treatment_status' => $indicators['tb_treatment']['value'] == 1,
                'tb_treatment_detail' => $indicators['tb_treatment']['detail'],

                // Indikator 7: Pengobatan Hipertensi
                'hypertension_treatment_relevant' => $indicators['hypertension_treatment']['relevant'],
                'hypertension_treatment_status' => $indicators['hypertension_treatment']['value'] == 1,
                'hypertension_treatment_detail' => $indicators['hypertension_treatment']['detail'],

                // Indikator 8: Pengobatan Gangguan Jiwa
                'mental_treatment_relevant' => $indicators['mental_treatment']['relevant'],
                'mental_treatment_status' => $indicators['mental_treatment']['value'] == 1,
                'mental_treatment_detail' => $indicators['mental_treatment']['detail'],

                // Indikator 9: Tidak Merokok
                'no_smoking_relevant' => $indicators['no_smoking']['relevant'],
                'no_smoking_status' => $indicators['no_smoking']['value'] == 1,
                'no_smoking_detail' => $indicators['no_smoking']['detail'],

                // Indikator 10: JKN
                'jkn_membership_relevant' => $indicators['jkn_membership']['relevant'],
                'jkn_membership_status' => $indicators['jkn_membership']['value'] == 1,
                'jkn_membership_detail' => $indicators['jkn_membership']['detail'],

                // Indikator 11: Air Bersih
                'clean_water_relevant' => $indicators['clean_water']['relevant'],
                'clean_water_status' => $indicators['clean_water']['value'] == 1,
                'clean_water_detail' => $indicators['clean_water']['detail'],

                // Indikator 12: Jamban Sehat
                'sanitary_toilet_relevant' => $indicators['sanitary_toilet']['relevant'],
                'sanitary_toilet_status' => $indicators['sanitary_toilet']['value'] == 1,
                'sanitary_toilet_detail' => $indicators['sanitary_toilet']['detail'],

                'calculated_at' => now(),
            ]
        );
    }
}
