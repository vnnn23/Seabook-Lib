<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;

class DashboardPetugasController extends Controller
{
    /**
     * Tampilan daftar buku untuk petugas
     */
    public function index(Request $request)
    {
        $query = Buku::withKetersediaan()->with('kategoris');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $kategoriId = $request->kategori;
            $query->whereHas('kategoris', function ($q) use ($kategoriId) {
                $q->where('kategoribuku.KategoriID', $kategoriId);
            });
        }

        $buku = $query->latest('BukuID')->get();
        $kategoris = KategoriBuku::all();

        return view('dashboardpetugas', compact('buku', 'kategoris'));
    }

    /**
     * Tampilan daftar peminjaman untuk petugas
     */
    public function peminjaman()
    {
        $peminjaman = Peminjaman::with(['user', 'buku'])->orderBy('PeminjamanID', 'desc')->get();
        return view('dashboardpetugas2', compact('peminjaman'));
    }
}
