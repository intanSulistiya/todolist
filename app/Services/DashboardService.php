<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Create a new class instance.
     * Constructor untuk DashboardService
     */
    public function __construct()
    {
        //
    }

    /**
     * Get dashboard data for manager
     * Mengambil data dashboard untuk Manager
     */
    public function getManagerDashboardData($user)
    {
        // Mengambil data status untuk perhitungan
        $statuses = $this->getStatuses();

        // Menyiapkan data dashboard untuk manager
        $data = [
            'totalTasks' => Task::count(), // Total semua tugas
            'todoTasks' => $this->getTasksByStatus($statuses['todo']), // Tugas dengan status Todo
            'doingTasks' => $this->getTasksByStatus($statuses['doing']), // Tugas dengan status Doing
            'doneTasks' => $this->getTasksByStatus($statuses['done']), // Tugas dengan status Done
            'totalStaff' => User::where('role_id', 2)->count(), // Total staf
            'myCreatedTasks' => Task::where('creator_id', $user->id)->count(), // Tugas yang dibuat oleh manager
            'pendingTasks' => $this->getPendingTasks($statuses), // Tugas yang masih pending
            'recentTasks' => $this->getRecentTasks(), // 5 tugas terbaru
            'myTasks' => $this->getMyCreatedTasks($user->id) // Tugas yang dibuat oleh manager
        ];

        return $data;
    }

    /**
     * Get dashboard data for staff
     * Mengambil data dashboard untuk Staff
     */
    public function getStaffDashboardData($user)
    {
        // Mengambil data status untuk perhitungan
        $statuses = $this->getStatuses();

        // Menyiapkan data dashboard untuk staff
        $data = [
            'totalTasks' => $this->getMyTotalTasks($user->id), // Total tugas staff (dibuat atau ditugaskan)
            'todoTasks' => $this->getMyTasksByStatus($user->id, $statuses['todo']), // Tugas Todo staff
            'doingTasks' => $this->getMyTasksByStatus($user->id, $statuses['doing']), // Tugas Doing staff
            'doneTasks' => $this->getMyTasksByStatus($user->id, $statuses['done']), // Tugas Done staff
            'recentTasks' => $this->getMyRecentTasks($user->id), // 5 tugas terbaru staff
            'myTasks' => $this->getTasksAssignedToMe($user->id) // Tugas yang ditugaskan kepada staff
        ];

        return $data;
    }

    /**
     * Get statuses
     * Mengambil data status untuk perhitungan
     */
    private function getStatuses()
    {
        // Mengambil status Todo, Doing, dan Done dari database
        return [
            'todo' => Status::where('name', 'todo')->first(),
            'doing' => Status::where('name', 'doing')->first(),
            'done' => Status::where('name', 'done')->first()
        ];
    }

    /**
     * Get tasks by status
     * Menghitung jumlah tugas berdasarkan status
     */
    private function getTasksByStatus($status)
    {
        // Menghitung jumlah tugas dengan status tertentu
        return $status ? Task::where('status_id', $status->id)->count() : 0;
    }

    /**
     * Get pending tasks
     * Menghitung jumlah tugas yang masih pending (Todo + Doing)
     */
    private function getPendingTasks($statuses)
    {
        // Mengambil ID status Todo dan Doing
        $todoId = $statuses['todo']->id ?? null;
        $doingId = $statuses['doing']->id ?? null;

        // Menghitung tugas dengan status Todo atau Doing
        if ($todoId && $doingId) {
            return Task::whereIn('status_id', [$todoId, $doingId])->count();
        }

        return 0;
    }

    /**
     * Get recent tasks
     * Mengambil 5 tugas terbaru untuk semua user
     */
    private function getRecentTasks()
    {
        // Mengambil 5 tugas terbaru dengan relasi yang diperlukan
        return Task::with(['creator', 'assignee', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get my created tasks
     * Mengambil 5 tugas terbaru yang dibuat oleh user tertentu
     */
    private function getMyCreatedTasks($userId)
    {
        // Mengambil 5 tugas terbaru yang dibuat oleh user dengan relasi
        return Task::with(['creator', 'assignee', 'status'])
            ->where('creator_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get my total tasks (created or assigned)
     * Menghitung total tugas user (yang dibuat atau ditugaskan)
     */
    private function getMyTotalTasks($userId)
    {
        // Menghitung total tugas yang dibuat atau ditugaskan kepada user
        return Task::where(function ($q) use ($userId) {
            $q->where('creator_id', $userId)
              ->orWhere('assignee_id', $userId);
        })->count();
    }

    /**
     * Get my tasks by status
     * Menghitung jumlah tugas user berdasarkan status
     */
    private function getMyTasksByStatus($userId, $status)
    {
        // Jika status tidak ada, return 0
        if (!$status) return 0;

        // Menghitung tugas user dengan status tertentu
        return Task::where(function ($q) use ($userId) {
            $q->where('creator_id', $userId)
              ->orWhere('assignee_id', $userId);
        })->where('status_id', $status->id)->count();
    }

    /**
     * Get my recent tasks
     * Mengambil 5 tugas terbaru user (yang dibuat atau ditugaskan)
     */
    private function getMyRecentTasks($userId)
    {
        // Mengambil 5 tugas terbaru user dengan relasi
        return Task::with(['creator', 'assignee', 'status'])
            ->where(function ($q) use ($userId) {
                $q->where('creator_id', $userId)
                  ->orWhere('assignee_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get tasks assigned to me
     * Mengambil 5 tugas terbaru yang ditugaskan kepada user
     */
    private function getTasksAssignedToMe($userId)
    {
        // Mengambil 5 tugas terbaru yang ditugaskan kepada user dengan relasi
        return Task::with(['creator', 'assignee', 'status'])
            ->where('assignee_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
