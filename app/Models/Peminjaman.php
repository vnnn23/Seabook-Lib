<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'PeminjamanID';
    public $incrementing = true;
    protected $keyType = 'int';

    // Konstanta Status State Machine Peminjaman Resmi
    const STATUS_MENUNGGU = 'menunggu persetujuan';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DIPINJAM = 'sedang dipinjam';
    const STATUS_MENUNGGU_PENGEMBALIAN = 'menunggu konfirmasi pengembalian';
    const STATUS_DIKEMBALIKAN = 'dikembalikan';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_DIBATALKAN = 'dibatalkan';
    const STATUS_KADALUARSA = 'kadaluarsa';

    // Daftar status peminjaman aktif untuk menghitung batas kuota 3 pinjaman user
    const STATUS_AKTIF_LOAN = [
        self::STATUS_MENUNGGU,
        self::STATUS_DISETUJUI,
        self::STATUS_DIPINJAM,
        self::STATUS_MENUNGGU_PENGEMBALIAN,
    ];

    // Daftar status yang mengunci fisik eksemplar buku (mengurangi ketersediaan)
    const STATUS_HOLDING_BOOK = [
        self::STATUS_DISETUJUI,
        self::STATUS_DIPINJAM,
        self::STATUS_MENUNGGU_PENGEMBALIAN,
    ];

    // Konstanta Kondisi Buku saat pengembalian
    const KONDISI_BAIK = 'baik';
    const KONDISI_RUSAK_RINGAN = 'rusak ringan';
    const KONDISI_RUSAK_BERAT = 'rusak berat';
    const KONDISI_HILANG = 'hilang';

    protected $fillable = [
        'UserID',
        'BukuID',
        'tanggal_pinjam',
        'tanggal_kembali',
        'statuspeminjaman',
        'durasi',
        'denda',
        'qr_code',
        'tanggal_kembali_aktual',
        'confirmed_by',
        'alasan_penolakan',
        'kondisi_buku',
        'catatan_petugas',
        'hari_terlambat',
        'poin_denda',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'tanggal_kembali_aktual' => 'datetime',
        'hari_terlambat' => 'integer',
        'poin_denda' => 'integer',
        'denda' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'BukuID', 'BukuID');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'UserID');
    }

    public function isMenunggu(): bool
    {
        return $this->statuspeminjaman === self::STATUS_MENUNGGU;
    }

    public function isDisetujui(): bool
    {
        return $this->statuspeminjaman === self::STATUS_DISETUJUI;
    }

    public function isDipinjam(): bool
    {
        return $this->statuspeminjaman === self::STATUS_DIPINJAM;
    }

    public function isMenungguPengembalian(): bool
    {
        return $this->statuspeminjaman === self::STATUS_MENUNGGU_PENGEMBALIAN;
    }

    public function isDikembalikan(): bool
    {
        return $this->statuspeminjaman === self::STATUS_DIKEMBALIKAN;
    }

    public function isDitolak(): bool
    {
        return $this->statuspeminjaman === self::STATUS_DITOLAK;
    }

    public function isDibatalkan(): bool
    {
        return $this->statuspeminjaman === self::STATUS_DIBATALKAN;
    }

    public function isKadaluarsa(): bool
    {
        return $this->statuspeminjaman === self::STATUS_KADALUARSA;
    }
}
