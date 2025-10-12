<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmIndicator;
use App\Models\SpmSubIndicator;

class SpmIndicatorsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Helper: ensure indicator exists
        $mk = fn($code, $name) => SpmIndicator::firstOrCreate(['code' => $code], ['name' => $name]);
        // Helper: upsert sub-indicator by code (update name and indicator binding if changed)
        $upd = function(SpmIndicator $indicator, string $code, string $name) {
            SpmSubIndicator::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'spm_indicator_id' => $indicator->id]
            );
        };

        $i01 = $mk('SPM_01', 'Pelayanan Kesehatan Ibu Hamil');
        foreach ([
            ['SPM_01_K1', 'Cakupan kunjungan ibu hamil K1'],
            ['SPM_01_K4', 'Cakupan kunjungan ibu hamil K4'],
            ['SPM_01_TT', 'Cakupan TT ibu hamil'],
            ['SPM_01_FE1', 'Cakupan pemberian Fe-1'],
            ['SPM_01_FE3', 'Cakupan pemberian Fe-3'],
            ['SPM_01_RISTI', 'Cakupan penanganan bumil risti'],
        ] as [$c,$n]) { $upd($i01, $c, $n); }

        $i02 = $mk('SPM_02', 'Pelayanan Kesehatan Ibu Bersalin');
        foreach ([
            ['SPM_02_NAKES', 'Cakupan pertolongan persalinan oleh bidan'],
            ['SPM_02_FASKES', 'Cakupan persalinan di faskes'],
            ['SPM_02_KF3', 'Cakupan pelayanan nifas KF1-KF3'],
            ['SPM_02_KB_AKTIF', 'Cakupan peserta KB aktif'],
            ['SPM_02_KOMPLIKASI', 'Cakupan komplikasi kebidanan yang ditangani'],
        ] as [$c,$n]) { $upd($i02, $c, $n); }

        $i03 = $mk('SPM_03', 'Pelayanan Kesehatan Bayi Baru Lahir');
        foreach ([
            ['SPM_03_KN1', 'Cakupan kunjungan bayi baru lahir KN1'],
            ['SPM_03_KN2', 'Cakupan kunjungan bayi baru lahir KN2'],
            ['SPM_03_KN3', 'Cakupan kunjungan bayi baru lahir KN3'],
            ['SPM_03_KOMPLIKASI', 'Cakupan Neonatus dengan komplikasi yang ditangani'],
        ] as [$c,$n]) { $upd($i03, $c, $n); }

        $i04 = $mk('SPM_04', 'Pelayanan Kesehatan Balita');
        foreach ([
            ['SPM_04_KUNJUNGAN', 'Cakupan kunjungan balita diposyandu'],
            ['SPM_04_IDL', 'Imunisasi dasar lengkap'],
            ['SPM_04_ASI_EKS', 'Cakupan bayi ASI eksklusif'],
            ['SPM_04_MP_ASI', 'Cakupan pemberian MP-ASI'],
            ['SPM_04_GIZI_BURUK', 'Balita gizi buruk yang mendapat penanganan'],
            ['SPM_04_VIT_A', 'Cakupan pemberian Vitamin A (umum)'],
            ['SPM_04_VIT_A_MERAH', 'Vitamin A Merah 12–59 bln'],
            ['SPM_04_VIT_A_BIRU', 'Vitamin A Biru 6–11 bln'],
            ['SPM_04_IM_LANJUTAN', 'Cakupan imunisasi lanjutan'],
            ['SPM_04_CACING', 'Cakupan pemberian obat cacing (balita)'],
        ] as [$c,$n]) { $upd($i04, $c, $n); }
        // Detail imunisasi spesifik
        foreach ([
            ['SPM_04_HB0', 'HB0'],
            ['SPM_04_IM_BCG', 'BCG'],
            ['SPM_04_IM_POLIO1', 'Polio 1'],
            ['SPM_04_IM_DPT_HB_HIB_1', 'DPT HB-HIB 1'],
            ['SPM_04_IM_POLIO2', 'Polio 2'],
            ['SPM_04_IM_DPT_HB_HIB_2', 'DPT HB-HIB 2'],
            ['SPM_04_IM_POLIO3', 'Polio 3'],
            ['SPM_04_IM_DPT_HB_HIB_3', 'DPT HB-HIB 3'],
            ['SPM_04_IM_POLIO4', 'Polio 4'],
            ['SPM_04_IM_CAMPAK', 'Campak'],
        ] as [$c,$n]) { $upd($i04, $c, $n); }

        $i05 = $mk('SPM_05', 'Pelayanan Kesehatan Anak Usia Pendidikan Dasar');
        foreach ([
            ['SPM_05_PENJARINGAN', 'Penjaringan kesehatan siswa sekolah'],
            ['SPM_05_PEMERIKSAAN_BERKALA', 'Pemeriksaan berkala peserta didik'],
            ['SPM_05_BIAS', 'Cakupan BIAS'],
            ['SPM_05_CACING', 'Cakupan pemberian obat cacing (umum)'],
            ['SPM_05_CACING_6_12', 'Pemberian obat cacing anak 6–12 tahun'],
            ['SPM_05_CACING_1_5', 'Pemberian obat cacing anak 1–5 tahun'],
            ['SPM_05_PHBS', 'PHBS Sekolah'],
        ] as [$c,$n]) { $upd($i05, $c, $n); }

        $i06 = $mk('SPM_06', 'Pelayanan Kesehatan Usia Reproduksi');
        foreach ([
            ['SPM_06_TT_WUS', 'Cakupan pemberian TT WUS'],
            ['SPM_06_FE_WUS', 'Cakupan pemeriksaan FE WUS'],
        ] as [$c,$n]) { $upd($i06, $c, $n); }

        $i07 = $mk('SPM_07', 'Pelayanan Kesehatan Lansia');
        foreach ([
            ['SPM_07_KUNJUNGAN', 'Kunjungan lansia posyandu'],
            ['SPM_07_RISTI', 'Kunjungan lansia risiko tinggi'],
        ] as [$c,$n]) { $upd($i07, $c, $n); }

        $i08 = $mk('SPM_08', 'Pelayanan Kesehatan Hipertensi');
        foreach ([
            ['SPM_08_PANDU_PTM', 'Pelaksanaan PANDU PTM'],
            ['SPM_08_POSBINDU', 'Pelaksanaan Posbindu PTM'],
            ['SPM_08_PENGOBATAN', 'Pemberian pengobatan antihipertensi'],
        ] as [$c,$n]) { $upd($i08, $c, $n); }

        $i09 = $mk('SPM_09', 'Pelayanan Kesehatan DM');
        $upd($i09, 'SPM_09_PENGOBATAN', 'Pemberian pengobatan antidiabetik pada penderita DM');

        $i10 = $mk('SPM_10', 'Pelayanan Kesehatan ODGJ Berat');
        $upd($i10, 'SPM_10_PELAYANAN', 'Cakupan pelayanan ODGJ Berat');

        $i11 = $mk('SPM_11', 'Pelayanan Kesehatan Orang Terduga TB');
        foreach ([
            ['SPM_11_PENEMUAN', 'Cakupan penemuan penderita TB'],
            ['SPM_11_PENGOBATAN', 'Cakupan penanganan penderita sesuai standar WHO'],
            ['SPM_11_KONTAK', 'Cakupan pelacakan kasus kontak'],
        ] as [$c,$n]) { $upd($i11, $c, $n); }

        $i12 = $mk('SPM_12', 'Pelayanan Kesehatan Risiko Terinfeksi HIV');
        foreach ([
            ['SPM_12_INTEGRASI_TB_HIV', 'Cakupan integrasi TB-HIV'],
            ['SPM_12_INTEGRASI_KIA_HIV', 'Cakupan integrasi KIA-HIV'],
            ['SPM_12_INTEGRASI_IMS_HIV', 'Cakupan integrasi IMS-HIV'],
        ] as [$c,$n]) { $upd($i12, $c, $n); }
    }
}
