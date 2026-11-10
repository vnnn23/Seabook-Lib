<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatPercakapan;
use App\Models\ChatPesan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffChatController extends Controller
{
    /**
     * Tampilan Inbox Chat untuk Petugas & Admin
     */
    public function inbox(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->isAdmin();

        // 1. Antrean Menunggu Petugas
        $menungguList = ChatPercakapan::with(['user', 'pesanTerbaru'])
            ->where('status', ChatPercakapan::STATUS_MENUNGGU)
            ->withCount(['pesan as unread_count' => function ($q) {
                $q->where('pengirim_tipe', ChatPesan::TIPE_USER)->whereNull('dibaca_pada');
            }])
            ->orderBy('terakhir_aktivitas', 'desc')
            ->get();

        // 2. Percakapan Aktif
        $aktifQuery = ChatPercakapan::with(['user', 'petugas', 'pesanTerbaru'])
            ->where('status', ChatPercakapan::STATUS_AKTIF)
            ->withCount(['pesan as unread_count' => function ($q) {
                $q->where('pengirim_tipe', ChatPesan::TIPE_USER)->whereNull('dibaca_pada');
            }]);

        if (!$isAdmin) {
            // Petugas biasa hanya melihat percakapan yang ia tangani
            $aktifQuery->where('petugas_id', $currentUser->UserID);
        }
        $aktifList = $aktifQuery->orderBy('terakhir_aktivitas', 'desc')->get();

        // 3. Riwayat Selesai
        $selesaiQuery = ChatPercakapan::with(['user', 'petugas', 'pesanTerbaru'])
            ->where('status', ChatPercakapan::STATUS_SELESAI);
        if (!$isAdmin) {
            $selesaiQuery->where('petugas_id', $currentUser->UserID);
        }
        $selesaiList = $selesaiQuery->orderBy('terakhir_aktivitas', 'desc')->take(20)->get();

        // 4. Percakapan Terpilih
        $selectedId = $request->get('id');
        $selectedPercakapan = null;
        if ($selectedId) {
            $selectedPercakapan = ChatPercakapan::with(['user', 'petugas', 'pesan.pengirim'])->find($selectedId);

            if ($selectedPercakapan) {
                // Tandai pesan user sebagai dibaca
                ChatPesan::where('percakapan_id', $selectedPercakapan->id)
                    ->where('pengirim_tipe', ChatPesan::TIPE_USER)
                    ->whereNull('dibaca_pada')
                    ->update(['dibaca_pada' => Carbon::now('Asia/Jakarta')]);
            }
        }

        return view('chat.inbox', compact('menungguList', 'aktifList', 'selesaiList', 'selectedPercakapan', 'isAdmin'));
    }

    /**
     * Ambil percakapan yang sedang menunggu (Concurrency Safe with lockForUpdate)
     */
    public function take(Request $request, $id)
    {
        $currentUser = Auth::user();

        try {
            $result = DB::transaction(function () use ($id, $currentUser) {
                $percakapan = ChatPercakapan::where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Pastikan percakapan masih dalam status 'menunggu' dan belum ada petugas yang mengambil
                if ($percakapan->status !== ChatPercakapan::STATUS_MENUNGGU || !is_null($percakapan->petugas_id)) {
                    return [
                        'success' => false,
                        'message' => 'Percakapan ini sudah diambil oleh petugas lain atau tidak lagi menunggu.',
                    ];
                }

                $percakapan->update([
                    'petugas_id' => $currentUser->UserID,
                    'status' => ChatPercakapan::STATUS_AKTIF,
                    'mode' => ChatPercakapan::MODE_PETUGAS,
                    'terakhir_aktivitas' => Carbon::now('Asia/Jakarta'),
                ]);

                // Kirim pesan perkenalan dari petugas
                $tipePengirim = $currentUser->isAdmin() ? ChatPesan::TIPE_ADMIN : ChatPesan::TIPE_PETUGAS;
                ChatPesan::create([
                    'percakapan_id' => $percakapan->id,
                    'pengirim_id' => $currentUser->UserID,
                    'pengirim_tipe' => $tipePengirim,
                    'isi' => "Halo! Saya {$currentUser->nama} dari tim layanan " . config('app.name', 'SeaBook') . " siap membantu Anda.",
                ]);

                return [
                    'success' => true,
                    'message' => 'Percakapan berhasil diambil.',
                    'percakapan_id' => $percakapan->id,
                ];
            });

            if (!$result['success']) {
                if ($request->wantsJson()) {
                    return response()->json($result, 409);
                }
                return redirect()->route('chat.inbox')->with('error', $result['message']);
            }

            if ($request->wantsJson()) {
                return response()->json($result);
            }
            return redirect()->route('chat.inbox', ['id' => $id])->with('success', $result['message']);

        } catch (\Exception $e) {
            \Log::error('Chat Take Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil percakapan.'], 500);
            }
            return redirect()->route('chat.inbox')->with('error', 'Gagal mengambil percakapan.');
        }
    }

    /**
     * Kirim balasan dari Petugas / Admin ke pemustaka
     */
    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'pesan' => 'required|string|max:500',
        ], [
            'pesan.required' => 'Pesan tidak boleh kosong.',
            'pesan.max' => 'Panjang pesan maksimal 500 karakter.',
        ]);

        $currentUser = Auth::user();
        $percakapan = ChatPercakapan::findOrFail($id);

        // Otorisasi: Petugas hanya boleh membalas percakapan yang diambilnya, Admin boleh semua
        if (!$currentUser->isAdmin() && $percakapan->petugas_id !== $currentUser->UserID) {
            abort(403, 'Anda bukan petugas yang menangani percakapan ini.');
        }

        if ($percakapan->status === ChatPercakapan::STATUS_SELESAI) {
            return redirect()->back()->with('error', 'Percakapan ini sudah selesai.');
        }

        $tipePengirim = $currentUser->isAdmin() ? ChatPesan::TIPE_ADMIN : ChatPesan::TIPE_PETUGAS;

        $pesan = ChatPesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim_id' => $currentUser->UserID,
            'pengirim_tipe' => $tipePengirim,
            'isi' => trim($validated['pesan']),
        ]);

        $percakapan->update([
            'terakhir_aktivitas' => Carbon::now('Asia/Jakarta'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $pesan->id,
                    'pengirim_tipe' => $pesan->pengirim_tipe,
                    'isi' => $pesan->isi,
                    'waktu' => $pesan->created_at->format('H:i'),
                ],
            ]);
        }

        return redirect()->route('chat.inbox', ['id' => $id]);
    }

    /**
     * Tandai percakapan sebagai selesai
     */
    public function close(Request $request, $id)
    {
        $currentUser = Auth::user();
        $percakapan = ChatPercakapan::findOrFail($id);

        if (!$currentUser->isAdmin() && $percakapan->petugas_id !== $currentUser->UserID) {
            abort(403, 'Akses ditolak.');
        }

        $percakapan->update([
            'status' => ChatPercakapan::STATUS_SELESAI,
            'mode' => ChatPercakapan::MODE_AI, // Kembalikan ke mode AI untuk sesi berikutnya
            'terakhir_aktivitas' => Carbon::now('Asia/Jakarta'),
        ]);

        $appName = config('app.name', 'SeaBook');
        // Pesan penutup otomatis
        ChatPesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim_id' => null,
            'pengirim_tipe' => ChatPesan::TIPE_AI,
            'isi' => "Sesi percakapan bersama petugas telah diselesaikan. Terima kasih telah menghubungi layanan {$appName}! Jika ada pertanyaan lain, Anda dapat bertanya kembali kepada Asisten AI kami.",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Percakapan telah ditandai selesai.']);
        }

        return redirect()->route('chat.inbox')->with('success', 'Percakapan berhasil diselesaikan.');
    }

    /**
     * Polling pesan baru pada detail percakapan petugas
     */
    public function pollConversation(Request $request, $id)
    {
        $currentUser = Auth::user();
        $percakapan = ChatPercakapan::findOrFail($id);

        if (!$currentUser->isAdmin() && $percakapan->petugas_id !== $currentUser->UserID && $percakapan->status !== ChatPercakapan::STATUS_MENUNGGU) {
            abort(403, 'Akses ditolak.');
        }

        $lastId = (int) $request->get('after_id', 0);

        $newMessages = ChatPesan::where('percakapan_id', $percakapan->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        // Tandai pesan user sebagai dibaca
        ChatPesan::where('percakapan_id', $percakapan->id)
            ->where('pengirim_tipe', ChatPesan::TIPE_USER)
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => Carbon::now('Asia/Jakarta')]);

        return response()->json([
            'success' => true,
            'status' => $percakapan->status,
            'messages' => $newMessages->map(function ($p) {
                return [
                    'id' => $p->id,
                    'pengirim_tipe' => $p->pengirim_tipe,
                    'pengirim_id' => $p->pengirim_id,
                    'isi' => $p->isi,
                    'waktu' => $p->created_at ? $p->created_at->format('H:i') : '',
                    'is_staff' => $p->isFromStaff(),
                ];
            }),
        ]);
    }

    /**
     * Polling daftar antrean dan percakapan aktif untuk Petugas & Admin.
     * Petugas HANYA menerima percakapan antrean menunggu dan percakapan aktif yang ditangani dirinya sendiri.
     * Admin menerima seluruh percakapan aktif.
     */
    public function pollInbox(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->isAdmin();

        // 1. Antrean Menunggu Petugas
        $menungguList = ChatPercakapan::with(['user', 'pesanTerbaru'])
            ->where('status', ChatPercakapan::STATUS_MENUNGGU)
            ->withCount(['pesan as unread_count' => function ($q) {
                $q->where('pengirim_tipe', ChatPesan::TIPE_USER)->whereNull('dibaca_pada');
            }])
            ->orderBy('terakhir_aktivitas', 'desc')
            ->get();

        // 2. Percakapan Aktif (Petugas hanya melihat miliknya sendiri)
        $aktifQuery = ChatPercakapan::with(['user', 'petugas', 'pesanTerbaru'])
            ->where('status', ChatPercakapan::STATUS_AKTIF)
            ->withCount(['pesan as unread_count' => function ($q) {
                $q->where('pengirim_tipe', ChatPesan::TIPE_USER)->whereNull('dibaca_pada');
            }]);

        if (!$isAdmin) {
            $aktifQuery->where('petugas_id', $currentUser->UserID);
        }
        $aktifList = $aktifQuery->orderBy('terakhir_aktivitas', 'desc')->get();

        $formatter = function ($c) {
            return [
                'id' => $c->id,
                'status' => $c->status,
                'user_nama' => $c->user->nama ?? 'Pengguna',
                'user_id' => $c->UserID,
                'petugas_id' => $c->petugas_id,
                'petugas_nama' => $c->petugas->nama ?? null,
                'last_message' => $c->pesanTerbaru ? \Illuminate\Support\Str::limit($c->pesanTerbaru->isi, 45) : '',
                'last_time' => $c->terakhir_aktivitas ? $c->terakhir_aktivitas->format('H:i') : '',
                'unread_count' => (int) $c->unread_count,
            ];
        };

        return response()->json([
            'success' => true,
            'counts' => [
                'menunggu' => $menungguList->count(),
                'aktif' => $aktifList->count(),
            ],
            'menunggu' => $menungguList->map($formatter),
            'aktif' => $aktifList->map($formatter),
        ]);
    }
}

