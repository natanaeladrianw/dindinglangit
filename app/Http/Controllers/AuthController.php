<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ShiftKasir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        return view('pages.auth');
    }

    // Proses sign-in
    public function signIn(Request $request)
    {
        // dd($request);
        try {
            $credentials = $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        }
        // Attempt login; support legacy non-bcrypt passwords by migrating to bcrypt
        $user = User::where('username', $credentials['username'])->first();
        $loggedIn = false;

        if ($user) {
            $stored = (string) $user->password;
            $isBcrypt = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$');

            if (!$isBcrypt && hash_equals($stored, $credentials['password'])) {
                // Migrate legacy plain/other-hash password to bcrypt
                $user->password = Hash::make($credentials['password']);
                $user->save();

                Auth::login($user);
                $loggedIn = true;
            } else {
                $loggedIn = Auth::attempt($credentials);
            }
        }

        if ($loggedIn) {
            $request->session()->regenerate();

            $user = Auth::user();
            // Cek apakah user kasir
            if ($user->role === 'kasir') {
                // Cek apakah ada shift aktif (tanpa jam_keluar)
                $activeShift = ShiftKasir::where('user_id', $user->id)
                    ->whereDate('updated_at', Carbon::today())
                    ->whereNull('jam_keluar')
                    ->latest() // Ambil yang terbaru
                    ->first();

                // Jika tidak ada shift aktif, buat shift baru
                if (!$activeShift) {
                    ShiftKasir::create([
                        'user_id' => $user->id,
                        'shift_date' => Carbon::now(),
                        'jam_masuk' => Carbon::now(),
                        'jam_keluar' => null,
                    ]);
                }
            }

            // Redirect by role
            if ($user->role === 'owner') {
                return redirect('/laporan-pembelian-bahan-baku');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Proses sign-out
    public function signOut(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->role === 'kasir') {
            $lastShift = ShiftKasir::where('user_id', $user->id)
                            ->whereNull('jam_keluar')
                            ->latest()
                            ->first();

            if ($lastShift) {
                $lastShift->update([
                    'jam_keluar' => Carbon::now(),
                ]);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->intended('/');
    }

    // Proses sign-up
    public function signUp(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        // dd($user);
        Auth::login($user);

        return redirect()->intended('/dashboard')->with('success', 'Akun berhasil dibuat!');
    }
}
