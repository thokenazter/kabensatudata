<?php

namespace App\Helpers;

use App\Models\FamilyMember;
use Carbon\Carbon;

class FamilyMemberFormHelper
{
    /**
     * Memeriksa apakah anggota keluarga adalah perempuan dengan anak di bawah 12 bulan
     */
    public static function hasInfantUnder12Months(FamilyMember $member = null): bool
    {
        if (!$member || !$member->family_id) {
            return false;
        }

        // Jika bukan perempuan, langsung return false
        if ($member->gender !== 'Perempuan') {
            return false;
        }

        // Cari anak-anak dalam keluarga yang sama
        $childrenUnder12Months = FamilyMember::where('family_id', $member->family_id)
            ->where('relationship', 'Anak')
            ->where(function ($query) {
                $cutoffDate = Carbon::now()->subMonths(12);
                $query->whereDate('birth_date', '>=', $cutoffDate);
            })
            ->count();

        return $childrenUnder12Months > 0;
    }

    /**
     * Memeriksa apakah anggota keluarga memenuhi kriteria KB
     * (perempuan menikah 10-54 tahun tidak hamil, atau laki-laki menikah > 10 tahun)
     */
    public static function isEligibleForKB(FamilyMember $member = null): bool
    {
        if (!$member || !$member->birth_date) {
            return false;
        }

        $age = Carbon::parse($member->birth_date)->age;

        // Kriteria KB untuk perempuan: usia 10-54, tidak hamil, dan hubungannya adalah istri atau kepala keluarga
        if ($member->gender === 'Perempuan') {
            return $age >= 10 && $age <= 54 && !$member->is_pregnant &&
                in_array($member->relationship, ['Kepala Keluarga', 'Istri']);
        }

        // Kriteria KB untuk laki-laki: usia > 10 dan hubungannya adalah suami atau kepala keluarga
        if ($member->gender === 'Laki-laki') {
            return $age > 10 && in_array($member->relationship, ['Kepala Keluarga', 'Suami']);
        }

        return false;
    }

    /**
     * Memeriksa apakah anggota keluarga adalah anak berusia 7-23 bulan (untuk ASI eksklusif)
     */
    public static function isAgedBetween7And23Months(FamilyMember $member = null): bool
    {
        if (!$member || !$member->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 7 && $ageInMonths <= 23;
    }

    /**
     * Memeriksa apakah anggota keluarga adalah anak berusia 12-23 bulan (untuk imunisasi)
     */
    public static function isAgedBetween12And23Months(FamilyMember $member = null): bool
    {
        if (!$member || !$member->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 12 && $ageInMonths <= 23;
    }

    /**
     * Memeriksa apakah anggota keluarga adalah anak berusia 2-59 bulan (untuk pemantauan pertumbuhan)
     */
    public static function isAgedBetween2And59Months(FamilyMember $member = null): bool
    {
        if (!$member || !$member->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($member->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 2 && $ageInMonths <= 59;
    }
}
