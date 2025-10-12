<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'user_id',
        'nama',
        'nip',
        'nik',
        'jenis_kelamin',
        'jabatan',
        'unit',
        'pangkat_gol',
        'pendidikan_terakhir',
        'profesi',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'foto_path',
        'ktp_path',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(PegawaiDokumen::class);
    }
}
