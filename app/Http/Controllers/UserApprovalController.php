<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserApprovalController extends Controller
{
    /**
     * Halaman persetujuan member untuk admin
     */
    public function index()
    {
        $pendingUsers = User::where('role', User::ROLE_USER)
            ->where('status_akun', User::STATUS_PENDING)
            ->latest('UserID')
            ->get();

        $historyUsers = User::where('role', User::ROLE_USER)
            ->whereIn('status_akun', [User::STATUS_AKTIF, User::STATUS_DITOLAK])
            ->whereNotNull('disetujui_pada')
            ->with('disetujuiOleh')
            ->latest('disetujui_pada')
            ->paginate(15);

        return view('admin.members.approval', compact('pendingUsers', 'historyUsers'));
    }

    /**
     * Setujui akun member
     */
    public function approve($id)
    {
        $user = User::where('UserID', $id)
            ->where('role', User::ROLE_USER)
            ->where('status_akun', User::STATUS_PENDING)
            ->firstOrFail();

        $user->status_akun = User::STATUS_AKTIF;
        $user->disetujui_oleh = Auth::id();
        $user->disetujui_pada = now();
        $user->alasan_penolakan = null;
        $user->poin = (int) config('perpustakaan.poin_awal', 100);
        $user->poin_pelanggaran = 0;
        $user->save();

        // Catat log pemberian poin perdana akun
        try {
            \App\Models\PoinPelanggaranLog::create([
                'UserID' => $user->UserID,
                'jenis' => \App\Models\PoinPelanggaranLog::JENIS_RESET_ADMIN,
                'poin' => (int) config('perpustakaan.poin_awal', 100),
                'keterangan' => 'Pemberian 100 poin perdana setelah akun disetujui oleh admin.',
                'dibuat_oleh' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::info("Log poin perdana gagal dibuat: " . $e->getMessage());
        }

        // Notifikasi email
        try {
            Mail::raw(
                "Halo {$user->nama},\n\nSelamat! Akun perpustakaan Anda di " . config('app.name', 'SeaBook') . " telah disetujui oleh admin. Sekarang Anda dapat login dan menikmati layanan peminjaman buku.\n\nSalam,\nTim " . config('app.name', 'SeaBook'),
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject("Akun Anda Telah Disetujui - " . config('app.name', 'SeaBook'));
                }
            );
        } catch (\Exception $e) {
            Log::info("Email approval untuk {$user->email} gagal dikirim lewat SMTP, tercatat di log: " . $e->getMessage());
        }

        return redirect()->back()->with('success', "Akun member {$user->nama} berhasil disetujui.");
    }

    /**
     * Tolak akun member dengan alasan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $user = User::where('UserID', $id)
            ->where('role', User::ROLE_USER)
            ->where('status_akun', User::STATUS_PENDING)
            ->firstOrFail();

        $user->status_akun = User::STATUS_DITOLAK;
        $user->disetujui_oleh = Auth::id();
        $user->disetujui_pada = now();
        $user->alasan_penolakan = $request->alasan;
        $user->poin = 0;
        $user->save();

        // Notifikasi email
        try {
            Mail::raw(
                "Halo {$user->nama},\n\nMohon maaf, pendaftaran akun Anda di " . config('app.name', 'SeaBook') . " ditolak dengan alasan:\n{$request->alasan}\n\nSalam,\nTim " . config('app.name', 'SeaBook'),
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject("Pemberitahuan Status Akun - " . config('app.name', 'SeaBook'));
                }
            );
        } catch (\Exception $e) {
            Log::info("Email penolakan untuk {$user->email} gagal dikirim lewat SMTP, tercatat di log: " . $e->getMessage());
        }

        return redirect()->back()->with('success', "Akun member {$user->nama} telah ditolak.");
    }
}

