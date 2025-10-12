<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiDokumen extends Model
{
    use HasFactory;

    protected $table = 'pegawai_dokumen';

    protected $fillable = [
        'pegawai_id',
        'jenis',
        'judul',
        'file_path',
        'issued_at',
        'keterangan',
    ];

    protected $casts = [
        'issued_at' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}

