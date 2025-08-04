@extends('layout')

@section('title', 'Tambah Tugas Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Tambah Tugas Baru</h2>
                <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('tasks.store') }}" class="p-6">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Masukkan judul tugas">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Masukkan deskripsi tugas">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Info -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-md">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        To Do
                    </span>
                    <p class="mt-1 text-sm text-gray-600">Status default untuk tugas baru. Status dapat diubah setelah tugas dibuat.</p>
                </div>
            </div>

            <!-- Assignee -->
            @if(Auth::user()->role->name === 'Manager')
            <div class="mb-6">
                <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Assignee
                </label>
                <select name="assignee_id" id="assignee_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Assignee</option>
                    @foreach($users as $user)
                        @if($user->role->name === 'Staf')
                        <option value="{{ $user->id }}" {{ old('assignee_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} (Staf)
                        </option>
                        @endif
                    @endforeach
                </select>
                @error('assignee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">
                    Anda dapat memberikan tugas kepada staf
                </p>
            </div>
            @elseif(Auth::user()->role->name === 'Staf')
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assignee
                </label>
                <div class="px-3 py-2 bg-blue-50 border border-blue-200 rounded-md">
                    <span class="text-sm text-blue-800">
                        <i class="fas fa-user mr-2"></i>{{ Auth::user()->name }} (Anda)
                    </span>
                    <p class="mt-1 text-sm text-gray-600">Tugas akan diberikan kepada Anda sendiri</p>
                </div>
            </div>
            @endif

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('tasks.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
