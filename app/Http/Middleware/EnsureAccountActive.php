<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureAccountActive
{
    /**
     * Handle an incoming request.
     * Memaksa logout jika user sedang login namun status_akun bukan 'aktif'
     * (misal sesi lama, remember-me cookie, atau baru saja dinonaktifkan/ditolak).
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->status_akun !== User::STATUS_AKTIF) {
                $statusMessage = $user->isPending()
                    ? 'Akun Anda masih menunggu persetujuan admin.'
                    : 'Akun Anda telah ditolak' . ($user->alasan_penolakan ? ': ' . $user->alasan_penolakan : '.');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->with('error', $statusMessage)->with('open_login_modal', true);
            }
        }

        return $next($request);
    }
}

