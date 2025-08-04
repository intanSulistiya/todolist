<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show login form
     * Menampilkan halaman form login
     */
    public function showLoginForm()
    {
        // Jika user sudah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Menampilkan halaman login
        return view('Login.login');
    }

    /**
     * Handle login
     * Memproses login user
     */
    public function login(Request $request)
    {
        // Validasi input form login
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Jika validasi gagal, kembali ke form dengan error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Mengambil credentials dari request
        $credentials = $request->only('email', 'password');

        // Mengecek apakah user ingin diingat (remember me)
        $remember = $request->has('remember');

        // Mencoba melakukan autentikasi
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            // Redirect ke halaman yang dituju atau dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Jika autentikasi gagal, kembali ke form dengan error
        return redirect()->back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->withInput($request->only('email'));
    }

    /**
     * Show register form
     * Menampilkan halaman form registrasi
     */
    public function showRegisterForm()
    {
        // Jika user sudah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Mengambil data roles untuk dropdown di form registrasi
        $roles = Role::all();

        // Mengambil data managers untuk dropdown supervisor (hanya role Manager)
        $managers = User::where('role_id', 1)->get(); // Manager only

        // Menampilkan halaman registrasi dengan data roles dan managers
        return view('register', compact('roles', 'managers'));
    }

    /**
     * Handle register
     * Memproses registrasi user baru
     */
    public function register(Request $request)
    {
        // Validasi input form registrasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Jika validasi gagal, kembali ke form dengan error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validasi role - memastikan role yang dipilih valid
        $role = Role::find($request->role_id);
        if (!$role) {
            return redirect()->back()->withErrors(['role_id' => 'Role tidak valid.'])->withInput();
        }

        // Validasi manager jika disediakan - memastikan manager adalah role Manager
        if ($request->manager_id) {
            $manager = User::find($request->manager_id);
            if (!$manager || $manager->role->name !== 'Manager') {
                return redirect()->back()->withErrors(['manager_id' => 'Manager tidak valid.'])->withInput();
            }
        }

        // Membuat user baru dengan data yang sudah divalidasi
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
            'role_id' => $request->role_id,
            'manager_id' => $request->manager_id,
        ]);

        // Auto login setelah registrasi berhasil
        Auth::login($user);

        // Redirect ke dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat dan Anda telah login!');
    }

    /**
     * Handle logout
     * Memproses logout user
     */
    public function logout(Request $request)
    {
        // Logout user dari sistem
        Auth::logout();

        // Invalidate session untuk keamanan
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Redirect ke halaman welcome
        return redirect()->route('welcome');
    }

    /**
     * Check user authentication
     * Mengecek status autentikasi user (untuk AJAX request)
     */
    public function check()
    {
        // Jika user sudah login
        if (Auth::check()) {
            $user = Auth::user();

            // Return data user dalam format JSON
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                ]
            ]);
        }

        // Jika user belum login, return false
        return response()->json(['authenticated' => false]);
    }
}
