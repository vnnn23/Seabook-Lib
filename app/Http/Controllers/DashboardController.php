<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilan dashboard grafik dan statistik untuk Admin & Petugas
     */
    public function index(Request $request)
    {
        $data = $this->getStatisticsData($request);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('dashboard.index', $data);
    }

    /**
     * Endpoint API JSON untuk data statistik dan grafik
     */
    public function data(Request $request)
    {
        $data = $this->getStatisticsData($request);
        return response()->json($data);
    }

    /**
     * Mengumpulkan seluruh data statistik, metrik ringkasan, dan grafik
     * Kompatibel dengan SQLite (:memory:) dan MySQL tanpa fungsi tanggal SQL mentah.
     */
    public function getStatisticsData(Request $request): array
    {
        $currentUser = Auth::user();

        // 1. Filter Rentang Tanggal (Opsional)
        $startDate = null;
        $endDate = null;
        $dateFilter = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start = Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay();
                $end = Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay();
                if ($start->lte($end)) {
                    $startDate = $start->format('Y-m-d');
                    $endDate = $end->format('Y-m-d');
                    $dateFilter = ['start' => $start, 'end' => $end];
                }
            } catch (\Exception $e) {
                // Ignore parsing errors and use default
            }
        }

        // 2. Kartu Ringkasan
        $totalJudul = Buku::count();
        $totalEksemplar = (int) Buku::sum('jumlah_total');

        $dipinjam = Peminjaman::whereIn('statuspeminjaman', Peminjaman::STATUS_HOLDING_BOOK)->count();
        $tersedia = max(0, $totalEksemplar - $dipinjam);

        $menungguPersetujuan = Peminjaman::where('statuspeminjaman', Peminjaman::STATUS_MENUNGGU)->count();

        $terlambat = Peminjaman::whereIn('statuspeminjaman', [
                Peminjaman::STATUS_DIPINJAM,
                Peminjaman::STATUS_MENUNGGU_PENGEMBALIAN,
            ])
            ->where('tanggal_kembali', '<', Carbon::now('Asia/Jakarta'))
            ->count();

        // Akun pending khusus admin
        $akunPending = null;
        if ($currentUser && $currentUser->role === User::ROLE_ADMIN) {
            $akunPending = User::where('status_akun', User::STATUS_PENDING)->count();
        }

        // 3. Grafik Garis: Tren Peminjaman per Bulan (12 Bulan Terakhir)
        // Dikelompokkan via PHP/Collection (Asia/Jakarta) agar 100% kompatibel di SQLite & MySQL
        $trenBulan = [];
        $trenBulanLabels = [];

        // Buat 12 bulan berurutan
        for ($i = 11; $i >= 0; $i--) {
            $monthTime = Carbon::now('Asia/Jakarta')->subMonths($i);
            $key = $monthTime->format('Y-m');
            $label = $monthTime->translatedFormat('M Y');
            $trenBulan[$key] = 0;
            $trenBulanLabels[$key] = $label;
        }

        $twelveMonthsAgo = Carbon::now('Asia/Jakarta')->subMonths(11)->startOfMonth();
        $loanQuery = Peminjaman::query();

        if ($dateFilter) {
            $loanQuery->whereBetween('tanggal_pinjam', [$dateFilter['start'], $dateFilter['end']]);
        } else {
            $loanQuery->where('tanggal_pinjam', '>=', $twelveMonthsAgo);
        }

        $loansForTrend = $loanQuery->get(['PeminjamanID', 'tanggal_pinjam', 'created_at']);
        foreach ($loansForTrend as $loan) {
            $date = $loan->tanggal_pinjam 
                ? Carbon::parse($loan->tanggal_pinjam)->timezone('Asia/Jakarta')
                : Carbon::parse($loan->created_at)->timezone('Asia/Jakarta');
            $monthKey = $date->format('Y-m');
            if (isset($trenBulan[$monthKey])) {
                $trenBulan[$monthKey]++;
            }
        }

        $trenValues = array_values($trenBulan);
        $chartTren = [
            'labels' => array_values($trenBulanLabels),
            'data' => $trenValues,
            'empty' => array_sum($trenValues) === 0,
        ];

        // 4. Grafik Batang: 10 Buku Terpopuler (withCount anti N+1)
        $topBooksQuery = Buku::query();
        $topBooks = $topBooksQuery->withCount(['peminjaman' => function ($q) use ($dateFilter) {
                if ($dateFilter) {
                    $q->whereBetween('tanggal_pinjam', [$dateFilter['start'], $dateFilter['end']]);
                }
            }])
            ->orderBy('peminjaman_count', 'desc')
            ->take(10)
            ->get(['BukuID', 'judul']);

        $topBooksLabels = [];
        $topBooksData = [];
        foreach ($topBooks as $b) {
            if ($b->peminjaman_count > 0) {
                $topBooksLabels[] = $b->judul;
                $topBooksData[] = (int) $b->peminjaman_count;
            }
        }

        $chartTopBooks = [
            'labels' => $topBooksLabels,
            'data' => $topBooksData,
            'empty' => empty($topBooksData),
        ];

        // 5. Grafik Donat: Distribusi Status Peminjaman
        $statusQuery = Peminjaman::query();
        if ($dateFilter) {
            $statusQuery->whereBetween('tanggal_pinjam', [$dateFilter['start'], $dateFilter['end']]);
        }
        $statusDist = $statusQuery->select('statuspeminjaman', DB::raw('count(*) as aggregate'))
            ->groupBy('statuspeminjaman')
            ->pluck('aggregate', 'statuspeminjaman')
            ->toArray();

        $chartStatus = [
            'labels' => array_keys($statusDist),
            'data' => array_values($statusDist),
            'empty' => empty($statusDist) || array_sum(array_values($statusDist)) === 0,
        ];

        // 6. Grafik Donat: Stok Tersedia vs Dipinjam
        $chartStok = [
            'labels' => ['Tersedia', 'Dipinjam'],
            'data' => [$tersedia, $dipinjam],
            'empty' => ($tersedia === 0 && $dipinjam === 0),
        ];

        // 7. Grafik Batang: Peminjaman per Kategori (ANSI SQL leftJoin + groupBy anti N+1)
        $kategoriQuery = DB::table('kategoribuku')
            ->leftJoin('kategoribuku_relasi', 'kategoribuku.KategoriID', '=', 'kategoribuku_relasi.KategoriID')
            ->leftJoin('peminjaman', function ($join) use ($dateFilter) {
                $join->on('kategoribuku_relasi.BukuID', '=', 'peminjaman.BukuID');
                if ($dateFilter) {
                    $join->whereBetween('peminjaman.tanggal_pinjam', [$dateFilter['start'], $dateFilter['end']]);
                }
            })
            ->select('kategoribuku.nama_kategori', DB::raw('count(peminjaman.PeminjamanID) as total_pinjam'))
            ->groupBy('kategoribuku.KategoriID', 'kategoribuku.nama_kategori')
            ->orderBy('total_pinjam', 'desc')
            ->get();

        $kategoriLabels = [];
        $kategoriData = [];
        foreach ($kategoriQuery as $kat) {
            if ($kat->total_pinjam > 0) {
                $kategoriLabels[] = $kat->nama_kategori;
                $kategoriData[] = (int) $kat->total_pinjam;
            }
        }

        $chartKategori = [
            'labels' => $kategoriLabels,
            'data' => $kategoriData,
            'empty' => empty($kategoriData),
        ];

        // 8. Tabel 10 User dengan Poin Tertinggi
        $topUsersPoin = User::where('role', User::ROLE_USER)
            ->where('poin_pelanggaran', '>', 0)
            ->orderBy('poin_pelanggaran', 'desc')
            ->take(10)
            ->get(['UserID', 'nama', 'email', 'poin_pelanggaran', 'status_akun']);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'total_judul' => $totalJudul,
                'total_eksemplar' => $totalEksemplar,
                'tersedia' => $tersedia,
                'dipinjam' => $dipinjam,
                'menunggu_persetujuan' => $menungguPersetujuan,
                'terlambat' => $terlambat,
                'akun_pending' => $akunPending,
            ],
            'charts' => [
                'tren_peminjaman' => $chartTren,
                'buku_terpopuler' => $chartTopBooks,
                'status_peminjaman' => $chartStatus,
                'stok_tersedia_dipinjam' => $chartStok,
                'peminjaman_kategori' => $chartKategori,
            ],
            'top_users_poin' => $topUsersPoin,
            'topUsersPoin' => $topUsersPoin,
        ];
    }
}
