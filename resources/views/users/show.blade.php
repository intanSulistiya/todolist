@extends('layout')

@section('title', 'Detail Staf')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Detail Staf</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900">{{ $user->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mt-1">
                                        {{ $user->role->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manager Info -->
                    @if($user->manager)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Manager</h3>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-tie text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $user->manager->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->manager->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Statistics -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik Tugas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                        <i class="fas fa-tasks text-blue-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-500">Total Tugas</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $totalTasks ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-500">Selesai</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $completedTasks ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-500">To Do</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $todoTasks ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                        <i class="fas fa-spinner text-blue-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-500">Doing</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $doingTasks ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Recent Tasks -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tugas Terbaru</h3>
                        @if(isset($recentTasks) && $recentTasks->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentTasks as $task)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $task->title }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($task->description, 50) }}</p>
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
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$task->status] }}">
                                                {{ $statusLabels[$task->status] }}
                                            </span>
                                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('tasks.index', ['assignee' => $user->id]) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    Lihat Semua Tugas →
                                </a>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Belum ada tugas</p>
                        @endif
                    </div>

                    <!-- Performance -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Performa</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Tingkat Penyelesaian</span>
                                <span class="text-sm font-medium text-gray-900">
                                    @if($totalTasks > 0)
                                        {{ round(($completedTasks / $totalTasks) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
