<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatPercakapan extends Model
{
    use HasFactory;

    protected $table = 'chat_percakapan';

    const STATUS_MENUNGGU = 'menunggu';
    const STATUS_AKTIF = 'aktif';
    const STATUS_SELESAI = 'selesai';

    const MODE_AI = 'ai';
    const MODE_PETUGAS = 'petugas';

    protected $fillable = [
        'UserID',
        'petugas_id',
        'status',
        'mode',
        'terakhir_aktivitas',
    ];

    protected $casts = [
        'terakhir_aktivitas' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id', 'UserID');
    }

    public function pesan()
    {
        return $this->hasMany(ChatPesan::class, 'percakapan_id')->orderBy('id', 'asc');
    }

    public function pesanTerbaru()
    {
        return $this->hasOne(ChatPesan::class, 'percakapan_id')->latestOfMany();
    }

    public function pesanBelumDibacaPetugas()
    {
        return $this->hasMany(ChatPesan::class, 'percakapan_id')
            ->where('pengirim_tipe', ChatPesan::TIPE_USER)
            ->whereNull('dibaca_pada');
    }

    public function pesanBelumDibacaUser()
    {
        return $this->hasMany(ChatPesan::class, 'percakapan_id')
            ->whereIn('pengirim_tipe', [ChatPesan::TIPE_PETUGAS, ChatPesan::TIPE_ADMIN])
            ->whereNull('dibaca_pada');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public function isTaken(): bool
    {
        return !is_null($this->petugas_id);
    }
}

