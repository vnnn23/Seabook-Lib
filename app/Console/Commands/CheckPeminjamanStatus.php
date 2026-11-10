<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckPeminjamanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peminjaman:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa peminjaman berstatus disetujui yang melewati batas waktu pengambilan dan mengubah status menjadi kadaluarsa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pickupExpiryHours = (int) config('perpustakaan.pickup_expiry_hours', 24);
        $threshold = Carbon::now()->subHours($pickupExpiryHours);

        $expiredLoans = Peminjaman::where('statuspeminjaman', Peminjaman::STATUS_DISETUJUI)
            ->where(function ($query) use ($threshold) {
                $query->where('updated_at', '<=', $threshold)
                      ->orWhere('tanggal_pinjam', '<=', $threshold);
            })
            ->get();

        $count = 0;
        foreach ($expiredLoans as $loan) {
            $loan->statuspeminjaman = Peminjaman::STATUS_KADALUARSA;
            $loan->save();
            $count++;
            Log::info("Peminjaman ID {$loan->PeminjamanID} (BukuID: {$loan->BukuID}, UserID: {$loan->UserID}) diubah statusnya menjadi kadaluarsa.");
        }

        $this->info("Pemeriksaan status selesai. {$count} peminjaman telah diubah menjadi kadaluarsa.");

        return Command::SUCCESS;
    }
}

