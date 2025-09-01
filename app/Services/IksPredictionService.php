<?php

namespace App\Services;

use App\Models\Family;
use App\Models\IksRecommendation;

class IksPredictionService
{
    protected $iksService;

    /**
     * Konstruktor Service
     */
    public function __construct(IksService $iksService)
    {
        $this->iksService = $iksService;
    }

    /**
     * Memprediksi perubahan IKS berdasarkan intervensi yang direncanakan
     * 
     * @param Family $family
     * @param array|null $selectedRecommendations Array ID rekomendasi yang dipilih atau null untuk semua
     * @return array Data prediksi
     */
    public function predictIksChange(Family $family, ?array $selectedRecommendations = null): array
    {
        // Dapatkan nilai IKS saat ini
        $currentIks = $family->healthIndex->iks_value ?? 0;
        $currentStatus = $family->healthIndex->health_status ?? 'Belum dihitung';

        // Dapatkan rekomendasi yang relevan
        $recommendations = $selectedRecommendations
            ? IksRecommendation::whereIn('id', $selectedRecommendations)->get()
            : $family->recommendations()->whereNotIn('status', ['completed', 'rejected'])->get();

        if ($recommendations->isEmpty()) {
            return [
                'current_iks' => $currentIks,
                'current_iks_percentage' => $currentIks * 100,
                'current_status' => $currentStatus,
                'predicted_iks' => $currentIks,
                'predicted_iks_percentage' => $currentIks * 100,
                'predicted_status' => $currentStatus,
                'improvement' => 0,
                'improvement_percentage' => 0,
                'recommendations' => [],
                'intervention_effects' => [],
            ];
        }

        // Hitung potensi dampak dari setiap rekomendasi
        $interventionEffects = [];
        $totalPredictedImprovement = 0;

        foreach ($recommendations as $recommendation) {
            $predictedImpact = $this->calculatePredictedImpact($recommendation, $family);

            $interventionEffects[] = [
                'id' => $recommendation->id,
                'title' => $recommendation->title,
                'indicator_code' => $recommendation->indicator_code,
                'indicator_name' => $this->getIndicatorName($recommendation->indicator_code),
                'predicted_impact' => $predictedImpact,
                'predicted_impact_percentage' => $predictedImpact * 100,
                'confidence_level' => $this->calculateConfidenceLevel($recommendation, $family),
            ];

            $totalPredictedImprovement += $predictedImpact;
        }

        // Batasi nilai IKS maksimal 1.0
        $predictedIks = min(1.0, $currentIks + $totalPredictedImprovement);
        $predictedStatus = $this->determineHealthStatus($predictedIks);

        return [
            'current_iks' => $currentIks,
            'current_iks_percentage' => $currentIks * 100,
            'current_status' => $currentStatus,
            'predicted_iks' => $predictedIks,
            'predicted_iks_percentage' => $predictedIks * 100,
            'predicted_status' => $predictedStatus,
            'improvement' => $predictedIks - $currentIks,
            'improvement_percentage' => ($predictedIks - $currentIks) * 100,
            'recommendations' => $recommendations,
            'intervention_effects' => $interventionEffects,
        ];
    }

    /**
     * Hitung dampak prediksi dari suatu rekomendasi
     */
    private function calculatePredictedImpact(IksRecommendation $recommendation, Family $family): float
    {
        // Jumlah indikator total (12 indikator)
        $totalIndicators = 12;

        // Dapatkan data indikator saat ini
        $iksData = $this->iksService->calculateIks($family);
        $relevantCount = $iksData['relevant_count'] > 0 ? $iksData['relevant_count'] : $totalIndicators;

        // Dampak dasar dari satu indikator (berdasarkan bobot rata-rata)
        $baseImpact = 1.0 / $relevantCount;

        // Faktor efektivitas berdasarkan jenis indikator
        $effectivenessFactor = $this->getEffectivenessFactor($recommendation->indicator_code);

        // Faktor kompleksitas berdasarkan tingkat kesulitan
        $complexityFactor = $this->getComplexityFactor($recommendation->difficulty_level);

        // Faktor urgensi berdasarkan skor prioritas
        $urgencyFactor = $this->getUrgencyFactor($recommendation->priority_score);

        // Hitung dampak total
        $predictedImpact = $baseImpact * $effectivenessFactor * $complexityFactor * $urgencyFactor;

        // Batasi dampak maksimal
        return min(0.2, $predictedImpact);
    }

    /**
     * Mendapatkan faktor efektivitas berdasarkan jenis indikator
     */
    private function getEffectivenessFactor(string $indicatorCode): float
    {
        return match ($indicatorCode) {
            'jkn_membership' => 1.0,  // Relatif mudah dicapai
            'clean_water', 'sanitary_toilet' => 0.7,  // Membutuhkan infrastruktur
            'tb_treatment', 'hypertension_treatment', 'mental_treatment' => 0.8,  // Membutuhkan kepatuhan jangka panjang
            'no_smoking' => 0.6,  // Sulit mengubah kebiasaan
            default => 0.9,
        };
    }

    /**
     * Mendapatkan faktor kompleksitas berdasarkan tingkat kesulitan
     */
    private function getComplexityFactor(string $difficultyLevel): float
    {
        return match ($difficultyLevel) {
            'Easy' => 1.0,
            'Medium' => 0.8,
            'Hard' => 0.6,
            default => 0.8,
        };
    }

    /**
     * Mendapatkan faktor urgensi berdasarkan skor prioritas
     */
    private function getUrgencyFactor(float $priorityScore): float
    {
        if ($priorityScore >= 8) {
            return 1.0; // Prioritas tinggi
        } elseif ($priorityScore >= 5) {
            return 0.9; // Prioritas sedang
        } else {
            return 0.8; // Prioritas rendah
        }
    }

    /**
     * Menghitung tingkat kepercayaan prediksi
     */
    private function calculateConfidenceLevel(IksRecommendation $recommendation, Family $family): string
    {
        // Dapatkan riwayat intervensi serupa
        $similarInterventions = IksRecommendation::where('indicator_code', $recommendation->indicator_code)
            ->where('status', 'completed')
            ->count();

        // Tingkat kepercayaan berdasarkan data historis
        if ($similarInterventions > 10) {
            return 'High'; // Banyak data sebelumnya
        } elseif ($similarInterventions > 3) {
            return 'Medium'; // Cukup data
        } else {
            return 'Low'; // Sedikit atau tidak ada data sebelumnya
        }
    }

    /**
     * Mendapatkan nama indikator dari kode
     */
    private function getIndicatorName(string $code): string
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
}
