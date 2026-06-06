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
        <!-- Production Decline Chart Card (Premium Light-Themed Red/White) -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-dim); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <span style="font-family: var(--font-mono); font-size: 0.65rem; color: var(--rose); letter-spacing: 0.2em; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.25rem;">06 / CHART</span>
                    <h2 style="font-family: var(--font-sans); font-size: 1.5rem; font-weight: 700; color: var(--text-primary); letter-spacing: 0.02em; text-transform: uppercase; margin: 0; border: none; padding: 0;">PRODUCTION DECLINE CHART</h2>
                    <span style="font-family: var(--font-sans); font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.35rem;">Production forecasting and decline curve visualization</span>
                </div>
                <div>
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(218, 37, 29, 0.2); background: rgba(218, 37, 29, 0.03); color: var(--rose); padding: 0.4rem 0.8rem; font-size: 0.7rem; font-family: var(--font-mono); font-weight: 700; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.1em;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--rose); display: inline-block;"></span>
                        FORECAST ANALYSIS
                    </span>
                </div>
            </div>
            <div style="position: relative; height: 450px; width: 100%;">
                <canvas id="productionDeclineChart"></canvas>
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

        <!-- Calculation of simplified metrics -->
        @php
            $totalInvestment = $project->capital_cost + $project->non_capital_cost;
            $totalNcf = $project->calculations->where('year', '>', 0)->sum('ncf');
            $netProfit = $project->calculations->sum('ncf');
        @endphp

        <!-- Simplified Stat Cards Grid -->
        <div class="stats-grid">
            <div class="stat-card stat-cyan">
                <span class="stat-label">Total NCF</span>
                <div class="stat-value">${{ number_format($totalNcf, 2) }} M</div>
                <span class="stat-desc">Total aliran kas masuk bersih selama umur proyek</span>
            </div>

            <div class="stat-card stat-rose">
                <span class="stat-label">Total Investment</span>
                <div class="stat-value">${{ number_format($totalInvestment, 2) }} M</div>
                <span class="stat-desc">Total modal awal (Capital + Non-Capital Cost)</span>
            </div>

            <div class="stat-card {{ $netProfit >= 0 ? 'stat-emerald' : 'stat-rose' }}">
                <span class="stat-label">Net Profit</span>
                <div class="stat-value">{{ $netProfit >= 0 ? '+' : '' }}${{ number_format($netProfit, 2) }} M</div>
                <span class="stat-desc">Keuntungan bersih kotor (Total NCF - Investasi)</span>
            </div>

            <div class="stat-card {{ $npv >= 0 ? 'stat-emerald' : 'stat-rose' }}">
                <span class="stat-label">NPV (Net Present Value)</span>
                <div class="stat-value">${{ number_format($npv, 2) }} M</div>
                <span class="stat-desc">Keuntungan bersih disesuaikan suku bunga (discounted)</span>
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
        let prodChart, cfChart;

        // Fetch chart data on load and render charts
        document.addEventListener('DOMContentLoaded', function() {
            // Light warm-paper colors for Chart.js
            Chart.defaults.font.family = "'Instrument Sans', sans-serif";

            fetch("{{ route('projects.chart-data', $project) }}")
                .then(res => res.json())
                .then(data => {
                    renderProductionDeclineChart(data);
                    renderCashFlowChart(data);
                })
                .catch(err => console.error("Gagal memuat grafik:", err));
        });

        // Render Production Decline Chart (Continuous unified line)
        function renderProductionDeclineChart(data) {
            const ctx = document.getElementById('productionDeclineChart').getContext('2d');
            
            const years = data.years;
            const combinedDataset = [];

            years.forEach(yr => {
                const val = data.production.actual[yr] !== undefined 
                    ? data.production.actual[yr] 
                    : (data.production.predicted[yr] !== undefined ? data.production.predicted[yr] : 0.0);
                combinedDataset.push(val);
            });

            // Create gradient fill for red theme
            const gradient = ctx.createLinearGradient(0, 0, 0, 350);
            gradient.addColorStop(0, 'rgba(218, 37, 29, 0.15)');
            gradient.addColorStop(1, 'rgba(218, 37, 29, 0.0)');

            prodChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: years.map(yr => yr),
                    datasets: [
                        {
                            label: 'Oil Production (M bbl)',
                            data: combinedDataset,
                            borderColor: '#da251d', // Pertamina Red
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#da251d',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'top', 
                            labels: { 
                                color: '#3a3a3a',
                                boxWidth: 15, 
                                padding: 15,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                }
                            } 
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#111111',
                            bodyColor: '#3a3a3a',
                            borderColor: 'rgba(218, 37, 29, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 4,
                            padding: 10
                        }
                    },
                    scales: {
                        y: { 
                            grid: { 
                                color: 'rgba(26, 26, 26, 0.06)',
                                drawBorder: false
                            },
                            ticks: { 
                                color: '#3a3a3a',
                                font: {
                                    family: "'JetBrains Mono', monospace",
                                    size: 10
                                }
                            } 
                        },
                        x: {
                            grid: {
                                color: 'rgba(26, 26, 26, 0.06)',
                                borderDash: [3, 3],
                                drawBorder: false
                            },
                            ticks: {
                                color: '#3a3a3a',
                                font: {
                                    family: "'JetBrains Mono', monospace",
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render Cash Flow Chart
        function renderCashFlowChart(data) {
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            const years = data.allYearsWithZero;
            
            const ncfValues = Object.values(data.cash_flow.ncf);
            const cumulativeValues = Object.values(data.cash_flow.cumulative_ncf);

            const barColors = ncfValues.map(v => v >= 0 ? 'rgba(17, 138, 68, 0.65)' : 'rgba(218, 37, 29, 0.65)');
            const borderColors = ncfValues.map(v => v >= 0 ? '#118a44' : '#da251d');

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
                            borderColor: '#7f00ff',
                            borderWidth: 3,
                            pointBackgroundColor: '#7f00ff',
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
                        legend: { position: 'top', labels: { color: '#3a3a3a', boxWidth: 15, padding: 15 } }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(26, 26, 26, 0.06)' },
                            ticks: { color: '#3a3a3a' },
                            title: { display: true, text: 'Tahunan NCF ($M)', color: '#3a3a3a' }
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { color: '#3a3a3a' },
                            title: { display: true, text: 'Kumulatif NCF ($M)', color: '#3a3a3a' }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
