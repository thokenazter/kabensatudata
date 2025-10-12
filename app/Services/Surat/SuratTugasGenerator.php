<?php

namespace App\Services\Surat;

use App\Models\Pegawai;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SuratTugasGenerator
{
    public function generate(Pegawai $pegawai, array $data): string
    {
        $templatePath = storage_path('app/templates/SuratTugas.xlsx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template SuratTugas.xlsx tidak ditemukan di storage/app/templates');
        }

        $spreadsheet = IOFactory::load($templatePath);

        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);
        $tanggalSurat = Carbon::parse($data['tanggal_surat']);

        $hari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $lamaTugas = $this->terbilang($hari) . ' ( ' . $hari . ' )';
        $tanggalRange = $tanggalMulai->format('j') . ' s/d ' . $this->formatTanggalIndo($tanggalSelesai);

        $tokenMap = [
            '{NAMA}' => (string) $pegawai->nama,
            '{NIP}' => (string) ($pegawai->nip ?? ''),
            '{PANGKAT_GOL}' => (string) ($pegawai->pangkat_gol ?? ''),
            '{JABATAN}' => (string) ($pegawai->jabatan ?? ''),
            '{DASAR1}' => (string) ($data['dasar1'] ?? ''),
            '{MAKSUD_TUGAS}' => (string) ($data['maksud_tugas'] ?? ''),
            '{LAMA_TUGAS}' => $lamaTugas,
            '{TANGGAL_RANGE}' => $tanggalRange,
            '{TANGGAL_SURAT}' => $this->formatTanggalIndo($tanggalSurat),
            '{KOTA_TERBIT}' => (string) ($data['kota_terbit'] ?? 'Dobo'),
            '{NOMOR_SURAT}' => (string) ($data['nomor_surat'] ?? ''),

            // Fallback sesuai contoh README (isi persis di dalam kurung kurawal)
            '{Thobias Edwin Dasmaselah, S.KM}' => (string) $pegawai->nama,
            '{19950612 202421 1 005}' => (string) ($pegawai->nip ?? ''),
            '{IX}' => (string) ($pegawai->pangkat_gol ?? ''),
            '{Administrator Kesehatan}' => (string) ($pegawai->jabatan ?? ''),
            '{Mengikuti Kegiatan Workshop Perencanaan Kebutuhan Sumber Daya Manusia Kesehatan}' => (string) ($data['maksud_tugas'] ?? ''),
            '{Dua ( 2 )}' => $lamaTugas,
            '{18 s/d 19 September 2025}' => $tanggalRange,
            '{17 September 2025}' => $this->formatTanggalIndo($tanggalSurat),
            '{Berdasarkan Surat Kepala Dinas Kesehatan Kabupaten Kepulauan Aru tertanggal 15 September 2025 nomor : 800/1421}' => (string) ($data['dasar1'] ?? ''),
        ];

        $this->replaceTokens($spreadsheet, $tokenMap);

        $filename = 'SuratTugas-' . now()->format('Ymd-His') . '-' . preg_replace('/\s+/', '', $pegawai->nip ?? 'nonip') . '.xlsx';
        $savePath = storage_path('app/public/surat/' . $filename);
        @mkdir(dirname($savePath), 0775, true);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savePath);

        return 'surat/' . $filename; // relative to public disk
    }

    protected function replaceTokens(Spreadsheet $spreadsheet, array $map): void
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $highestRow = $sheet->getHighestRow();
            $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $value = $cell->getValue();
                    if (is_string($value) && str_contains($value, '{')) {
                        $cell->setValue(strtr($value, $map));
                    }
                }
            }
        }
    }

    protected function formatTanggalIndo(Carbon $date): string
    {
        $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return $date->format('j') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y');
    }

    protected function terbilang(int $number): string
    {
        $angka = [0=>'Nol','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas'];
        if ($number < 12) return $angka[$number];
        if ($number < 20) return $angka[$number-10] . ' Belas';
        if ($number < 100) {
            $puluh = intdiv($number, 10);
            $sisa = $number % 10;
            return trim($angka[$puluh] . ' Puluh ' . ($sisa ? $angka[$sisa] : ''));
        }
        if ($number < 200) return 'Seratus ' . $this->terbilang($number-100);
        if ($number < 1000) {
            $ratus = intdiv($number, 100);
            $sisa = $number % 100;
            return trim(($ratus==1?'Seratus':$angka[$ratus].' Ratus') . ' ' . ($sisa? $this->terbilang($sisa):''));
        }
        return (string)$number;
    }
}
