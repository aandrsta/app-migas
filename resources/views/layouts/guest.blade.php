<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' . config('app.name', 'Migas Calculator') : config('app.name', 'Migas Calculator') }}</title>

        <!-- Custom Glassmorphism CSS -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Fonts & Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
