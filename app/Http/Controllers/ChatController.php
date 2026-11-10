<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatPercakapan;
use App\Models\ChatPesan;
use App\Services\ChatbotService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Mengambil atau membuat percakapan aktif untuk user yang sedang login
     */
    public function getActiveConversation(Request $request)
    {
        $user = Auth::user();

        // Cari percakapan yang belum selesai
        $percakapan = ChatPercakapan::with(['petugas', 'pesan' => function ($q) {
            $q->orderBy('id', 'asc');
        }])
        ->where('UserID', $user->UserID)
        ->whereIn('status', [ChatPercakapan::STATUS_MENUNGGU, ChatPercakapan::STATUS_AKTIF])
        ->latest('id')
        ->first();

        if (!$percakapan) {
            $percakapan = ChatPercakapan::create([
                'UserID' => $user->UserID,
                'status' => ChatPercakapan::STATUS_AKTIF,
                'mode' => ChatPercakapan::MODE_AI,
                'terakhir_aktivitas' => Carbon::now('Asia/Jakarta'),
            ]);

            $appName = config('app.name', 'SeaBook');
            // Pesan sambutan awal dari AI
            ChatPesan::create([
                'percakapan_id' => $percakapan->id,
                'pengirim_id' => null,
                'pengirim_tipe' => ChatPesan::TIPE_AI,
                'isi' => "Halo {$user->nama}! Saya Asisten AI resmi Perpustakaan {$appName}. Ada yang bisa saya bantu terkait pencarian buku, ketersediaan, atau aturan peminjaman hari ini?",
            ]);

            $percakapan->load(['petugas', 'pesan']);
        }

        return response()->json([
            'success' => true,
            'percakapan' => [
                'id' => $percakapan->id,
                'status' => $percakapan->status,
                'mode' => $percakapan->mode,
                'petugas' => $percakapan->petugas ? [
                    'nama' => $percakapan->petugas->nama,
                    'role' => $percakapan->petugas->role,
                ] : null,
            ],
            'pesan' => $percakapan->pesan->map(function ($p) {
                return [
                    'id' => $p->id,
                    'pengirim_tipe' => $p->pengirim_tipe,
                    'isi' => $p->isi,
                    'waktu' => $p->created_at ? $p->created_at->format('H:i') : '',
                    'dibaca' => !is_null($p->dibaca_pada),
                ];
            }),
        ]);
    }

    /**
     * Mengirim pesan dari pemustaka (Mode AI atau Mode Petugas)
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'percakapan_id' => 'required|exists:chat_percakapan,id',
            'pesan' => 'required|string|max:500',
        ], [
            'pesan.required' => 'Pesan tidak boleh kosong.',
            'pesan.max' => 'Panjang pesan maksimal 500 karakter.',
        ]);

        $user = Auth::user();
        $percakapan = ChatPercakapan::findOrFail($validated['percakapan_id']);

        // Keamanan: Pastikan hanya pemilik percakapan yang bisa mengirim
        if ($percakapan->UserID !== $user->UserID) {
            abort(403, 'Anda tidak berwenang mengakses percakapan ini.');
        }

        $isiPesan = trim($validated['pesan']);

        // JIKA MODE AI: Cek batas kuota harian pesan AI per user
        if ($percakapan->mode === ChatPercakapan::MODE_AI) {
            $dailyLimit = (int) config('chatbot.daily_limit_per_user', 50);
            $todayAiCount = ChatPesan::whereHas('percakapan', function ($q) use ($user) {
                    $q->where('UserID', $user->UserID);
                })
                ->where('pengirim_tipe', ChatPesan::TIPE_USER)
                ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
                ->count();

            if ($todayAiCount >= $dailyLimit) {
                return response()->json([
                    'success' => false,
                    'message' => config('chatbot.messages.daily_limit_reached', 'Anda telah mencapai batas maksimal kuota harian pesan AI (' . $dailyLimit . ' pesan/hari). Silakan coba lagi besok atau gunakan mode Hubungi Petugas.'),
                ], 429);
            }
        }

        // Simpan pesan user
        $pesanUser = ChatPesan::create([
            'percakapan_id' => $percakapan->id,
            'pengirim_id' => $user->UserID,
            'pengirim_tipe' => ChatPesan::TIPE_USER,
            'isi' => $isiPesan,
        ]);

        $percakapan->update([
            'terakhir_aktivitas' => Carbon::now('Asia/Jakarta'),
        ]);

        // JIKA MODE AI: Bangun balasan AI
        if ($percakapan->mode === ChatPercakapan::MODE_AI) {
            $aiReplyText = $this->chatbotService->generateReply($isiPesan, $user);

            $pesanAi = ChatPesan::create([
                'percakapan_id' => $percakapan->id,
                'pengirim_id' => null,
                'pengirim_tipe' => ChatPesan::TIPE_AI,
                'isi' => $aiReplyText,
            ]);

            return response()->json([
                'success' => true,
                'mode' => 'ai',
                'user_message' => [
                    'id' => $pesanUser->id,
                    'isi' => $pesanUser->isi,
                    'pengirim_tipe' => $pesanUser->pengirim_tipe,
                    'waktu' => $pesanUser->created_at->format('H:i'),
                ],
                'ai_reply' => [
                    'id' => $pesanAi->id,
                    'isi' => $pesanAi->isi,
                    'pengirim_tipe' => $pesanAi->pengirim_tipe,
                    'waktu' => $pesanAi->created_at->format('H:i'),
                ],
            ]);
        }

        // JIKA MODE HUBUNGI PETUGAS:
        // Jika belum ada petugas yang mengambil, pastikan status 'menunggu'
        if (!$percakapan->petugas_id && $percakapan->status !== ChatPercakapan::STATUS_MENUNGGU) {
            $percakapan->update(['status' => ChatPercakapan::STATUS_MENUNGGU]);
        }

        return response()->json([
            'success' => true,
            'mode' => 'petugas',
            'user_message' => [
                'id' => $pesanUser->id,
                'isi' => $pesanUser->isi,
                'pengirim_tipe' => $pesanUser->pengirim_tipe,
                'waktu' => $pesanUser->created_at->format('H:i'),
            ],
            'waiting' => is_null($percakapan->petugas_id),
            'message' => is_null($percakapan->petugas_id)
                ? 'Pesan telah dikirim. Menunggu petugas merespons percakapan...'
                : 'Pesan telah dikirim ke petugas.',
        ]);
    }

    /**
     * Polling pesan baru untuk widget user
     */
    public function pollMessages(Request $request, $id)
    {
        $user = Auth::user();
        $percakapan = ChatPercakapan::with('petugas')->findOrFail($id);

        if ($percakapan->UserID !== $user->UserID) {
            abort(403, 'Akses ditolak.');
        }

        $lastId = (int) $request->get('after_id', 0);

        $newMessages = ChatPesan::where('percakapan_id', $percakapan->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        // Tandai pesan dari petugas/admin sebagai telah dibaca
        ChatPesan::where('percakapan_id', $percakapan->id)
            ->whereIn('pengirim_tipe', [ChatPesan::TIPE_PETUGAS, ChatPesan::TIPE_ADMIN])
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => Carbon::now('Asia/Jakarta')]);

        return response()->json([
            'success' => true,
            'status' => $percakapan->status,
            'mode' => $percakapan->mode,
            'petugas' => $percakapan->petugas ? [
                'nama' => $percakapan->petugas->nama,
                'role' => $percakapan->petugas->role,
            ] : null,
            'messages' => $newMessages->map(function ($p) {
                return [
                    'id' => $p->id,
                    'pengirim_tipe' => $p->pengirim_tipe,
                    'isi' => $p->isi,
                    'waktu' => $p->created_at ? $p->created_at->format('H:i') : '',
                    'dibaca' => !is_null($p->dibaca_pada),
                ];
            }),
        ]);
    }

    /**
     * Beralih mode antara 'ai' dan 'petugas'
     */
    public function switchMode(Request $request, $id)
    {
        $validated = $request->validate([
            'mode' => 'required|in:ai,petugas',
        ]);

        $user = Auth::user();
        $percakapan = ChatPercakapan::findOrFail($id);

        if ($percakapan->UserID !== $user->UserID) {
            abort(403, 'Akses ditolak.');
        }

        $newMode = $validated['mode'];
        $updateData = ['mode' => $newMode];

        if ($newMode === ChatPercakapan::MODE_PETUGAS) {
            if (!$percakapan->petugas_id) {
                $updateData['status'] = ChatPercakapan::STATUS_MENUNGGU;
            }
            // Kirim pesan sistem bahwa user beralih ke petugas
            ChatPesan::create([
                'percakapan_id' => $percakapan->id,
                'pengirim_id' => null,
                'pengirim_tipe' => ChatPesan::TIPE_AI,
                'isi' => 'Anda telah beralih ke mode "Hubungi Petugas". Pesan Anda berikutnya akan masuk ke antrean layanan petugas perpustakaan.',
            ]);
        } else {
            // Kembali ke mode AI
            ChatPesan::create([
                'percakapan_id' => $percakapan->id,
                'pengirim_id' => null,
                'pengirim_tipe' => ChatPesan::TIPE_AI,
                'isi' => 'Anda telah kembali ke mode "Asisten AI". Saya siap membantu menjawab pertanyaan Anda.',
            ]);
        }

        $percakapan->update($updateData);

        return response()->json([
            'success' => true,
            'mode' => $newMode,
            'status' => $percakapan->status,
        ]);
    }
}

