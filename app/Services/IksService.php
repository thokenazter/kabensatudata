<?php

namespace App\Services;

use App\Models\Family;
use App\Models\FamilyHealthIndex;
use App\Models\FamilyHealthIndexHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IksService
{
    /**
     * Hitung IKS untuk sebuah keluarga - versi yang sudah dimodifikasi sesuai standar PIS-PK resmi
     * 
     * @param Family $family
     * @param string|null $notes Catatan tambahan untuk perhitungan ini
     * @return array Hasil perhitungan IKS beserta detailnya
     */
    public function calculateIks(Family $family, ?string $notes = null): array
    {
        // Inisialisasi array untuk menyimpan hasil perhitungan tiap indikator
        $indicators = [
            'kb' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'birth_facility' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'immunization' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'exclusive_breastfeeding' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'growth_monitoring' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'tb_treatment' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'hypertension_treatment' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'mental_treatment' => ['relevant' => false, 'value' => 0, 'detail' => ''],
            'no_smoking' => ['relevant' => true, 'value' => 0, 'detail' => ''], // Selalu relevan
            'jkn_membership' => ['relevant' => true, 'value' => 0, 'detail' => ''], // Selalu relevan
            'clean_water' => ['relevant' => true, 'value' => 0, 'detail' => ''], // Selalu relevan
            'sanitary_toilet' => ['relevant' => true, 'value' => 0, 'detail' => ''], // Selalu relevan
        ];

        // Load family members
        $family->load('members');
        $members = $family->members;

        // Indikator 1: Keluarga Mengikuti KB
        $this->checkFamilyPlanningIndicator($family, $members, $indicators);

        // Indikator 2: Persalinan di Fasilitas Kesehatan
        $this->checkBirthFacilityIndicator($family, $members, $indicators);

        // Indikator 3: Imunisasi Dasar Lengkap
        $this->checkImmunizationIndicator($family, $members, $indicators);

        // Indikator 4: ASI Eksklusif
        $this->checkExclusiveBreastfeedingIndicator($family, $members, $indicators);

        // Indikator 5: Pemantauan Pertumbuhan Balita
        $this->checkGrowthMonitoringIndicator($family, $members, $indicators);

        // Indikator 6: Pengobatan TB
        $this->checkTbTreatmentIndicator($family, $members, $indicators);

        // Indikator 7: Pengobatan Hipertensi
        $this->checkHypertensionTreatmentIndicator($family, $members, $indicators);

        // Indikator 8: Pengobatan Gangguan Jiwa
        $this->checkMentalTreatmentIndicator($family, $indicators);

        // Indikator 9: Tidak Merokok
        $this->checkNoSmokingIndicator($family, $members, $indicators);

        // Indikator 10: Kepesertaan JKN
        $this->checkJknMembershipIndicator($family, $members, $indicators);

        // Indikator 11: Akses Air Bersih
        $this->checkCleanWaterIndicator($family, $indicators);

        // Indikator 12: Jamban Sehat
        $this->checkSanitaryToiletIndicator($family, $indicators);

        // Hitung IKS
        $relevantCount = 0;
        $positiveCount = 0;

        foreach ($indicators as $indicator) {
            if ($indicator['relevant']) {
                $relevantCount++;
                if ($indicator['value'] == 1) {
                    $positiveCount++;
                }
            }
        }

        // Penanganan kasus khusus: jika tidak ada indikator yang relevan
        if ($relevantCount == 0) {
            // Log peringatan
            Log::warning("Keluarga ID: {$family->id} tidak memiliki indikator relevan untuk perhitungan IKS");

            // Tetapkan nilai default (bisa disesuaikan sesuai kebijakan)
            $iksValue = 0;
            $healthStatus = 'Tidak Dapat Dihitung';
        } else {
            $iksValue = $positiveCount / $relevantCount;
            $healthStatus = $this->determineHealthStatus($iksValue);
        }

        $iksPercentage = $iksValue * 100;

        // Buat hasil perhitungan
        $result = [
            'family' => $family,
            'indicators' => $indicators,
            'iks_value' => $iksValue,
            'iks_percentage' => $iksPercentage,
            'health_status' => $healthStatus,
            'relevant_count' => $relevantCount,
            'positive_count' => $positiveCount,
            'notes' => $notes,
        ];

        // Tambahkan analisis perubahan jika ada riwayat sebelumnya
        $previousData = $this->getPreviousIksData($family);
        if ($previousData) {
            $changeFactors = $this->identifyChangeFactors($result, $previousData);
            $result['improvement_factors'] = $changeFactors['improvements'];
            $result['decline_factors'] = $changeFactors['declines'];
            $result['net_change'] = $changeFactors['net_change'];
            $result['previous_iks'] = $previousData['iks_value'];
        }

        return $result;
    }

    /**
     * Menentukan status kesehatan berdasarkan nilai IKS sesuai standar PIS-PK resmi
     */
    private function determineHealthStatus(float $iksValue): string
    {
        if ($iksValue > 0.8) {
            return 'Keluarga Sehat';
        } elseif ($iksValue >= 0.5 && $iksValue <= 0.8) {
            return 'Keluarga Pra-Sehat';
        } else {
            return 'Keluarga Tidak Sehat';
        }
    }

    /**
     * Indikator 1: Keluarga Mengikuti KB
     * Sesuai pedoman PIS-PK, indikator KB tidak relevan untuk keluarga dengan istri yang sedang hamil
     */
    private function checkFamilyPlanningIndicator(Family $family, $members, array &$indicators): void
    {
        // Cek apakah keluarga memiliki Pasangan Usia Subur (PUS)
        $hasPUS = $family->has_pus;

        // Cek apakah ada istri yang sedang hamil
        $hasPregnantWife = false;

        foreach ($members as $member) {
            if (
                $member->gender === 'Perempuan' &&
                in_array($member->relationship, ['Istri', 'Kepala Keluarga']) &&
                $member->is_pregnant
            ) {
                $hasPregnantWife = true;
                break;
            }
        }

        // Jika ada istri yang sedang hamil, indikator KB tidak relevan
        $isKbRelevant = $hasPUS && !$hasPregnantWife;

        // Cek apakah keluarga mengikuti program KB
        $followsKB = $isKbRelevant ? $family->follows_family_planning : false;

        $indicators['kb']['relevant'] = $isKbRelevant;
        $indicators['kb']['value'] = ($isKbRelevant && $followsKB) ? 1 : 0;

        if (!$hasPUS) {
            $indicators['kb']['detail'] = 'Keluarga tidak memiliki Pasangan Usia Subur (PUS)';
        } else if ($hasPregnantWife) {
            $indicators['kb']['detail'] = 'Indikator KB tidak relevan karena istri sedang hamil';
        } else {
            $indicators['kb']['detail'] = $followsKB ? 'Keluarga mengikuti program KB' : 'Keluarga tidak mengikuti program KB';
        }
    }

    /**
     * Indikator 2: Persalinan di Fasilitas Kesehatan
     */
    private function checkBirthFacilityIndicator(Family $family, $members, array &$indicators): void
    {
        $hasRecentBirth = false;
        $allBirthsInFacility = true;

        foreach ($members as $member) {
            if (in_array($member->relationship, ['Anak', 'Cucu']) && $member->birth_date) {
                $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());

                if ($ageInMonths <= 12) { // Kelahiran dalam 1 tahun terakhir
                    $hasRecentBirth = true;

                    if (!$member->birth_in_facility) {
                        $allBirthsInFacility = false;
                        break;
                    }
                }
            }
        }

        $indicators['birth_facility']['relevant'] = $hasRecentBirth;
        $indicators['birth_facility']['value'] = ($hasRecentBirth && $allBirthsInFacility) ? 1 : 0;
        $indicators['birth_facility']['detail'] = $hasRecentBirth
            ? ($allBirthsInFacility ? 'Semua persalinan di fasilitas kesehatan' : 'Ada persalinan tidak di fasilitas kesehatan')
            : 'Tidak ada kelahiran dalam 1 tahun terakhir';
    }

    /**
     * Indikator 3: Imunisasi Dasar Lengkap
     */
    private function checkImmunizationIndicator(Family $family, $members, array &$indicators): void
    {
        $hasToddler = false;
        $completeImmunization = true;

        foreach ($members as $member) {
            if (in_array($member->relationship, ['Anak', 'Cucu']) && $member->birth_date) {
                $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());

                if ($ageInMonths >= 12 && $ageInMonths <= 23) { // Sesuai standar: 12-23 bulan
                    $hasToddler = true;

                    if (!$member->complete_immunization) {
                        $completeImmunization = false;
                        break;
                    }
                }
            }
        }

        $indicators['immunization']['relevant'] = $hasToddler;
        $indicators['immunization']['value'] = ($hasToddler && $completeImmunization) ? 1 : 0;
        $indicators['immunization']['detail'] = $hasToddler
            ? ($completeImmunization ? 'Balita mendapat imunisasi dasar lengkap' : 'Balita tidak mendapat imunisasi dasar lengkap')
            : 'Tidak ada balita berusia 12-23 bulan';
    }

    /**
     * Indikator 4: ASI Eksklusif
     * Sesuai format pendataan puskesmas, indikator ASI eksklusif relevan untuk bayi usia 7-23 bulan
     * Pertanyaan: "Usia 0-6 Bulan hanya diberi ASI Eksklusif?"
     */
    private function checkExclusiveBreastfeedingIndicator(Family $family, $members, array &$indicators): void
    {
        $hasInfant = false;
        $exclusiveBreastfeeding = true;

        foreach ($members as $member) {
            if (in_array($member->relationship, ['Anak', 'Cucu']) && $member->birth_date) {
                $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());

                if ($ageInMonths >= 7 && $ageInMonths <= 23) { // Sesuai format pendataan puskesmas: 7-23 bulan
                    $hasInfant = true;

                    if (!$member->exclusive_breastfeeding) {
                        $exclusiveBreastfeeding = false;
                        break;
                    }
                }
            }
        }

        $indicators['exclusive_breastfeeding']['relevant'] = $hasInfant;
        $indicators['exclusive_breastfeeding']['value'] = ($hasInfant && $exclusiveBreastfeeding) ? 1 : 0;
        $indicators['exclusive_breastfeeding']['detail'] = $hasInfant
            ? ($exclusiveBreastfeeding ? 'Bayi mendapat ASI eksklusif' : 'Bayi tidak mendapat ASI eksklusif')
            : 'Tidak ada bayi berusia 7-23 bulan';
    }

    /**
     * Indikator 5: Pemantauan Pertumbuhan Balita
     * Sesuai format pendataan puskesmas, indikator ini relevan untuk anak berusia 2-59 bulan
     * Pertanyaan: "Dalam 1 bulan terakhir ikut posyandu?"
     */
    private function checkGrowthMonitoringIndicator(Family $family, $members, array &$indicators): void
    {
        $hasChild = false;
        $growthMonitored = true;

        foreach ($members as $member) {
            if (in_array($member->relationship, ['Anak', 'Cucu']) && $member->birth_date) {
                $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());

                if ($ageInMonths >= 2 && $ageInMonths <= 59) { // Sesuai format pendataan puskesmas: 2-59 bulan
                    $hasChild = true;

                    if (!$member->growth_monitoring) {
                        $growthMonitored = false;
                        break;
                    }
                }
            }
        }

        $indicators['growth_monitoring']['relevant'] = $hasChild;
        $indicators['growth_monitoring']['value'] = ($hasChild && $growthMonitored) ? 1 : 0;
        $indicators['growth_monitoring']['detail'] = $hasChild
            ? ($growthMonitored ? 'Pertumbuhan balita dipantau' : 'Pertumbuhan balita tidak dipantau')
            : 'Tidak ada balita berusia 2-59 bulan';
    }

    /**
     * Indikator 6: Pengobatan TB
     */
    private function checkTbTreatmentIndicator(Family $family, $members, array &$indicators): void
    {
        $hasTbPatient = false;
        $allTbTreated = true;

        foreach ($members as $member) {
            if ($member->has_tuberculosis) {
                $hasTbPatient = true;

                if (!$member->takes_tb_medication_regularly) {
                    $allTbTreated = false;
                    break;
                }
            }
        }

        $indicators['tb_treatment']['relevant'] = $hasTbPatient;
        $indicators['tb_treatment']['value'] = ($hasTbPatient && $allTbTreated) ? 1 : 0;
        $indicators['tb_treatment']['detail'] = $hasTbPatient
            ? ($allTbTreated ? 'Semua penderita TB berobat teratur' : 'Ada penderita TB yang tidak berobat teratur')
            : 'Tidak ada penderita TB dalam keluarga';
    }

    /**
     * Indikator 7: Pengobatan Hipertensi
     */
    private function checkHypertensionTreatmentIndicator(Family $family, $members, array &$indicators): void
    {
        $hasHypertensionPatient = false;
        $allHypertensionTreated = true;

        foreach ($members as $member) {
            if ($member->has_hypertension) {
                $hasHypertensionPatient = true;

                if (!$member->takes_hypertension_medication_regularly) {
                    $allHypertensionTreated = false;
                    break;
                }
            }
        }

        $indicators['hypertension_treatment']['relevant'] = $hasHypertensionPatient;
        $indicators['hypertension_treatment']['value'] = ($hasHypertensionPatient && $allHypertensionTreated) ? 1 : 0;
        $indicators['hypertension_treatment']['detail'] = $hasHypertensionPatient
            ? ($allHypertensionTreated ? 'Semua penderita hipertensi berobat teratur' : 'Ada penderita hipertensi yang tidak berobat teratur')
            : 'Tidak ada penderita hipertensi dalam keluarga';
    }

    /**
     * Indikator 8: Pengobatan Gangguan Jiwa
     */
    private function checkMentalTreatmentIndicator(Family $family, array &$indicators): void
    {
        $hasMentalIllness = $family->has_mental_illness;
        $treatedMentalIllness = $hasMentalIllness ? $family->takes_medication_regularly : false;
        $hasRestrainedMember = $hasMentalIllness ? $family->has_restrained_member : false;

        $indicators['mental_treatment']['relevant'] = $hasMentalIllness;
        $indicators['mental_treatment']['value'] = ($hasMentalIllness && $treatedMentalIllness && !$hasRestrainedMember) ? 1 : 0;

        if (!$hasMentalIllness) {
            $indicators['mental_treatment']['detail'] = 'Tidak ada penderita gangguan jiwa dalam keluarga';
        } else {
            if ($treatedMentalIllness && !$hasRestrainedMember) {
                $indicators['mental_treatment']['detail'] = 'Penderita gangguan jiwa berobat dan tidak ditelantarkan';
            } else {
                $indicators['mental_treatment']['detail'] = $hasRestrainedMember
                    ? 'Ada anggota keluarga dengan gangguan jiwa yang dipasung'
                    : 'Penderita gangguan jiwa tidak berobat teratur';
            }
        }
    }

    /**
     * Indikator 9: Tidak Merokok
     */
    private function checkNoSmokingIndicator(Family $family, $members, array &$indicators): void
    {
        $hasSmoker = false;

        foreach ($members as $member) {
            if ($member->is_smoker) {
                $hasSmoker = true;
                break;
            }
        }

        // Indikator ini selalu relevan sesuai standar PIS-PK
        $indicators['no_smoking']['relevant'] = true;
        $indicators['no_smoking']['value'] = $hasSmoker ? 0 : 1;
        $indicators['no_smoking']['detail'] = $hasSmoker
            ? 'Ada anggota keluarga yang merokok'
            : 'Tidak ada anggota keluarga yang merokok';
    }

    /**
     * Indikator 10: Kepesertaan JKN
     */
    private function checkJknMembershipIndicator(Family $family, $members, array &$indicators): void
    {
        $allMembersHaveJKN = true;

        foreach ($members as $member) {
            if (!$member->has_jkn) {
                $allMembersHaveJKN = false;
                break;
            }
        }

        // Indikator ini selalu relevan sesuai standar PIS-PK
        $indicators['jkn_membership']['relevant'] = true;
        $indicators['jkn_membership']['value'] = $allMembersHaveJKN ? 1 : 0;
        $indicators['jkn_membership']['detail'] = $allMembersHaveJKN
            ? 'Semua anggota keluarga terdaftar JKN'
            : 'Ada anggota keluarga yang tidak terdaftar JKN';
    }

    /**
     * Indikator 11: Akses Air Bersih
     */
    private function checkCleanWaterIndicator(Family $family, array &$indicators): void
    {
        $hasCleanWater = $family->has_clean_water;
        $isWaterProtected = $family->is_water_protected;

        // Indikator ini selalu relevan sesuai standar PIS-PK
        $indicators['clean_water']['relevant'] = true;
        $indicators['clean_water']['value'] = ($hasCleanWater && $isWaterProtected) ? 1 : 0;
        $indicators['clean_water']['detail'] = $hasCleanWater
            ? ($isWaterProtected ? 'Keluarga memiliki akses air bersih yang terlindungi' : 'Keluarga memiliki akses air bersih namun tidak terlindungi')
            : 'Keluarga tidak memiliki akses air bersih';
    }

    /**
     * Indikator 12: Jamban Sehat
     */
    private function checkSanitaryToiletIndicator(Family $family, array &$indicators): void
    {
        $hasToilet = $family->has_toilet;
        $isToiletSanitary = $family->is_toilet_sanitary;

        // Indikator ini selalu relevan sesuai standar PIS-PK
        $indicators['sanitary_toilet']['relevant'] = true;
        $indicators['sanitary_toilet']['value'] = ($hasToilet && $isToiletSanitary) ? 1 : 0;
        $indicators['sanitary_toilet']['detail'] = $hasToilet
            ? ($isToiletSanitary ? 'Keluarga memiliki jamban sehat' : 'Keluarga memiliki jamban namun tidak memenuhi standar kesehatan')
            : 'Keluarga tidak memiliki jamban';
    }

    /**
     * Mengidentifikasi faktor-faktor yang berkontribusi pada perubahan IKS
     * 
     * @param array $currentData Data IKS saat ini
     * @param array $previousData Data IKS sebelumnya
     * @return array Faktor perubahan
     */
    public function identifyChangeFactors(array $currentData, array $previousData): array
    {
        $improvements = [];
        $declines = [];

        // Bandingkan setiap indikator
        foreach ($currentData['indicators'] as $code => $current) {
            // Pastikan indikator ini ada di data sebelumnya dan relevan
            if (!isset($previousData['indicators'][$code]) || !$current['relevant']) {
                continue;
            }

            $previous = $previousData['indicators'][$code];

            // Jika sebelumnya tidak relevan, lanjutkan
            if (!$previous['relevant']) {
                continue;
            }

            // Cek perubahan nilai indikator
            if ($current['value'] > $previous['value']) {
                // Indikator membaik
                $improvements[] = [
                    'code' => $code,
                    'name' => $this->getIndicatorName($code),
                    'from' => $previous['value'],
                    'to' => $current['value'],
                    'detail' => $current['detail'],
                ];
            } elseif ($current['value'] < $previous['value']) {
                // Indikator memburuk
                $declines[] = [
                    'code' => $code,
                    'name' => $this->getIndicatorName($code),
                    'from' => $previous['value'],
                    'to' => $current['value'],
                    'detail' => $current['detail'],
                ];
            }
        }

        return [
            'improvements' => $improvements,
            'declines' => $declines,
            'net_change' => $currentData['iks_value'] - $previousData['iks_value'],
        ];
    }

    /**
     * Mendapatkan nama indikator dari kode
     */
    protected function getIndicatorName(string $code): string
    {
        $names = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Fasilitas Kesehatan',
            'immunization' => 'Imunisasi Dasar Lengkap',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Akses Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        return $names[$code] ?? 'Indikator Tidak Dikenal';
    }

    /**
     * Mengambil data IKS sebelumnya untuk perbandingan
     */
    public function getPreviousIksData(Family $family): ?array
    {
        $lastHistory = $family->healthIndexHistories()->skip(1)->first();

        if (!$lastHistory) {
            return null;
        }

        // Konversi data dari riwayat ke format yang sama dengan hasil kalkulasi IKS
        $indicators = [];

        // Indikator 1: KB
        $indicators['kb'] = [
            'relevant' => $lastHistory->kb_relevant,
            'value' => $lastHistory->kb_status ? 1 : 0,
            'detail' => $lastHistory->kb_detail,
        ];

        // Indikator 2: Persalinan di Fasilitas Kesehatan
        $indicators['birth_facility'] = [
            'relevant' => $lastHistory->birth_facility_relevant,
            'value' => $lastHistory->birth_facility_status ? 1 : 0,
            'detail' => $lastHistory->birth_facility_detail,
        ];

        // Indikator 3: Imunisasi
        $indicators['immunization'] = [
            'relevant' => $lastHistory->immunization_relevant,
            'value' => $lastHistory->immunization_status ? 1 : 0,
            'detail' => $lastHistory->immunization_detail,
        ];

        // Indikator 4: ASI Eksklusif
        $indicators['exclusive_breastfeeding'] = [
            'relevant' => $lastHistory->exclusive_breastfeeding_relevant,
            'value' => $lastHistory->exclusive_breastfeeding_status ? 1 : 0,
            'detail' => $lastHistory->exclusive_breastfeeding_detail,
        ];

        // Indikator 5: Pemantauan Pertumbuhan
        $indicators['growth_monitoring'] = [
            'relevant' => $lastHistory->growth_monitoring_relevant,
            'value' => $lastHistory->growth_monitoring_status ? 1 : 0,
            'detail' => $lastHistory->growth_monitoring_detail,
        ];

        // Indikator 6: Pengobatan TB
        $indicators['tb_treatment'] = [
            'relevant' => $lastHistory->tb_treatment_relevant,
            'value' => $lastHistory->tb_treatment_status ? 1 : 0,
            'detail' => $lastHistory->tb_treatment_detail,
        ];

        // Indikator 7: Pengobatan Hipertensi
        $indicators['hypertension_treatment'] = [
            'relevant' => $lastHistory->hypertension_treatment_relevant,
            'value' => $lastHistory->hypertension_treatment_status ? 1 : 0,
            'detail' => $lastHistory->hypertension_treatment_detail,
        ];

        // Indikator 8: Pengobatan Gangguan Jiwa
        $indicators['mental_treatment'] = [
            'relevant' => $lastHistory->mental_treatment_relevant,
            'value' => $lastHistory->mental_treatment_status ? 1 : 0,
            'detail' => $lastHistory->mental_treatment_detail,
        ];

        // Indikator 9: Tidak Merokok
        $indicators['no_smoking'] = [
            'relevant' => $lastHistory->no_smoking_relevant,
            'value' => $lastHistory->no_smoking_status ? 1 : 0,
            'detail' => $lastHistory->no_smoking_detail,
        ];

        // Indikator 10: JKN
        $indicators['jkn_membership'] = [
            'relevant' => $lastHistory->jkn_membership_relevant,
            'value' => $lastHistory->jkn_membership_status ? 1 : 0,
            'detail' => $lastHistory->jkn_membership_detail,
        ];

        // Indikator 11: Air Bersih
        $indicators['clean_water'] = [
            'relevant' => $lastHistory->clean_water_relevant,
            'value' => $lastHistory->clean_water_status ? 1 : 0,
            'detail' => $lastHistory->clean_water_detail,
        ];

        // Indikator 12: Jamban Sehat
        $indicators['sanitary_toilet'] = [
            'relevant' => $lastHistory->sanitary_toilet_relevant,
            'value' => $lastHistory->sanitary_toilet_status ? 1 : 0,
            'detail' => $lastHistory->sanitary_toilet_detail,
        ];

        return [
            'iks_value' => $lastHistory->iks_value,
            'health_status' => $lastHistory->health_status,
            'relevant_count' => $lastHistory->relevant_indicators,
            'positive_count' => $lastHistory->fulfilled_indicators,
            'indicators' => $indicators,
            'calculated_at' => $lastHistory->calculated_at,
        ];
    }
}
