<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Buku;
use App\Models\User;
use App\Models\PoinPelanggaranLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjaman = Peminjaman::with('user', 'buku')->orderBy('PeminjamanID', 'desc')->get();
        return view('peminjaman.index', compact('peminjaman'));
    }

    public function create($bukuId)
    {
        $buku = Buku::findOrFail($bukuId);
        return view('peminjaman.create', compact('buku'));
    }

    /**
     * Simpan pengajuan peminjaman baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:buku,BukuID',
            'tanggal_pinjam' => 'required',
            'tanggal_kembali' => 'required',
        ]);

        $user = Auth::user();

        // 0. Cek validasi pemblokiran & status akun
        if ($user->isBlockedFromBorrowing()) {
            $msg = ($user->isPending() || $user->isDitolak())
                ? "Akun Anda belum disetujui atau dinonaktifkan. Silakan hubungi admin perpustakaan."
                : "Akun Anda diblokir dari peminjaman buku karena memiliki pinjaman aktif yang melewati tanggal jatuh tempo (terlambat). Harap kembalikan buku tersebut terlebih dahulu.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        // Cek apakah ada transaksi peminjaman aktif yang sudah melewati tenggat waktu (terlambat)
        $hasOverdueLoan = Peminjaman::where('UserID', $user->UserID)
            ->whereIn('statuspeminjaman', [
                Peminjaman::STATUS_DIPINJAM,
                Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
            ])
            ->where('tanggal_kembali', '<', now())
            ->exists();

        if ($hasOverdueLoan) {
            $msg = 'Peminjaman diblokir: Anda memiliki peminjaman buku yang telah melewati batas tanggal pengembalian (terlambat). Kembalikan buku tersebut terlebih dahulu sebelum mengajukan peminjaman baru.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        // 1. Cek batas kuota peminjaman aktif per anggota (default 3)
        $maxActive = (int) config('perpustakaan.max_active_loans', 3);
        $activeLoansCount = Peminjaman::where('UserID', $user->UserID)
            ->whereIn('statuspeminjaman', Peminjaman::STATUS_AKTIF_LOAN)
            ->count();

        if ($activeLoansCount >= $maxActive) {
            $msg = "Anda telah mencapai batas maksimal peminjaman aktif ({$maxActive} buku). Harap selesaikan peminjaman sebelumnya terlebih dahulu.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        // 2. Larangan meminjam buku yang sama bila masih memiliki transaksi peminjaman aktif untuk buku tersebut
        $hasActiveBook = Peminjaman::where('UserID', $user->UserID)
            ->where('BukuID', $request->buku_id)
            ->whereIn('statuspeminjaman', Peminjaman::STATUS_AKTIF_LOAN)
            ->exists();

        if ($hasActiveBook) {
            $msg = 'Anda masih memiliki peminjaman aktif untuk buku ini. Anda tidak dapat meminjam kembali buku yang sama sebelum mengembalikannya.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        $rawPinjam = trim((string) $request->tanggal_pinjam);
        $rawKembali = trim((string) $request->tanggal_kembali);
        $isDateOnly = strlen($rawPinjam) <= 10;

        try {
            $tanggalPinjam = Carbon::parse($rawPinjam);
            $tanggalKembali = Carbon::parse($rawKembali);

            if ($isDateOnly) {
                // Jika input tanggal saja (tanpa jam), atur jam default yang aman
                if ($tanggalPinjam->isToday()) {
                    $tanggalPinjam = Carbon::now();
                } else {
                    $tanggalPinjam = $tanggalPinjam->setTime(9, 0, 0);
                }
                $tanggalKembali = $tanggalKembali->setTime(16, 0, 0);
            }
        } catch (\Exception $e) {
            $msg = 'Format tanggal peminjaman tidak valid.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }
        $now = Carbon::now();

        // 3. Validasi tanggal pinjam tidak boleh di masa lalu
        if ($isDateOnly) {
            if ($tanggalPinjam->copy()->startOfDay()->lt($now->copy()->startOfDay())) {
                $msg = 'Tanggal pinjam tidak boleh di masa lalu. Pilih tanggal hari ini atau di kemudian hari.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg)->withInput();
            }
        } else {
            if ($tanggalPinjam->lt($now->copy()->subMinutes(10))) {
                $msg = 'Tanggal pinjam tidak boleh di masa lalu. Pilih tanggal dan waktu sekarang atau di kemudian hari.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg)->withInput();
            }
        }

        // 4. Validasi jam operasional dari config (08:00 - 17:00) HANYA jika waktu disertakan eksplisit
        if (!$isDateOnly) {
            $jamBuka = (int) config('perpustakaan.jam_buka', 8);
            $jamTutup = (int) config('perpustakaan.jam_tutup', 17);

            if ($tanggalPinjam->hour < $jamBuka || $tanggalPinjam->hour >= $jamTutup) {
                $msg = "Peminjaman hanya bisa dilakukan pada jam operasional (" . sprintf('%02d:00', $jamBuka) . " - " . sprintf('%02d:00', $jamTutup) . ").";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg)->withInput();
            }

            if ($tanggalKembali->hour < $jamBuka || $tanggalKembali->hour >= $jamTutup) {
                $msg = "Pengembalian hanya bisa dilakukan pada jam operasional (" . sprintf('%02d:00', $jamBuka) . " - " . sprintf('%02d:00', $jamTutup) . ").";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg)->withInput();
            }
        }

        if ($tanggalKembali->lte($tanggalPinjam)) {
            $msg = 'Tanggal pengembalian harus lebih maju dari tanggal pinjam.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        // 5. Validasi durasi maksimal dari config (30 hari)
        $maxDurasi = (int) config('perpustakaan.durasi_maksimal_hari', 30);
        $durasiHari = (int) ceil($tanggalKembali->diffInSeconds($tanggalPinjam) / 86400);
        if ($durasiHari > $maxDurasi) {
            $msg = "Durasi peminjaman maksimal {$maxDurasi} hari. Durasi yang Anda pilih adalah {$durasiHari} hari.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        // 6. DB Transaction dengan lockForUpdate pada baris buku & verifikasi ulang ketersediaan
        return DB::transaction(function () use ($request, $user, $tanggalPinjam, $tanggalKembali, $durasiHari) {
            $buku = Buku::where('BukuID', $request->buku_id)->lockForUpdate()->firstOrFail();

            if ($buku->tersedia <= 0) {
                $msg = 'Eksemplar buku sedang tidak tersedia saat ini.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg)->withInput();
            }

            Peminjaman::create([
                'UserID' => $user->UserID,
                'BukuID' => $buku->BukuID,
                'tanggal_pinjam' => $tanggalPinjam,
                'tanggal_kembali' => $tanggalKembali,
                'statuspeminjaman' => Peminjaman::STATUS_MENUNGGU,
                'durasi' => max(1, $durasiHari),
            ]);

            $successMsg = 'Peminjaman berhasil diajukan dan sedang menunggu persetujuan petugas.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $successMsg], 200);
            }
            return redirect()->route('peminjaman.riwayat')->with('success', $successMsg);
        });
    }

    /**
     * Konfirmasi peminjaman oleh admin/petugas
     * Whitelist: hanya dari 'menunggu persetujuan'
     */
    public function confirm($id)
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('PeminjamanID', $id)->lockForUpdate()->firstOrFail();

            if ($peminjaman->statuspeminjaman !== Peminjaman::STATUS_MENUNGGU) {
                return back()->with('error', 'Status peminjaman tidak valid untuk disetujui (hanya status menunggu persetujuan).');
            }

            $buku = Buku::where('BukuID', $peminjaman->BukuID)->lockForUpdate()->firstOrFail();
            if ($buku->tersedia <= 0) {
                return back()->with('error', 'Eksemplar buku sudah tidak tersedia untuk disetujui.');
            }

            // Generate kode QR unik dengan prefix dari config dan pencegahan bentrok
            $prefix = config('perpustakaan.qr_prefix', 'SYB-');
            do {
                $uniqueCode = $prefix . strtoupper(Str::random(10));
            } while (Peminjaman::where('qr_code', $uniqueCode)->exists());

            $peminjaman->statuspeminjaman = Peminjaman::STATUS_DISETUJUI;
            $peminjaman->confirmed_by = Auth::id();
            $peminjaman->qr_code = $uniqueCode;
            $peminjaman->save();

            return back()->with('success', 'Peminjaman disetujui. QR Code pengambilan telah diterbitkan.');
        });
    }

    /**
     * Tolak peminjaman oleh admin/petugas
     * Whitelist: hanya dari 'menunggu persetujuan'
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->statuspeminjaman !== Peminjaman::STATUS_MENUNGGU) {
            return back()->with('error', 'Status peminjaman tidak valid untuk ditolak.');
        }

        $peminjaman->statuspeminjaman = Peminjaman::STATUS_DITOLAK;
        $peminjaman->alasan_penolakan = $request->input('alasan_penolakan');
        $peminjaman->confirmed_by = Auth::id();
        $peminjaman->save();

        return back()->with('success', 'Peminjaman berhasil ditolak.');
    }

    /**
     * Halaman scan QR code pengambilan buku
     */
    public function scanQrPage()
    {
        return view('peminjaman.scan');
    }

    /**
     * Scan QR code untuk pengambilan buku
     * Whitelist: hanya dari 'disetujui'
     */
    public function scan(Request $request)
    {
        $request->validate([
            'kode_peminjaman' => 'required|string',
        ]);

        $peminjaman = Peminjaman::where('qr_code', $request->kode_peminjaman)->first();

        if (!$peminjaman) {
            return back()->with('error', 'Kode peminjaman tidak ditemukan.');
        }

        if ($peminjaman->statuspeminjaman !== Peminjaman::STATUS_DISETUJUI) {
            return back()->with('error', 'Status peminjaman tidak valid untuk pengambilan (hanya status disetujui yang dapat diambil).');
        }

        // Cek apakah masa pengambilan telah kadaluarsa (default 24 jam)
        $pickupExpiryHours = (int) config('perpustakaan.pickup_expiry_hours', 24);
        $threshold = Carbon::now()->subHours($pickupExpiryHours);
        if ($peminjaman->updated_at && $peminjaman->updated_at->lte($threshold)) {
            $peminjaman->statuspeminjaman = Peminjaman::STATUS_KADALUARSA;
            $peminjaman->save();
            return back()->with('error', 'Batas waktu pengambilan buku telah kadaluarsa (' . $pickupExpiryHours . ' jam). Peminjaman dibatalkan.');
        }

        $peminjaman->statuspeminjaman = Peminjaman::STATUS_DIPINJAM;
        $peminjaman->save();

        return back()->with('success', 'Buku berhasil diambil. Status peminjaman diubah menjadi sedang dipinjam.');
    }

    /**
     * Riwayat peminjaman user yang sedang login
     */
    public function riwayat()
    {
        $peminjaman = Peminjaman::where('UserID', Auth::id())
            ->with('buku')
            ->orderBy('PeminjamanID', 'desc')
            ->get();

        return view('peminjaman.riwayat', compact('peminjaman'));
    }

    /**
     * Pengajuan pengembalian oleh user pemilik
     * Whitelist: hanya dari 'sedang dipinjam' dan milik user sendiri
     */
    public function kembalikan($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        // Verifikasi kepemilikan (bandingkan int)
        if ((int) $peminjaman->UserID != (int) Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki hak untuk mengajukan pengembalian peminjaman ini.');
        }

        if ($peminjaman->statuspeminjaman !== Peminjaman::STATUS_DIPINJAM) {
            return back()->with('error', 'Hanya peminjaman dengan status sedang dipinjam yang dapat diajukan pengembalian.');
        }

        $peminjaman->statuspeminjaman = Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN;
        $peminjaman->save();

        return back()->with('success', 'Pengajuan pengembalian berhasil dikirim. Silakan serahkan buku fisik ke meja petugas.');
    }

    /**
     * Pembatalan oleh user pemilik
     * Whitelist: hanya dari 'menunggu persetujuan' atau 'disetujui'
     */
    public function cancelByUser($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ((int) $peminjaman->UserID != (int) Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki hak untuk membatalkan peminjaman ini.');
        }

        if (!in_array($peminjaman->statuspeminjaman, [Peminjaman::STATUS_MENUNGGU, Peminjaman::STATUS_DISETUJUI])) {
            return back()->with('error', 'Peminjaman tidak dapat dibatalkan pada status ini.');
        }

        $peminjaman->statuspeminjaman = Peminjaman::STATUS_DIBATALKAN;
        $peminjaman->save();

        return back()->with('success', 'Peminjaman berhasil dibatalkan.');
    }

    /**
     * Pembatalan oleh petugas/admin
     * Whitelist: dari 'menunggu persetujuan' atau 'disetujui'
     */
    public function cancel($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if (!in_array($peminjaman->statuspeminjaman, [Peminjaman::STATUS_MENUNGGU, Peminjaman::STATUS_DISETUJUI])) {
            return back()->with('error', 'Status peminjaman tidak dapat dibatalkan.');
        }

        $peminjaman->statuspeminjaman = Peminjaman::STATUS_DIBATALKAN;
        $peminjaman->save();

        return back()->with('success', 'Peminjaman berhasil dibatalkan.');
    }

    /**
     * Konfirmasi pengembalian buku oleh petugas/admin
     * Mendukung 2 jalur:
     * 1. Dari pengajuan user (menunggu konfirmasi pengembalian)
     * 2. Langsung oleh petugas (sedang dipinjam)
     */
    public function confirmReturn(Request $request, $id)
    {
        // Opsi: Belum diterima / Tolak pengembalian
        if ($request->input('aksi') === 'tolak' || $request->has('belum_diterima')) {
            $peminjaman = Peminjaman::findOrFail($id);
            if ($peminjaman->statuspeminjaman === Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN) {
                $peminjaman->statuspeminjaman = Peminjaman::STATUS_DIPINJAM;
                $peminjaman->catatan_petugas = $request->input('catatan_petugas', 'Buku fisik belum diserahkan ke meja petugas.');
                $peminjaman->save();

                return back()->with('info', 'Status peminjaman dikembalikan ke sedang dipinjam karena buku belum diterima.');
            }
            return back()->with('error', 'Status peminjaman tidak valid untuk penolakan pengembalian.');
        }

        return DB::transaction(function () use ($request, $id) {
            $peminjaman = Peminjaman::where('PeminjamanID', $id)->lockForUpdate()->firstOrFail();

            // Whitelist status asal: hanya dari 'sedang dipinjam' atau 'menunggu konfirmasi pengembalian'
            if (!in_array($peminjaman->statuspeminjaman, [
                Peminjaman::STATUS_DIPINJAM,
                Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
            ])) {
                return back()->with('error', 'Status peminjaman tidak valid untuk konfirmasi pengembalian.');
            }

            $buku = Buku::where('BukuID', $peminjaman->BukuID)->lockForUpdate()->firstOrFail();

            $now = Carbon::now();
            $peminjaman->tanggal_kembali_aktual = $now;

            // Hitung keterlambatan (detik / 86400 dibulatkan ke atas dengan ceil)
            $hariTerlambat = 0;
            if ($now->gt($peminjaman->tanggal_kembali)) {
                $selisihDetik = $now->diffInSeconds($peminjaman->tanggal_kembali);
                $hariTerlambat = (int) ceil($selisihDetik / 86400);
            }
            $peminjaman->hari_terlambat = $hariTerlambat;

            $kondisi = $request->input('kondisi_buku', Peminjaman::KONDISI_BAIK);
            $peminjaman->kondisi_buku = $kondisi;
            $peminjaman->catatan_petugas = $request->input('catatan_petugas');
            $peminjaman->confirmed_by = Auth::id();

            // Jika kondisi buku 'hilang' atau 'rusak berat', kurangi jumlah_total buku
            // Jaga tidak pernah di bawah 0 dan tidak di bawah jumlah eksemplar yang masih dipinjam lainnya
            if (in_array($kondisi, [Peminjaman::KONDISI_HILANG, Peminjaman::KONDISI_RUSAK_BERAT])) {
                $masihDipinjamLain = Peminjaman::where('BukuID', $buku->BukuID)
                    ->where('PeminjamanID', '!=', $peminjaman->PeminjamanID)
                    ->whereIn('statuspeminjaman', Peminjaman::STATUS_HOLDING_BOOK)
                    ->count();

                $buku->jumlah_total = max($masihDipinjamLain, max(0, $buku->jumlah_total - 1));
                $buku->save();
                Log::info("Jumlah total buku ID {$buku->BukuID} berkurang menjadi {$buku->jumlah_total} karena kondisi {$kondisi}.");
            }

            // Hitung dan catat poin pelanggaran (Fase 4: Sistem Poin Pelanggaran)
            $this->prosesPoinPelanggaran($peminjaman, $hariTerlambat, $kondisi);

            $peminjaman->statuspeminjaman = Peminjaman::STATUS_DIKEMBALIKAN;
            $peminjaman->save();

            return back()->with('success', 'Pengembalian buku berhasil diverifikasi.');
        });
    }

    /**
     * Pencatatan poin pelanggaran & penalti (Ditiadakan sesuai instruksi)
     */
    protected function prosesPoinPelanggaran(Peminjaman $peminjaman, int $hariTerlambat, ?string $kondisi): void
    {
        // Sistem poin pelanggaran dinonaktifkan
        $peminjaman->poin_denda = 0;
    }

    public function adminIndex()
    {
        $peminjaman = Peminjaman::with('user', 'buku')->orderBy('PeminjamanID', 'desc')->get();
        return view('dashboardadmin4', compact('peminjaman'));
    }

    public function getPeminjamanByCode(Request $request)
    {
        $kode = $request->input('kode_peminjaman');
        $peminjaman = Peminjaman::with('user', 'buku')->where('qr_code', $kode)->first();

        if ($peminjaman) {
            return response()->json([
                'nama' => $peminjaman->user->nama,
                'buku' => $peminjaman->buku->judul,
                'status' => $peminjaman->statuspeminjaman,
                'durasi' => $peminjaman->durasi . ' Hari / ' . $peminjaman->tanggal_pinjam->format('d M Y H:i') . ' - ' . $peminjaman->tanggal_kembali->format('d M Y H:i'),
            ]);
        }

        return response()->json(['error' => 'Kode tidak ditemukan'], 404);
    }

    public function exportPdf()
    {
        $peminjaman = Peminjaman::with('user', 'buku')->get();
        $pdf = \PDF::loadView('peminjaman.pdf', compact('peminjaman'));
        return $pdf->download('daftar_peminjaman.pdf');
    }
}
