<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    public function index()
    {
        $buku = Buku::withKetersediaan()->with('kategoris')->orderBy('created_at', 'desc')->get();
        $kategoris = \App\Models\KategoriBuku::all();
        $authors = $buku->pluck('penulis')->unique()->sort();
        $years = $buku->pluck('tahun_terbit')->unique()->sort()->reverse();
        return view('books', [
            'buku' => $buku,
            'kategoris' => $kategoris,
            'authors' => $authors,
            'years' => $years
        ]);
    }

    public function create()
    {
        $kategoris = \App\Models\KategoriBuku::all();
        return view('books.create', compact('kategoris'));
    }

    public function show($id)
    {
        $buku = Buku::with(['kategoris', 'ulasan.user'])->findOrFail($id);
        
        // Total eksemplar yang pernah dipinjam dan selesai
        $pembacaCount = \App\Models\Peminjaman::where('BukuID', $id)
            ->where('statuspeminjaman', 'dikembalikan')
            ->count();
        
        // Ambil ulasan dari relasi yang sudah eager-loaded dan urutkan
        $ulasans = $buku->ulasan->sortByDesc('created_at')->values();
        
        // Cek status user terhadap buku
        $sudahMembaca = false;
        $sudahMemberiBulasan = false;
        $inKoleksi = false;
        $hasBorrowedAndReturned = false;
        if (Auth::check()) {
            $hasBorrowedAndReturned = \App\Models\Peminjaman::where('UserID', Auth::id())
                ->where('BukuID', $id)
                ->where('statuspeminjaman', 'dikembalikan')
                ->exists();
            
            $sudahMembaca = $hasBorrowedAndReturned;
            
            $sudahMemberiBulasan = Ulasan::where('UserID', Auth::id())
                ->where('BukuID', $id)
                ->exists();

            $inKoleksi = \App\Models\KoleksiPribadi::where('UserID', Auth::id())
                ->where('BukuID', $id)
                ->exists();
        }
        
        return view('detailbooks', [
            'buku' => $buku,
            'totalBuku' => $buku->total,
            'bukuTersedia' => $buku->tersedia,
            'peminjamanCount' => $buku->dipinjam,
            'pembacaCount' => $pembacaCount,
            'ulasans' => $ulasans,
            'sudahMembaca' => $sudahMembaca,
            'sudahMemberiBulasan' => $sudahMemberiBulasan,
            'hasBorrowedAndReturned' => $hasBorrowedAndReturned,
            'inKoleksi' => $inKoleksi
        ]);
    }

    /**
     * Memeriksa apakah nilai ISBN merupakan placeholder (misal: "dalam proses", "proses", "-")
     * Placeholder diizinkan sama untuk lebih dari 1 buku, sedangkan ISBN riil wajib unik.
     */
    public static function isPlaceholderIsbn(?string $isbn): bool
    {
        if (empty($isbn)) {
            return false;
        }
        $normalized = strtolower(trim($isbn));
        return in_array($normalized, [
            'dalam proses',
            'proses',
            'sedang proses',
            '-',
            'belum ada',
            'tidak ada',
            'n/a',
            'none',
        ]) || str_contains($normalized, 'proses');
    }

    public function store(Request $request)
    {
        $isbnRules = ['required', 'string', 'max:50'];
        if (!static::isPlaceholderIsbn($request->isbn)) {
            $isbnRules[] = 'unique:buku,isbn';
        }

        $this->validate($request, [
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4|integer|min:1000|max:'.(date('Y')+1),
            'isbn' => $isbnRules,
            'deskripsi' => 'nullable|string',
            'kategoris' => 'required|exists:kategoribuku,KategoriID',
            'jumlah_total' => 'required|integer|min:0',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'isbn.unique' => 'ISBN ini sudah terdaftar untuk buku lain. Jika buku belum memiliki ISBN resmi, Anda dapat mengisi "dalam proses".',
        ]);

        $data = $request->only(['judul','penulis','penerbit','tahun_terbit','isbn','deskripsi','jumlah_total']);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $buku = Buku::create($data);

        if ($request->has('kategoris') && $request->kategoris) {
            $buku->kategoris()->attach($request->kategoris);
        }

        $route = 'dashboardpetugas';
        if (Auth::check() && Auth::user()->role === 'admin') {
            $route = 'dashboardadmin';
        }

        return redirect()->route($route)->with('success', 'Buku berhasil ditambahkan');
    }

    public function edit($id)
    {
        $buku = Buku::with('kategoris')->findOrFail($id);
        $kategoris = \App\Models\KategoriBuku::all();
        return view('books.edit', compact('buku', 'kategoris'));
    }

    public function edit2($id)
    {
        return $this->edit($id);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::findOrFail($id);

        $isbnRules = ['required', 'string', 'max:50'];
        if (!static::isPlaceholderIsbn($request->isbn)) {
            $isbnRules[] = 'unique:buku,isbn,' . $buku->BukuID . ',BukuID';
        }

        $this->validate($request, [
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|digits:4|integer|min:1000|max:'.(date('Y')+1),
            'isbn' => $isbnRules,
            'deskripsi' => 'nullable|string',
            'kategoris' => 'required|exists:kategoribuku,KategoriID',
            'jumlah_total' => 'required|integer|min:0',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'isbn.unique' => 'ISBN ini sudah terdaftar untuk buku lain. Jika buku belum memiliki ISBN resmi, Anda dapat mengisi "dalam proses".',
        ]);

        // Validasi: jumlah_total tidak boleh di bawah jumlah eksemplar yang sedang dipinjam
        if ($request->jumlah_total < $buku->dipinjam) {
            return back()->withErrors([
                'jumlah_total' => "Jumlah total ({$request->jumlah_total}) tidak boleh kurang dari jumlah buku yang sedang dipinjam ({$buku->dipinjam} eksemplar)."
            ])->withInput();
        }

        $data = $request->only(['judul','penulis','penerbit','tahun_terbit','isbn','deskripsi','jumlah_total']);

        if ($request->hasFile('cover')) {
            if ($buku->cover && $buku->cover !== 'covers/default.png' && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $buku->update($data);

        if ($request->has('kategoris') && $request->kategoris) {
            $buku->kategoris()->sync([$request->kategoris]);
        }

        $route = 'dashboardpetugas';
        if (Auth::check() && Auth::user()->role === 'admin') {
            $route = 'dashboardadmin';
        }

        return redirect()->route($route)->with('success', 'Buku berhasil diperbarui');
    }

    public function destroy($id)
    {
        $buku = Buku::findOrFail($id);

        if ($buku->cover && $buku->cover !== 'covers/default.png' && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        if (method_exists($buku, 'kategoris')) {
            $buku->kategoris()->detach();
        }

        $buku->delete();

        $route = 'dashboardpetugas';
        if (Auth::check() && Auth::user()->role === 'admin') {
            $route = 'dashboardadmin';
        }

        return redirect()->route($route)->with('success', 'Buku berhasil dihapus');
    }
}
