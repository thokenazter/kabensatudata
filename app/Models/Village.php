<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sequence_number',
        'district',
        'regency',
        'province'
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    // Relasi ke model Family
    public function families()
    {
        return $this->hasManyThrough(Family::class, Building::class);
    }
}
