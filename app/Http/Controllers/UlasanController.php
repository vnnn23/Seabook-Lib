<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ulasan;
use App\Models\Peminjaman;
use App\Models\Buku;
use Illuminate\Support\Facades\Auth;

class UlasanController extends Controller
{
    // Store ulasan (rating dan review)
    public function store(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:buku,BukuID',
            'rating' => 'required|integer|min:1|max:5',
            'isi_ulasan' => 'required|string|min:10|max:1000',
        ]);

        // Cek apakah user sudah membaca buku (status dikembalikan)
        $sudahMembaca = Peminjaman::where('UserID', Auth::id())
            ->where('BukuID', $request->buku_id)
            ->where('statuspeminjaman', 'dikembalikan')
            ->exists();

        if (!$sudahMembaca) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya bisa memberikan ulasan setelah membaca dan mengembalikan buku.'
                ], 403);
            }
            return back()->with('error', 'Anda hanya bisa memberikan ulasan setelah membaca dan mengembalikan buku.');
        }

        // Cek apakah user sudah pernah memberikan ulasan untuk buku ini
        $ulasanExists = Ulasan::where('UserID', Auth::id())
            ->where('BukuID', $request->buku_id)
            ->exists();

        if ($ulasanExists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memberikan ulasan untuk buku ini.'
                ], 400);
            }
            return back()->with('error', 'Anda sudah memberikan ulasan untuk buku ini.');
        }

        // Buat ulasan baru
        Ulasan::create([
            'UserID' => Auth::id(),
            'BukuID' => $request->buku_id,
            'rating' => $request->rating,
            'isi_ulasan' => $request->isi_ulasan,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil ditambahkan.'
            ], 200);
        }

        return back()->with('success', 'Ulasan berhasil ditambahkan.');
    }

    // Admin: list all ulasan
    public function index()
    {
        $ulasans = Ulasan::with(['user', 'buku'])->orderBy('created_at', 'desc')->get();
        return view('ulasan.index', compact('ulasans'));
    }

    // Update ulasan
    public function update(Request $request, $id)
    {
        $ulasan = Ulasan::findOrFail($id);

        // Cek apakah user adalah pemilik ulasan
        if ($ulasan->UserID !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'isi_ulasan' => 'required|string|min:10|max:1000',
        ]);

        $ulasan->update([
            'rating' => $request->rating,
            'isi_ulasan' => $request->isi_ulasan,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil diperbarui.'
            ], 200);
        }

        return back()->with('success', 'Ulasan berhasil diperbarui.');
    }

    // Delete ulasan
    public function destroy($id)
    {
        $ulasan = Ulasan::findOrFail($id);

        // Cek apakah user adalah pemilik ulasan atau admin
        if ($ulasan->UserID !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $ulasan->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
