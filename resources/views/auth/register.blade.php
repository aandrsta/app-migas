<x-guest-layout>
    <x-slot name="title">Daftar Akun</x-slot>

    <div class="auth-header">
        <a href="/" class="auth-logo">
            <i class="fa-solid fa-fire-flame-simple"></i>
            <span>Migas Calc</span>
        </a>
        <div class="auth-subtitle">Daftarkan akun baru untuk mengelola analisis proyek Anda</div>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="John Doe" />
            @if ($errors->has('name'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('name') }}
                </span>
            @endif
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@perusahaan.com" />
            @if ($errors->has('email'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('email') }}
                </span>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            @if ($errors->has('password'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('password') }}
                </span>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            @if ($errors->has('password_confirmation'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('password_confirmation') }}
                </span>
            @endif
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
            <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
        </button>
    </form>

    <div class="auth-footer-link">
        Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>
</x-guest-layout>
