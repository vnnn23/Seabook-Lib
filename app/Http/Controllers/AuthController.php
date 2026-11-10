<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    /**
     * Redirect user to Google for authentication
     */
    public function redirectToGoogle()
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect('/')->with('error', 'Google Login belum dikonfigurasi. Harap lengkapi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.')->with('open_login_modal', true);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Gagal melakukan autentikasi Google: ' . $e->getMessage())->with('open_login_modal', true);
        }

        // Check if user exists
        $authUser = User::where('email', $user->email)->first();

        if ($authUser) {
            // Check status akun
            if ($authUser->isPending()) {
                return redirect('/')->with('error', 'Akun Anda masih menunggu persetujuan admin.')->with('open_login_modal', true);
            }

            if ($authUser->isDitolak()) {
                $alasan = $authUser->alasan_penolakan ? ': ' . $authUser->alasan_penolakan : '.';
                return redirect('/')->with('error', 'Akun Anda telah ditolak' . $alasan)->with('open_login_modal', true);
            }

            Auth::login($authUser, true);
            return $this->redirectByRole(Auth::user());
        }

        // Create new user with role 'user' and status 'pending'
        User::create([
            'nama' => $user->name,
            'email' => $user->email,
            'password' => bcrypt(uniqid()),
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_PENDING,
            'foto_profil' => $user->avatar,
            'poin' => 0,
            'poin_pelanggaran' => 0,
        ]);

        return redirect('/')->with('success', 'Akun Google Anda berhasil didaftarkan. Akun Anda sedang menunggu persetujuan admin.')->with('open_login_modal', true);
    }

    /**
     * Handle email/password login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_login_modal', true);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $user = Auth::user();

            // Cek status akun
            if ($user->isPending()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda masih menunggu persetujuan admin.')->with('open_login_modal', true)->withInput();
            }

            if ($user->isDitolak()) {
                $alasan = $user->alasan_penolakan ? ': ' . $user->alasan_penolakan : '.';
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error', 'Akun Anda telah ditolak' . $alasan)->with('open_login_modal', true)->withInput();
            }

            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput()->with('open_login_modal', true);
    }

    /**
     * Handle email/password register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna',
            'password' => 'required|string|min:8|confirmed',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_register_modal', true);
        }

        $data = $request->only(['nama', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = User::ROLE_USER;
        $data['status_akun'] = User::STATUS_PENDING;
        $data['poin'] = 0;
        $data['poin_pelanggaran'] = 0;

        if ($request->hasFile('foto_profil')) {
            try {
                $file = $request->file('foto_profil');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('profiles', $file, $filename);
                $data['foto_profil'] = $path;
            } catch (Exception $e) {
                \Log::error('Profile photo upload error: ' . $e->getMessage());
            }
        }

        User::create($data);

        return redirect('/')->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin.')->with('open_login_modal', true);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect user based on their role
     */
    public function redirectByRole($user)
    {
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect('/dashboardadmin');
        }

        if ($user->role === User::ROLE_PETUGAS) {
            return redirect('/dashboardpetugas');
        }

        return redirect()->route('dashboard');
    }
}
