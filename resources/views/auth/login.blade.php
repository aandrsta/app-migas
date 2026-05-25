<x-guest-layout>
    <x-slot name="title">Masuk</x-slot>

    <div class="auth-header">
        <a href="/" class="auth-logo">
            <i class="fa-solid fa-fire-flame-simple"></i>
            <span>Migas Calc</span>
        </a>
        <div class="auth-subtitle">Sistem Analisis Kelayakan Investasi Proyek Migas</div>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; font-size: 0.85rem;">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@perusahaan.com" />
            @if ($errors->has('email'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('email') }}
                </span>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            @if ($errors->has('password'))
                <span style="color: var(--rose); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    {{ $errors->first('password') }}
                </span>
            @endif
        </div>

        <!-- Remember Me & Forgot Password -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <label for="remember_me" style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary);">
                <input id="remember_me" type="checkbox" name="remember" style="accent-color: var(--cyan);">
                <span>Ingat Saya</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color: var(--text-muted); text-decoration: none; transition: var(--transition-smooth);" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-muted)'">Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Dashboard
        </button>
    </form>

    <div class="auth-footer-link">
        Belum memiliki akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
    </div>
</x-guest-layout>
