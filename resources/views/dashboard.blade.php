@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Selamat Datang, {{ Auth::user()->name }}!</h2>
        <p class="text-gray-600">
            @if(Auth::user()->role->name === 'Manager')
                Kelola tim dan tugas dengan mudah melalui dashboard manager ini.
            @else
                Kelola tugas Anda dengan mudah melalui dashboard staf ini.
            @endif
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Tasks -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">
                        @if(Auth::user()->role->name === 'Manager')
                            Total Tugas
                        @else
                            Tugas Saya
                        @endif
                    </p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalTasks }}</p>
                </div>
            </div>
        </div>

        <!-- To Do Tasks -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">To Do</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $todoTasks }}</p>
                </div>
            </div>
        </div>

        <!-- Doing Tasks -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-spinner text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Doing</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $doingTasks }}</p>
                </div>
            </div>
        </div>

        <!-- Done Tasks -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Done</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $doneTasks }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Manager Specific Stats -->
    @if(Auth::user()->role->name === 'Manager')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Staf</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalStaff ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-user-edit text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tugas Saya Buat</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $myCreatedTasks ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tugas Tertunda</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $pendingTasks ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Tasks & My Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    @if(Auth::user()->role->name === 'Manager')
                        Tugas Terbaru
                    @else
                        Tugas Saya Terbaru
                    @endif
                </h3>
            </div>
            <div class="p-6">
                @if($recentTasks->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentTasks as $task)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 50) }}</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span class="text-xs text-gray-500">{{ $task->creator->name }}</span>
                                    @if($task->assignee)
                                        <span class="text-xs text-gray-500">→ {{ $task->assignee->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @php
                                    $statusColors = [
                                        'todo' => 'bg-yellow-100 text-yellow-800',
                                        'doing' => 'bg-blue-100 text-blue-800',
                                        'done' => 'bg-green-100 text-green-800',
                                        'canceled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusLabels = [
                                        'todo' => 'To Do',
                                        'doing' => 'Doing',
                                        'done' => 'Done',
                                        'canceled' => 'Canceled'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$task->status->name] }}">
                                    {{ $statusLabels[$task->status->name] }}
                                </span>
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('tasks.index') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            Lihat Semua Tugas →
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Belum ada tugas</p>
                @endif
            </div>
        </div>

        <!-- My Tasks / Assigned Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    @if(Auth::user()->role->name === 'Manager')
                        Tugas Saya Buat
                    @else
                        Tugas Diberikan ke Saya
                    @endif
                </h3>
            </div>
            <div class="p-6">
                @if($myTasks->count() > 0)
                    <div class="space-y-4">
                        @foreach($myTasks as $task)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 50) }}</p>
                                <div class="flex items-center mt-2 space-x-4">
                                    @if($task->creator_id == Auth::user()->id)
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Creator</span>
                                    @endif
                                    @if($task->assignee_id == Auth::user()->id)
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Assignee</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @php
                                    $statusColors = [
                                        'todo' => 'bg-yellow-100 text-yellow-800',
                                        'doing' => 'bg-blue-100 text-blue-800',
                                        'done' => 'bg-green-100 text-green-800',
                                        'canceled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusLabels = [
                                        'todo' => 'To Do',
                                        'doing' => 'Doing',
                                        'done' => 'Done',
                                        'canceled' => 'Canceled'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$task->status->name] }}">
                                    {{ $statusLabels[$task->status->name] }}
                                </span>
                                <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        @if(Auth::user()->role->name === 'Manager')
                            <a href="{{ route('tasks.index', ['my_created' => 1]) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Lihat Semua Tugas Saya →
                            </a>
                        @else
                            <a href="{{ route('tasks.index', ['assigned_to_me' => 1]) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Lihat Semua Tugas Saya →
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">
                        @if(Auth::user()->role->name === 'Manager')
                            Anda belum membuat tugas
                        @else
                            Belum ada tugas yang diberikan kepada Anda
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('tasks.create') }}"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Tambah Tugas</h4>
                    <p class="text-xs text-gray-500">Buat tugas baru</p>
                </div>
            </a>

            @if(Auth::user()->role->name === 'Manager')
            <a href="{{ route('users.index') }}"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Kelola Staf</h4>
                    <p class="text-xs text-gray-500">Kelola anggota tim</p>
                </div>
            </a>
            @else
            <a href="{{ route('tasks.index', ['assigned_to_me' => 1]) }}"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Tugas Saya</h4>
                    <p class="text-xs text-gray-500">Lihat tugas yang diberikan</p>
                </div>
            </a>
            @endif

            <a href="{{ route('tasks.index') }}?status=doing"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-spinner text-yellow-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Tugas Sedang Berjalan</h4>
                    <p class="text-xs text-gray-500">Lihat tugas yang sedang dikerjakan</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
