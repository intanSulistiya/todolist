@extends('layout')

@section('title', 'Daftar Tugas')

@section('content')
<div class="bg-white shadow rounded-lg">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    @if(request('my_created'))
                        Tugas yang saya buat
                    @elseif(request('my_tasks'))
                        Tugas Saya
                    @elseif(request('assigned_to_me'))
                        Tugas Diberikan ke Saya
                    @else
                        Daftar Tugas
                    @endif
                </h2>
                @if(request('my_created'))
                    <p class="text-sm text-gray-500 mt-1">Tugas yang Anda buat sebagai Manager</p>
                @elseif(request('my_tasks'))
                    <p class="text-sm text-gray-500 mt-1">Tugas yang Anda buat atau diberikan kepada Anda</p>
                @elseif(request('assigned_to_me'))
                    <p class="text-sm text-gray-500 mt-1">Tugas yang diberikan kepada Anda</p>
                @else
                    <p class="text-sm text-gray-500 mt-1">Semua tugas dalam sistem</p>
                @endif
            </div>
            @if(Auth::user()->role->name === 'Manager' || Auth::user()->role->name === 'Staf')
            <a href="{{ route('tasks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>Tambah Tugas
            </a>
            @endif
        </div>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Cari judul atau deskripsi...">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="doing" {{ request('status') == 'doing' ? 'selected' : '' }}>Doing</option>
                    <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>

            @if(Auth::user()->role->name === 'Manager')
            <div>
                <label for="assignee" class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                <select name="assignee" id="assignee" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Assignee</option>
                    @foreach($users as $user)
                        @if($user->role->name === 'Staf')
                        <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            @elseif(Auth::user()->role->name === 'Staf')
            <div>
                <label for="creator" class="block text-sm font-medium text-gray-700 mb-1">Creator</label>
                <select name="creator" id="creator" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Creator</option>
                    @foreach($users as $user)
                        @if($user->role->name === 'Manager')
                        <option value="{{ $user->id }}" {{ request('creator') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </form>

        <!-- Quick Filter Buttons -->
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('tasks.index') }}" class="px-3 py-1 text-xs font-medium rounded-full {{ !request('my_created') && !request('my_tasks') && !request('assigned_to_me') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                Semua
            </a>
            @if(Auth::user()->role->name === 'Manager')
            <a href="{{ route('tasks.index', ['my_created' => 1]) }}" class="px-3 py-1 text-xs font-medium rounded-full {{ request('my_created') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                Saya Buat
            </a>
            @else
            <a href="{{ route('tasks.index', ['my_tasks' => 1]) }}" class="px-3 py-1 text-xs font-medium rounded-full {{ request('my_tasks') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                Tugas Saya
            </a>
            <a href="{{ route('tasks.index', ['assigned_to_me' => 1]) }}" class="px-3 py-1 text-xs font-medium rounded-full {{ request('assigned_to_me') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                Diberikan ke Saya
            </a>
            @endif
        </div>
    </div>

    <!-- Daftar Tugas -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ $task->description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm text-gray-900">{{ $task->creator->name }}</div>
                            @if($task->creator->role->name === 'Manager')
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Manager
                                </span>
                            @else
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Staf
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($task->assignee)
                            <div class="flex items-center">
                                <div class="text-sm text-gray-900">{{ $task->assignee->name }}</div>
                                @if($task->assignee->role->name === 'Manager')
                                    <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Manager
                                    </span>
                                @else
                                    <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Staf
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($task->report)
                            <span class="text-green-600 text-sm">✓ Sudah ada</span>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(Auth::user()->id == $task->creator_id && !in_array($task->status, ['doing', 'done']))
                            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @if(Auth::user()->id == $task->creator_id)
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline" onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        @if(request('my_created'))
                            Anda belum membuat tugas
                        @elseif(request('my_tasks'))
                            Anda belum memiliki tugas
                        @elseif(request('assigned_to_me'))
                            Belum ada tugas yang diberikan kepada Anda
                        @else
                            Tidak ada tugas yang ditemukan.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $tasks->links() }}
    </div>
    @endif
</div>
@endsection
