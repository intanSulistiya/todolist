@extends('layout')

@section('title', 'Detail Tugas')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Detail Tugas</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('tasks.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    @if(Auth::user()->id == $task->creator_id || Auth::user()->id == $task->assignee_id)
                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Task Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Judul Tugas</h3>
                        <p class="text-gray-700">{{ $task->title }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Deskripsi</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $task->description }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Status</h3>
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
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$task->status->name] }}">
                            {{ $statusLabels[$task->status->name] }}
                        </span>
                    </div>

                    <!-- Report -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Report</h3>
                        @if($task->report)
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $task->report }}</p>
                                @if($task->status->name == 'done')
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-lock mr-1"></i>Report Final (Tidak dapat diedit)
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500 italic">Belum ada report</p>
                        @endif

                        @if($task->status->name == 'done' && $task->report)
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                    <p class="text-sm text-yellow-800">
                                        <strong>Info:</strong> Report tidak dapat diedit setelah status "Done".
                                        Report ini bersifat final dan tidak dapat diubah lagi.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Creator -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Creator</h3>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $task->creator->name }}</p>
                                <p class="text-sm text-gray-500">{{ $task->creator->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assignee -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Assignee</h3>
                        @if($task->assignee)
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-check text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $task->assignee->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $task->assignee->email }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 italic">Belum ada assignee</p>
                        @endif
                    </div>

                    <!-- Created & Updated -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Waktu</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dibuat:</span>
                                <span class="text-gray-900">{{ $task->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Terakhir diupdate:</span>
                                <span class="text-gray-900">{{ $task->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Section (hanya untuk status Doing) -->
            @if($task->status->name == 'doing' && (Auth::user()->id == $task->creator_id || Auth::user()->id == $task->assignee_id))
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    @if($task->report)
                        Edit Report
                    @else
                        Isi Report
                    @endif
                </h3>

                <form method="POST" action="{{ route('tasks.updateReport', $task) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="report" class="block text-sm font-medium text-gray-700 mb-2">
                            Report <span class="text-red-500">*</span>
                        </label>
                        <textarea name="report" id="report" rows="6" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Masukkan hasil kerja tugas, kemajuan, dan detail pekerjaan yang telah dilakukan...">{{ old('report', $task->report) }}</textarea>
                        @error('report')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Report Guidelines -->
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <h4 class="text-sm font-medium text-green-900 mb-2">Panduan Mengisi Report:</h4>
                        <ul class="text-sm text-green-800 space-y-1">
                            <li>• <strong>Deskripsi pekerjaan:</strong> Jelaskan apa yang telah dikerjakan</li>
                            <li>• <strong>Kemajuan:</strong> Berapa persen progress yang telah dicapai</li>
                            <li>• <strong>Kendala:</strong> Jika ada hambatan atau masalah</li>
                            <li>• <strong>Langkah selanjutnya:</strong> Rencana pekerjaan berikutnya</li>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium">
                            <i class="fas fa-save mr-2"></i>
                            @if($task->report)
                                Update Report
                            @else
                                Simpan Report
                            @endif
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Status Change Section -->
            @if(Auth::user()->id == $task->creator_id || Auth::user()->id == $task->assignee_id)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ubah Status</h3>

                <form method="POST" action="{{ route('tasks.updateStatus', $task) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Status Selection -->
                        <div>
                            <label for="new_status_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Status Baru
                            </label>
                            <select name="new_status_id" id="new_status_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Status</option>

                                <!-- Doing (hanya dari To Do) -->
                                @if($task->status->name == 'todo')
                                    <option value="{{ \App\Models\Status::where('name', 'doing')->first()->id ?? '' }}">Doing</option>
                                @endif

                                <!-- Done (hanya dari Doing) -->
                                @if($task->status->name == 'doing')
                                    <option value="{{ \App\Models\Status::where('name', 'done')->first()->id ?? '' }}">Done</option>
                                @endif

                                <!-- Canceled (hanya dari To Do/Doing, belum ada report, hanya creator) -->
                                @if(($task->status->name == 'todo' || $task->status->name == 'doing') && !$task->report && Auth::user()->id == $task->creator_id)
                                    <option value="{{ \App\Models\Status::where('name', 'canceled')->first()->id ?? '' }}">Canceled</option>
                                @endif
                            </select>
                            @error('new_status_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Rules Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Aturan Perubahan Status:</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• <strong>To Do → Doing:</strong> Tugas mulai dikerjakan</li>
                            <li>• <strong>Doing → Done:</strong> Tugas selesai dikerjakan</li>
                            <li>• <strong>To Do/Doing → Canceled:</strong> Hanya creator, belum ada report</li>
                            <li>• <strong>Done/Canceled:</strong> Tidak dapat diubah lagi</li>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                            <i class="fas fa-save mr-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>


@endsection
