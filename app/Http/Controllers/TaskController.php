<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Property untuk menyimpan instance TaskService
    protected $taskService;

    /**
     * Constructor untuk dependency injection TaskService
     * Memungkinkan controller menggunakan layanan task management
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     * Menampilkan daftar semua tugas dengan filter berdasarkan role user
     */
    public function index(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil data tugas dengan filter berdasarkan role (Manager/Staff)
        $tasks = $this->taskService->getTasksWithFilters($request, $user);

        // Mengambil data users untuk dropdown filter (berbeda berdasarkan role)
        $users = $this->taskService->getUsersForFilters($user);

        // Menampilkan halaman index dengan data tugas dan users
        return view('tasks.index', compact('tasks', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk membuat tugas baru
     */
    public function create()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi: hanya user dengan role tertentu yang bisa membuat tugas
        if (!$this->taskService->canCreateTask($user)) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak dapat membuat tugas.');
        }

        // Mengambil data users untuk dropdown assignee (berbeda berdasarkan role)
        $users = $this->taskService->getUsersForAssignee($user);

        // Menampilkan form create dengan data users
        return view('tasks.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan tugas baru ke database
     */
    public function store(Request $request)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi: hanya user dengan role tertentu yang bisa membuat tugas
        if (!$this->taskService->canCreateTask($user)) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak dapat membuat tugas.');
        }

        // Memproses pembuatan tugas melalui service
        // Service akan melakukan validasi dan penyimpanan
        $result = $this->taskService->createTask($request, $user);

        // Jika ada error validasi atau proses gagal, kembali ke form dengan error
        if (!$result['success']) {
            return redirect()->back()->withErrors($result['errors'])->withInput();
        }

        // Jika berhasil, redirect ke halaman index dengan pesan sukses
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     * Menampilkan detail tugas tertentu
     */
    public function show(Task $task)
    {
        // Menampilkan halaman detail tugas
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit tugas
     */
    public function edit(Task $task)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Validasi: hanya creator yang bisa mengedit tugas
        if (!$this->taskService->canEditTask($task, $user)) {
            return redirect()->route('tasks.index')->with('error', 'Anda tidak dapat mengedit tugas ini.');
        }

        // Validasi: tugas tidak bisa diedit jika statusnya Doing atau Done
        if (!$this->taskService->canTaskBeEdited($task)) {
            return redirect()->route('tasks.index')->with('error', 'Tugas tidak dapat diedit jika statusnya Doing atau Done.');
        }

        // Mengambil data users untuk dropdown assignee
        $users = $this->taskService->getUsersForAssignee($user);

        // Menampilkan form edit dengan data task dan users
        return view('tasks.edit', compact('task', 'users'));
    }

    /**
     * Update the specified resource in storage.
     * Mengupdate data tugas di database
     */
    public function update(Request $request, Task $task)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memproses update tugas melalui service
        // Service akan melakukan validasi dan update data
        $result = $this->taskService->updateTask($request, $task, $user);

        // Jika ada error validasi atau proses gagal
        if (!$result['success']) {
            // Jika ada errors array, tampilkan sebagai validation errors
            if (isset($result['errors'])) {
                return redirect()->back()->withErrors($result['errors'])->withInput();
            }
            // Jika ada message string, tampilkan sebagai error message
            return redirect()->route('tasks.index')->with('error', $result['message']);
        }

        // Jika berhasil, redirect ke halaman index dengan pesan sukses
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diupdate!');
    }

    /**
     * Update task status
     * Mengupdate status tugas (Todo -> Doing -> Done)
     */
    public function updateStatus(Request $request, Task $task)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memproses update status tugas melalui service
        // Service akan melakukan validasi transisi status
        $result = $this->taskService->updateTaskStatus($request, $task, $user);

        // Jika ada error validasi atau proses gagal
        if (!$result['success']) {
            // Jika ada errors array, tampilkan sebagai validation errors
            if (isset($result['errors'])) {
                return redirect()->back()->withErrors($result['errors'])->withInput();
            }
            // Jika ada message string, tampilkan sebagai error message
            return redirect()->route('tasks.index')->with('error', $result['message']);
        }

        // Jika berhasil, redirect ke halaman detail tugas dengan pesan sukses
        return redirect()->route('tasks.show', $task)->with('success', 'Status tugas berhasil diupdate!');
    }

    /**
     * Update task report
     * Mengupdate report tugas (hanya bisa diisi saat status Doing)
     */
    public function updateReport(Request $request, Task $task)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memproses update report tugas melalui service
        // Service akan melakukan validasi status dan permission
        $result = $this->taskService->updateTaskReport($request, $task, $user);

        // Jika ada error validasi atau proses gagal
        if (!$result['success']) {
            // Jika ada errors array, tampilkan sebagai validation errors
            if (isset($result['errors'])) {
                return redirect()->back()->withErrors($result['errors'])->withInput();
            }
            // Jika ada message string, tampilkan sebagai error message
            return redirect()->route('tasks.index')->with('error', $result['message']);
        }

        // Jika berhasil, redirect ke halaman detail tugas dengan pesan sukses
        return redirect()->route('tasks.show', $task)->with('success', 'Report berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus tugas dari database
     */
    public function destroy(Task $task)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memproses penghapusan tugas melalui service
        // Service akan melakukan validasi permission (hanya creator yang bisa hapus)
        $result = $this->taskService->deleteTask($task, $user);

        // Jika ada error atau proses gagal, kembali ke index dengan pesan error
        if (!$result['success']) {
            return redirect()->route('tasks.index')->with('error', $result['message']);
        }

        // Jika berhasil, redirect ke halaman index dengan pesan sukses
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }
}
