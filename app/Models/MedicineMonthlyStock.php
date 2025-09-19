<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineMonthlyStock extends Model
{
    protected $fillable = [
        'medicine_id',
        'period_start',
        'opening_stock',
        'usage_quantity',
        'adjustment_quantity',
        'closing_stock',
    ];

    protected $casts = [
        'period_start' => 'date',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::parse($this->period_start)->translatedFormat('F Y');
    }
}
