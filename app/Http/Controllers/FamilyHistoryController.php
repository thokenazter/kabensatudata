<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyHealthIndexHistory;
use App\Services\IksRecommendationService;
use App\Services\IksPredictionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyHistoryController extends Controller
{
    protected $iksRecommendationService;
    protected $iksPredictionService;

    public function __construct(
        IksRecommendationService $iksRecommendationService,
        IksPredictionService $iksPredictionService
    ) {
        $this->iksRecommendationService = $iksRecommendationService;
        $this->iksPredictionService = $iksPredictionService;
    }

    /**
     * Menampilkan dashboard riwayat IKS keluarga
     */
    public function index(Family $family)
    {
        // Muat relasi yang diperlukan
        $family->load(['healthIndex', 'healthIndexHistories', 'recommendations', 'members']);

        // Dapatkan riwayat IKS terurut berdasarkan waktu - dari terbaru ke tertua
        $histories = $family->healthIndexHistories()->orderBy('calculated_at', 'desc')->get();

        // Dapatkan rekomendasi yang aktif
        $activeRecommendations = $family->recommendations()
            ->whereNotIn('status', ['completed', 'rejected'])
            ->orderBy('priority_score', 'desc')
            ->get();

        // Dapatkan prediksi perubahan IKS jika semua rekomendasi diimplementasikan
        $predictions = [];
        if ($activeRecommendations->isNotEmpty()) {
            $predictions = $this->iksPredictionService->predictIksChange($family);
        }

        // Siapkan data untuk chart - untuk chart tetap urutan kronologis (terlama ke terbaru)
        $chartHistories = $family->healthIndexHistories()->orderBy('calculated_at', 'asc')->get();
        $chartData = [
            'labels' => $chartHistories->pluck('calculated_at')->map(function ($date) {
                return $date->format('d-m-Y');
            })->toArray(),
            'values' => $chartHistories->pluck('iks_value')->map(function ($value) {
                return $value * 100;
            })->toArray(),
            'status' => $chartHistories->pluck('health_status')->toArray(),
        ];

        // Tambahkan prediksi ke chart jika ada
        if (!empty($predictions) && isset($predictions['predicted_iks_percentage'])) {
            $chartData['labels'][] = 'Prediksi';
            $chartData['values'][] = $predictions['predicted_iks_percentage'];
            $chartData['status'][] = $predictions['predicted_status'];
        }

        // Indikator untuk tabel perbandingan
        $indicators = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Faskes',
            'immunization' => 'Imunisasi Dasar',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        // Data indikator dari riwayat terakhir dan pertama untuk perbandingan
        // Dibalik: first adalah yang terbaru, last adalah yang tertua
        $firstHistory = $histories->first(); // Data terbaru
        $lastHistory = $histories->last();   // Data tertua

        $indicatorComparison = [];

        foreach ($indicators as $code => $name) {
            // Perbaikan kunci: Masalah utama terletak pada definisi logika improved/declined
            // Kondisi membaik: jika dulu tidak terpenuhi (last/awal), sekarang terpenuhi (first/terkini)
            $improved = false;
            $declined = false;

            if ($firstHistory && $lastHistory) {
                if (!$lastHistory->{$code . '_status'} && $firstHistory->{$code . '_status'}) {
                    $improved = true;
                } elseif ($lastHistory->{$code . '_status'} && !$firstHistory->{$code . '_status'}) {
                    $declined = true;
                }
            }

            $indicatorComparison[$code] = [
                'name' => $name,
                'first' => [
                    'relevant' => $firstHistory ? $firstHistory->{$code . '_relevant'} : false,
                    'status' => $firstHistory ? $firstHistory->{$code . '_status'} : false,
                    'detail' => $firstHistory ? $firstHistory->{$code . '_detail'} : '',
                    'date' => $firstHistory ? $firstHistory->calculated_at->format('d-m-Y') : '-',
                ],
                'last' => [
                    'relevant' => $lastHistory ? $lastHistory->{$code . '_relevant'} : false,
                    'status' => $lastHistory ? $lastHistory->{$code . '_status'} : false,
                    'detail' => $lastHistory ? $lastHistory->{$code . '_detail'} : '',
                    'date' => $lastHistory ? $lastHistory->calculated_at->format('d-m-Y') : '-',
                ],
                'improved' => $improved,
                'declined' => $declined,
            ];
        }

        return view('families.history', compact(
            'family',
            'histories',
            'chartData',
            'activeRecommendations',
            'predictions',
            'indicatorComparison'
        ));
    }

    /**
     * Menampilkan detail satu riwayat IKS
     */
    public function show(Family $family, FamilyHealthIndexHistory $history)
    {
        // Muat relasi yang diperlukan
        $family->load(['healthIndex', 'members']);

        // Indikator untuk detail
        $indicators = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Faskes',
            'immunization' => 'Imunisasi Dasar',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        // Persiapkan detail indikator
        $indicatorDetails = [];

        foreach ($indicators as $code => $name) {
            $indicatorDetails[$code] = [
                'name' => $name,
                'relevant' => $history->{$code . '_relevant'},
                'status' => $history->{$code . '_status'},
                'detail' => $history->{$code . '_detail'},
            ];
        }

        // Cari riwayat sebelumnya untuk perbandingan
        $previousHistory = FamilyHealthIndexHistory::where('family_id', $family->id)
            ->where('calculated_at', '<', $history->calculated_at)
            ->orderBy('calculated_at', 'desc')
            ->first();

        // Siapkan data perubahan jika ada riwayat sebelumnya
        $changes = null;

        if ($previousHistory) {
            $improvements = [];
            $declines = [];

            foreach ($indicators as $code => $name) {
                // Cek apakah indikator relevan di kedua riwayat
                if ($previousHistory->{$code . '_relevant'} && $history->{$code . '_relevant'}) {
                    // Cek perubahan status
                    if (!$previousHistory->{$code . '_status'} && $history->{$code . '_status'}) {
                        // Indikator membaik
                        $improvements[] = [
                            'code' => $code,
                            'name' => $name,
                            'detail' => $history->{$code . '_detail'},
                        ];
                    } elseif ($previousHistory->{$code . '_status'} && !$history->{$code . '_status'}) {
                        // Indikator memburuk
                        $declines[] = [
                            'code' => $code,
                            'name' => $name,
                            'detail' => $history->{$code . '_detail'},
                        ];
                    }
                }
            }

            $changes = [
                'previous_iks' => $previousHistory->iks_value,
                'current_iks' => $history->iks_value,
                'net_change' => $history->iks_value - $previousHistory->iks_value,
                'improvements' => $improvements,
                'declines' => $declines,
                'previous_date' => $previousHistory->calculated_at->format('d-m-Y'),
            ];
        }

        return view('families.history-detail', compact(
            'family',
            'history',
            'indicatorDetails',
            'changes'
        ));
    }

    /**
     * Menampilkan prediksi IKS berdasarkan rekomendasi yang dipilih
     */
    public function predictIks(Request $request, Family $family)
    {
        $request->validate([
            'recommendation_ids' => 'nullable|array',
            'recommendation_ids.*' => 'exists:iks_recommendations,id',
        ]);

        $recommendationIds = $request->input('recommendation_ids', []);

        // Dapatkan prediksi perubahan IKS
        $predictions = $this->iksPredictionService->predictIksChange($family, $recommendationIds);

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Membuat rekomendasi otomatis untuk keluarga
     */
    public function generateRecommendations(Family $family)
    {
        // Buat rekomendasi otomatis
        $recommendations = $this->iksRecommendationService->generateRecommendations($family);

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations_count' => count($recommendations),
                'recommendations' => $recommendations,
            ],
        ]);
    }
}
