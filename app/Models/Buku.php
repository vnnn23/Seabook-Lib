<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';
    protected $primaryKey = 'BukuID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'deskripsi',
        'jumlah_total',
        'cover',
    ];

    protected $appends = [
        'total',
        'dipinjam',
        'tersedia',
    ];

    protected static function booted()
    {
        // Kompatibilitas skema lama: pastikan kolom lawas terisi default saat record baru dibuat
        static::creating(function ($buku) {
            if (!isset($buku->attributes['stok'])) {
                $buku->attributes['stok'] = 0;
            }
            if (!isset($buku->attributes['cover'])) {
                $buku->attributes['cover'] = 'covers/default.png';
            }
        });
    }

    /**
     * Total seluruh eksemplar milik perpustakaan
     */
    public function getTotalAttribute(): int
    {
        return (int) ($this->jumlah_total ?? 0);
    }

    /**
     * Scope untuk eager loading hitungan buku yang sedang dipinjam (mencegah N+1 query)
     */
    public function scopeWithKetersediaan($query)
    {
        return $query->withCount([
            'peminjaman as dipinjam_count' => function ($q) {
                $q->whereIn('statuspeminjaman', Peminjaman::STATUS_HOLDING_BOOK);
            }
        ]);
    }

    /**
     * Jumlah eksemplar yang sedang terkunci / dipinjam
     * Menggunakan dipinjam_count jika sudah di-eager load lewat scopeWithKetersediaan(),
     * atau query langsung jika dipanggil per instance individual.
     */
    public function getDipinjamAttribute(): int
    {
        if (array_key_exists('dipinjam_count', $this->attributes)) {
            return (int) $this->attributes['dipinjam_count'];
        }

        return $this->peminjaman()
            ->whereIn('statuspeminjaman', Peminjaman::STATUS_HOLDING_BOOK)
            ->count();
    }

    /**
     * Jumlah eksemplar yang tersedia untuk dipinjam
     */
    public function getTersediaAttribute(): int
    {
        return max(0, $this->total - $this->dipinjam);
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'BukuID', 'BukuID');
    }

    public function kategoris()
    {
        return $this->belongsToMany(KategoriBuku::class, 'kategoribuku_relasi', 'BukuID', 'KategoriID');
    }

    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'BukuID', 'BukuID');
    }
}
