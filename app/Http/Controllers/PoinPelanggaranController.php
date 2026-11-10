<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PoinPelanggaranLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoinPelanggaranController extends Controller
{
    /**
     * Tampilan daftar pengguna dan ringkasan poin & status tinjau akun
     */
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_USER);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $ambangTinjau = (int) config('perpustakaan.ambang_tinjau', 60);
        $ambangPeringatan = (int) config('perpustakaan.ambang_peringatan', 75);

        if ($request->filter === 'tinjau' || $request->filter === 'terblokir') {
            $query->where(function ($q) use ($ambangTinjau) {
                $q->where('status_akun', User::STATUS_TINJAU)
                  ->orWhere(function ($sub) use ($ambangTinjau) {
                      $sub->where('status_akun', User::STATUS_AKTIF)
                          ->where('poin', '<=', $ambangTinjau);
                  });
            });
        } elseif ($request->filter === 'peringatan') {
            $query->where('status_akun', User::STATUS_AKTIF)
                  ->where('poin', '<=', $ambangPeringatan)
                  ->where('poin', '>', $ambangTinjau);
        } elseif ($request->filter === 'aman') {
            $query->where('status_akun', User::STATUS_AKTIF)
                  ->where('poin', '>', $ambangPeringatan);
        } elseif ($request->filter === 'pending') {
            $query->where('status_akun', User::STATUS_PENDING);
        }

        // Tampilkan yang membutuhkan tinjau akun terlebih dahulu, lalu pending persetujuan
        $users = $query->orderByRaw("CASE 
                WHEN status_akun = 'tinjau' OR (status_akun = 'aktif' AND poin <= {$ambangTinjau}) THEN 0 
                WHEN status_akun = 'pending' THEN 1 
                ELSE 2 
            END")
            ->orderBy('poin', 'asc')
            ->paginate(15);

        $totalTinjau = User::where('role', User::ROLE_USER)
            ->where(function ($q) use ($ambangTinjau) {
                $q->where('status_akun', User::STATUS_TINJAU)
                  ->orWhere(function ($sub) use ($ambangTinjau) {
                      $sub->where('status_akun', User::STATUS_AKTIF)
                          ->where('poin', '<=', $ambangTinjau);
                  });
            })->count();

        $totalPending = User::where('role', User::ROLE_USER)
            ->where('status_akun', User::STATUS_PENDING)
            ->count();

        return view('admin.poin.index', compact('users', 'totalTinjau', 'totalPending'));
    }

    /**
     * Detail riwayat log dan peninjauan akun user tertentu
     */
    public function show($userId)
    {
        $user = User::with(['poinLogs.creator', 'poinLogs.peminjaman.buku'])->findOrFail($userId);
        return view('admin.poin.show', compact('user'));
    }

    /**
     * Tinjau akun: pulihkan, tambah, kurangi, atau reset poin oleh admin dengan alasan wajib
     */
    public function adjust(Request $request, $userId)
    {
        $request->validate([
            'aksi' => 'required|in:pulihkan_reset,tambah,kurangi,reset,set_status',
            'jumlah_poin' => 'nullable|integer|min:1',
            'status_tujuan' => 'nullable|in:aktif,tinjau',
            'alasan' => 'required|string|max:500',
        ], [
            'alasan.required' => 'Alasan peninjauan / penyesuaian wajib diisi.',
        ]);

        return DB::transaction(function () use ($request, $userId) {
            $user = User::where('UserID', $userId)->lockForUpdate()->firstOrFail();
            $poinAwal = $user->poin ?? 100;
            $poinPelanggaranAwal = $user->poin_pelanggaran ?? 0;
            $ambangTinjau = (int) config('perpustakaan.ambang_tinjau', 60);

            if ($request->aksi === 'pulihkan_reset' || $request->aksi === 'reset') {
                $delta = $poinPelanggaranAwal > 0 ? -$poinPelanggaranAwal : (100 - $poinAwal);
                $user->poin = 100;
                $user->poin_pelanggaran = 0;
                $user->status_akun = User::STATUS_AKTIF;
                $jenis = PoinPelanggaranLog::JENIS_RESET_ADMIN;
                $keterangan = "Pemulihan akun dan reset poin ke 100 oleh Admin. Alasan: " . $request->alasan;
            } elseif ($request->aksi === 'tambah') {
                $jumlah = (int) $request->input('jumlah_poin', 0);
                $user->poin = min(100, $poinAwal + $jumlah);
                $user->poin_pelanggaran = max(0, $user->poin_pelanggaran - $jumlah);
                $delta = $user->poin - $poinAwal;
                if ($user->poin > $ambangTinjau && $user->status_akun === User::STATUS_TINJAU) {
                    $user->status_akun = User::STATUS_AKTIF;
                }
                $jenis = PoinPelanggaranLog::JENIS_PENAMBAHAN_ADMIN;
                $keterangan = "Penambahan {$delta} poin oleh Admin. Alasan: " . $request->alasan;
            } elseif ($request->aksi === 'kurangi') {
                $jumlah = (int) $request->input('jumlah_poin', 0);
                $user->poin = max(0, $poinAwal - $jumlah);
                $user->poin_pelanggaran = max(0, $user->poin_pelanggaran - $jumlah);
                $delta = -$jumlah;
                if ($user->poin <= $ambangTinjau) {
                    $user->status_akun = User::STATUS_TINJAU;
                }
                $jenis = PoinPelanggaranLog::JENIS_PENGURANGAN_ADMIN;
                $keterangan = "Pengurangan {$jumlah} poin oleh Admin. Alasan: " . $request->alasan;
            } elseif ($request->aksi === 'set_status') {
                $delta = 0;
                $user->status_akun = $request->status_tujuan;
                $jenis = 'perubahan_status';
                $keterangan = "Perubahan status akun menjadi {$request->status_tujuan} oleh Admin. Alasan: " . $request->alasan;
            }

            $user->save();

            PoinPelanggaranLog::create([
                'UserID' => $user->UserID,
                'jenis' => $jenis,
                'poin' => $delta,
                'keterangan' => $keterangan,
                'dibuat_oleh' => Auth::id(),
            ]);

            return back()->with('success', "Akun dan poin anggota {$user->nama} berhasil ditinjau & diperbarui.");
        });
    }
}

