<?php

namespace App\Services;

use App\Models\Family;
use App\Models\IksRecommendation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class IksRecommendationService
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
     * Menghasilkan rekomendasi untuk meningkatkan IKS sebuah keluarga
     * 
     * @param Family $family
     * @return array Daftar rekomendasi yang dibuat
     */
    public function generateRecommendations(Family $family): array
    {
        // Hitung IKS saat ini (jangan simpan)
        $iksData = $this->iksService->calculateIks($family);

        $recommendations = [];
        $createdRecommendations = [];

        // Analisis indikator yang tidak terpenuhi
        foreach ($iksData['indicators'] as $code => $indicator) {
            if ($indicator['relevant'] && $indicator['value'] == 0) {
                // Indikator relevan tapi tidak terpenuhi
                $priorityScore = $this->calculatePriorityScore($family, $code);

                $recommendation = $this->createRecommendation(
                    $family,
                    $code,
                    $priorityScore
                );

                $recommendations[] = $recommendation;

                // Simpan ke database
                $createdRecommendations[] = IksRecommendation::create([
                    'family_id' => $family->id,
                    'user_id' => auth()->id(),
                    'indicator_code' => $code,
                    'recommendation_type' => $recommendation['type'],
                    'title' => $recommendation['title'],
                    'description' => $recommendation['description'],
                    'priority_score' => $recommendation['priority_score'],
                    'priority_level' => $recommendation['priority_level'],
                    'actions' => $recommendation['actions'],
                    'resources' => $recommendation['resources'],
                    'expected_days_to_complete' => $recommendation['expected_days'],
                    'difficulty_level' => $recommendation['difficulty_level'],
                    'status' => 'pending',
                    'target_date' => Carbon::now()->addDays($recommendation['expected_days']),
                ]);
            }
        }

        return $createdRecommendations;
    }

    /**
     * Menghitung skor prioritas berdasarkan indikator dan karakteristik keluarga
     */
    private function calculatePriorityScore(Family $family, string $code): float
    {
        // Skor dasar
        $baseScore = $this->getBaseScore($code);

        // Faktor berdasarkan karakteristik keluarga
        $familyFactor = $this->getFamilyFactor($family, $code);

        // Faktor urgensi berdasarkan indikator
        $urgencyFactor = $this->getUrgencyFactor($code);

        // Skor final (semakin tinggi semakin prioritas)
        return $baseScore * $familyFactor * $urgencyFactor;
    }

    /**
     * Mendapatkan skor dasar berdasarkan jenis indikator
     */
    private function getBaseScore(string $code): float
    {
        return match ($code) {
            'tb_treatment' => 9.5,  // Sangat penting untuk kesehatan publik
            'mental_treatment' => 9.0,
            'hypertension_treatment' => 8.5,
            'birth_facility' => 8.0,
            'immunization' => 7.5,
            'growth_monitoring' => 7.0,
            'exclusive_breastfeeding' => 6.5,
            'clean_water' => 6.0,
            'sanitary_toilet' => 5.5,
            'jkn_membership' => 5.0,
            'no_smoking' => 4.5,
            'kb' => 4.0,
            default => 5.0,
        };
    }

    /**
     * Mendapatkan faktor berdasarkan karakteristik keluarga
     */
    private function getFamilyFactor(Family $family, string $code): float
    {
        $factor = 1.0;

        // Hubungkan dengan indikator spesifik
        switch ($code) {
            case 'tb_treatment':
                // Jika ada balita dalam keluarga, prioritaskan TB
                $hasToddler = $family->members()->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 5')->exists();
                if ($hasToddler) {
                    $factor *= 1.5;
                }
                break;

            case 'jkn_membership':
                // Jika keluarga besar, JKN lebih penting
                $memberCount = $family->members()->count();
                if ($memberCount > 5) {
                    $factor *= 1.3;
                }
                break;

            case 'clean_water':
            case 'sanitary_toilet':
                // Jika ada bayi, prioritaskan sanitasi
                $hasInfant = $family->members()->whereRaw('TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) < 12')->exists();
                if ($hasInfant) {
                    $factor *= 1.4;
                }
                break;

                // Tambahkan faktor lain sesuai kebutuhan
        }

        return $factor;
    }

    /**
     * Mendapatkan faktor urgensi berdasarkan indikator
     */
    private function getUrgencyFactor(string $code): float
    {
        // Beberapa indikator lebih mendesak dibanding lainnya
        return match ($code) {
            'tb_treatment', 'mental_treatment', 'hypertension_treatment' => 1.5,  // Penyakit memerlukan penanganan segera
            'birth_facility', 'immunization', 'exclusive_breastfeeding' => 1.3,  // Penting untuk kesehatan ibu dan anak
            'clean_water', 'sanitary_toilet' => 1.2,  // Dasar untuk kesehatan lingkungan
            default => 1.0,
        };
    }

    /**
     * Membuat objek rekomendasi
     */
    private function createRecommendation(Family $family, string $code, float $priorityScore): array
    {
        $template = $this->getRecommendationTemplate($code);

        // Tentukan level prioritas
        $priorityLevel = 'Low';
        if ($priorityScore >= 10) {
            $priorityLevel = 'High';
        } elseif ($priorityScore >= 5) {
            $priorityLevel = 'Medium';
        }

        // Sesuaikan template dengan keluarga spesifik
        $customizedTitle = $this->customizeText($template['title'], $family, $code);
        $customizedDescription = $this->customizeText($template['description'], $family, $code);

        return [
            'type' => $template['type'],
            'indicator_code' => $code,
            'indicator_name' => $this->getIndicatorName($code),
            'title' => $customizedTitle,
            'description' => $customizedDescription,
            'priority_score' => $priorityScore,
            'priority_level' => $priorityLevel,
            'actions' => $template['actions'],
            'resources' => $template['resources'],
            'expected_days' => $template['expected_days'],
            'difficulty_level' => $template['difficulty_level'],
        ];
    }

    /**
     * Mendapatkan template rekomendasi berdasarkan indikator
     */
    private function getRecommendationTemplate(string $code): array
    {
        $templates = [
            'kb' => [
                'type' => 'education',
                'title' => 'Ikuti Program Keluarga Berencana (KB)',
                'description' => 'Keluarga ini perlu mendapatkan informasi dan akses ke program Keluarga Berencana (KB) untuk meningkatkan kesejahteraan keluarga.',
                'actions' => [
                    'Kunjungi Puskesmas atau Posyandu terdekat untuk konsultasi KB',
                    'Pilih metode kontrasepsi yang sesuai dengan kebutuhan keluarga',
                    'Daftarkan diri dalam program KB',
                ],
                'resources' => [
                    'Puskesmas atau Posyandu terdekat',
                    'Bidan desa',
                    'Materi edukasi KB',
                ],
                'expected_days' => 30,
                'difficulty_level' => 'Medium',
            ],

            'birth_facility' => [
                'type' => 'education',
                'title' => 'Persalinan di Fasilitas Kesehatan',
                'description' => 'Pastikan persalinan dilakukan di fasilitas kesehatan yang memadai untuk menjamin keselamatan ibu dan bayi.',
                'actions' => [
                    'Konsultasikan rencana persalinan dengan bidan atau dokter',
                    'Siapkan dokumen untuk persalinan di fasilitas kesehatan',
                    'Rencanakan transportasi ke fasilitas kesehatan saat waktu persalinan',
                ],
                'resources' => [
                    'Puskesmas atau rumah sakit terdekat',
                    'Kartu JKN untuk biaya persalinan',
                    'Kontak bidan desa',
                ],
                'expected_days' => 60,
                'difficulty_level' => 'Medium',
            ],

            'immunization' => [
                'type' => 'intervention',
                'title' => 'Lengkapi Imunisasi Dasar Bayi',
                'description' => 'Pastikan bayi mendapatkan imunisasi dasar lengkap untuk melindungi dari penyakit berbahaya.',
                'actions' => [
                    'Kunjungi Posyandu atau Puskesmas untuk imunisasi',
                    'Bawa buku KIA saat kunjungan imunisasi',
                    'Ikuti jadwal imunisasi yang ditetapkan',
                ],
                'resources' => [
                    'Posyandu terdekat',
                    'Puskesmas',
                    'Buku KIA',
                ],
                'expected_days' => 90,
                'difficulty_level' => 'Easy',
            ],

            'exclusive_breastfeeding' => [
                'type' => 'education',
                'title' => 'Berikan ASI Eksklusif',
                'description' => 'Pastikan bayi mendapatkan ASI eksklusif selama 6 bulan pertama untuk tumbuh kembang optimal.',
                'actions' => [
                    'Konsultasi dengan bidan atau konselor ASI',
                    'Ikuti kelas ibu tentang pemberian ASI',
                    'Praktikkan teknik menyusui yang benar',
                ],
                'resources' => [
                    'Konselor ASI',
                    'Posyandu terdekat',
                    'Materi edukasi ASI eksklusif',
                ],
                'expected_days' => 30,
                'difficulty_level' => 'Medium',
            ],

            'growth_monitoring' => [
                'type' => 'monitoring',
                'title' => 'Pantau Pertumbuhan Balita',
                'description' => 'Lakukan pemantauan pertumbuhan balita secara rutin untuk memastikan tumbuh kembang yang optimal.',
                'actions' => [
                    'Bawa balita ke Posyandu setiap bulan',
                    'Catat perkembangan berat dan tinggi badan',
                    'Ikuti anjuran petugas kesehatan terkait nutrisi',
                ],
                'resources' => [
                    'Posyandu terdekat',
                    'Buku KIA',
                    'Petugas gizi Puskesmas',
                ],
                'expected_days' => 30,
                'difficulty_level' => 'Easy',
            ],

            'tb_treatment' => [
                'type' => 'treatment',
                'title' => 'Jalani Pengobatan TB Secara Teratur',
                'description' => 'Penting untuk menjalani pengobatan TB secara teratur dan lengkap hingga sembuh untuk mencegah resistensi obat.',
                'actions' => [
                    'Konsultasi dengan dokter di Puskesmas',
                    'Ambil dan minum obat TB sesuai jadwal',
                    'Lakukan pemeriksaan dahak secara berkala',
                    'Hadiri kontrol rutin selama pengobatan',
                ],
                'resources' => [
                    'Puskesmas',
                    'Pengawas Minum Obat (PMO)',
                    'Petugas TB Puskesmas',
                ],
                'expected_days' => 180,
                'difficulty_level' => 'Hard',
            ],

            'hypertension_treatment' => [
                'type' => 'treatment',
                'title' => 'Jalani Pengobatan Hipertensi Secara Teratur',
                'description' => 'Pengobatan hipertensi harus dijalani secara teratur untuk mencegah komplikasi seperti stroke dan serangan jantung.',
                'actions' => [
                    'Konsultasi dengan dokter di Puskesmas',
                    'Minum obat hipertensi secara teratur',
                    'Ukur tekanan darah secara rutin',
                    'Terapkan pola hidup sehat (diet rendah garam, olahraga)',
                ],
                'resources' => [
                    'Puskesmas',
                    'Posbindu PTM',
                    'Alat ukur tekanan darah',
                ],
                'expected_days' => 90,
                'difficulty_level' => 'Medium',
            ],

            'mental_treatment' => [
                'type' => 'treatment',
                'title' => 'Jalani Pengobatan Gangguan Jiwa Secara Teratur',
                'description' => 'Pengobatan gangguan jiwa memerlukan pendampingan dan kepatuhan dalam mengonsumsi obat sesuai anjuran.',
                'actions' => [
                    'Konsultasi dengan dokter/psikiater',
                    'Minum obat gangguan jiwa secara teratur',
                    'Ikuti sesi terapi yang dianjurkan',
                    'Berikan dukungan keluarga untuk penderita',
                ],
                'resources' => [
                    'Puskesmas dengan layanan kesehatan jiwa',
                    'Rumah Sakit Jiwa terdekat',
                    'Kelompok dukungan kesehatan jiwa',
                ],
                'expected_days' => 180,
                'difficulty_level' => 'Hard',
            ],

            'no_smoking' => [
                'type' => 'lifestyle',
                'title' => 'Berhenti Merokok',
                'description' => 'Menghentikan kebiasaan merokok sangat penting untuk kesehatan keluarga dan mencegah berbagai penyakit kronis.',
                'actions' => [
                    'Ikuti konseling berhenti merokok di Puskesmas',
                    'Tetapkan tanggal untuk berhenti merokok',
                    'Hindari situasi yang memicu keinginan merokok',
                    'Gunakan pengganti nikotin jika diresepkan',
                ],
                'resources' => [
                    'Puskesmas',
                    'Klinik berhenti merokok',
                    'Materi edukasi bahaya rokok',
                ],
                'expected_days' => 90,
                'difficulty_level' => 'Hard',
            ],

            'jkn_membership' => [
                'type' => 'administration',
                'title' => 'Daftarkan Keluarga dalam JKN',
                'description' => 'Kepesertaan JKN penting untuk mendapatkan akses layanan kesehatan yang terjangkau dan berkualitas.',
                'actions' => [
                    'Siapkan dokumen (KTP, KK, foto)',
                    'Daftar di kantor BPJS Kesehatan atau online',
                    'Bayar iuran pertama',
                    'Aktivasi kartu JKN',
                ],
                'resources' => [
                    'Kantor BPJS Kesehatan',
                    'Website/aplikasi Mobile JKN',
                    'Petugas BPJS di Puskesmas',
                ],
                'expected_days' => 30,
                'difficulty_level' => 'Medium',
            ],

            'clean_water' => [
                'type' => 'infrastructure',
                'title' => 'Akses Air Bersih yang Terlindungi',
                'description' => 'Keluarga harus memiliki akses ke sumber air bersih yang terlindungi untuk mencegah penyakit bawaan air.',
                'actions' => [
                    'Identifikasi sumber air bersih yang tersedia',
                    'Lindungi sumber air dari kontaminasi',
                    'Lakukan pengolahan air sederhana jika diperlukan',
                    'Simpan air di wadah tertutup dan bersih',
                ],
                'resources' => [
                    'Sanitarian Puskesmas',
                    'Program PAMSIMAS jika tersedia',
                    'Materi edukasi pengelolaan air bersih',
                ],
                'expected_days' => 60,
                'difficulty_level' => 'Medium',
            ],

            'sanitary_toilet' => [
                'type' => 'infrastructure',
                'title' => 'Gunakan Jamban Sehat',
                'description' => 'Jamban sehat penting untuk mencegah penyebaran penyakit dan menjaga lingkungan tetap bersih.',
                'actions' => [
                    'Identifikasi jenis jamban yang sesuai',
                    'Bangun atau perbaiki jamban sesuai standar',
                    'Pastikan jamban memiliki septic tank',
                    'Jaga kebersihan jamban secara rutin',
                ],
                'resources' => [
                    'Sanitarian Puskesmas',
                    'Program sanitasi di desa',
                    'Tukang bangunan setempat',
                ],
                'expected_days' => 90,
                'difficulty_level' => 'Hard',
            ],
        ];

        return $templates[$code] ?? [
            'type' => 'general',
            'title' => 'Tingkatkan Indikator ' . $this->getIndicatorName($code),
            'description' => 'Keluarga perlu meningkatkan indikator ' . $this->getIndicatorName($code) . ' untuk meningkatkan Indeks Keluarga Sehat.',
            'actions' => [
                'Konsultasi dengan petugas kesehatan',
                'Ikuti anjuran yang diberikan',
            ],
            'resources' => [
                'Puskesmas terdekat',
                'Posyandu',
            ],
            'expected_days' => 60,
            'difficulty_level' => 'Medium',
        ];
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
     * Menyesuaikan teks rekomendasi dengan keluarga
     */
    private function customizeText(string $text, Family $family, string $code): string
    {
        $headName = $family->head_name;

        // Sesuaikan teks berdasarkan indikator
        switch ($code) {
            case 'tb_treatment':
                $tbMembers = $family->members()->where('has_tuberculosis', true)->get();
                if ($tbMembers->count() > 0) {
                    $names = $tbMembers->pluck('name')->implode(', ');
                    $text = str_replace('Jalani Pengobatan TB', "Pengobatan TB untuk $names", $text);
                }
                break;

            case 'hypertension_treatment':
                $hyperMembers = $family->members()->where('has_hypertension', true)->get();
                if ($hyperMembers->count() > 0) {
                    $names = $hyperMembers->pluck('name')->implode(', ');
                    $text = str_replace('Jalani Pengobatan Hipertensi', "Pengobatan Hipertensi untuk $names", $text);
                }
                break;

            case 'growth_monitoring':
                $toddlers = $family->members()->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 5')->get();
                if ($toddlers->count() > 0) {
                    $names = $toddlers->pluck('name')->implode(', ');
                    $text = str_replace('Pantau Pertumbuhan Balita', "Pantau Pertumbuhan $names", $text);
                }
                break;
        }

        // Tambahkan nama kepala keluarga jika relevan
        if (Str::contains($text, 'Keluarga')) {
            $text = str_replace('Keluarga ini', "Keluarga $headName", $text);
            $text = str_replace('keluarga perlu', "keluarga $headName perlu", $text);
        }

        return $text;
    }
}
