<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineUsage extends Model
{
    protected $fillable = [
        'medical_record_id',
        'medicine_id',
        'quantity_used',
        'instruction_text',
        'frequency',
        'dosage',
        'notes',
    ];

    /**
     * Get the medical record that owns the medicine usage.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the medicine that owns the medicine usage.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get formatted prescription text following Indonesian standards
     * Format: "Medicine Name Strength X FrequencyDosage Unit"
     * Example: "Paracetamol 500mg X 3dd1 tab"
     */
    public function getFormattedPrescriptionAttribute(): string
    {
        $medicine = $this->medicine;
        $prescription = $medicine->full_name;
        
        if ($this->instruction_text) {
            $prescription .= ' X ' . $this->instruction_text;
        }
        
        $prescription .= ' ' . $medicine->unit;
        
        if ($this->notes) {
            $prescription .= ' (' . $this->notes . ')';
        }
        
        return $prescription;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Reduce medicine stock when creating usage
        static::created(function ($medicineUsage) {
            $medicineUsage->medicine->reduceStock($medicineUsage->quantity_used);
        });

        // Handle stock changes when updating usage
        static::updating(function ($medicineUsage) {
            $original = $medicineUsage->getOriginal();
            
            if ($medicineUsage->isDirty('quantity_used') || $medicineUsage->isDirty('medicine_id')) {
                // Restore original stock if medicine or quantity changed
                if (isset($original['medicine_id']) && isset($original['quantity_used'])) {
                    $originalMedicine = Medicine::find($original['medicine_id']);
                    if ($originalMedicine) {
                        $originalMedicine->addStock($original['quantity_used']);
                    }
                }
                
                // Reduce new stock
                $medicineUsage->medicine->reduceStock($medicineUsage->quantity_used);
            }
        });

        // Restore stock when deleting usage
        static::deleting(function ($medicineUsage) {
            $medicineUsage->medicine->addStock($medicineUsage->quantity_used);
        });
    }
}