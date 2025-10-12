<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmAchievementOverride extends Model
{
    protected $fillable = [
        'spm_sub_indicator_id',
        'year',
        'month',
        'village_id',
        'value',
        'updated_by',
        'note',
    ];
}

