<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpmTarget extends Model
{
    protected $fillable = [
        'year',
        'village_id',
        'spm_indicator_code',
        'spm_indicator_name',
        'spm_sub_indicator_id',
        'denominator_dinkes',
        'target_percentage',
        'notes',
    ];

    protected $casts = [
        'target_percentage' => 'decimal:2',
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
