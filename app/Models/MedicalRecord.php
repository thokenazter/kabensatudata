<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    protected $fillable = [
        'family_member_id',
        'visit_date',
        'chief_complaint',
        'anamnesis',
        'systolic',
        'diastolic',
        'weight',
        'height',
        'heart_rate',
        'body_temperature',
        'respiratory_rate',
        'diagnosis_code',
        'diagnosis_name',
        'therapy',
        'medication',
        'procedure',
        'created_by',
        // Patient identity fields (denormalized)
        'patient_name',
        'patient_address',
        'patient_gender',
        'patient_nik',
        'patient_rm_number',
        'patient_birth_date',
        'patient_age'
    ];

    protected $casts = [
        'visit_date' => 'date',
        'patient_birth_date' => 'date',
    ];

    /**
     * Get the family member that owns the medical record.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the user who created the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate BMI (Body Mass Index)
     */
    public function getBmiAttribute()
    {
        if ($this->weight && $this->height) {
            // BMI = weight(kg) / (height(m))²
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 1);
        }

        return null;
    }

    /**
     * Get BMI category
     */
    public function getBmiCategoryAttribute()
    {
        $bmi = $this->bmi;

        if (!$bmi) return null;

        if ($bmi < 18.5) return 'Berat Badan Kurang';
        if ($bmi < 25) return 'Berat Badan Normal';
        if ($bmi < 30) return 'Berat Badan Lebih';
        return 'Obesitas';
    }

    /**
     * Get blood pressure classification
     */
    public function getBloodPressureCategoryAttribute()
    {
        if (!$this->systolic || !$this->diastolic) return null;

        if ($this->systolic < 120 && $this->diastolic < 80) return 'Normal';
        if ($this->systolic < 130 && $this->diastolic < 80) return 'Elevated';
        if ($this->systolic < 140 || $this->diastolic < 90) return 'Hipertensi Stage 1';
        return 'Hipertensi Stage 2';
    }

    /**
     * Calculate current age based on patient_birth_date
     * 
     * @return int|null
     */
    public function getCurrentPatientAgeAttribute(): ?int
    {
        if (!$this->patient_birth_date) {
            return $this->patient_age; // Return stored age if birth date not available
        }

        return \Carbon\Carbon::parse($this->patient_birth_date)->age;
    }

    /**
     * Sync patient data from related FamilyMember
     * This method fills patient identity fields from FamilyMember for denormalization
     * 
     * @return void
     */
    public function syncPatientData(): void
    {
        if ($this->familyMember) {
            $this->patient_name = $this->familyMember->name;
            $this->patient_gender = $this->familyMember->gender;
            $this->patient_nik = $this->familyMember->nik;
            $this->patient_rm_number = $this->familyMember->rm_number;
            $this->patient_birth_date = $this->familyMember->birth_date;
            $this->patient_age = $this->familyMember->age;
            
            // Get address from family->building->village
            if ($this->familyMember->family && $this->familyMember->family->building) {
                $building = $this->familyMember->family->building;
                $village = $building->village ?? null;
                
                if ($village) {
                    $this->patient_address = "{$building->address}, {$village->name}, {$village->district}, {$village->regency}";
                } else {
                    $this->patient_address = $building->address ?? '';
                }
            } else {
                $this->patient_address = '';
            }
        }
    }

    /**
     * Boot method to handle model events for auto-sync
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-sync patient data when creating new medical record
        static::creating(function ($medicalRecord) {
            $medicalRecord->syncPatientData();
        });

        // Auto-sync patient data when family_member_id changes
        static::updating(function ($medicalRecord) {
            if ($medicalRecord->isDirty('family_member_id')) {
                $medicalRecord->syncPatientData();
            }
        });
    }
}
