<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'village_id',
        'building_number',
        'longitude',
        'latitude',
        'description'
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }
}
