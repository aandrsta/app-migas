<x-app-layout>
    <x-slot name="title">Detail Proyek - {{ $project->name }}</x-slot>

    <div class="page-title">
        <div>
            <span>{{ $project->name }}</span>
            <div style="font-size: 0.9rem; color: var(--text-secondary); font-weight: 400; margin-top: 0.25rem;">
                Analisis Investasi & Keekonomian Migas
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">
                <i class="fa-solid fa-pen-to-square"></i> Edit Proyek
            </a>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-folder-open"></i> Semua Proyek
            </a>
        </div>
    </div>

    <!-- TABS NAVIGATION -->
    <nav class="tabs-navigation">
        <button class="tab-btn active" onclick="switchTab(event, 'tab-produksi')">
            <i class="fa-solid fa-chart-line"></i> Proyeksi Produksi
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'tab-depresiasi')">
            <i class="fa-solid fa-calculator"></i> Analisis Depresiasi
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'tab-cashflow')">
            <i class="fa-solid fa-money-bill-trend-up"></i> Cash Flow (NCF)
        </button>
        <button class="tab-btn" onclick="switchTab(event, 'tab-indikator')">
            <i class="fa-solid fa-shield-halved"></i> Kelayakan Investasi
        </button>
    </nav>

    <!-- ========================================== -->
    <!-- TAB 1: PRODUCTION FORECAST -->
    <!-- ========================================== -->
    <div class="tab-pane active" id="tab-produksi">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            <!-- Chart Container -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.5rem; color: var(--cyan);"><i class="fa-solid fa-chart-line"></i> Grafik Proyeksi Produksi (MBBL)</h3>
                <div style="position: relative; height: 380px; width: 100%;">
                    <canvas id="productionChart"></canvas>
                </div>
            </div>

            <!-- Regression Info Box -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="glass-card glow-cyan">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: var(--amber);">
                        <i class="fa-solid fa-square-root-variable"></i> Hasil Regresi Linier
                    </h3>
                    <div style="font-size: 1.35rem; font-weight: 700; font-family: monospace; color: var(--text-primary); margin: 0.5rem 0;">
                        y = {{ number_format($linearRegression['m'], 4) }}x + {{ number_format($linearRegression['b'], 2) }}
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">
                        Digunakan untuk memproyeksikan data produksi tahun ke-{{ $project->known_years + 1 }} sampai ke-{{ $project->known_years + $project->prediction_years }}.
                    </p>
                    <div style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--text-muted);">
                        Nilai Koefisien R²: <span style="color: var(--cyan); font-weight: bold;">{{ number_format($linearRegression['r_squared'], 4) }}</span>
                    </div>
                </div>

                <div class="glass-card glow-cyan">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem; color: var(--violet);">
                        <i class="fa-solid fa-chart-curve"></i> Kurva Regresi Kuadratik
                    </h3>
                    <div style="font-size: 1.2rem; font-weight: 700; font-family: monospace; color: var(--text-primary); margin: 0.5rem 0; word-break: break-all;">
                        y = {{ number_format($quadraticRegression['a'], 3) }}x² + {{ number_format($quadraticRegression['b'], 3) }}x + {{ number_format($quadraticRegression['c'], 2) }}
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">
                        Kurva pembanding kuadratik untuk mengukur kesesuaian tren peningkatan/penurunan produksi lapangan.
                    </p>
                    <div style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--text-muted);">
                        Nilai Koefisien R²: <span style="color: var(--violet); font-weight: bold;">{{ number_format($quadraticRegression['r_squared'], 4) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Table -->
        <div class="glass-card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);"><i class="fa-solid fa-list-ol"></i> Rincian Volume Produksi Tahunan</h3>
            <div class="table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Volume Produksi (MBBL)</th>
                            <th>Status Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->productionData as $data)
                            <tr>
                                <td>Tahun {{ $data->year }}</td>
                                <td style="font-weight: bold;">{{ number_format($data->production, 4) }} MBBL</td>
                                <td>
                                    @if($data->is_predicted)
                                        <span class="badge badge-amber"><i class="fa-solid fa-robot"></i> Prediksi Linier</span>
                                    @else
                                        <span class="badge badge-cyan"><i class="fa-solid fa-circle-check"></i> Aktual</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: DEPRECIATION TABS -->
    <!-- ========================================== -->
    <div class="tab-pane" id="tab-depresiasi">
        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; color: var(--cyan);"><i class="fa-solid fa-chart-column"></i> Perbandingan Kurva Depresiasi ($M)</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; max-width: 700px;">
                Grafik di bawah memperbandingkan metode depresiasi **{{ str_replace('_', ' ', $project->depreciation_method) }}** yang Anda pilih (bar berwarna solid) dengan metode-metode lainnya (garis putus-putus) untuk dasar investasi sebesar **${{ number_format($project->capital_cost + $project->non_capital_cost, 2) }} M**.
            </p>
            <div style="position: relative; height: 380px; width: 100%;">
                <canvas id="depreciationChart"></canvas>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);"><i class="fa-solid fa-calculator"></i> Tabel Depresiasi Metode Pilihan</h3>
            <div class="table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Metode Terpilih</th>
                            <th>Nilai Depresiasi ($M)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDep = 0.0; @endphp
                        @foreach($project->calculations as $calc)
                            @if($calc->year > 0)
                                @php $totalDep += $calc->depreciation; @endphp
                                <tr>
                                    <td>Tahun {{ $calc->year }}</td>
                                    <td><span class="badge badge-cyan">{{ str_replace('_', ' ', $project->depreciation_method) }}</span></td>
                                    <td style="font-weight: 700;">${{ number_format($calc->depreciation, 4) }} M</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="total-row">
                            <td>Total Kumulatif</td>
                            <td>—</td>
                            <td>${{ number_format($totalDep, 4) }} M</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: CASH FLOW TABLE -->
    <!-- ========================================== -->
    <div class="tab-pane" id="tab-cashflow">
        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; color: var(--cyan);"><i class="fa-solid fa-money-bill-transfer"></i> Grafik Net Cash Flow vs Kumulatif ($M)</h3>
            <div style="position: relative; height: 380px; width: 100%;">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-primary);"><i class="fa-solid fa-table-list"></i> Lembar Kerja Keekonomian Proyek (Cash Flow)</h3>
            <div class="table-responsive">
                <table class="premium-table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Thn</th>
                            <th>Produksi (Mbbl)</th>
                            <th>Income ($M)</th>
                            <th>Capital ($M)</th>
                            <th>Non-Cap ($M)</th>
                            <th>OPEX ($M)</th>
                            <th>Depresiasi ($M)</th>
                            <th>Taxable Inc ($M)</th>
                            <th>Tax ($M)</th>
                            <th>NCF ($M)</th>
                            <th>Kum. NCF ($M)</th>
                            <th>D.F</th>
                            <th>PV NCF ($M)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sumProd = 0.0;
                            $sumInc = 0.0;
                            $sumCap = 0.0;
                            $sumNonCap = 0.0;
                            $sumOpex = 0.0;
                            $sumDep = 0.0;
                            $sumTax = 0.0;
                            $sumNcf = 0.0;
                            $sumPvNcf = 0.0;
                        @endphp
                        @foreach($project->calculations as $calc)
                            @php
                                $sumProd += $calc->production;
                                $sumInc += $calc->income;
                                $sumCap += $calc->capital;
                                $sumNonCap += $calc->non_capital;
                                $sumOpex += $calc->opex;
                                $sumDep += $calc->depreciation;
                                $sumTax += $calc->tax;
                                $sumNcf += $calc->ncf;
                                $sumPvNcf += $calc->pv_ncf;
                            @endphp
                            <tr class="{{ $calc->year === 0 ? 'total-row' : '' }}" style="{{ $calc->year === 0 ? 'background: rgba(244, 63, 94, 0.05); color: var(--rose);' : '' }}">
                                <td>{{ $calc->year }}</td>
                                <td>{{ $calc->year === 0 ? '—' : number_format($calc->production, 2) }}</td>
                                <td>{{ $calc->year === 0 ? '—' : '$'.number_format($calc->income, 2) }}</td>
                                <td>{{ $calc->capital > 0 ? '$'.number_format($calc->capital, 1) : '—' }}</td>
                                <td>{{ $calc->non_capital > 0 ? '$'.number_format($calc->non_capital, 1) : '—' }}</td>
                                <td>{{ $calc->year === 0 ? '—' : '$'.number_format($calc->opex, 1) }}</td>
                                <td>{{ $calc->year === 0 ? '—' : '$'.number_format($calc->depreciation, 1) }}</td>
                                <td>{{ $calc->year === 0 ? '—' : '$'.number_format($calc->taxable_income, 1) }}</td>
                                <td>{{ $calc->year === 0 ? '—' : '$'.number_format($calc->tax, 1) }}</td>
                                <td style="font-weight: 700; color: {{ $calc->ncf >= 0 ? 'var(--emerald)' : 'var(--rose)' }}">
                                    ${{ number_format($calc->ncf, 2) }}
                                </td>
                                <td style="font-weight: 500;">${{ number_format($calc->cumulative_ncf, 2) }}</td>
                                <td>{{ number_format($calc->discount_factor, 4) }}</td>
                                <td style="font-weight: 700; color: {{ $calc->pv_ncf >= 0 ? 'var(--emerald)' : 'var(--rose)' }}">
                                    ${{ number_format($calc->pv_ncf, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td>{{ number_format($sumProd, 2) }}</td>
                            <td>${{ number_format($sumInc, 2) }}</td>
                            <td>${{ number_format($sumCap, 1) }}</td>
                            <td>${{ number_format($sumNonCap, 1) }}</td>
                            <td>${{ number_format($sumOpex, 1) }}</td>
                            <td>${{ number_format($sumDep, 1) }}</td>
                            <td>—</td>
                            <td>${{ number_format($sumTax, 2) }}</td>
                            <td style="color: {{ $sumNcf >= 0 ? 'var(--emerald)' : 'var(--rose)' }}">${{ number_format($sumNcf, 2) }}</td>
                            <td>—</td>
                            <td>—</td>
                            <td style="color: {{ $sumPvNcf >= 0 ? 'var(--emerald)' : 'var(--rose)' }}">${{ number_format($sumPvNcf, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: ECONOMIC INDICATORS -->
    <!-- ========================================== -->
    <div class="tab-pane" id="tab-indikator">
        <!-- Feasibility Box -->
        <div class="feasibility-box" style="background: rgba({{ $feasibility['color'] === 'emerald' ? '16, 185, 129' : '244, 63, 94' }}, 0.15); border: 1px solid var(--{{ $feasibility['color'] }});">
            <div class="feasibility-icon" style="background: var(--{{ $feasibility['color'] }}); color: var(--bg-main);">
                @if($feasibility['is_feasible'])
                    <i class="fa-solid fa-thumbs-up"></i>
                @else
                    <i class="fa-solid fa-thumbs-down"></i>
                @endif
            </div>
            <div class="feasibility-details">
                <span style="color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Kesimpulan Analisis Keekonomian:</span>
                <div class="feasibility-title" style="color: var(--{{ $feasibility['color'] }});">
                    PROYEK INI DINYATAKAN {{ $feasibility['status'] }}
                </div>
                <p style="color: var(--text-primary); font-size: 0.85rem; margin-top: 0.25rem;">
                    @if($feasibility['is_feasible'])
                        Proyek ini layak dijalankan karena menghasilkan NPV Positif (${{ number_format($npv, 2) }} M) dan tingkat pengembalian internal (IRR = {{ $irr }}%) berada di atas discount rate acuan ({{ $project->discount_rate }}%).
                    @else
                        Proyek ini tidak layak dijalankan karena nilai NPV negatif (${{ number_format($npv, 2) }} M) atau nilai IRR ({{ $irr }}%) berada di bawah discount rate acuan ({{ $project->discount_rate }}%).
                    @endif
                </p>
            </div>
        </div>

        <!-- 5 Stat Cards Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-amber">
                <span class="stat-label">POT (Pay Out Time)</span>
                <div class="stat-value">{{ $pot !== null ? $pot . ' Thn' : 'Never' }}</div>
                <span class="stat-desc">Waktu pengembalian investasi modal (Capital)</span>
            </div>

            <div class="stat-card {{ $npv >= 0 ? 'stat-emerald' : 'stat-rose' }}">
                <span class="stat-label">NPV (Net Present Value)</span>
                <div class="stat-value">${{ number_format($npv, 2) }} M</div>
                <span class="stat-desc">Nilai bersih proyek saat ini (discounted)</span>
            </div>

            <div class="stat-card {{ $irr >= $project->discount_rate ? 'stat-emerald' : 'stat-rose' }}">
                <span class="stat-label">IRR (Internal Rate of Return)</span>
                <div class="stat-value">{{ $irr }} %</div>
                <span class="stat-desc">Kemampuan pengembalian bunga proyek (ROR)</span>
            </div>

            <div class="stat-card stat-cyan">
                <span class="stat-label">PIR (Profitability Index)</span>
                <div class="stat-value">{{ $pir }}</div>
                <span class="stat-desc">Rasio keuntungan undiscounted vs investasi</span>
            </div>

            <div class="stat-card stat-violet">
                <span class="stat-label">DPR (Discounted Profitability)</span>
                <div class="stat-value">{{ $dpr }}</div>
                <span class="stat-desc">Rasio keuntungan discounted vs PV investasi</span>
            </div>
        </div>

        <!-- Sensitivity Chart -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; color: var(--cyan);"><i class="fa-solid fa-chart-area"></i> Sensitivitas NPV vs Suku Bunga (Discount Rate)</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; max-width: 700px;">
                Grafik di bawah menggambarkan sensitivitas NPV proyek pada rentang discount rate dari 0% hingga 50%. Titik potong garis dengan sumbu horizontal (NPV = 0) menunjukkan nilai **IRR ({{ $irr }}%)**.
            </p>
            <div style="position: relative; height: 380px; width: 100%;">
                <canvas id="sensitivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- JAVASCRIPT FOR TABS & CHART RENDERING -->
    <!-- ========================================== -->
    <script>
        // Tab switching logic
        function switchTab(evt, tabId) {
            // Hide all tab content
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(pane => pane.classList.remove('active'));

            // Remove active state from all buttons
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));

            // Show active pane and button
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        // Global Chart variables so we can destroy/init
        let prodChart, depChart, cfChart, sensChart;

        // Fetch chart data on load and render charts
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode colors for ChartJs
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
            Chart.defaults.font.family = "'Inter', sans-serif";

            fetch("{{ route('projects.chart-data', $project) }}")
                .then(res => res.json())
                .then(data => {
                    renderProductionChart(data);
                    renderDepreciationChart(data);
                    renderCashFlowChart(data);
                    renderSensitivityChart(data);
                })
                .catch(err => console.error("Gagal memuat grafik:", err));
        });

        // 1. Render Production Forecast Chart
        function renderProductionChart(data) {
            const ctx = document.getElementById('productionChart').getContext('2d');
            
            // Map actual and predicted data to full years range so they connect
            const years = data.years;
            const actualDataset = [];
            const predictedDataset = [];
            const linearDataset = [];
            const quadraticDataset = [];

            years.forEach(yr => {
                actualDataset.push(data.production.actual[yr] !== undefined ? data.production.actual[yr] : null);
                predictedDataset.push(data.production.predicted[yr] !== undefined ? data.production.predicted[yr] : null);
                linearDataset.push(data.production.linearCurve[yr] !== undefined ? data.production.linearCurve[yr] : null);
                quadraticDataset.push(data.production.quadraticCurve[yr] !== undefined ? data.production.quadraticCurve[yr] : null);
            });

            // Connect actual with predicted for beautiful solid flow
            const lastKnownYear = {{ $project->known_years }};
            if(data.production.actual[lastKnownYear] !== undefined) {
                predictedDataset[lastKnownYear - 1] = data.production.actual[lastKnownYear];
            }

            prodChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: years.map(yr => 'Thn ' + yr),
                    datasets: [
                        {
                            label: 'Produksi Aktual',
                            data: actualDataset,
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6, 182, 212, 0.05)',
                            borderWidth: 3,
                            pointBackgroundColor: '#06b6d4',
                            fill: true,
                            tension: 0.15
                        },
                        {
                            label: 'Proyeksi Linier',
                            data: predictedDataset,
                            borderColor: '#f59e0b',
                            borderDash: [5, 5],
                            borderWidth: 3,
                            pointBackgroundColor: '#f59e0b',
                            fill: false,
                            tension: 0.1
                        },
                        {
                            label: 'Tren Regresi Linier (y=mx+b)',
                            data: linearDataset,
                            borderColor: 'rgba(245, 158, 11, 0.35)',
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            label: 'Pembanding Kuadratik',
                            data: quadraticDataset,
                            borderColor: 'rgba(139, 92, 246, 0.45)',
                            borderDash: [2, 3],
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: false,
                            tension: 0.25
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 15, padding: 15 } }
                    },
                    scales: {
                        y: { title: { display: true, text: 'Volume Produksi (MBBL)' } }
                    }
                }
            });
        }

        // 2. Render Depreciation Comparison Chart
        function renderDepreciationChart(data) {
            const ctx = document.getElementById('depreciationChart').getContext('2d');
            const chosenMethod = data.depreciation.chosen_method;
            const comparison = data.depreciation.comparison;

            // Generate X labels
            const yearsCount = Object.keys(comparison.straight_line).length;
            const labels = Array.from({length: yearsCount}, (_, i) => 'Thn ' + (i + 1));

            // Map data
            const datasets = [
                {
                    label: 'Straight Line',
                    data: Object.values(comparison.straight_line),
                    borderColor: '#06b6d4',
                    borderWidth: chosenMethod === 'straight_line' ? 3.5 : 1.5,
                    borderDash: chosenMethod === 'straight_line' ? [] : [4, 4],
                    backgroundColor: chosenMethod === 'straight_line' ? 'rgba(6, 182, 212, 0.15)' : 'transparent',
                    type: chosenMethod === 'straight_line' ? 'bar' : 'line',
                    fill: false
                },
                {
                    label: 'Declining Balance',
                    data: Object.values(comparison.declining_balance),
                    borderColor: '#f59e0b',
                    borderWidth: chosenMethod === 'declining_balance' ? 3.5 : 1.5,
                    borderDash: chosenMethod === 'declining_balance' ? [] : [4, 4],
                    backgroundColor: chosenMethod === 'declining_balance' ? 'rgba(245, 158, 11, 0.15)' : 'transparent',
                    type: chosenMethod === 'declining_balance' ? 'bar' : 'line',
                    fill: false
                },
                {
                    label: 'Double Declining',
                    data: Object.values(comparison.double_declining),
                    borderColor: '#10b981',
                    borderWidth: chosenMethod === 'double_declining' ? 3.5 : 1.5,
                    borderDash: chosenMethod === 'double_declining' ? [] : [4, 4],
                    backgroundColor: chosenMethod === 'double_declining' ? 'rgba(16, 185, 129, 0.15)' : 'transparent',
                    type: chosenMethod === 'double_declining' ? 'bar' : 'line',
                    fill: false
                },
                {
                    label: 'Unit of Production',
                    data: Object.values(comparison.unit_of_production),
                    borderColor: '#f43f5e',
                    borderWidth: chosenMethod === 'unit_of_production' ? 3.5 : 1.5,
                    borderDash: chosenMethod === 'unit_of_production' ? [] : [4, 4],
                    backgroundColor: chosenMethod === 'unit_of_production' ? 'rgba(244, 63, 94, 0.15)' : 'transparent',
                    type: chosenMethod === 'unit_of_production' ? 'bar' : 'line',
                    fill: false
                },
                {
                    label: 'Sum of Year',
                    data: Object.values(comparison.sum_of_year),
                    borderColor: '#8b5cf6',
                    borderWidth: chosenMethod === 'sum_of_year' ? 3.5 : 1.5,
                    borderDash: chosenMethod === 'sum_of_year' ? [] : [4, 4],
                    backgroundColor: chosenMethod === 'sum_of_year' ? 'rgba(139, 92, 246, 0.15)' : 'transparent',
                    type: chosenMethod === 'sum_of_year' ? 'bar' : 'line',
                    fill: false
                }
            ];

            depChart = new Chart(ctx, {
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 15, padding: 15 } }
                    },
                    scales: {
                        y: { title: { display: true, text: 'Nilai Depresiasi ($M - Juta Dolar)' } }
                    }
                }
            });
        }

        // 3. Render Cash Flow Chart
        function renderCashFlowChart(data) {
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            const years = data.allYearsWithZero;
            
            // Map cash flows
            const ncfValues = Object.values(data.cash_flow.ncf);
            const cumulativeValues = Object.values(data.cash_flow.cumulative_ncf);

            // Generate conditional bar colors (green for positive, red for negative)
            const barColors = ncfValues.map(v => v >= 0 ? 'rgba(16, 185, 129, 0.55)' : 'rgba(244, 63, 94, 0.55)');
            const borderColors = ncfValues.map(v => v >= 0 ? '#10b981' : '#f43f5e');

            cfChart = new Chart(ctx, {
                data: {
                    labels: years.map(yr => 'Thn ' + yr),
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Net Cash Flow (NCF)',
                            data: ncfValues,
                            backgroundColor: barColors,
                            borderColor: borderColors,
                            borderWidth: 1.5,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'Kumulatif NCF',
                            data: cumulativeValues,
                            borderColor: '#8b5cf6',
                            borderWidth: 3,
                            pointBackgroundColor: '#8b5cf6',
                            fill: false,
                            yAxisID: 'y2',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 15, padding: 15 } }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Tahunan NCF ($M)' }
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }, // Only draw grid for left axis
                            title: { display: true, text: 'Kumulatif NCF ($M)' }
                        }
                    }
                }
            });
        }

        // 4. Render Sensitivity Chart
        function renderSensitivityChart(data) {
            const ctx = document.getElementById('sensitivityChart').getContext('2d');
            const rates = Object.keys(data.npv_sensitivity);
            const npvs = Object.values(data.npv_sensitivity);

            sensChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: rates.map(r => r + '%'),
                    datasets: [{
                        label: 'NPV Proyek',
                        data: npvs,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#06b6d4',
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { 
                            title: { display: true, text: 'NPV ($M - Juta Dolar)' }
                        },
                        x: {
                            title: { display: true, text: 'Suku Bunga / Discount Rate (%)' }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
