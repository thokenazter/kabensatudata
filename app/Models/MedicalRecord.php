<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'spm_service_type',
        'medication',
        'procedure',
        'created_by',
        'workflow_status',
        'queued_at',
        'started_at',
        'completed_at',
        'assigned_to',
        'queue_position',
        // Queue management fields
        'queue_number',
        'priority_level',
        'estimated_service_time',
        'current_role_handler',
        'registration_start_time',
        'registration_end_time',
        'nurse_start_time',
        'nurse_end_time',
        'doctor_start_time',
        'doctor_end_time',
        'pharmacy_start_time',
        'pharmacy_end_time',
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
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'registration_start_time' => 'datetime',
        'registration_end_time' => 'datetime',
        'nurse_start_time' => 'datetime',
        'nurse_end_time' => 'datetime',
        'doctor_start_time' => 'datetime',
        'doctor_end_time' => 'datetime',
        'pharmacy_start_time' => 'datetime',
        'pharmacy_end_time' => 'datetime',
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
     * Get the user assigned to this record.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who currently handles this record in the active role.
     */
    public function currentHandler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_role_handler');
    }

    /**
     * Get the medicine usages for this medical record.
     */
    public function medicineUsages(): HasMany
    {
        return $this->hasMany(MedicineUsage::class);
    }

    /**
     * Many-to-many with SPM sub-indicators.
     */
    public function spmSubIndicators()
    {
        return $this->belongsToMany(\App\Models\SpmSubIndicator::class, 'medical_record_spm_sub_indicators')
            ->withTimestamps();
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
     * Based on AHA/ACC Guidelines:
     * - Normal: Systolic < 120 AND Diastolic < 80
     * - Elevated: Systolic 120-129 AND Diastolic < 80
     * - Stage 1 Hypertension: Systolic 130-139 OR Diastolic 80-89
     * - Stage 2 Hypertension: Systolic >= 140 OR Diastolic >= 90
     */
    public function getBloodPressureCategoryAttribute()
    {
        if (!$this->systolic || !$this->diastolic) return null;

        // Stage 2 Hypertension: Systolic >= 140 OR Diastolic >= 90 (check first for highest priority)
        if ($this->systolic >= 140 || $this->diastolic >= 90) return 'Hipertensi Stage 2';
        
        // Stage 1 Hypertension: Systolic 130-139 OR Diastolic 80-89
        if (($this->systolic >= 130 && $this->systolic <= 139) || ($this->diastolic >= 80 && $this->diastolic <= 89)) return 'Hipertensi Stage 1';
        
        // Elevated: Systolic 120-129 AND Diastolic < 80
        if ($this->systolic >= 120 && $this->systolic <= 129 && $this->diastolic < 80) return 'Elevated';
        
        // Normal: Systolic < 120 AND Diastolic < 80
        return 'Normal';
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
     * Get workflow status badge color
     * 
     * @return string
     */
    public function getWorkflowStatusColorAttribute(): string
    {
        return match($this->workflow_status) {
            'pending_registration' => 'gray',
            'pending_nurse' => 'info',
            'pending_doctor' => 'warning',
            'pending_pharmacy' => 'success',
            'completed' => 'primary',
            default => 'gray'
        };
    }

    /**
     * Get workflow status label
     * 
     * @return string
     */
    public function getWorkflowStatusLabelAttribute(): string
    {
        return match($this->workflow_status) {
            'pending_registration' => 'Menunggu Pendaftaran',
            'pending_nurse' => 'Menunggu Perawat',
            'pending_doctor' => 'Menunggu Dokter',
            'pending_pharmacy' => 'Menunggu Apoteker',
            'completed' => 'Selesai',
            default => 'Menunggu Pendaftaran'
        };
    }

    /**
     * Queue scopes for different roles
     */
    public function scopePendingRegistration($query)
    {
        return $query->where('workflow_status', 'pending_registration')
                    ->orderBy('queued_at', 'asc');
    }

    public function scopePendingNurse($query)
    {
        return $query->where('workflow_status', 'pending_nurse')
                    ->orderBy('queued_at', 'asc');
    }

    public function scopePendingDoctor($query)
    {
        return $query->where('workflow_status', 'pending_doctor')
                    ->orderBy('queued_at', 'asc');
    }

    public function scopePendingPharmacy($query)
    {
        return $query->where('workflow_status', 'pending_pharmacy')
                    ->orderBy('queued_at', 'asc');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Workflow progression methods
     */
    public function moveToNextStage(): bool
    {
        $nextStatus = match($this->workflow_status) {
            'pending_registration' => 'pending_nurse',
            'pending_nurse' => 'pending_doctor',
            'pending_doctor' => 'pending_pharmacy',
            'pending_pharmacy' => 'completed',
            default => null
        };

        if ($nextStatus) {
            $this->update([
                'workflow_status' => $nextStatus,
                'queued_at' => now(),
                'completed_at' => $nextStatus === 'completed' ? now() : null,
                'assigned_to' => null,
                'queue_position' => $this->getNextQueuePosition($nextStatus)
            ]);
            return true;
        }

        return false;
    }

    public function assignToUser($userId): bool
    {
        return $this->update([
            'assigned_to' => $userId,
            'started_at' => now()
        ]);
    }

    public function completeCurrentStage(): bool
    {
        return $this->moveToNextStage();
    }

    private function getNextQueuePosition($status): int
    {
        return self::where('workflow_status', $status)->max('queue_position') + 1;
    }

    /**
     * Get queue position display
     */
    public function getQueuePositionDisplayAttribute(): string
    {
        if (!$this->queue_position) return '';
        
        return "#{$this->queue_position}";
    }

    /**
     * Get waiting time
     */
    public function getWaitingTimeAttribute(): string
    {
        if (!$this->queued_at) return '';
        
        $minutes = now()->diffInMinutes($this->queued_at);
        
        if ($minutes < 60) {
            return "{$minutes} menit";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return "{$hours}j {$remainingMinutes}m";
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
     * Generate formatted medication text from medicineUsages relationship
     * Format: "Medicine Full Name - Quantity Unit (Instruction) - Notes"
     * Example: "Paracetamol 500mg - 10 tablet (3x1 tab) - Diminum setelah makan"
     * 
     * @return string
     */
    public function generateMedicationText(): string
    {
        if (!$this->medicineUsages || $this->medicineUsages->isEmpty()) {
            return '';
        }

        $medicationLines = [];

        foreach ($this->medicineUsages as $usage) {
            $line = '';
            
            // Medicine name with strength
            if ($usage->medicine) {
                $line .= $usage->medicine->full_name;
            }
            
            // Quantity and unit
            if ($usage->quantity_used && $usage->medicine) {
                $line .= " - {$usage->quantity_used} {$usage->medicine->unit}";
            }
            
            // Prefer pharmacist-provided frequency (e.g., 3x1),
            // fallback to instruction_text (e.g., 3dd1) converted to 3x1.
            $display = null;
            if (!empty($usage->frequency)) {
                $display = $usage->frequency;
            } elseif (!empty($usage->instruction_text)) {
                $display = self::instructionToShortFrequency($usage->instruction_text) ?? $usage->instruction_text;
            }
            if ($display) {
                $line .= " ({$display})";
            }
            
            // Notes if available
            if ($usage->notes) {
                $line .= " - {$usage->notes}";
            }
            
            if (!empty($line)) {
                $medicationLines[] = $line;
            }
        }

        return implode("\n", $medicationLines);
    }

    /**
     * Convert common Indonesian instruction format, e.g., "3dd1" -> "3x1".
     */
    public static function instructionToShortFrequency(?string $instr): ?string
    {
        if (empty($instr)) return null;
        if (preg_match('/^\s*(\d+)\s*dd\s*(\d+)\s*$/i', $instr, $m)) {
            return $m[1] . 'x' . $m[2];
        }
        if (preg_match('/(\d+)\s*d+\s*(\d+)/i', $instr, $m)) {
            return $m[1] . 'x' . $m[2];
        }
        return null;
    }

    /**
     * Generate next queue number for a given date.
     * Format: YYYY-MM-DD-XXX (incremental per day, based on queue_number prefix).
     */
    public static function generateQueueNumberForDate($date): string
    {
        $day = \Carbon\Carbon::parse($date)->format('Y-m-d');
        // Use prefix match on queue_number to avoid relying on visit_date consistency
        $last = self::where('queue_number', 'like', $day . '-%')
            ->orderByRaw('CAST(SUBSTRING(queue_number, -3) AS UNSIGNED) DESC')
            ->first();
        if (!$last) return $day . '-001';
        $lastNumber = intval(substr($last->queue_number, -3));
        return $day . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Backward-compatible helper: generate next queue number for today.
     */
    public static function generateQueueNumber(): string
    {
        return self::generateQueueNumberForDate(now());
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
