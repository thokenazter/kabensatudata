<?php

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\Village;
use Illuminate\Console\Command;

class CalculateFamilyHealthIndex extends Command
{
    protected $signature = 'iks:calculate {--village=* : ID desa untuk dihitung (kosongkan untuk semua desa)} {--all : Hitung semua keluarga (termasuk yang sudah dihitung)}';

    protected $description = 'Menghitung Indeks Keluarga Sehat (IKS) untuk semua keluarga';

    public function handle()
    {
        $villageIds = $this->option('village');
        $calculateAll = $this->option('all');

        $query = Family::query();

        // Filter berdasarkan desa jika ada
        if (!empty($villageIds)) {
            $villageNames = Village::whereIn('id', $villageIds)->pluck('name')->toArray();
            $this->info('Menghitung IKS untuk desa: ' . implode(', ', $villageNames));

            $query->whereHas('building', function ($q) use ($villageIds) {
                $q->whereIn('village_id', $villageIds);
            });
        }

        // Jika tidak calculate all, exclude yang sudah dihitung
        if (!$calculateAll) {
            $query->whereDoesntHave('healthIndex', function ($q) {
                $q->whereDate('calculated_at', now()->toDateString());
            });
        }

        $totalFamilies = $query->count();

        if ($totalFamilies === 0) {
            $this->info('Tidak ada keluarga yang perlu dihitung IKS-nya.');
            return 0;
        }

        $this->info("Akan menghitung IKS untuk {$totalFamilies} keluarga...");

        $bar = $this->output->createProgressBar($totalFamilies);
        $bar->start();

        $success = 0;
        $failed = 0;

        $query->chunk(100, function ($families) use (&$success, &$failed, $bar) {
            foreach ($families as $family) {
                try {
                    $iksData = $family->calculateIks();
                    $family->saveIksResult($iksData);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("Error pada keluarga ID:{$family->id}: " . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Selesai menghitung IKS untuk {$totalFamilies} keluarga.");
        $this->info("Berhasil: {$success}, Gagal: {$failed}");

        return 0;
    }
}
