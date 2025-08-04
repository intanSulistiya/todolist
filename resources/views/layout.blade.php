<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'To-Do List App')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">To-Do List App</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ Auth::user()->role->name === 'Manager' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ Auth::user()->role->name }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i>Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-sign-in-alt mr-1"></i>Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        @auth
        <aside class="w-64 bg-white shadow-sm">
            <nav class="mt-5 px-2">
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                        Dashboard
                    </a>

                    <!-- Menu untuk Manager -->
                    @if(Auth::user()->role->name === 'Manager')
                        <a href="{{ route('tasks.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-tasks mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Semua Tugas
                        </a>
                        <a href="{{ route('tasks.create') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-plus mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Tambah Tugas
                        </a>
                        <a href="{{ route('users.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-users mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Kelola Staf
                        </a>
                        {{-- <a href="{{ route('tasks.index', ['my_created' => 1]) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-user-edit mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Tugas Saya
                        </a> --}}
                    @endif

                    <!-- Menu untuk Staf -->
                    @if(Auth::user()->role->name === 'Staf')
                        <a href="{{ route('tasks.index', ['my_tasks' => 1]) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-tasks mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Tugas Saya
                        </a>
                        {{-- <a href="{{ route('tasks.index', ['assigned_to_me' => 1]) }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-user-check mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Tugas Diberikan ke Saya
                        </a> --}}
                        {{-- <a href="{{ route('tasks.create') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-plus mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Buat Tugas Baru
                        </a> --}}
                    @endif

                    <!-- Menu untuk semua role -->
                    {{-- <a href="{{ route('tasks.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <i class="fas fa-list mr-3 text-gray-400 group-hover:text-gray-500"></i>
                        Semua Tugas
                    </a> --}}
                </div>
            </nav>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <main class="flex-1 overflow-auto">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} To-Do List App. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- JavaScript untuk interaksi -->
    <script>
        // Toggle sidebar untuk mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
        }

        // Konfirmasi sebelum menghapus
        function confirmDelete(message = 'Apakah Anda yakin ingin menghapus item ini?') {
            return confirm(message);
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
