<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoinPelanggaranLog extends Model
{
    use HasFactory;

    protected $table = 'poin_pelanggaran_log';

    const JENIS_KETERLAMBATAN = 'keterlambatan';
    const JENIS_RUSAK_RINGAN = 'rusak ringan';
    const JENIS_RUSAK_BERAT = 'rusak berat';
    const JENIS_HILANG = 'hilang';
    const JENIS_PENGURANGAN_ADMIN = 'pengurangan_admin';
    const JENIS_PENAMBAHAN_ADMIN = 'penambahan_admin';
    const JENIS_RESET_ADMIN = 'reset_admin';
    const JENIS_PULIHKAN_AKUN = 'pulihkan_akun';

    protected $fillable = [
        'UserID',
        'PeminjamanID',
        'jenis',
        'poin',
        'keterangan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'poin' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'PeminjamanID', 'PeminjamanID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'UserID');
    }
}

