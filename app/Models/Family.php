<?php

namespace App\Models;

use App\Services\IksService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Family extends Model
{
    protected $fillable = [
        'building_id',
        'family_number',
        'sequence_number_in_building',
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

    // Tambahkan relasi ini ke model Family.php

    /**
     * Relasi ke model FamilyHealthIndexHistory
     */
    public function healthIndexHistories()
    {
        return $this->hasMany(FamilyHealthIndexHistory::class)->orderBy('calculated_at', 'desc');
    }

    /**
     * Mendapatkan riwayat IKS terakhir
     */
    public function getLatestHealthIndexHistoryAttribute()
    {
        return $this->healthIndexHistories()->first();
    }

    /**
     * Mendapatkan perubahan IKS dari perhitungan terakhir
     */
    public function getIksChangeAttribute()
    {
        $histories = $this->healthIndexHistories()->orderBy('calculated_at', 'desc')->take(2)->get();

        if ($histories->count() < 2) {
            return 0;
        }

        $current = $histories[0]->iks_value;
        $previous = $histories[1]->iks_value;

        return $current - $previous;
    }

    /**
     * Method untuk menyimpan hasil perhitungan IKS ke dalam riwayat
     * dengan penanganan nilai default untuk mencegah constraint violation
     */
    public function saveIksHistory(array $iksData)
    {
        try {
            // Pastikan data indikator ada, jika tidak gunakan array kosong
            $indicators = isset($iksData['indicators']) && is_array($iksData['indicators'])
                ? $iksData['indicators']
                : [];

            // Dapatkan ID pengguna yang sedang login
            $userId = auth()->id();

            // Buat objek untuk menyimpan data riwayat IKS
            $history = new FamilyHealthIndexHistory();
            $history->family_id = $this->getKey();
            $history->user_id = $userId;

            // Data utama IKS dengan nilai default jika tidak ada
            $history->iks_value = $iksData['iks_value'] ?? 0;
            $history->health_status = $iksData['health_status'] ?? 'Tidak Dapat Dihitung';
            $history->relevant_indicators = $iksData['relevant_count'] ?? 0;
            $history->fulfilled_indicators = $iksData['positive_count'] ?? 0;
            $history->calculated_at = now();

            // Indikator 1: KB
            $this->setIndicatorValues($history, 'kb', $indicators['kb'] ?? []);

            // Indikator 2: Persalinan di Fasilitas Kesehatan
            $this->setIndicatorValues($history, 'birth_facility', $indicators['birth_facility'] ?? []);

            // Indikator 3: Imunisasi
            $this->setIndicatorValues($history, 'immunization', $indicators['immunization'] ?? []);

            // Indikator 4: ASI Eksklusif
            $this->setIndicatorValues($history, 'exclusive_breastfeeding', $indicators['exclusive_breastfeeding'] ?? []);

            // Indikator 5: Pemantauan Pertumbuhan
            $this->setIndicatorValues($history, 'growth_monitoring', $indicators['growth_monitoring'] ?? []);

            // Indikator 6: Pengobatan TB
            $this->setIndicatorValues($history, 'tb_treatment', $indicators['tb_treatment'] ?? []);

            // Indikator 7: Pengobatan Hipertensi
            $this->setIndicatorValues($history, 'hypertension_treatment', $indicators['hypertension_treatment'] ?? []);

            // Indikator 8: Pengobatan Gangguan Jiwa
            $this->setIndicatorValues($history, 'mental_treatment', $indicators['mental_treatment'] ?? []);

            // Indikator 9: Tidak Merokok
            $this->setIndicatorValues($history, 'no_smoking', $indicators['no_smoking'] ?? []);

            // Indikator 10: JKN
            $this->setIndicatorValues($history, 'jkn_membership', $indicators['jkn_membership'] ?? []);

            // Indikator 11: Air Bersih
            $this->setIndicatorValues($history, 'clean_water', $indicators['clean_water'] ?? []);

            // Indikator 12: Jamban Sehat
            $this->setIndicatorValues($history, 'sanitary_toilet', $indicators['sanitary_toilet'] ?? []);

            // Simpan riwayat IKS
            $history->save();

            return $history;
        } catch (\Exception $e) {
            // Log error
            Log::error('Error saving IKS history: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper untuk mengatur nilai indikator dengan nilai default jika tidak ada
     */
    private function setIndicatorValues($history, $indicator, $data)
    {
        $history->{$indicator . '_relevant'} = $data['relevant'] ?? false;
        $history->{$indicator . '_status'} = isset($data['value']) && $data['value'] == 1;
        $history->{$indicator . '_detail'} = $data['detail'] ?? 'Tidak ada data';
    }

    // Tambahkan relasi ini ke model Family.php

    /**
     * Relasi ke model IksRecommendation
     */
    public function recommendations()
    {
        return $this->hasMany(IksRecommendation::class)->orderBy('priority_score', 'desc');
    }

    /**
     * Mendapatkan rekomendasi aktif (yang belum selesai)
     */
    public function getActiveRecommendationsAttribute()
    {
        return $this->recommendations()->whereNotIn('status', ['completed', 'rejected'])->get();
    }

    /**
     * Mendapatkan rekomendasi berdasarkan prioritas
     */
    public function getHighPriorityRecommendationsAttribute()
    {
        return $this->recommendations()->where('priority_level', 'High')->whereNotIn('status', ['completed', 'rejected'])->get();
    }

    /**
     * Mendapatkan status keluarga memiliki Pasangan Usia Subur (PUS)
     * Kriteria PUS: Pasangan (suami/istri) usia produktif
     * - Perempuan berusia 10-54 tahun
     * - Laki-laki berusia > 10 tahun
     * - Berstatus kawin
     *
     * @return bool
     */
    public function getHasPusAttribute(): bool
    {
        if (!$this->relationLoaded('members')) {
            $this->load('members');
        }

        // Hitung anggota keluarga yang memenuhi kriteria PUS
        $pusMembersCount = $this->members->filter(function ($member) {
            if ($member->gender === 'Perempuan') {
                return $member->age >= 10 && $member->age <= 54 &&
                    $member->marital_status === 'Kawin';
            } elseif ($member->gender === 'Laki-laki') {
                return $member->age > 10 && $member->marital_status === 'Kawin';
            }
            return false;
        })->count();

        return $pusMembersCount > 0;
    }

    /**
     * Mendapatkan status keluarga mengikuti program Keluarga Berencana (KB)
     * Keluarga dianggap mengikuti KB jika ada minimal 1 anggota PUS yang menggunakan kontrasepsi
     *
     * @return bool
     */
    public function getFollowsFamilyPlanningAttribute(): bool
    {
        if (!$this->relationLoaded('members')) {
            $this->load('members');
        }

        // Menghitung anggota keluarga yang menggunakan kontrasepsi
        $usingContraceptionCount = $this->members->filter(function ($member) {
            // Kriteria PUS: Pasangan (suami/istri) usia produktif
            $isPUS = false;

            if ($member->gender === 'Perempuan') {
                $isPUS = $member->age >= 10 && $member->age <= 54 &&
                    $member->marital_status === 'Kawin';
            } elseif ($member->gender === 'Laki-laki') {
                $isPUS = $member->age > 10 && $member->marital_status === 'Kawin';
            }

            return $isPUS && $member->uses_contraception;
        })->count();

        return $usingContraceptionCount > 0;
    }

    /**
     * Generate sequence number in building for new family
     */
    public function generateSequenceNumber()
    {
        if (!$this->sequence_number_in_building) {
            $maxSequence = static::where('building_id', $this->building_id)
                ->max('sequence_number_in_building');
            
            $this->sequence_number_in_building = ($maxSequence ?? 0) + 1;
        }
        
        return $this->sequence_number_in_building;
    }

    /**
     * Get formatted sequence number (3 digits)
     */
    public function getFamilySequenceNumber()
    {
        return str_pad($this->sequence_number_in_building ?? 0, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($family) {
            $family->generateSequenceNumber();
        });
    }
}
