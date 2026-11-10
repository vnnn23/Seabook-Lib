<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PetugasController extends Controller
{
    /**
     * Tampilkan daftar petugas untuk admin
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'petugas');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        $petugas = $query->latest('UserID')->get();

        return view('dashboardadmin3', compact('petugas'));
    }

    /**
     * Simpan data petugas baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email',
            'password' => 'required|string|min:8|confirmed',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nomor_telepon' => $request->nomor_telepon,
            'role' => User::ROLE_PETUGAS,
            'status_akun' => User::STATUS_AKTIF,
            'disetujui_oleh' => \Illuminate\Support\Facades\Auth::id(),
            'disetujui_pada' => now(),
        ]);

        return redirect()->route('dashboardadmin3')->with('success', 'Petugas berhasil ditambahkan.');
    }

    /**
     * Hapus data petugas
     */
    public function destroy($id)
    {
        $petugas = User::where('UserID', $id)->where('role', 'petugas')->firstOrFail();
        $petugas->delete();

        return redirect()->route('dashboardadmin3')->with('success', 'Petugas berhasil dihapus.');
    }
}
