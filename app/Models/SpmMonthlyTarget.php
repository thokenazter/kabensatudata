<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpmMonthlyTarget extends Model
{
    protected $fillable = [
        'year',
        'month',
        'spm_sub_indicator_id',
        'village_id',
        'target_absolute',
        'notes',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function subIndicator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SpmSubIndicator::class, 'spm_sub_indicator_id');
    }
}

