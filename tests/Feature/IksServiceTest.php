<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\IksService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IksServiceTest extends TestCase
{
    use RefreshDatabase;

    protected IksService $iksService;

    public function setUp(): void
    {
        parent::setUp();
        $this->iksService = new IksService();
    }

    /**
     * Test perhitungan IKS untuk keluarga dengan semua indikator positif
     */
    public function test_calculate_iks_all_indicators_positive()
    {
        // Buat keluarga dengan semua indikator positif
        $family = Family::factory()->create([
            'has_pus' => true,
            'follows_family_planning' => true,
            'has_clean_water' => true,
            'is_water_protected' => true,
            'has_toilet' => true,
            'is_toilet_sanitary' => true,
            'has_mental_illness' => true,
            'takes_medication_regularly' => true,
            'has_restrained_member' => false,
        ]);

        // Tambahkan anggota keluarga dengan kondisi sesuai untuk semua indikator
        $this->createFamilyMembers($family);

        // Hitung IKS
        $result = $this->iksService->calculateIks($family);

        // Verifikasi hasil
        $this->assertEquals(1.0, $result['iks_value']);
        $this->assertEquals(100.0, $result['iks_percentage']);
        $this->assertEquals('Keluarga Sehat', $result['health_status']);
        $this->assertEquals(12, $result['relevant_count']);
        $this->assertEquals(12, $result['positive_count']);
    }

    /**
     * Test perhitungan IKS untuk keluarga dengan beberapa indikator negatif
     */
    public function test_calculate_iks_some_indicators_negative()
    {
        // Buat keluarga dengan beberapa indikator negatif
        $family = Family::factory()->create([
            'has_pus' => true,
            'follows_family_planning' => false, // Negatif
            'has_clean_water' => true,
            'is_water_protected' => false, // Negatif
            'has_toilet' => true,
            'is_toilet_sanitary' => true,
            'has_mental_illness' => true,
            'takes_medication_regularly' => false, // Negatif
            'has_restrained_member' => true, // Negatif
        ]);

        // Tambahkan anggota keluarga dengan beberapa kondisi negatif
        $this->createFamilyMembersWithNegativeIndicators($family);

        // Hitung IKS
        $result = $this->iksService->calculateIks($family);

        // Verifikasi hasil
        $this->assertLessThan(0.8, $result['iks_value']);
        $this->assertGreaterThanOrEqual(0.5, $result['iks_value']);
        $this->assertEquals('Keluarga Pra-Sehat', $result['health_status']);
    }

    /**
     * Test perhitungan IKS untuk keluarga dengan banyak indikator negatif
     */
    public function test_calculate_iks_many_indicators_negative()
    {
        // Buat keluarga dengan banyak indikator negatif
        $family = Family::factory()->create([
            'has_pus' => true,
            'follows_family_planning' => false, // Negatif
            'has_clean_water' => false, // Negatif
            'is_water_protected' => false, // Negatif
            'has_toilet' => false, // Negatif
            'is_toilet_sanitary' => false, // Negatif
            'has_mental_illness' => true,
            'takes_medication_regularly' => false, // Negatif
            'has_restrained_member' => true, // Negatif
        ]);

        // Tambahkan anggota keluarga dengan banyak kondisi negatif
        $this->createFamilyMembersWithManyNegativeIndicators($family);

        // Hitung IKS
        $result = $this->iksService->calculateIks($family);

        // Verifikasi hasil
        $this->assertLessThan(0.5, $result['iks_value']);
        $this->assertEquals('Keluarga Tidak Sehat', $result['health_status']);
    }

    /**
     * Fungsi helper untuk membuat anggota keluarga dengan semua indikator positif
     */
    private function createFamilyMembers(Family $family)
    {
        // Kepala keluarga
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Kepala Keluarga',
            'gender' => 'Laki-laki',
            'age' => 40,
            'marital_status' => 'Kawin',
            'is_smoker' => false,
            'has_jkn' => true,
        ]);

        // Istri
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Istri',
            'gender' => 'Perempuan',
            'age' => 35,
            'marital_status' => 'Kawin',
            'is_smoker' => false,
            'has_jkn' => true,
            'uses_contraception' => true,
        ]);

        // Anak 1 (bayi 6-11 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(8),
            'is_smoker' => false,
            'has_jkn' => true,
            'exclusive_breastfeeding' => true,
            'birth_in_facility' => true,
        ]);

        // Anak 2 (bayi 12-23 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Perempuan',
            'birth_date' => Carbon::now()->subMonths(18),
            'is_smoker' => false,
            'has_jkn' => true,
            'complete_immunization' => true,
            'birth_in_facility' => true,
        ]);

        // Anak 3 (balita)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(36),
            'is_smoker' => false,
            'has_jkn' => true,
            'growth_monitored' => true,
        ]);

        // Anggota dengan TB
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Laki-laki',
            'age' => 50,
            'is_smoker' => false,
            'has_jkn' => true,
            'has_tuberculosis' => true,
            'takes_tb_medication_regularly' => true,
        ]);

        // Anggota dengan hipertensi
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Perempuan',
            'age' => 55,
            'is_smoker' => false,
            'has_jkn' => true,
            'has_hypertension' => true,
            'takes_hypertension_medication_regularly' => true,
        ]);
    }

    /**
     * Fungsi helper untuk membuat anggota keluarga dengan beberapa indikator negatif
     */
    private function createFamilyMembersWithNegativeIndicators(Family $family)
    {
        // Kepala keluarga (merokok)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Kepala Keluarga',
            'gender' => 'Laki-laki',
            'age' => 40,
            'marital_status' => 'Kawin',
            'is_smoker' => true, // Negatif
            'has_jkn' => true,
        ]);

        // Istri
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Istri',
            'gender' => 'Perempuan',
            'age' => 35,
            'marital_status' => 'Kawin',
            'is_smoker' => false,
            'has_jkn' => false, // Negatif
            'uses_contraception' => false, // Negatif
        ]);

        // Anak 1 (bayi 6-11 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(8),
            'is_smoker' => false,
            'has_jkn' => true,
            'exclusive_breastfeeding' => false, // Negatif
            'birth_in_facility' => true,
        ]);

        // Anak 2 (bayi 12-23 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Perempuan',
            'birth_date' => Carbon::now()->subMonths(18),
            'is_smoker' => false,
            'has_jkn' => true,
            'complete_immunization' => true,
            'birth_in_facility' => true,
        ]);

        // Anak 3 (balita)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(36),
            'is_smoker' => false,
            'has_jkn' => true,
            'growth_monitored' => true,
        ]);

        // Anggota dengan TB
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Laki-laki',
            'age' => 50,
            'is_smoker' => false,
            'has_jkn' => true,
            'has_tuberculosis' => true,
            'takes_tb_medication_regularly' => false, // Negatif
        ]);

        // Anggota dengan hipertensi
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Perempuan',
            'age' => 55,
            'is_smoker' => false,
            'has_jkn' => true,
            'has_hypertension' => true,
            'takes_hypertension_medication_regularly' => false, // Negatif
        ]);
    }

    /**
     * Fungsi helper untuk membuat anggota keluarga dengan banyak indikator negatif
     */
    private function createFamilyMembersWithManyNegativeIndicators(Family $family)
    {
        // Kepala keluarga (merokok)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Kepala Keluarga',
            'gender' => 'Laki-laki',
            'age' => 40,
            'marital_status' => 'Kawin',
            'is_smoker' => true, // Negatif
            'has_jkn' => false, // Negatif
        ]);

        // Istri
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Istri',
            'gender' => 'Perempuan',
            'age' => 35,
            'marital_status' => 'Kawin',
            'is_smoker' => true, // Negatif
            'has_jkn' => false, // Negatif
            'uses_contraception' => false, // Negatif
        ]);

        // Anak 1 (bayi 6-11 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(8),
            'is_smoker' => false,
            'has_jkn' => false, // Negatif
            'exclusive_breastfeeding' => false, // Negatif
            'birth_in_facility' => false, // Negatif
        ]);

        // Anak 2 (bayi 12-23 bulan)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Perempuan',
            'birth_date' => Carbon::now()->subMonths(18),
            'is_smoker' => false,
            'has_jkn' => false, // Negatif
            'complete_immunization' => false, // Negatif
            'birth_in_facility' => false, // Negatif
        ]);

        // Anak 3 (balita)
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Anak',
            'gender' => 'Laki-laki',
            'birth_date' => Carbon::now()->subMonths(36),
            'is_smoker' => false,
            'has_jkn' => false, // Negatif
            'growth_monitored' => false, // Negatif
        ]);

        // Anggota dengan TB
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Laki-laki',
            'age' => 50,
            'is_smoker' => true, // Negatif
            'has_jkn' => false, // Negatif
            'has_tuberculosis' => true,
            'takes_tb_medication_regularly' => false, // Negatif
        ]);

        // Anggota dengan hipertensi
        FamilyMember::factory()->create([
            'family_id' => $family->id,
            'relationship' => 'Saudara',
            'gender' => 'Perempuan',
            'age' => 55,
            'is_smoker' => true, // Negatif
            'has_jkn' => false, // Negatif
            'has_hypertension' => true,
            'takes_hypertension_medication_regularly' => false, // Negatif
        ]);
    }
}
