<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    /**
     * Get staff with filters
     * Mengambil data staf dengan filter pencarian
     */
    public function getStaffWithFilters(Request $request)
    {
        // Query untuk mengambil user dengan role Staf (role_id = 2)
        // Include relasi role dan manager untuk efisiensi query
        $query = User::with(['role', 'manager'])->where('role_id', 2); // Staf only

        // Filter berdasarkan pencarian nama atau email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Return data dengan pagination (10 item per halaman)
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Get staff statistics
     * Mengambil statistik staf untuk dashboard
     */
    public function getStaffStatistics()
    {
        // Menghitung total staf
        $totalStaff = User::where('role_id', 2)->count();

        // Menghitung staf aktif (semua staf dianggap aktif untuk saat ini)
        $activeStaff = User::where('role_id', 2)->count(); // All staff are active for now

        // Mengambil ID status untuk tugas aktif (todo dan doing)
        $todoStatus = Status::where('name', 'todo')->first();
        $doingStatus = Status::where('name', 'doing')->first();

        // Menghitung tugas aktif
        $activeTasks = Task::whereIn('status_id', [$todoStatus->id, $doingStatus->id])->count();

        // Menghitung rata-rata tugas per staf
        $avgTasksPerStaff = $totalStaff > 0 ? round(Task::count() / $totalStaff, 1) : 0;

        // Return array statistik
        return [
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'activeTasks' => $activeTasks,
            'avgTasksPerStaff' => $avgTasksPerStaff
        ];
    }

    /**
     * Get user statistics
     * Mengambil statistik khusus untuk user tertentu
     */
    public function getUserStatistics(User $user)
    {
        // Mengambil ID status untuk perhitungan
        $todoStatus = Status::where('name', 'todo')->first();
        $doingStatus = Status::where('name', 'doing')->first();
        $doneStatus = Status::where('name', 'done')->first();

        // Menghitung total tugas user (yang dibuat atau ditugaskan)
        $totalTasks = Task::where('creator_id', $user->id)
                         ->orWhere('assignee_id', $user->id)
                         ->count();

        // Menghitung tugas yang sudah selesai
        $completedTasks = Task::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('assignee_id', $user->id);
        })->where('status_id', $doneStatus->id)->count();

        // Menghitung tugas yang masih todo
        $todoTasks = Task::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('assignee_id', $user->id);
        })->where('status_id', $todoStatus->id)->count();

        // Menghitung tugas yang sedang doing
        $doingTasks = Task::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('assignee_id', $user->id);
        })->where('status_id', $doingStatus->id)->count();

        // Mengambil 5 tugas terbaru user
        $recentTasks = Task::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('assignee_id', $user->id);
        })->with(['creator', 'assignee', 'status'])->orderBy('created_at', 'desc')->limit(5)->get();

        // Return array statistik user
        return [
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'todoTasks' => $todoTasks,
            'doingTasks' => $doingTasks,
            'recentTasks' => $recentTasks
        ];
    }

    /**
     * Create new user
     * Membuat user baru dengan validasi
     */
    public function createUser(Request $request, $currentUser)
    {
        // Validasi input form
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Validasi role - Manager hanya bisa membuat Staf
        $role = Role::find($request->role_id);
        if ($role->name !== 'Staf') {
            return ['success' => false, 'errors' => ['role_id' => 'Manager hanya dapat menambahkan Staf']];
        }

        // Validasi manager - memastikan manager adalah role Manager
        if ($request->manager_id) {
            $manager = User::find($request->manager_id);
            if (!$manager || $manager->role->name !== 'Manager') {
                return ['success' => false, 'errors' => ['manager_id' => 'Manager ID tidak valid']];
            }
        }

        // Membuat user baru
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
            'role_id' => $request->role_id,
            'manager_id' => $request->manager_id ?: $currentUser->id, // Default ke manager saat ini
        ]);

        return ['success' => true, 'user' => $newUser];
    }

    /**
     * Update user
     * Mengupdate data user dengan validasi
     */
    public function updateUser(Request $request, User $user)
    {
        // Validasi input form (email unique kecuali untuk user yang sedang diedit)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Validasi role - Manager hanya bisa mengedit Staf
        $role = Role::find($request->role_id);
        if ($role->name !== 'Staf') {
            return ['success' => false, 'errors' => ['role_id' => 'Manager hanya dapat mengedit Staf']];
        }

        // Validasi manager - memastikan manager adalah role Manager
        if ($request->manager_id) {
            $manager = User::find($request->manager_id);
            if (!$manager || $manager->role->name !== 'Manager') {
                return ['success' => false, 'errors' => ['manager_id' => 'Manager ID tidak valid']];
            }
        }

        // Menyiapkan data untuk update
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'manager_id' => $request->manager_id,
        ];

        // Update password hanya jika disediakan
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Melakukan update
        $user->update($updateData);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Delete user
     * Menghapus user dengan validasi
     */
    public function deleteUser(User $user, $currentUser)
    {
        // Mencegah penghapusan diri sendiri
        if ($user->id === $currentUser->id) {
            return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri.'];
        }

        // Mengambil ID status untuk tugas aktif
        $todoStatus = Status::where('name', 'todo')->first();
        $doingStatus = Status::where('name', 'doing')->first();

        // Mengecek apakah user memiliki tugas aktif
        $activeTasks = Task::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere('assignee_id', $user->id);
        })->whereIn('status_id', [$todoStatus->id, $doingStatus->id])->count();

        // Jika ada tugas aktif, tidak bisa dihapus
        if ($activeTasks > 0) {
            return ['success' => false, 'message' => 'Tidak dapat menghapus staf yang memiliki tugas aktif.'];
        }

        // Melakukan penghapusan user
        $user->delete();
        return ['success' => true];
    }

    /**
     * Check if user can access user management
     * Mengecek apakah user bisa mengakses manajemen user
     */
    public function canAccessUserManagement($user)
    {
        // Hanya Manager yang bisa mengakses manajemen user
        return $user->role->name === 'Manager';
    }

    /**
     * Check if user is staff
     * Mengecek apakah user adalah staf
     */
    public function isStaff(User $user)
    {
        // Mengecek apakah role user adalah Staf
        return $user->role->name === 'Staf';
    }

    /**
     * Get roles for dropdown
     * Mengambil data roles untuk dropdown
     */
    public function getRoles()
    {
        // Mengambil semua role dari database
        return Role::all();
    }

    /**
     * Get managers for dropdown
     * Mengambil data managers untuk dropdown
     */
    public function getManagers()
    {
        // Mengambil user dengan role Manager (role_id = 1)
        return User::where('role_id', 1)->get(); // Manager only
    }
}
