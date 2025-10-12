<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SpmDenominatorService
{
    /**
     * Hitung denominator riil untuk sub-indikator tertentu berdasarkan kode.
     * $baseQuery adalah query FamilyMember yang sudah difilter desa (opsional).
     */
    public function countForSubIndicator(string $code, Builder $baseQuery, Carbon $periodStart, Carbon $periodEnd): int
    {
        return $this->queryForSubIndicator($code, $baseQuery, $periodStart, $periodEnd)->count();
    }

    /**
     * Bangun query anggota keluarga (FamilyMember) yang menjadi sasaran (denominator)
     * untuk sub‑indikator tertentu pada periode.
     */
    public function queryForSubIndicator(string $code, Builder $baseQuery, Carbon $periodStart, Carbon $periodEnd): Builder
    {
        $q = clone $baseQuery;
        $uc = strtoupper($code);

        // Helper closures for month/year boundaries relative to period end
        $ltMonths = fn(int $m) => (clone $q)->whereDate('birth_date', '>', $periodEnd->copy()->subMonths($m));
        $eqMonth = function (int $m) use ($q, $periodEnd) {
            $start = $periodEnd->copy()->subMonths($m + 1);
            $end = $periodEnd->copy()->subMonths($m);
            return (clone $q)->whereDate('birth_date', '>', $start)->whereDate('birth_date', '<=', $end);
        };
        $betweenMonths = function (int $minM, int $maxM) use ($q, $periodEnd) {
            $older = $periodEnd->copy()->subMonths($maxM + 1);
            $younger = $periodEnd->copy()->subMonths($minM);
            return (clone $q)->whereDate('birth_date', '>', $older)->whereDate('birth_date', '<=', $younger);
        };
        $betweenYears = function (int $minY, int $maxY) use ($q, $periodEnd) {
            $older = $periodEnd->copy()->subYears($maxY);
            $younger = $periodEnd->copy()->subYears($minY);
            return (clone $q)->whereBetween('birth_date', [$older, $younger]);
        };

        // SPM_01
        if (str_starts_with($uc, 'SPM_01') && preg_match('/^SPM_01_(K1|K4|FE1|FE3|TT|RISTI)$/', $uc)) {
            return (clone $q)->where('is_pregnant', true);
        }

        // SPM_03 (Bayi Baru Lahir / Neonatus)
        // Denominator yang lebih tepat untuk laporan per periode adalah kelahiran hidup pada periode tsb (birth_date in period)
        if (str_starts_with($uc, 'SPM_03') && in_array($uc, ['SPM_03_KN1','SPM_03_KN2','SPM_03_KN3','SPM_03_KOMPLIKASI'], true)) {
            return (clone $q)->whereBetween('birth_date', [$periodStart, $periodEnd]);
        }

        // SPM_04
        if (str_starts_with($uc, 'SPM_04')) {
            // ASI Eksklusif: Bayi 0–6 bulan aktif (longgar: tanpa batasan relationship)
            if ($uc === 'SPM_04_ASI_EKS') return $betweenMonths(0, 6);
            // HB0: Bayi usia 0–7 hari selama periode laporan
            // Ambil kelahiran dari 7 hari sebelum periode hingga akhir periode,
            // agar bayi yang lahir di akhir bulan sebelumnya tetapi masih <=7 hari di awal periode tetap terhitung.
            if ($uc === 'SPM_04_HB0') {
                $start = $periodStart->copy()->subDays(7);
                return (clone $q)->whereBetween('birth_date', [$start, $periodEnd]);
            }
            if (in_array($uc, ['SPM_04_IM_BCG','SPM_04_IM_POLIO1'], true)) return $ltMonths(2);
            if (in_array($uc, ['SPM_04_IM_DPT_HB_HIB_1','SPM_04_IM_POLIO2'], true)) return $eqMonth(2);
            if (in_array($uc, ['SPM_04_IM_DPT_HB_HIB_2','SPM_04_IM_POLIO3'], true)) return $eqMonth(3);
            if (in_array($uc, ['SPM_04_IM_DPT_HB_HIB_3','SPM_04_IM_POLIO4'], true)) return $eqMonth(4);
            if ($uc === 'SPM_04_IM_CAMPAK') return $eqMonth(9);
            // IDL: Bayi 0–11 bulan (sesuai Permenkes, longgar: tanpa batasan relationship)
            if ($uc === 'SPM_04_IDL') return $betweenMonths(0, 11);
            if ($uc === 'SPM_04_VIT_A') return $betweenMonths(6, 59);
            if ($uc === 'SPM_04_VIT_A_MERAH') return $betweenMonths(12, 59);
            if ($uc === 'SPM_04_VIT_A_BIRU') return $betweenMonths(6, 11);
            if ($uc === 'SPM_04_KUNJUNGAN') return $ltMonths(60);
            if ($uc === 'SPM_04_IM_LANJUTAN') return $betweenMonths(18, 24);
            if ($uc === 'SPM_04_CACING') return $betweenMonths(12, 59);
            if ($uc === 'SPM_04_MP_ASI') return $betweenMonths(6, 23);
            if ($uc === 'SPM_04_GIZI_BURUK') return $ltMonths(60);
        }

        // SPM_11
        if ($uc === 'SPM_11_PENEMUAN') return (clone $q)->where('has_chronic_cough', true);
        if (in_array($uc, ['SPM_11_PENGOBATAN','SPM_11_KONTAK'], true)) return (clone $q)->where('has_tuberculosis', true);

        // SPM_02
        if (in_array($uc, ['SPM_02_NAKES','SPM_02_FASKES','SPM_02_KF3','SPM_02_KOMPLIKASI'], true)) return (clone $q)->where('is_pregnant', true);
        // SPM_02_KB_AKTIF: Denominator = PUS (Pasangan Usia Subur)
        // Klasifikasi sasaran: Perempuan menikah usia 15–49 tahun.
        if ($uc === 'SPM_02_KB_AKTIF') {
            return (clone $q)
                ->where('gender', 'Perempuan')
                ->where('marital_status', 'Kawin')
                ->where(function($w){
                    $w->where('is_pregnant', false)->orWhereNull('is_pregnant');
                })
                ->whereBetween('birth_date', [
                    $periodEnd->copy()->subYears(49),
                    $periodEnd->copy()->subYears(15)
                ]);
        }

        // SPM_05/06/07
        if (str_starts_with($uc, 'SPM_05')) {
            if ($uc === 'SPM_05_CACING_6_12') return $betweenYears(6, 12);
            if ($uc === 'SPM_05_CACING_1_5') return $betweenYears(1, 5);
            return $betweenYears(7, 15);
        }
        // WUS (Wanita Usia Subur) = Perempuan 15–49 tahun
        if (in_array($uc, ['SPM_06_TT_WUS','SPM_06_FE_WUS'], true)) {
            return $betweenYears(15, 49)->where('gender', 'Perempuan');
        }
        // PHBS Sekolah (proxy siswa): gunakan usia 7–15 tahun
        if ($uc === 'SPM_06_PHBS') return $betweenYears(7, 15);
        if (str_starts_with($uc, 'SPM_06')) return $betweenYears(15, 59);
        if (str_starts_with($uc, 'SPM_07')) return (clone $q)->whereDate('birth_date', '<=', $periodEnd->copy()->subYears(60));

        // SPM_08/09/10/12
        if ($uc === 'SPM_08_PENGOBATAN') return (clone $q)->where('has_hypertension', true);
        if (in_array($uc, ['SPM_08_PANDU_PTM','SPM_08_POSBINDU'], true)) return $betweenYears(15, 59);
        if (str_starts_with($uc, 'SPM_09')) return (clone $q)->where('has_diabetes_mellitus', true);
        if (str_starts_with($uc, 'SPM_10')) return (clone $q)->where('has_mental_disorder', true);
        if ($uc === 'SPM_12_INTEGRASI_TB_HIV') return (clone $q)->where(function($w){ $w->where('has_tuberculosis', true)->orWhere('is_at_risk_for_hiv', true); });
        if ($uc === 'SPM_12_INTEGRASI_KIA_HIV') {
            $under5Ids = $ltMonths(60)->pluck('id');
            return (clone $q)->where('is_pregnant', true)->orWhereIn('id', $under5Ids);
        }
        if ($uc === 'SPM_12_INTEGRASI_IMS_HIV') return (clone $q)->where('is_at_risk_for_hiv', true);

        // Default empty
        return (clone $q)->whereRaw('0=1');
    }
}
