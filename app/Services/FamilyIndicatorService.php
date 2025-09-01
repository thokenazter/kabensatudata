<?php

namespace App\Services;

use App\Models\Family;
use Carbon\Carbon;

class FamilyIndicatorService
{
    /**
     * Mendapatkan status agregat indikator untuk seluruh keluarga
     *
     * @param Family $family
     * @return array
     */
    public function getAggregateIndicators(Family $family): array
    {
        // Load anggota keluarga jika belum dimuat
        if (!$family->relationLoaded('members')) {
            $family->load('members');
        }

        $indicators = [];
        $members = $family->members;

        // 1. KB (Keluarga Berencana)
        $hasPUS = $family->has_pus;
        $pusMembersCount = $this->getPasanganUsiaSuburCount($members);
        $usingContraceptionCount = $this->getUsingContraceptionCount($members);

        // Cek apakah ada istri yang sedang hamil
        $hasPregnantWife = $members->contains(function ($member) {
            return $member->gender === 'Perempuan' &&
                in_array($member->relationship, ['Istri', 'Kepala Keluarga']) &&
                $member->is_pregnant;
        });

        // Indikator KB tidak relevan jika ada istri yang sedang hamil
        $isKbRelevant = $hasPUS && !$hasPregnantWife;

        $indicators['kb'] = [
            'name' => 'Keluarga Berencana',
            'description' => $hasPregnantWife ? 'Tidak relevan karena istri sedang hamil' : 'Keluarga mengikuti program KB',
            'is_relevant' => $isKbRelevant,
            'status' => $isKbRelevant && $usingContraceptionCount > 0,
            'details' => [
                'total_relevant' => $isKbRelevant ? $pusMembersCount : 0,
                'total_fulfilled' => $isKbRelevant ? $usingContraceptionCount : 0,
                'percentage' => $isKbRelevant && $pusMembersCount > 0 ? round(($usingContraceptionCount / $pusMembersCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-heart',
            'color' => $isKbRelevant && $usingContraceptionCount > 0 ? 'green' : 'red'
        ];

        // 2. Persalinan di Fasilitas Kesehatan
        $mothersWithInfantCount = $this->getMothersWithInfantCount($family);
        $birthInFacilityCount = $this->getBirthInFacilityCount($members);

        $indicators['birth_facility'] = [
            'name' => 'Persalinan di Faskes',
            'description' => 'Ibu melahirkan di fasilitas kesehatan',
            'is_relevant' => $mothersWithInfantCount > 0,
            'status' => $birthInFacilityCount > 0,
            'details' => [
                'total_relevant' => $mothersWithInfantCount,
                'total_fulfilled' => $birthInFacilityCount,
                'percentage' => $mothersWithInfantCount > 0 ? round(($birthInFacilityCount / $mothersWithInfantCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-building-office-2',
            'color' => $birthInFacilityCount > 0 ? 'green' : 'red'
        ];

        // 3. Imunisasi Dasar Lengkap
        $infantsCount = $this->getInfants12To23MonthsCount($members);
        $immunizedCount = $this->getImmunizedInfantsCount($members);

        $indicators['immunization'] = [
            'name' => 'Imunisasi Dasar Lengkap',
            'description' => 'Bayi mendapat imunisasi dasar lengkap',
            'is_relevant' => $infantsCount > 0,
            'status' => $immunizedCount == $infantsCount && $infantsCount > 0,
            'details' => [
                'total_relevant' => $infantsCount,
                'total_fulfilled' => $immunizedCount,
                'percentage' => $infantsCount > 0 ? round(($immunizedCount / $infantsCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-beaker',
            'color' => ($immunizedCount == $infantsCount && $infantsCount > 0) ? 'green' : 'red'
        ];

        // 4. ASI Eksklusif
        $infantsForASICount = $this->getInfants6To11MonthsCount($members);
        $exclusiveBreastfeedingCount = $this->getExclusiveBreastfeedingCount($members);

        $indicators['exclusive_breastfeeding'] = [
            'name' => 'ASI Eksklusif',
            'description' => 'Usia 0-6 Bulan hanya diberi ASI Eksklusif',
            'is_relevant' => $infantsForASICount > 0,
            'status' => $exclusiveBreastfeedingCount == $infantsForASICount && $infantsForASICount > 0,
            'details' => [
                'total_relevant' => $infantsForASICount,
                'total_fulfilled' => $exclusiveBreastfeedingCount,
                'percentage' => $infantsForASICount > 0 ? round(($exclusiveBreastfeedingCount / $infantsForASICount) * 100) : 0
            ],
            'icon' => 'heroicon-o-face-smile',
            'color' => ($exclusiveBreastfeedingCount == $infantsForASICount && $infantsForASICount > 0) ? 'green' : 'red'
        ];

        // 5. Pemantauan Pertumbuhan Balita
        $toddlersCount = $this->getToddlersCount($members);
        $monitoredToddlersCount = $this->getMonitoredToddlersCount($members);

        $indicators['growth_monitoring'] = [
            'name' => 'Pemantauan Pertumbuhan',
            'description' => 'Balita mendapat pemantauan pertumbuhan',
            'is_relevant' => $toddlersCount > 0,
            'status' => $monitoredToddlersCount == $toddlersCount && $toddlersCount > 0,
            'details' => [
                'total_relevant' => $toddlersCount,
                'total_fulfilled' => $monitoredToddlersCount,
                'percentage' => $toddlersCount > 0 ? round(($monitoredToddlersCount / $toddlersCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-chart-bar',
            'color' => ($monitoredToddlersCount == $toddlersCount && $toddlersCount > 0) ? 'green' : 'red'
        ];

        // 6. TB Treatment
        $tbPatientsCount = $this->getTBPatientsCount($members);
        $treatedTBCount = $this->getTreatedTBCount($members);

        $indicators['tb_treatment'] = [
            'name' => 'Pengobatan TB',
            'description' => 'Penderita TB berobat sesuai standar',
            'is_relevant' => $tbPatientsCount > 0,
            'status' => $treatedTBCount == $tbPatientsCount && $tbPatientsCount > 0,
            'details' => [
                'total_relevant' => $tbPatientsCount,
                'total_fulfilled' => $treatedTBCount,
                'percentage' => $tbPatientsCount > 0 ? round(($treatedTBCount / $tbPatientsCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-bug-ant',
            'color' => ($treatedTBCount == $tbPatientsCount && $tbPatientsCount > 0) ? 'green' : 'red'
        ];

        // 7. Hipertensi Treatment
        $hyperPatientCount = $this->getHypertensionPatientsCount($members);
        $treatedHyperCount = $this->getTreatedHypertensionCount($members);

        $indicators['hypertension_treatment'] = [
            'name' => 'Pengobatan Hipertensi',
            'description' => 'Penderita hipertensi berobat teratur',
            'is_relevant' => $hyperPatientCount > 0,
            'status' => $treatedHyperCount == $hyperPatientCount && $hyperPatientCount > 0,
            'details' => [
                'total_relevant' => $hyperPatientCount,
                'total_fulfilled' => $treatedHyperCount,
                'percentage' => $hyperPatientCount > 0 ? round(($treatedHyperCount / $hyperPatientCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-heart',
            'color' => ($treatedHyperCount == $hyperPatientCount && $hyperPatientCount > 0) ? 'green' : 'red'
        ];

        // 8. Pengobatan Gangguan Jiwa (tingkat keluarga)
        $mentalIllnessRelevant = $family->has_mental_illness;
        $mentalIllnessTreated = $family->takes_medication_regularly;
        $noRestrainedMember = !$family->has_restrained_member;

        $indicators['mental_treatment'] = [
            'name' => 'Pengobatan Gangguan Jiwa',
            'description' => 'Penderita gangguan jiwa berobat',
            'is_relevant' => $mentalIllnessRelevant,
            'status' => $mentalIllnessRelevant && $mentalIllnessTreated && $noRestrainedMember,
            'details' => [
                'has_mental_illness' => $mentalIllnessRelevant,
                'takes_medication' => $mentalIllnessTreated,
                'not_restrained' => $noRestrainedMember
            ],
            'icon' => 'heroicon-o-brain',
            'color' => ($mentalIllnessRelevant && $mentalIllnessTreated && $noRestrainedMember) ? 'green' : 'red'
        ];

        // 9. Tidak Merokok
        $adultCount = $this->getAdultsCount($members);
        $smokerCount = $this->getSmokersCount($members);

        $indicators['no_smoking'] = [
            'name' => 'Tidak Merokok',
            'description' => 'Anggota keluarga tidak merokok',
            'is_relevant' => $adultCount > 0,
            'status' => $smokerCount == 0,
            'details' => [
                'total_adults' => $adultCount,
                'total_smokers' => $smokerCount,
                'percentage_non_smokers' => $adultCount > 0 ? round((($adultCount - $smokerCount) / $adultCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-no-symbol',
            'color' => $smokerCount == 0 ? 'green' : 'red'
        ];

        // 10. JKN Membership
        $membersCount = count($members);
        $jknMembersCount = $this->getJKNMembersCount($members);

        $indicators['jkn_membership'] = [
            'name' => 'Kepesertaan JKN',
            'description' => 'Anggota keluarga terdaftar JKN',
            'is_relevant' => $membersCount > 0,
            'status' => $jknMembersCount == $membersCount,
            'details' => [
                'total_members' => $membersCount,
                'total_jkn_members' => $jknMembersCount,
                'percentage' => $membersCount > 0 ? round(($jknMembersCount / $membersCount) * 100) : 0
            ],
            'icon' => 'heroicon-o-identification',
            'color' => ($jknMembersCount == $membersCount) ? 'green' : 'red'
        ];

        // 11. Air Bersih
        $indicators['clean_water'] = [
            'name' => 'Akses Air Bersih',
            'description' => 'Keluarga menggunakan air bersih',
            'is_relevant' => true,
            'status' => $family->has_clean_water && $family->is_water_protected,
            'details' => [
                'has_clean_water' => $family->has_clean_water,
                'is_water_protected' => $family->is_water_protected
            ],
            'icon' => 'heroicon-o-beaker',
            'color' => ($family->has_clean_water && $family->is_water_protected) ? 'green' : 'red'
        ];

        // 12. Jamban Sehat
        $indicators['sanitary_toilet'] = [
            'name' => 'Jamban Sehat',
            'description' => 'Keluarga menggunakan jamban sehat',
            'is_relevant' => true,
            'status' => $family->has_toilet && $family->is_toilet_sanitary,
            'details' => [
                'has_toilet' => $family->has_toilet,
                'is_toilet_sanitary' => $family->is_toilet_sanitary
            ],
            'icon' => 'heroicon-o-home',
            'color' => ($family->has_toilet && $family->is_toilet_sanitary) ? 'green' : 'red'
        ];

        return $indicators;
    }

    // Helper methods untuk menghitung jumlah

    private function getPasanganUsiaSuburCount($members)
    {
        return $members->filter(function ($member) {
            if ($member->gender === 'Perempuan') {
                return $member->age >= 10 && $member->age <= 54 &&
                    $member->marital_status === 'Kawin';
            } elseif ($member->gender === 'Laki-laki') {
                return $member->age > 10 && $member->marital_status === 'Kawin';
            }
            return false;
        })->count();
    }

    private function getUsingContraceptionCount($members)
    {
        return $members->filter(function ($member) {
            return $this->isMemberEligibleForKB($member) && $member->uses_contraception;
        })->count();
    }

    private function isMemberEligibleForKB($member)
    {
        if ($member->gender === 'Perempuan') {
            return $member->age >= 10 && $member->age <= 54 &&
                $member->marital_status === 'Kawin';
        } elseif ($member->gender === 'Laki-laki') {
            return $member->age > 10 && $member->marital_status === 'Kawin';
        }
        return false;
    }

    private function getMothersWithInfantCount($family)
    {
        // Hitung jumlah ibu yang memiliki bayi < 12 bulan
        $hasInfant = $family->members()->where(function ($query) {
            $query->where('relationship', 'Anak')
                ->whereDate('birth_date', '>=', now()->subMonths(12));
        })->exists();

        if (!$hasInfant) {
            return 0;
        }

        // Hitung jumlah perempuan yang mungkin ibu dari bayi
        return $family->members()
            ->where('gender', 'Perempuan')
            ->whereIn('relationship', ['Kepala Keluarga', 'Istri'])
            ->count();
    }

    private function getBirthInFacilityCount($members)
    {
        return $members->filter(function ($member) {
            return $member->gender === 'Perempuan' &&
                in_array($member->relationship, ['Kepala Keluarga', 'Istri']) &&
                $member->gave_birth_in_health_facility;
        })->count();
    }

    private function getInfants12To23MonthsCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu']) || !$member->birth_date) {
                return false;
            }

            $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
            return $ageInMonths >= 12 && $ageInMonths <= 23;
        })->count();
    }

    private function getImmunizedInfantsCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu']) || !$member->birth_date) {
                return false;
            }

            $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
            return $ageInMonths >= 12 && $ageInMonths <= 23 && $member->complete_immunization;
        })->count();
    }

    /**
     * Menghitung jumlah bayi berusia 7-23 bulan dalam keluarga
     * Sesuai format pendataan puskesmas, indikator ASI eksklusif relevan untuk bayi usia 7-23 bulan
     * Pertanyaan: "Usia 0-6 Bulan hanya diberi ASI Eksklusif?"
     */
    private function getInfants6To11MonthsCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu']) || !$member->birth_date) {
                return false;
            }

            $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
            return $ageInMonths >= 7 && $ageInMonths <= 23;
        })->count();
    }

    private function getExclusiveBreastfeedingCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu']) || !$member->birth_date) {
                return false;
            }

            $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
            return $ageInMonths >= 7 && $ageInMonths <= 23 && $member->exclusive_breastfeeding;
        })->count();
    }

    private function getToddlersCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu'])) {
                return false;
            }

            return $member->age !== null && $member->age < 5;
        })->count();
    }

    private function getMonitoredToddlersCount($members)
    {
        return $members->filter(function ($member) {
            if (!in_array($member->relationship, ['Anak', 'Cucu'])) {
                return false;
            }

            return $member->age !== null && $member->age < 5 && $member->growth_monitoring;
        })->count();
    }

    private function getTBPatientsCount($members)
    {
        return $members->filter(function ($member) {
            return $member->has_tuberculosis;
        })->count();
    }

    private function getTreatedTBCount($members)
    {
        return $members->filter(function ($member) {
            return $member->has_tuberculosis && $member->takes_tb_medication_regularly;
        })->count();
    }

    private function getHypertensionPatientsCount($members)
    {
        return $members->filter(function ($member) {
            return $member->has_hypertension;
        })->count();
    }

    private function getTreatedHypertensionCount($members)
    {
        return $members->filter(function ($member) {
            return $member->has_hypertension && $member->takes_hypertension_medication_regularly;
        })->count();
    }

    private function getAdultsCount($members)
    {
        return $members->filter(function ($member) {
            return $member->age !== null && $member->age > 15;
        })->count();
    }

    private function getSmokersCount($members)
    {
        return $members->filter(function ($member) {
            return $member->is_smoker;
        })->count();
    }

    private function getJKNMembersCount($members)
    {
        return $members->filter(function ($member) {
            return $member->has_jkn;
        })->count();
    }
}
