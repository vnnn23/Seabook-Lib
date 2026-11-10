<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna (anggota perpustakaan)
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('UserID')->get();

        return view('dashboardadmin2', compact('users'));
    }

    /**
     * Hapus akun pengguna
     */
    public function destroy($id)
    {
        $user = User::where('UserID', $id)->first();
        if (! $user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Delete related reviews and loans to avoid FK issues
        Ulasan::where('UserID', $user->UserID)->delete();
        Peminjaman::where('UserID', $user->UserID)->delete();

        // Remove profile photo from storage if present
        if (! empty($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $user->delete();

        return redirect()->route('dashboardadmin2')->with('success', 'Pengguna berhasil dihapus.');
    }
}
