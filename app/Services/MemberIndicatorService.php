<?php

namespace App\Services;

use App\Models\FamilyMember;
use Carbon\Carbon;

class MemberIndicatorService
{
    /**
     * Menentukan indikator mana yang relevan untuk anggota keluarga tertentu
     *
     * @param FamilyMember $member
     * @return array
     */
    public function getRelevantIndicators(FamilyMember $member): array
    {
        $relevantIndicators = [];

        // Indikator 1: KB
        if ($this->isEligibleForKB($member)) {
            $relevantIndicators['kb'] = [
                'name' => 'Keluarga Berencana',
                'description' => 'Mengikuti program KB',
                'is_relevant' => true,
                'status' => $member->uses_contraception,
                'icon' => 'heroicon-o-heart',
                'color' => $member->uses_contraception ? 'green' : 'red'
            ];
        }

        // Indikator 2: Persalinan di Fasilitas Kesehatan
        if ($this->isEligibleForBirthFacility($member)) {
            $relevantIndicators['birth_facility'] = [
                'name' => 'Persalinan di Faskes',
                'description' => 'Melahirkan di fasilitas kesehatan',
                'is_relevant' => true,
                'status' => $member->gave_birth_in_health_facility,
                'icon' => 'heroicon-o-building-office-2',
                'color' => $member->gave_birth_in_health_facility ? 'green' : 'red'
            ];
        }

        // Indikator 3: Imunisasi Lengkap
        if ($this->isEligibleForImmunization($member)) {
            $relevantIndicators['immunization'] = [
                'name' => 'Imunisasi Dasar Lengkap',
                'description' => 'Mendapat imunisasi dasar lengkap',
                'is_relevant' => true,
                'status' => $member->complete_immunization,
                'icon' => 'heroicon-o-beaker',
                'color' => $member->complete_immunization ? 'green' : 'red'
            ];
        }

        // Indikator 4: ASI Eksklusif
        if ($this->isEligibleForExclusiveBreastfeeding($member)) {
            $relevantIndicators['exclusive_breastfeeding'] = [
                'name' => 'ASI Eksklusif',
                'description' => 'Diberi ASI eksklusif 0-6 bulan',
                'is_relevant' => true,
                'status' => $member->exclusive_breastfeeding,
                'icon' => 'heroicon-o-face-smile',
                'color' => $member->exclusive_breastfeeding ? 'green' : 'red'
            ];
        }

        // Indikator 5: Pemantauan Pertumbuhan
        if ($this->isUnderFive($member)) {
            $relevantIndicators['growth_monitoring'] = [
                'name' => 'Pemantauan Pertumbuhan',
                'description' => 'Mendapat pemantauan pertumbuhan',
                'is_relevant' => true,
                'status' => $member->growth_monitoring,
                'icon' => 'heroicon-o-chart-bar',
                'color' => $member->growth_monitoring ? 'green' : 'red'
            ];
        }

        // Indikator 6: Pengobatan TB
        if ($member->has_tuberculosis) {
            $relevantIndicators['tb_treatment'] = [
                'name' => 'Pengobatan TB',
                'description' => 'Berobat TB secara teratur',
                'is_relevant' => true,
                'status' => $member->takes_tb_medication_regularly,
                'icon' => 'heroicon-o-bug-ant',
                'color' => $member->takes_tb_medication_regularly ? 'green' : 'red'
            ];
        }

        // Indikator 7: Pengobatan Hipertensi
        if ($member->has_hypertension) {
            $relevantIndicators['hypertension_treatment'] = [
                'name' => 'Pengobatan Hipertensi',
                'description' => 'Berobat hipertensi secara teratur',
                'is_relevant' => true,
                'status' => $member->takes_hypertension_medication_regularly,
                'icon' => 'heroicon-o-heart',
                'color' => $member->takes_hypertension_medication_regularly ? 'green' : 'red'
            ];
        }

        // Indikator 9: Tidak Merokok (untuk usia di atas 15 tahun)
        if ($member->age > 15) {
            $relevantIndicators['no_smoking'] = [
                'name' => 'Tidak Merokok',
                'description' => 'Tidak mengonsumsi rokok',
                'is_relevant' => true,
                'status' => !$member->is_smoker,
                'icon' => 'heroicon-o-no-symbol',
                'color' => !$member->is_smoker ? 'green' : 'red'
            ];
        }

        // Indikator 10: JKN (semua anggota keluarga relevan)
        $relevantIndicators['jkn_membership'] = [
            'name' => 'Kepesertaan JKN',
            'description' => 'Terdaftar sebagai peserta JKN',
            'is_relevant' => true,
            'status' => $member->has_jkn,
            'icon' => 'heroicon-o-identification',
            'color' => $member->has_jkn ? 'green' : 'red'
        ];

        // Indikator 11b: Air Bersih Terlindungi (informasi dari keluarga)
        if ($member->family && $member->family->has_clean_water) {
            $relevantIndicators['protected_water'] = [
                'name' => 'Air Bersih Terlindungi',
                'description' => 'Menggunakan sumber air yang terlindungi',
                'is_relevant' => true,
                'status' => $member->family->is_water_protected ?? false,
                'icon' => 'heroicon-o-beaker',
                'color' => ($member->family->is_water_protected ?? false) ? 'green' : 'red'
            ];
        }

        // Indikator 12b: Jamban Saniter (informasi dari keluarga)
        if ($member->family && $member->family->has_toilet) {
            $relevantIndicators['sanitary_toilet'] = [
                'name' => 'Jamban Saniter',
                'description' => 'Menggunakan jamban yang memenuhi standar kesehatan',
                'is_relevant' => true,
                'status' => $member->family->is_toilet_sanitary ?? false,
                'icon' => 'heroicon-o-home',
                'color' => ($member->family->is_toilet_sanitary ?? false) ? 'green' : 'red'
            ];
        }

        return $relevantIndicators;
    }

    /**
     * Memeriksa apakah anggota keluarga memenuhi kriteria KB
     */
    private function isEligibleForKB(FamilyMember $member): bool
    {
        if (!$member->birth_date) {
            return false;
        }

        $age = Carbon::parse($member->birth_date)->age;

        // Kriteria KB untuk perempuan: usia 10-54, tidak hamil, dan berstatus kawin
        if ($member->gender === 'Perempuan') {
            return $age >= 10 && $age <= 54 && !$member->is_pregnant && $member->marital_status === 'Kawin';
        }

        // Kriteria KB untuk laki-laki: usia > 10 dan berstatus kawin
        if ($member->gender === 'Laki-laki') {
            return $age > 10 && $member->marital_status === 'Kawin';
        }

        return false;
    }

    /**
     * Memeriksa apakah anggota keluarga relevan untuk indikator persalinan di faskes
     */
    private function isEligibleForBirthFacility(FamilyMember $member): bool
    {
        // Hanya relevan untuk perempuan dengan status Kepala Keluarga atau Istri
        if ($member->gender !== 'Perempuan' || !in_array($member->relationship, ['Kepala Keluarga', 'Istri'])) {
            return false;
        }

        // Cek apakah ada bayi < 12 bulan dalam keluarga
        if (!$member->family) {
            return false;
        }

        $hasInfant = $member->family->members()->where(function ($query) {
            $query->where('relationship', 'Anak')
                ->whereDate('birth_date', '>=', now()->subMonths(12));
        })->exists();

        return $hasInfant;
    }

    /**
     * Memeriksa apakah anggota keluarga relevan untuk indikator imunisasi
     */
    private function isEligibleForImmunization(FamilyMember $member): bool
    {
        if (!$member->birth_date) {
            return false;
        }

        // Harus berupa anak
        if (!in_array($member->relationship, ['Anak', 'Cucu'])) {
            return false;
        }

        // Cek usia 12-23 bulan
        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 12 && $ageInMonths <= 23;
    }

    /**
     * Memeriksa apakah anggota keluarga relevan untuk indikator ASI eksklusif
     * Sesuai format pendataan puskesmas, indikator ASI eksklusif relevan untuk bayi usia 7-23 bulan
     * Pertanyaan: "Usia 0-6 Bulan hanya diberi ASI Eksklusif?"
     */
    private function isEligibleForExclusiveBreastfeeding(FamilyMember $member): bool
    {
        if (!$member->birth_date) {
            return false;
        }

        // Harus berupa anak
        if (!in_array($member->relationship, ['Anak', 'Cucu'])) {
            return false;
        }

        // Cek usia 7-23 bulan sesuai format pendataan puskesmas
        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 7 && $ageInMonths <= 23;
    }

    /**
     * Memeriksa apakah anggota keluarga relevan untuk pemantauan pertumbuhan balita
     * Sesuai format pendataan puskesmas, indikator ini relevan untuk anak berusia 2-59 bulan
     * Pertanyaan: "Dalam 1 bulan terakhir ikut posyandu?"
     */
    private function isUnderFive(FamilyMember $member): bool
    {
        if (!$member->birth_date) {
            return false;
        }

        // Harus berupa anak
        if (!in_array($member->relationship, ['Anak', 'Cucu'])) {
            return false;
        }

        // Cek usia 2-59 bulan sesuai format pendataan puskesmas
        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 2 && $ageInMonths <= 59;
    }
}
