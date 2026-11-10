<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'UserID';
    public $incrementing = true;
    protected $keyType = 'int';

    // Konstanta Role
    const ROLE_ADMIN = 'admin';
    const ROLE_PETUGAS = 'petugas';
    const ROLE_USER = 'user';

    // Konstanta Status Akun
    const STATUS_PENDING = 'pending';
    const STATUS_AKTIF = 'aktif';
    const STATUS_TINJAU = 'tinjau';
    const STATUS_DITOLAK = 'ditolak';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'status_akun',
        'disetujui_oleh',
        'disetujui_pada',
        'alasan_penolakan',
        'foto_profil',
        'jenis_kelamin',
        'nomor_telepon',
        'alamat',
        'kecamatan',
        'poin',
        'poin_pelanggaran',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'disetujui_pada' => 'datetime',
        'poin' => 'integer',
        'poin_pelanggaran' => 'integer',
    ];

    /**
     * Relasi ke Log Poin Pelanggaran
     */
    public function poinLogs()
    {
        return $this->hasMany(PoinPelanggaranLog::class, 'UserID', 'UserID')->latest();
    }

    /**
     * Accessor poin akun: jika akun masih pending atau ditolak, belum memiliki poin (0).
     */
    public function getPoinAttribute($value): int
    {
        if ($this->status_akun === self::STATUS_PENDING || $this->status_akun === self::STATUS_DITOLAK) {
            return 0;
        }
        return $value !== null ? (int) $value : 100;
    }

    /**
     * Mengecek apakah user diblokir dari meminjam buku:
     * 1. Akun belum disetujui (pending), ditolak, atau non-aktif
     * 2. Atau memiliki peminjaman aktif yang sudah melewati tanggal kembali (terlambat belum dikembalikan)
     * (Sistem poin pelanggaran ditiadakan)
     */
    public function isBlockedFromBorrowing(): bool
    {
        if ($this->isPending() || $this->isDitolak() || !$this->isAktif()) {
            return true;
        }

        return $this->peminjaman()
            ->whereIn('statuspeminjaman', [
                Peminjaman::STATUS_DIPINJAM,
                Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
            ])
            ->where('tanggal_kembali', '<', now())
            ->exists();
    }

    /**
     * Accessor status akun member: 'pending' | 'ditolak' | 'aman'
     */
    public function getStatusPoinAttribute(): string
    {
        if ($this->isPending()) {
            return 'pending';
        }

        if ($this->isDitolak()) {
            return 'ditolak';
        }

        return 'aman';
    }

    /**
     * Relasi ke User yang menyetujui akun
     */
    public function disetujuiOleh()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'UserID');
    }

    /**
     * Relasi ke Peminjaman
     */
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'UserID', 'UserID');
    }

    /**
     * Relasi ke Ulasan
     */
    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'UserID', 'UserID');
    }

    /**
     * Relasi ke Koleksi Pribadi
     */
    public function koleksi()
    {
        return $this->hasMany(KoleksiPribadi::class, 'UserID', 'UserID');
    }

    /**
     * Helper check roles
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Helper check status akun
     */
    public function isPending(): bool
    {
        return $this->status_akun === self::STATUS_PENDING;
    }

    public function isAktif(): bool
    {
        return $this->status_akun === self::STATUS_AKTIF;
    }

    public function isDitolak(): bool
    {
        return $this->status_akun === self::STATUS_DITOLAK;
    }

    public function isTinjau(): bool
    {
        return $this->status_akun === self::STATUS_TINJAU;
    }

    /**
     * Relasi percakapan chat sebagai pengguna
     */
    public function percakapanUser()
    {
        return $this->hasMany(ChatPercakapan::class, 'UserID', 'UserID');
    }

    /**
     * Relasi percakapan chat yang ditangani sebagai petugas
     */
    public function percakapanPetugas()
    {
        return $this->hasMany(ChatPercakapan::class, 'petugas_id', 'UserID');
    }
}
