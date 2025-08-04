<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskService
{
    /**
     * Get tasks with filters based on user role
     * Mengambil tugas dengan filter berdasarkan role user
     */
    public function getTasksWithFilters(Request $request, $user)
    {
        // Query dasar dengan relasi yang diperlukan
        $query = Task::with(['creator', 'assignee', 'status']);

        // Filter berdasarkan role user
        if ($user->role->name === 'Manager') {
            // Manager bisa melihat semua tugas dengan filter khusus
            $this->applyManagerFilters($query, $request, $user);
        } else {
            // Staff hanya bisa melihat tugas yang mereka buat atau yang ditugaskan kepada mereka
            $this->applyStaffFilters($query, $request, $user);
        }

        // Menerapkan filter umum (pencarian, status, dll)
        $this->applyCommonFilters($query, $request, $user);

        // Return data dengan pagination
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Get users for filter dropdowns
     * Mengambil data users untuk dropdown filter
     */
    public function getUsersForFilters($user)
    {
        if ($user->role->name === 'Manager') {
            // Manager bisa filter berdasarkan Staff
            return User::where('role_id', 2)->get(); // Staf only
        } else {
            // Staff bisa filter berdasarkan Manager
            return User::where('role_id', 1)->get(); // Manager only
        }
    }

    /**
     * Get users for assignee dropdown
     * Mengambil data users untuk dropdown assignee
     */
    public function getUsersForAssignee($user)
    {
        if ($user->role->name === 'Manager') {
            // Manager bisa assign tugas kepada Staff
            return User::where('role_id', 2)->get(); // Staf only
        } else {
            // Staff hanya bisa assign ke diri sendiri
            return collect([$user]);
        }
    }

    /**
     * Create new task
     * Membuat tugas baru dengan validasi
     */
    public function createTask(Request $request, $user)
    {
        // Validasi input form
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Mengambil status default "To Do"
        $todoStatus = Status::where('name', 'todo')->first();
        if (!$todoStatus) {
            return ['success' => false, 'errors' => ['status' => 'Status "To Do" tidak ditemukan']];
        }

        // Validasi assignee berdasarkan role
        $assigneeValidation = $this->validateAssignee($request, $user);
        if (!$assigneeValidation['success']) {
            return $assigneeValidation;
        }

        // Membuat tugas baru
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $todoStatus->id,
            'creator_id' => $user->id,
            'assignee_id' => $request->assignee_id,
            'report' => null,
        ]);

        return ['success' => true, 'task' => $task];
    }

    /**
     * Update task
     * Mengupdate tugas dengan validasi
     */
    public function updateTask(Request $request, Task $task, $user)
    {
        // Mengecek apakah user bisa mengedit tugas ini (hanya creator)
        if ($task->creator_id !== $user->id) {
            return ['success' => false, 'message' => 'Anda tidak dapat mengedit tugas ini.'];
        }

        // Mengecek apakah tugas bisa diedit (tidak bisa edit jika status Doing atau Done)
        if (in_array($task->status->name, ['doing', 'done'])) {
            return ['success' => false, 'message' => 'Tugas tidak dapat diedit jika statusnya Doing atau Done.'];
        }

        // Validasi input form
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Validasi assignee berdasarkan role
        $assigneeValidation = $this->validateAssignee($request, $user);
        if (!$assigneeValidation['success']) {
            return $assigneeValidation;
        }

        // Melakukan update tugas
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
        ]);

        return ['success' => true, 'task' => $task];
    }

    /**
     * Update task status
     * Mengupdate status tugas dengan validasi transisi
     */
    public function updateTaskStatus(Request $request, Task $task, $user)
    {
        // Mengecek apakah user bisa mengupdate status (creator atau assignee)
        if ($task->creator_id !== $user->id && $task->assignee_id !== $user->id) {
            return ['success' => false, 'message' => 'Anda tidak dapat mengupdate status tugas ini.'];
        }

        // Validasi input form
        $validator = Validator::make($request->all(), [
            'new_status_id' => 'required|exists:statuses,id',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Mengambil status baru dan status saat ini
        $newStatus = Status::find($request->new_status_id);
        $currentStatus = $task->status;

        // Validasi aturan transisi status
        if (!$this->isValidStatusTransition($currentStatus->name, $newStatus->name, $task, $user)) {
            return ['success' => false, 'errors' => ['new_status_id' => 'Transisi status tidak valid']];
        }

        // Melakukan update status
        $task->update([
            'status_id' => $newStatus->id,
        ]);

        return ['success' => true, 'task' => $task];
    }

    /**
     * Update task report
     * Mengupdate report tugas (hanya bisa diisi saat status Doing)
     */
    public function updateTaskReport(Request $request, Task $task, $user)
    {
        // Mengecek apakah user bisa mengupdate report (creator atau assignee)
        if ($task->creator_id !== $user->id && $task->assignee_id !== $user->id) {
            return ['success' => false, 'message' => 'Anda tidak dapat mengupdate report tugas ini.'];
        }

        // Mengecek apakah tugas dalam status Doing
        if ($task->status->name !== 'doing') {
            return ['success' => false, 'message' => 'Report hanya dapat diisi saat status Doing.'];
        }

        // Mengecek apakah tugas sudah Done (tidak bisa edit report)
        if ($task->status->name === 'done') {
            return ['success' => false, 'message' => 'Report tidak dapat diedit setelah status Done.'];
        }

        // Validasi input form
        $validator = Validator::make($request->all(), [
            'report' => 'required|string|min:10',
        ], [
            'report.required' => 'Report wajib diisi.',
            'report.min' => 'Report minimal 10 karakter.',
        ]);

        // Jika validasi gagal, return error
        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Melakukan update report
        $task->update([
            'report' => $request->report,
        ]);

        return ['success' => true, 'task' => $task];
    }

    /**
     * Delete task
     * Menghapus tugas (hanya creator yang bisa hapus)
     */
    public function deleteTask(Task $task, $user)
    {
        // Hanya creator yang bisa menghapus tugas
        if ($task->creator_id !== $user->id) {
            return ['success' => false, 'message' => 'Anda tidak dapat menghapus tugas ini.'];
        }

        // Melakukan penghapusan tugas
        $task->delete();
        return ['success' => true];
    }

    /**
     * Check if user can create task
     * Mengecek apakah user bisa membuat tugas
     */
    public function canCreateTask($user)
    {
        // Manager dan Staff bisa membuat tugas
        return in_array($user->role->name, ['Manager', 'Staf']);
    }

    /**
     * Check if user can edit task
     * Mengecek apakah user bisa mengedit tugas
     */
    public function canEditTask(Task $task, $user)
    {
        // Hanya creator yang bisa mengedit tugas
        return $task->creator_id === $user->id;
    }

    /**
     * Check if task can be edited
     * Mengecek apakah tugas bisa diedit
     */
    public function canTaskBeEdited(Task $task)
    {
        // Tugas tidak bisa diedit jika statusnya Doing atau Done
        return !in_array($task->status->name, ['doing', 'done']);
    }

    /**
     * Apply manager filters
     * Menerapkan filter khusus untuk Manager
     */
    private function applyManagerFilters($query, Request $request, $user)
    {
        // Filter untuk tugas yang dibuat oleh manager
        if ($request->has('my_created')) {
            $query->where('creator_id', $user->id);
        }
    }

    /**
     * Apply staff filters
     * Menerapkan filter khusus untuk Staff
     */
    private function applyStaffFilters($query, Request $request, $user)
    {
        if ($request->has('my_tasks')) {
            // Filter untuk semua tugas user (yang dibuat atau ditugaskan)
            $query->where(function($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            });
        } elseif ($request->has('assigned_to_me')) {
            // Filter untuk tugas yang ditugaskan kepada user
            $query->where('assignee_id', $user->id);
        } else {
            // Default: tugas yang mereka buat atau yang diberikan kepada mereka
            $query->where(function($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            });
        }
    }

    /**
     * Apply common filters
     * Menerapkan filter umum untuk semua role
     */
    private function applyCommonFilters($query, Request $request, $user)
    {
        // Filter berdasarkan pencarian judul atau deskripsi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $status = Status::where('name', $request->status)->first();
            if ($status) {
                $query->where('status_id', $status->id);
            }
        }

        // Filter berdasarkan assignee (Manager only)
        if ($user->role->name === 'Manager' && $request->filled('assignee')) {
            $query->where('assignee_id', $request->assignee);
        }

        // Filter berdasarkan creator (Staff only)
        if ($user->role->name === 'Staf' && $request->filled('creator')) {
            $query->where('creator_id', $request->creator);
        }
    }

    /**
     * Validate assignee based on role
     * Validasi assignee berdasarkan role user
     */
    private function validateAssignee(Request $request, $user)
    {
        if ($user->role->name === 'Manager') {
            // Manager hanya bisa assign kepada Staff
            if ($request->assignee_id) {
                $assignee = User::find($request->assignee_id);
                if (!$assignee || $assignee->role->name !== 'Staf') {
                    return ['success' => false, 'errors' => ['assignee_id' => 'Manager hanya dapat assign tugas kepada Staf']];
                }
            }
        } else {
            // Staff hanya bisa assign ke diri sendiri
            $request->merge(['assignee_id' => $user->id]);
        }

        return ['success' => true];
    }

    /**
     * Validate status transition
     * Validasi aturan transisi status
     */
    private function isValidStatusTransition($currentStatus, $newStatus, $task, $user)
    {
        // To Do -> Doing
        if ($newStatus === 'doing') {
            return $currentStatus === 'todo';
        }

        // Doing -> Done
        if ($newStatus === 'done') {
            return $currentStatus === 'doing';
        }

        // To Do/Doing -> Canceled (hanya creator, belum ada report)
        if ($newStatus === 'canceled') {
            return ($currentStatus === 'todo' || $currentStatus === 'doing') &&
                   !$task->report &&
                   $task->creator_id === $user->id;
        }

        // Done -> Canceled (tidak diperbolehkan)
        if ($newStatus === 'canceled' && $currentStatus === 'done') {
            return false;
        }

        // Canceled -> status lain (tidak diperbolehkan)
        if ($currentStatus === 'canceled') {
            return false;
        }

        // Done -> status lain (tidak diperbolehkan)
        if ($currentStatus === 'done') {
            return false;
        }

        return false;
    }
}
