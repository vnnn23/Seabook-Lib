<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatPesan extends Model
{
    use HasFactory;

    protected $table = 'chat_pesan';

    const TIPE_USER = 'user';
    const TIPE_PETUGAS = 'petugas';
    const TIPE_ADMIN = 'admin';
    const TIPE_AI = 'ai';

    protected $fillable = [
        'percakapan_id',
        'pengirim_id',
        'pengirim_tipe',
        'isi',
        'dibaca_pada',
    ];

    protected $casts = [
        'dibaca_pada' => 'datetime',
    ];

    public function percakapan()
    {
        return $this->belongsTo(ChatPercakapan::class, 'percakapan_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id', 'UserID');
    }

    public function isFromStaff(): bool
    {
        return in_array($this->pengirim_tipe, [self::TIPE_PETUGAS, self::TIPE_ADMIN]);
    }

    public function isFromAi(): bool
    {
        return $this->pengirim_tipe === self::TIPE_AI;
    }

    public function isFromUser(): bool
    {
        return $this->pengirim_tipe === self::TIPE_USER;
    }
}

