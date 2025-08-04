<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Property untuk menyimpan instance DashboardService
    protected $dashboardService;

    /**
     * Constructor untuk dependency injection DashboardService
     * Memungkinkan controller menggunakan layanan dashboard
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display dashboard with statistics based on user role
     * Menampilkan dashboard dengan statistik berdasarkan role user
     */
    public function index()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil data dashboard berdasarkan role user
        if ($user->role->name === 'Manager') {
            // Jika user adalah Manager, ambil data dashboard untuk manager
            // Data termasuk: total tasks, tasks per status, total staff, dll
            $data = $this->dashboardService->getManagerDashboardData($user);
        } else {
            // Jika user adalah Staff, ambil data dashboard untuk staff
            // Data termasuk: tasks yang dibuat/ditugaskan, tasks per status, dll
            $data = $this->dashboardService->getStaffDashboardData($user);
        }

        // Menampilkan halaman dashboard dengan data yang sesuai
        return view('dashboard', $data);
    }
}
