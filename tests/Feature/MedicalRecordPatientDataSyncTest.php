<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MedicalRecordPatientDataSyncTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $village;
    protected $building;
    protected $family;
    protected $familyMember;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        
        $this->village = Village::create([
            'name' => 'Test Village',
            'code' => '123456',
            'sequence_number' => 1,
            'district' => 'Test District',
            'regency' => 'Test Regency',
            'province' => 'Test Province'
        ]);

        $this->building = Building::create([
            'building_number' => 'B-001',
            'village_id' => $this->village->id,
            'address' => 'Jl. Test No. 123',
            'latitude' => -6.2088,
            'longitude' => 106.8456
        ]);

        $this->family = Family::create([
            'building_id' => $this->building->id,
            'family_number' => 'F-001',
            'sequence_number_in_building' => 1,
            'head_name' => 'Test Head'
        ]);

        $this->familyMember = FamilyMember::create([
            'family_id' => $this->family->id,
            'name' => 'John Doe',
            'nik' => '1234567890123456',
            'rm_number' => 'RM-001',
            'sequence_number_in_family' => 1,
            'relationship' => 'Kepala Keluarga',
            'birth_place' => 'Jakarta',
            'birth_date' => Carbon::parse('1990-01-01'),
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'occupation' => 'Pegawai'
        ]);
    }

    /**
     * Test that patient data is automatically synced when creating a new medical record
     */
    public function test_patient_data_synced_on_medical_record_creation(): void
    {
        $medicalRecord = MedicalRecord::create([
            'family_member_id' => $this->familyMember->id,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        // Assert patient data was synced
        $this->assertEquals($this->familyMember->name, $medicalRecord->patient_name);
        $this->assertEquals($this->familyMember->gender, $medicalRecord->patient_gender);
        $this->assertEquals($this->familyMember->nik, $medicalRecord->patient_nik);
        $this->assertEquals($this->familyMember->rm_number, $medicalRecord->patient_rm_number);
        $this->assertEquals($this->familyMember->birth_date->format('Y-m-d'), $medicalRecord->patient_birth_date->format('Y-m-d'));
        $this->assertEquals($this->familyMember->age, $medicalRecord->patient_age);
        
        // Assert address was constructed correctly
        $expectedAddress = "{$this->building->address}, {$this->village->name}, {$this->village->district}, {$this->village->regency}";
        $this->assertEquals($expectedAddress, $medicalRecord->patient_address);
    }

    /**
     * Test that patient data is synced when family_member_id is updated
     */
    public function test_patient_data_synced_on_family_member_id_update(): void
    {
        // Create another family member
        $anotherFamilyMember = FamilyMember::create([
            'family_id' => $this->family->id,
            'name' => 'Jane Doe',
            'nik' => '6543210987654321',
            'rm_number' => 'RM-002',
            'sequence_number_in_family' => 2,
            'relationship' => 'Istri',
            'birth_place' => 'Bandung',
            'birth_date' => Carbon::parse('1992-05-15'),
            'gender' => 'Perempuan',
            'religion' => 'Islam',
            'occupation' => 'Ibu Rumah Tangga'
        ]);

        // Create medical record with first family member
        $medicalRecord = MedicalRecord::create([
            'family_member_id' => $this->familyMember->id,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        // Update to second family member
        $medicalRecord->update(['family_member_id' => $anotherFamilyMember->id]);

        // Assert patient data was updated to second family member
        $this->assertEquals($anotherFamilyMember->name, $medicalRecord->patient_name);
        $this->assertEquals($anotherFamilyMember->gender, $medicalRecord->patient_gender);
        $this->assertEquals($anotherFamilyMember->nik, $medicalRecord->patient_nik);
        $this->assertEquals($anotherFamilyMember->rm_number, $medicalRecord->patient_rm_number);
    }

    /**
     * Test syncPatientData method directly
     */
    public function test_sync_patient_data_method(): void
    {
        $medicalRecord = new MedicalRecord([
            'family_member_id' => $this->familyMember->id,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        // Load the relationship
        $medicalRecord->setRelation('familyMember', $this->familyMember);
        $this->familyMember->setRelation('family', $this->family);
        $this->family->setRelation('building', $this->building);
        $this->building->setRelation('village', $this->village);

        // Call syncPatientData manually
        $medicalRecord->syncPatientData();

        // Assert all patient data was synced
        $this->assertEquals($this->familyMember->name, $medicalRecord->patient_name);
        $this->assertEquals($this->familyMember->gender, $medicalRecord->patient_gender);
        $this->assertEquals($this->familyMember->nik, $medicalRecord->patient_nik);
        $this->assertEquals($this->familyMember->rm_number, $medicalRecord->patient_rm_number);
        $this->assertEquals($this->familyMember->birth_date, $medicalRecord->patient_birth_date);
        $this->assertEquals($this->familyMember->age, $medicalRecord->patient_age);

        $expectedAddress = "{$this->building->address}, {$this->village->name}, {$this->village->district}, {$this->village->regency}";
        $this->assertEquals($expectedAddress, $medicalRecord->patient_address);
    }

    /**
     * Test current patient age accessor
     */
    public function test_current_patient_age_accessor(): void
    {
        $medicalRecord = MedicalRecord::create([
            'family_member_id' => $this->familyMember->id,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        // Test that current age is calculated from birth date
        $expectedAge = Carbon::parse($this->familyMember->birth_date)->age;
        $this->assertEquals($expectedAge, $medicalRecord->current_patient_age);
    }

    /**
     * Test handling when family member has no family/building/village
     */
    public function test_sync_patient_data_with_missing_relations(): void
    {
        // Create family member without proper relations
        $orphanFamilyMember = FamilyMember::create([
            'family_id' => null,
            'name' => 'Orphan Member',
            'nik' => '9999999999999999',
            'rm_number' => 'RM-999',
            'sequence_number_in_family' => 1,
            'relationship' => 'Kepala Keluarga',
            'birth_place' => 'Unknown',
            'birth_date' => Carbon::parse('1985-01-01'),
            'gender' => 'Laki-laki'
        ]);

        $medicalRecord = new MedicalRecord([
            'family_member_id' => $orphanFamilyMember->id,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        $medicalRecord->setRelation('familyMember', $orphanFamilyMember);

        // Should not throw error and should handle missing relations gracefully
        $medicalRecord->syncPatientData();

        $this->assertEquals($orphanFamilyMember->name, $medicalRecord->patient_name);
        $this->assertEquals('', $medicalRecord->patient_address); // Should be empty string
    }

    /**
     * Test handling when family member is null
     */
    public function test_sync_patient_data_with_null_family_member(): void
    {
        $medicalRecord = new MedicalRecord([
            'family_member_id' => null,
            'visit_date' => Carbon::today(),
            'chief_complaint' => 'Test complaint',
            'created_by' => $this->user->id
        ]);

        // Should not throw error when family member is null
        $medicalRecord->syncPatientData();

        $this->assertNull($medicalRecord->patient_name);
        $this->assertNull($medicalRecord->patient_address);
    }

    /**
     * Test that existing medical records maintain their data integrity
     */
    public function test_backward_compatibility(): void
    {
        // Create medical record without patient data (simulating existing record)
        $medicalRecord = new MedicalRecord();
        $medicalRecord->family_member_id = $this->familyMember->id;
        $medicalRecord->visit_date = Carbon::today();
        $medicalRecord->chief_complaint = 'Test complaint';
        $medicalRecord->created_by = $this->user->id;
        
        // Save without triggering events
        $medicalRecord->saveQuietly();

        // Verify patient data is null initially
        $this->assertNull($medicalRecord->patient_name);
        
        // Now sync patient data manually
        $medicalRecord->syncPatientData();
        $medicalRecord->saveQuietly();

        // Verify patient data is now populated
        $this->assertEquals($this->familyMember->name, $medicalRecord->patient_name);
    }
}
