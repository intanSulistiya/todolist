<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Property untuk menyimpan instance UserService
    protected $userService;

    /**
     * Constructor untuk dependency injection UserService
     * Memungkinkan controller menggunakan layanan user management
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     * Menampilkan daftar semua staf dengan filter dan statistik
     */
    public function index(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($user)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Mengambil data staf dengan filter dari request (pencarian, sorting, dll)
        $staff = $this->userService->getStaffWithFilters($request);

        // Mengambil statistik staf (total, aktif, non-aktif, dll)
        $statistics = $this->userService->getStaffStatistics();

        // Menggabungkan data staf dan statistik untuk ditampilkan ke view
        return view('users.index', array_merge(compact('staff'), $statistics));
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk membuat staf baru
     */
    public function create()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($user)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Mengambil data roles untuk dropdown di form
        $roles = $this->userService->getRoles();

        // Mengambil data managers untuk dropdown supervisor di form
        $managers = $this->userService->getManagers();

        // Menampilkan form create dengan data roles dan managers
        return view('users.create', compact('roles', 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan data staf baru ke database
     */
    public function store(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($user)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Memproses pembuatan user baru melalui service
        // Service akan melakukan validasi, enkripsi password, dan penyimpanan
        $result = $this->userService->createUser($request, $user);

        // Jika ada error validasi atau proses gagal, kembali ke form dengan error
        if (!$result['success']) {
            return redirect()->back()->withErrors($result['errors'])->withInput();
        }

        // Jika berhasil, redirect ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Staf berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     * Menampilkan detail staf tertentu
     */
    public function show(User $user)
    {
        // Mendapatkan user yang sedang login
        $currentUser = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($currentUser)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Validasi: hanya bisa melihat detail staf, bukan manager atau admin
        if (!$this->userService->isStaff($user)) {
            return redirect()->route('users.index')->with('error', 'Hanya dapat melihat detail Staf.');
        }

        // Mengambil statistik khusus untuk user ini (task completion, performance, dll)
        $statistics = $this->userService->getUserStatistics($user);

        // Menampilkan halaman detail dengan data user dan statistiknya
        return view('users.show', array_merge(compact('user'), $statistics));
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit staf
     */
    public function edit(User $user)
    {
        // Mendapatkan user yang sedang login
        $currentUser = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($currentUser)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Validasi: hanya bisa mengedit staf, bukan manager atau admin
        if (!$this->userService->isStaff($user)) {
            return redirect()->route('users.index')->with('error', 'Hanya dapat mengedit Staf.');
        }

        // Mengambil data roles untuk dropdown di form edit
        $roles = $this->userService->getRoles();

        // Mengambil data managers untuk dropdown supervisor di form edit
        $managers = $this->userService->getManagers();

        // Menampilkan form edit dengan data user, roles, dan managers
        return view('users.edit', compact('user', 'roles', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     * Mengupdate data staf di database
     */
    public function update(Request $request, User $user)
    {
        // Mendapatkan user yang sedang login
        $currentUser = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($currentUser)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Validasi: hanya bisa mengupdate staf, bukan manager atau admin
        if (!$this->userService->isStaff($user)) {
            return redirect()->route('users.index')->with('error', 'Hanya dapat mengedit Staf.');
        }

        // Memproses update user melalui service
        // Service akan melakukan validasi dan update data
        $result = $this->userService->updateUser($request, $user);

        // Jika ada error validasi atau proses gagal, kembali ke form dengan error
        if (!$result['success']) {
            return redirect()->back()->withErrors($result['errors'])->withInput();
        }

        // Jika berhasil, redirect ke halaman detail user dengan pesan sukses
        return redirect()->route('users.show', $user)->with('success', 'Staf berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus staf dari database
     */
    public function destroy(User $user)
    {
        // Mendapatkan user yang sedang login
        $currentUser = Auth::user();

        // Validasi akses: hanya user dengan role tertentu yang bisa mengakses
        if (!$this->userService->canAccessUserManagement($currentUser)) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Validasi: hanya bisa menghapus staf, bukan manager atau admin
        if (!$this->userService->isStaff($user)) {
            return redirect()->route('users.index')->with('error', 'Hanya dapat menghapus Staf.');
        }

        // Memproses penghapusan user melalui service
        // Service akan melakukan validasi (misal: tidak bisa hapus diri sendiri) dan soft delete
        $result = $this->userService->deleteUser($user, $currentUser);

        // Jika ada error atau proses gagal, kembali ke index dengan pesan error
        if (!$result['success']) {
            return redirect()->route('users.index')->with('error', $result['message']);
        }

        // Jika berhasil, redirect ke halaman index dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'Staf berhasil dihapus!');
    }
}

