<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpmSubIndicator extends Model
{
    protected $fillable = [
        'spm_indicator_id', 'code', 'name', 'definition',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(SpmIndicator::class, 'spm_indicator_id');
    }

    public function medicalRecords()
    {
        return $this->belongsToMany(\App\Models\MedicalRecord::class, 'medical_record_spm_sub_indicators')
            ->withTimestamps();
    }
}
