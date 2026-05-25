<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Migas Calc') }} - Kalkulator Investasi & Keekonomian Migas</title>
        <meta name="description" content="Aplikasi analisis investasi minyak dan gas bumi presisi tinggi berbasis model PSC / Royalty-Tax.">

        <!-- Custom Impeccable CSS -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Fonts & Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="landing-container">
            <!-- Header Navigation -->
            <header class="landing-header">
                <a href="/" class="landing-brand">
                    <i class="fa-solid fa-fire-flame-simple"></i>
                    <span>Migas Calc</span>
                </a>
                
                <nav class="landing-nav">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" style="margin-right: 1.5rem;">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </header>

            <!-- Hero Section -->
            <section class="landing-hero">
                <!-- Left: Headline, Tagline, & CTA -->
                <div class="hero-left">
                    <h1 class="hero-title">
                        Analisis Keekonomian Proyek <em>Migas</em> Presisi Tinggi.
                    </h1>
                    
                    <p class="hero-tagline">
                        Kalkulator investasi minyak & gas bumi modern. Dilengkapi dengan estimasi kurva regresi linier/kuadratik otomatis, 5 pilihan metode depresiasi aset, dan analisis kelayakan komparatif yang disesuaikan untuk standar industri.
                    </p>

                    <div class="hero-feature-box">
                        <span class="hero-feature-title">Fitur Analisis Utama</span>
                        <div class="hero-feature-list">
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Linear Regression Forecast</span>
                            </div>
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Exponential Decline Curve</span>
                            </div>
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>5 Metode Depresiasi Terintegrasi</span>
                            </div>
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Indikator Kelayakan (NPV, IRR, POT)</span>
                            </div>
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Komparasi Profitability (PIR, DPR)</span>
                            </div>
                            <div class="hero-feature-item">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Sensitivitas Suku Bunga (0%-50%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-cta-group">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 0.85rem;">
                                Masuk ke Workspace <i class="fa-solid fa-chevron-right" style="margin-left: 0.5rem;"></i>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 0.85rem;">
                                Mulai Analisis Baru <i class="fa-solid fa-chevron-right" style="margin-left: 0.5rem;"></i>
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-secondary" style="padding: 0.85rem 2rem; font-size: 0.85rem;">
                                Masuk Akun
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right: High-fidelity Simulated Live Card -->
                <div class="hero-right">
                    <div class="mock-card">
                        <div class="mock-card-header">
                            <span class="mock-card-title">Lapangan Jatibarang Alpha</span>
                            <span class="mock-card-badge">Layak (Feasible)</span>
                        </div>

                        <div class="mock-stat-grid">
                            <div class="mock-stat-item">
                                <span class="mock-stat-label">Net Present Value (NPV)</span>
                                <span class="mock-stat-val positive">$18,069.82 M</span>
                            </div>
                            <div class="mock-stat-item">
                                <span class="mock-stat-label">Internal Rate (IRR)</span>
                                <span class="mock-stat-val">18.57 %</span>
                            </div>
                        </div>

                        <div class="mock-table-wrapper">
                            <table class="mock-table">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Prod (Mbbl)</th>
                                        <th>Income ($M)</th>
                                        <th>Net Cash Flow ($M)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Thn 0</td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td class="negative">-$21,000.0</td>
                                    </tr>
                                    <tr>
                                        <td>Thn 1</td>
                                        <td>175.0</td>
                                        <td>$5,600.0</td>
                                        <td class="positive">+$2,141.3</td>
                                    </tr>
                                    <tr>
                                        <td>Thn 2</td>
                                        <td>201.0</td>
                                        <td>$6,432.0</td>
                                        <td class="positive">+$2,549.0</td>
                                    </tr>
                                    <tr>
                                        <td>Thn 3</td>
                                        <td>217.0</td>
                                        <td>$6,944.0</td>
                                        <td class="positive">+$2,790.6</td>
                                    </tr>
                                    <tr>
                                        <td>Thn 4</td>
                                        <td>198.0</td>
                                        <td>$6,336.0</td>
                                        <td class="positive">+$2,488.7</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="landing-footer">
                <div>
                    © {{ date('Y') }} Migas Calc — Dirancang khusus dengan gaya editorial berkinerja tinggi.
                </div>
            </footer>
        </div>
    </body>
</html>
