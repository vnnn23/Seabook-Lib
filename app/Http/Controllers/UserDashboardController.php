<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peminjaman;

class UserDashboardController extends Controller
{
    /**
     * Menampilkan profil lengkap anggota (informasi akun, sistem 100 poin, dan form edit)
     */
    public function index()
    {
        return app(ProfileController::class)->edit();
    }
}

