<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpmIndicator extends Model
{
    protected $fillable = [
        'code', 'name', 'description',
    ];

    public function subIndicators(): HasMany
    {
        return $this->hasMany(SpmSubIndicator::class);
    }
}

