<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' . config('app.name', 'Migas Calculator') : config('app.name', 'Migas Calculator') }}</title>
        <meta name="description" content="Aplikasi Perhitungan Investasi Minyak dan Gas Bumi - Premium Analysis Tool">

        <!-- Custom Glassmorphism CSS -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Fonts & Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Chart.js 4.x CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <div class="app-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a href="{{ route('projects.index') }}" class="sidebar-logo">
                    <i class="fa-solid fa-fire-flame-simple"></i>
                    <span>Migas Calc</span>
                </a>

                <ul class="sidebar-menu">
                    <li class="menu-item {{ Route::is('projects.index') || Route::is('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('projects.index') }}">
                            <i class="fa-solid fa-folder-open"></i>
                            <span>Proyek Saya</span>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('projects.create') ? 'active' : '' }}">
                        <a href="{{ route('projects.create') }}">
                            <span>Proyek Baru</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-footer">
                    <div class="user-info">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-email">{{ auth()->user()->email }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Alerts / Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
