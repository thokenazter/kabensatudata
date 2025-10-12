<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratArchive extends Model
{
    use HasFactory;

    protected $table = 'surat_archives';

    protected $fillable = [
        'jenis',
        'nomor_surat',
        'pegawai_id',
        'perihal',
        'issued_at',
        'file_path',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'meta' => 'array',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

