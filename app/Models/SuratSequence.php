<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis', 'year', 'last_seq'
    ];
}

