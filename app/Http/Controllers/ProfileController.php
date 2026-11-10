<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $poinLogs = $user->poinLogs()->with(['peminjaman.buku', 'creator'])->take(20)->get();
        $peminjamanAktif = \App\Models\Peminjaman::where('UserID', $user->UserID)
            ->whereIn('statuspeminjaman', \App\Models\Peminjaman::STATUS_AKTIF_LOAN)
            ->with('buku')
            ->latest('PeminjamanID')
            ->get();

        return view('profile.edit', [
            'user' => $user,
            'poinLogs' => $poinLogs,
            'peminjamanAktif' => $peminjamanAktif,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kecamatan' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $user->update($request->only(['nama', 'jenis_kelamin', 'nomor_telepon', 'alamat', 'kecamatan']));

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    public function uploadFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_photos', $filename, 'public');

            $user->update(['foto_profil' => $path]);
        }

        return redirect()->back()->with('success', 'Foto profil berhasil diperbarui');
    }
}