<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardPetugasController;
use App\Http\Controllers\KategoriBukuController;
use App\Http\Controllers\KoleksiPribadiController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PoinPelanggaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StaffChatController;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Google Auth disabled
Route::get('/auth/google', function () {
    return redirect('/')->with('error', 'Metode login Google telah dinonaktifkan.');
})->name('auth.google');
Route::get('/auth/google/callback', function () {
    return redirect('/')->with('error', 'Metode login Google telah dinonaktifkan.');
})->name('auth.google.callback');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Books CRUD (Admin & Petugas) - Terdaftar SEBELUM /books/{id} agar tidak tertangkap sebagai parameter {id}
Route::middleware(['auth', 'account.active', 'role:admin,petugas'])->group(function () {
    Route::get('/books/create', [BukuController::class, 'create'])->name('books.create');
    Route::post('/books', [BukuController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit', [BukuController::class, 'edit'])->name('books.edit');
    Route::get('/books/{id}/edit2', [BukuController::class, 'edit2'])->name('books.edit2');
    Route::put('/books/{id}', [BukuController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BukuController::class, 'destroy'])->name('books.destroy');
    Route::post('/kategoribuku', [KategoriBukuController::class, 'store'])->name('kategoribuku.store');
});

// Books (Public / Catalog)
Route::get('/books', [BukuController::class, 'index'])->name('books');
Route::get('/books/{id}', [BukuController::class, 'show'])->name('detailbooks');

// Protected PDF Reader (Auth only, sanitasi basename, cek eksistensi file, cegah directory traversal)
Route::get('/read/{filename}', function ($filename) {
    $cleanFilename = basename($filename);
    if (!str_ends_with(strtolower($cleanFilename), '.pdf')) {
        abort(404, 'File harus berformat PDF.');
    }

    $filePath = public_path('pdfs/' . $cleanFilename);
    if (!file_exists($filePath)) {
        abort(404, 'Buku tidak ditemukan.');
    }

    return view('read', ['filename' => $cleanFilename]);
})->middleware(['auth', 'account.active'])->name('book.read');

// Admin Dashboard & Management
Route::middleware(['auth', 'account.active', 'role:admin'])->group(function () {
    Route::get('/dashboardadmin', [DashboardAdminController::class, 'index'])->name('dashboardadmin');
    Route::get('/dashboardadmin2', [UserController::class, 'index'])->name('dashboardadmin2');
    Route::get('/dashboardadmin3', [PetugasController::class, 'index'])->name('dashboardadmin3');
    Route::get('/dashboardadmin4', [PeminjamanController::class, 'adminIndex'])->name('dashboardadmin4');

    Route::post('/petugas', [PetugasController::class, 'store'])->name('petugas.store');
    Route::delete('/petugas/{id}', [PetugasController::class, 'destroy'])->name('petugas.destroy');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/admin/ulasan', [UlasanController::class, 'index'])->name('ulasan.index');

    // Member Approval (Admin Only)
    Route::get('/admin/members/approval', [UserApprovalController::class, 'index'])->name('admin.members.approval');
    Route::post('/admin/members/{id}/approve', [UserApprovalController::class, 'approve'])->name('admin.members.approve');
    Route::post('/admin/members/{id}/reject', [UserApprovalController::class, 'reject'])->name('admin.members.reject');

    // Manajemen Poin Pelanggaran (Admin Only)
    Route::get('/admin/poin', [PoinPelanggaranController::class, 'index'])->name('admin.poin.index');
    Route::get('/admin/poin/{id}', [PoinPelanggaranController::class, 'show'])->name('admin.poin.show');
    Route::post('/admin/poin/{id}/adjust', [PoinPelanggaranController::class, 'adjust'])->name('admin.poin.adjust');
});

// Petugas Dashboard
Route::middleware(['auth', 'account.active', 'role:petugas'])->group(function () {
    Route::get('/dashboardpetugas', [DashboardPetugasController::class, 'index'])->name('dashboardpetugas');
    Route::get('/dashboardpetugas2', [DashboardPetugasController::class, 'peminjaman'])->name('dashboardpetugas2');
});

// General Authenticated User Routes
Route::middleware(['auth', 'account.active'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Profile Multi-role
    Route::get('/profile', function () {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return view('profiladmin');
        }
        if ($user->isPetugas()) {
            return view('profilpetugas');
        }
        return app(ProfileController::class)->edit();
    })->name('profile');

    // Petugas Profile
    Route::get('/profile-petugas', function () {
        return view('profilpetugas');
    })->name('profile.petugas')->middleware('role:petugas');

    // Profile Settings
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-foto', [ProfileController::class, 'uploadFoto'])->name('profile.uploadFoto');

    // Peminjaman User
    Route::get('/peminjaman/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');
    Route::get('/peminjaman/create/{bukuId}', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/kembalikan/{id}', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
    Route::post('/peminjaman/cancel-user/{id}', [PeminjamanController::class, 'cancelByUser'])->name('peminjaman.cancel.user');

    // Ulasan
    Route::post('/ulasan', [UlasanController::class, 'store'])->name('ulasan.store');
    Route::put('/ulasan/{id}', [UlasanController::class, 'update'])->name('ulasan.update');
    Route::delete('/ulasan/{id}', [UlasanController::class, 'destroy'])->name('ulasan.destroy');

    // Koleksi Pribadi
    Route::get('/koleksi', [KoleksiPribadiController::class, 'index'])->name('koleksi.index');
    Route::post('/koleksi', [KoleksiPribadiController::class, 'store'])->name('koleksi.store');
    Route::delete('/koleksi/{id}', [KoleksiPribadiController::class, 'destroy'])->name('koleksi.destroy');
    Route::delete('/koleksi-buku/{bukuId}', [KoleksiPribadiController::class, 'destroyByBuku'])->name('koleksi.destroyByBuku');
    Route::get('/koleksi/check/{bukuId}', [KoleksiPribadiController::class, 'isInCollection'])->name('koleksi.check');

    // Live Chat & Asisten AI (Pemustaka)
    Route::get('/chat/active', [ChatController::class, 'getActiveConversation'])->name('chat.user.active');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->middleware('throttle:20,1')->name('chat.user.send');
    Route::get('/chat/messages/{id}', [ChatController::class, 'pollMessages'])->middleware('throttle:120,1')->name('chat.user.poll');
    Route::post('/chat/switch-mode/{id}', [ChatController::class, 'switchMode'])->name('chat.user.switch');
});

// Peminjaman Management & Statistik (Admin & Petugas)
Route::middleware(['auth', 'account.active', 'role:admin,petugas'])->group(function () {
    // Live Chat Inbox & Management Petugas/Admin
    Route::get('/staff/chat', [StaffChatController::class, 'inbox'])->name('chat.inbox');
    Route::get('/staff/chat/inbox-poll', [StaffChatController::class, 'pollInbox'])->middleware('throttle:120,1')->name('chat.inbox.poll');
    Route::post('/staff/chat/{id}/take', [StaffChatController::class, 'take'])->name('chat.take');
    Route::post('/staff/chat/{id}/reply', [StaffChatController::class, 'reply'])->middleware('throttle:30,1')->name('chat.reply');
    Route::post('/staff/chat/{id}/close', [StaffChatController::class, 'close'])->name('chat.close');
    Route::get('/staff/chat/{id}/messages', [StaffChatController::class, 'pollConversation'])->middleware('throttle:120,1')->name('chat.poll');

    // Dashboard Statistik & Grafik
    Route::get('/dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/statistik', [DashboardController::class, 'index'])->name('dashboard.statistik');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/statistik/data', [DashboardController::class, 'data'])->name('dashboard.statistik.data');

    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/confirm/{id}', [PeminjamanController::class, 'confirm'])->name('peminjaman.confirm');
    Route::post('/peminjaman/confirm-return/{id}', [PeminjamanController::class, 'confirmReturn'])->name('peminjaman.confirmReturn');
    Route::post('/peminjaman/reject/{id}', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
    Route::post('/peminjaman/cancel/{id}', [PeminjamanController::class, 'cancel'])->name('peminjaman.cancel');
    Route::get('/peminjaman/scan', [PeminjamanController::class, 'scanQrPage'])->name('peminjaman.scan.page');
    Route::post('/peminjaman/scan', [PeminjamanController::class, 'scan'])->name('peminjaman.scan');
    Route::get('/admin/peminjaman', [PeminjamanController::class, 'adminIndex'])->name('admin.peminjaman');
    Route::post('/peminjaman/get-by-code', [PeminjamanController::class, 'getPeminjamanByCode'])->name('peminjaman.getByCode');
    Route::get('/peminjaman/export-pdf', [PeminjamanController::class, 'exportPdf'])->name('peminjaman.exportPdf');
});

// Fallback untuk menyajikan file storage di shared hosting (InfinityFree) tanpa artisan storage:link
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');
