<?php

namespace App\Services;

use App\Models\Family;
use Carbon\Carbon;

class IksService
{
    /**
     * Hitung IKS untuk sebuah keluarga
     * 
     * @param Family $family
     * @return array Hasil perhitungan IKS beserta detailnya
     */
    public function calculateIks(Family $family): array
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
            'no_smoking' => ['relevant' => true, 'value' => 0, 'detail' => ''],
            'jkn_membership' => ['relevant' => true, 'value' => 0, 'detail' => ''],
            'clean_water' => ['relevant' => true, 'value' => 0, 'detail' => ''],
            'sanitary_toilet' => ['relevant' => true, 'value' => 0, 'detail' => ''],
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

        $iksValue = $relevantCount > 0 ? $positiveCount / $relevantCount : 0;
        $iksPercentage = $iksValue * 100;

        // Tentukan status kesehatan keluarga
        $healthStatus = $this->determineHealthStatus($iksValue);

        return [
            'family' => $family,
            'indicators' => $indicators,
            'iks_value' => $iksValue,
            'iks_percentage' => $iksPercentage,
            'health_status' => $healthStatus,
            'relevant_count' => $relevantCount,
            'positive_count' => $positiveCount
        ];
    }

    /**
     * Menentukan status kesehatan berdasarkan nilai IKS
     */
    private function determineHealthStatus(float $iksValue): string
    {
        if ($iksValue > 0.8) {
            return 'Keluarga Sehat';
        } elseif ($iksValue >= 0.5) {
            return 'Keluarga Pra-Sehat';
        } else {
            return 'Keluarga Tidak Sehat';
        }
    }

    /**
     * Indikator 1: Keluarga Mengikuti Program KB
     */
    private function checkFamilyPlanningIndicator(Family $family, $members, array &$indicators): void
    {
        $hasPUS = false;
        $followsKB = false;

        foreach ($members as $member) {
            // Perempuan PUS: menikah, 10-54 tahun, tidak hamil
            $isFemalePUS = $member->gender === 'Perempuan' &&
                $member->marital_status === 'Kawin' &&
                $member->age >= 10 &&
                $member->age <= 54 &&
                !$member->is_pregnant;

            // Laki-laki PUS: menikah, > 10 tahun
            $isMalePUS = $member->gender === 'Laki-laki' &&
                $member->marital_status === 'Kawin' &&
                $member->age > 10;

            if ($isFemalePUS || $isMalePUS) {
                $hasPUS = true;

                if ($member->uses_contraception) {
                    $followsKB = true;
                    break;
                }
            }
        }

        $indicators['kb']['relevant'] = $hasPUS;
        $indicators['kb']['value'] = $followsKB ? 1 : 0;
        $indicators['kb']['detail'] = $hasPUS
            ? ($followsKB ? 'Keluarga mengikuti program KB' : 'Keluarga tidak mengikuti program KB')
            : 'Tidak ada PUS dalam keluarga';
    }

    /**
     * Indikator 2: Persalinan di Fasilitas Kesehatan
     */
    private function checkBirthFacilityIndicator(Family $family, $members, array &$indicators): void
    {
        $hasInfant = false;
        $birthInFacility = false;
        $today = Carbon::now();

        foreach ($members as $member) {
            if ($member->age !== null && $member->age < 1) {
                $hasInfant = true;

                // Cari ibu dari bayi (ini adalah simplifikasi, asumsi semua persalinan ibu-ibu tercatat)
                foreach ($members as $potentialMother) {
                    if (
                        $potentialMother->gender === 'Perempuan' &&
                        $potentialMother->gave_birth_in_health_facility
                    ) {
                        $birthInFacility = true;
                        break;
                    }
                }

                break;
            }
        }

        $indicators['birth_facility']['relevant'] = $hasInfant;
        $indicators['birth_facility']['value'] = $birthInFacility ? 1 : 0;
        $indicators['birth_facility']['detail'] = $hasInfant
            ? ($birthInFacility ? 'Persalinan dilakukan di fasilitas kesehatan' : 'Persalinan tidak dilakukan di fasilitas kesehatan')
            : 'Tidak ada bayi berusia < 1 tahun';
    }

    /**
     * Indikator 3: Imunisasi Dasar Lengkap
     */
    private function checkImmunizationIndicator(Family $family, $members, array &$indicators): void
    {
        $hasToddler = false;
        $completeImmunization = false;

        foreach ($members as $member) {
            // Cek bayi berusia 12-23 bulan
            if ($member->age !== null && $member->age >= 1 && $member->age < 2) {
                $hasToddler = true;

                if ($member->complete_immunization) {
                    $completeImmunization = true;
                }

                break;
            }
        }

        $indicators['immunization']['relevant'] = $hasToddler;
        $indicators['immunization']['value'] = $completeImmunization ? 1 : 0;
        $indicators['immunization']['detail'] = $hasToddler
            ? ($completeImmunization ? 'Bayi mendapat imunisasi dasar lengkap' : 'Bayi tidak mendapat imunisasi dasar lengkap')
            : 'Tidak ada bayi berusia 12-23 bulan';
    }

    /**
     * Indikator 4: ASI Eksklusif
     */
    private function checkExclusiveBreastfeedingIndicator(Family $family, $members, array &$indicators): void
    {
        $hasInfant = false;
        $exclusiveBreastfeeding = false;

        foreach ($members as $member) {
            // Cek bayi berusia 7-23 bulan
            if ($member->age !== null && $member->age < 2) {
                // Hitung umur dalam bulan
                $ageInMonths = ($member->age * 12) + (Carbon::now()->diffInMonths($member->birth_date) % 12);

                if ($ageInMonths >= 7 && $ageInMonths <= 23) {
                    $hasInfant = true;

                    if ($member->exclusive_breastfeeding) {
                        $exclusiveBreastfeeding = true;
                    }

                    break;
                }
            }
        }

        $indicators['exclusive_breastfeeding']['relevant'] = $hasInfant;
        $indicators['exclusive_breastfeeding']['value'] = $exclusiveBreastfeeding ? 1 : 0;
        $indicators['exclusive_breastfeeding']['detail'] = $hasInfant
            ? ($exclusiveBreastfeeding ? 'Bayi mendapat ASI eksklusif' : 'Bayi tidak mendapat ASI eksklusif')
            : 'Tidak ada bayi berusia 7-23 bulan';
    }

    /**
     * Indikator 5: Pemantauan Pertumbuhan Balita
     */
    private function checkGrowthMonitoringIndicator(Family $family, $members, array &$indicators): void
    {
        $hasToddler = false;
        $growthMonitoring = false;

        foreach ($members as $member) {
            if ($member->is_under_five) {
                $hasToddler = true;

                if ($member->growth_monitoring) {
                    $growthMonitoring = true;
                    break;
                }
            }
        }

        $indicators['growth_monitoring']['relevant'] = $hasToddler;
        $indicators['growth_monitoring']['value'] = $growthMonitoring ? 1 : 0;
        $indicators['growth_monitoring']['detail'] = $hasToddler
            ? ($growthMonitoring ? 'Balita mendapat pemantauan pertumbuhan' : 'Balita tidak mendapat pemantauan pertumbuhan')
            : 'Tidak ada balita dalam keluarga';
    }

    /**
     * Indikator 6: Pengobatan TB
     */
    private function checkTbTreatmentIndicator(Family $family, $members, array &$indicators): void
    {
        $hasTB = false;
        $treatedTB = true; // Default true, akan menjadi false jika ada yang tidak berobat

        foreach ($members as $member) {
            if ($member->has_tuberculosis) {
                $hasTB = true;

                if (!$member->takes_tb_medication_regularly) {
                    $treatedTB = false;
                    break;
                }
            }
        }

        $indicators['tb_treatment']['relevant'] = $hasTB;
        $indicators['tb_treatment']['value'] = $treatedTB ? 1 : 0;
        $indicators['tb_treatment']['detail'] = $hasTB
            ? ($treatedTB ? 'Penderita TB berobat sesuai standar' : 'Ada penderita TB yang tidak berobat sesuai standar')
            : 'Tidak ada penderita TB dalam keluarga';
    }

    /**
     * Indikator 7: Pengobatan Hipertensi
     */
    private function checkHypertensionTreatmentIndicator(Family $family, $members, array &$indicators): void
    {
        $hasHypertension = false;
        $treatedHypertension = true; // Default true, akan menjadi false jika ada yang tidak berobat

        foreach ($members as $member) {
            if ($member->has_hypertension) {
                $hasHypertension = true;

                if (!$member->takes_hypertension_medication_regularly) {
                    $treatedHypertension = false;
                    break;
                }
            }
        }

        $indicators['hypertension_treatment']['relevant'] = $hasHypertension;
        $indicators['hypertension_treatment']['value'] = $treatedHypertension ? 1 : 0;
        $indicators['hypertension_treatment']['detail'] = $hasHypertension
            ? ($treatedHypertension ? 'Penderita hipertensi berobat teratur' : 'Ada penderita hipertensi yang tidak berobat teratur')
            : 'Tidak ada penderita hipertensi dalam keluarga';
    }

    /**
     * Indikator 8: Pengobatan Gangguan Jiwa
     */
    private function checkMentalTreatmentIndicator(Family $family, array &$indicators): void
    {
        $hasMentalIllness = $family->has_mental_illness;
        $treatedMentalIllness = $family->takes_medication_regularly;
        $hasRestrainedMember = $family->has_restrained_member;

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

        $indicators['sanitary_toilet']['value'] = ($hasToilet && $isToiletSanitary) ? 1 : 0;
        $indicators['sanitary_toilet']['detail'] = $hasToilet
            ? ($isToiletSanitary ? 'Keluarga memiliki jamban sehat' : 'Keluarga memiliki jamban namun tidak memenuhi standar kesehatan')
            : 'Keluarga tidak memiliki jamban';
    }
}
