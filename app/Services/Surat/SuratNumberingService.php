<?php

namespace App\Services\Surat;

use App\Models\SuratArchive;
use App\Models\SuratSequence;
use Illuminate\Support\Facades\DB;

class SuratNumberingService
{
    public function getLastNomor(string $jenis): ?string
    {
        return SuratArchive::where('jenis', strtoupper($jenis))
            ->whereNotNull('nomor_surat')
            ->latest('id')
            ->value('nomor_surat');
    }

    public function suggestNomor(string $jenis, ?int $year = null): ?string
    {
        $year = $year ?? (int)date('Y');
        $config = $this->getConfig($jenis);
        $seq = optional(SuratSequence::firstWhere(['jenis' => strtoupper($jenis), 'year' => $year]))->last_seq ?? 0;
        if ($seq <= 0) {
            // fallback from last arsip number
            $last = $this->getLastNomor($jenis);
            if ($last && preg_match('#/(\d+)/(\d{4})#', $last, $m)) {
                $seq = (int)$m[1];
            }
        }
        $next = max(0, $seq) + 1;
        return $this->format($config['format'], $next, $year, $config['padding'] ?? 3);
    }

    public function assignNomor(string $jenis, ?int $year = null): string
    {
        $year = $year ?? (int)date('Y');
        $config = $this->getConfig($jenis);
        return DB::transaction(function () use ($jenis, $year, $config) {
            $row = SuratSequence::lockForUpdate()->firstWhere(['jenis' => strtoupper($jenis), 'year' => $year]);
            if (!$row) {
                $row = SuratSequence::create(['jenis' => strtoupper($jenis), 'year' => $year, 'last_seq' => 0]);
            }
            $row->last_seq++;
            $row->save();
            return $this->format($config['format'], $row->last_seq, $year, $config['padding'] ?? 3);
        });
    }

    protected function getConfig(string $jenis): array
    {
        $cfg = config('surat.numbering.' . strtoupper($jenis));
        if (!$cfg || empty($cfg['format'])) {
            // default format
            $cfg = ['format' => '{SEQ}/{YEAR}', 'padding' => 3];
        }
        return $cfg;
    }

    protected function format(string $format, int $seq, int $year, int $padding = 3): string
    {
        $map = [
            '{SEQ}' => str_pad((string)$seq, $padding, '0', STR_PAD_LEFT),
            '{YEAR}' => (string)$year,
        ];
        return strtr($format, $map);
    }
}

