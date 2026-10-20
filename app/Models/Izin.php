<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izin';

    protected $fillable = [
        'siswa_id',
        'jenis',
        'tgl_mulai',
        'tgl_selesai',
        'alasan',
        'bukti_path',
        'status',
        'diproses_oleh',
        'catatan',
        'diproses_pada',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'string',
            'tgl_selesai' => 'string',
            'diproses_pada' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'izin_id');
    }

    public function presensiJam(): HasMany
    {
        return $this->hasMany(PresensiJam::class, 'izin_id');
    }
}
