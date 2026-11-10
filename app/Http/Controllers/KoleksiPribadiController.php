<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KoleksiPribadi;
use App\Models\Buku;
use Illuminate\Support\Facades\Auth;

class KoleksiPribadiController extends Controller
{
    /**
     * Show user's personal collection
     */
    public function index()
    {
        $koleksis = KoleksiPribadi::where('UserID', Auth::id())
            ->with('buku')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('koleksipribadi', ['koleksis' => $koleksis]);
    }

    /**
     * Add book to personal collection
     */
    public function store(Request $request)
    {
        $request->validate([
            'BukuID' => 'required|exists:buku,BukuID',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            // Check if book already in collection
            $exists = KoleksiPribadi::where('UserID', Auth::id())
                ->where('BukuID', $request->BukuID)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Buku sudah ada di koleksi pribadi Anda.');
            }

            KoleksiPribadi::create([
                'UserID' => Auth::id(),
                'BukuID' => $request->BukuID,
                'catatan' => $request->catatan,
            ]);

            return back()->with('success', 'Buku berhasil ditambahkan ke koleksi pribadi!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove book from personal collection
     */
    public function destroy($koleksiId)
    {
        try {
            $koleksi = KoleksiPribadi::findOrFail($koleksiId);

            // Check if user owns this collection
            if ($koleksi->UserID !== Auth::id()) {
                return back()->with('error', 'Anda tidak memiliki akses ke koleksi ini.');
            }

            $koleksi->delete();

            return back()->with('success', 'Buku dihapus dari koleksi pribadi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove book by BukuID from personal collection
     */
    public function destroyByBuku($bukuId)
    {
        try {
            $koleksi = KoleksiPribadi::where('UserID', Auth::id())
                ->where('BukuID', $bukuId)
                ->firstOrFail();

            $koleksi->delete();

            return back()->with('success', 'Buku dihapus dari koleksi pribadi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Check if book is in user's collection
     */
    public function isInCollection($bukuId)
    {
        if (!Auth::check()) {
            return response()->json(['inCollection' => false]);
        }

        $inCollection = KoleksiPribadi::where('UserID', Auth::id())
            ->where('BukuID', $bukuId)
            ->exists();

        return response()->json(['inCollection' => $inCollection]);
    }
}
