<x-app-layout>
    <x-slot name="title">Proyek Baru</x-slot>

    <div class="page-title">
        <span>Buat Analisis Proyek Baru</span>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="solid-card" style="max-width: 800px; margin: 0 auto;">
        <!-- Step Indicators -->
        <div class="wizard-steps">
            <div class="step-indicator active" id="step-indicator-1">
                <div class="step-circle">1</div>
                <span class="step-label">Info Utama</span>
            </div>
            <div class="step-indicator" id="step-indicator-2">
                <div class="step-circle">2</div>
                <span class="step-label">Data Produksi</span>
            </div>
            <div class="step-indicator" id="step-indicator-3">
                <div class="step-circle">3</div>
                <span class="step-label">Forecasting & Depresiasi</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-top: -1rem; margin-bottom: 2rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div style="flex-grow: 1;">
                    <strong style="display: block; margin-bottom: 0.25rem;">Terjadi Kesalahan Input:</strong>
                    <ul style="margin-left: 1.25rem; font-size: 0.85rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('projects.store') }}" method="POST" id="wizard-form">
            @csrf

            <!-- STEP 1: PROJECT INFO PANEL -->
            <div class="wizard-panel active" id="panel-1">
                <h3 style="margin-bottom: 1.5rem; color: var(--cyan); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                    <i class="fa-solid fa-circle-info"></i> Parameter Finansial & Proyek
                </h3>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Proyek / Lapangan</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Lapangan Jatibarang Alpha" />
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="oil_price">Harga Minyak ($ / bbl)</label>
                        <input type="number" step="any" id="oil_price" name="oil_price" class="form-control" value="{{ old('oil_price') }}" required placeholder="Contoh: 32" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="discount_rate">Discount Rate / Suku Bunga (%)</label>
                        <input type="number" step="any" id="discount_rate" name="discount_rate" class="form-control" value="{{ old('discount_rate') }}" required placeholder="Contoh: 10" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="capital_cost">Capital Cost ($M - Juta Dolar)</label>
                        <input type="number" step="any" id="capital_cost" name="capital_cost" class="form-control" value="{{ old('capital_cost') }}" required placeholder="Contoh: 13000" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="non_capital_cost">Non-Capital Cost ($M - Juta Dolar)</label>
                        <input type="number" step="any" id="non_capital_cost" name="non_capital_cost" class="form-control" value="{{ old('non_capital_cost') }}" required placeholder="Contoh: 8000" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="opex_per_year">OPEX per Tahun ($M - Juta Dolar)</label>
                        <input type="number" step="any" id="opex_per_year" name="opex_per_year" class="form-control" value="{{ old('opex_per_year') }}" required placeholder="Contoh: 180" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tax_rate">Tarif Pajak Pendapatan (%)</label>
                        <input type="number" step="any" id="tax_rate" name="tax_rate" class="form-control" value="{{ old('tax_rate') }}" required placeholder="Contoh: 51" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="known_years">Durasi Data Produksi Aktual (Tahun)</label>
                    <input type="number" id="known_years" name="known_years" class="form-control" value="{{ old('known_years') }}" required min="1" max="100" placeholder="Jumlah tahun data produksi yang Anda miliki (min: 1)" />
                </div>
            </div>

            <!-- STEP 2: DYNAMIC PRODUCTION DATA -->
            <div class="wizard-panel" id="panel-2">
                <h3 style="margin-bottom: 1.5rem; color: var(--cyan); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                    <i class="fa-solid fa-database"></i> Input Data Produksi Lapangan
                </h3>
                
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Masukkan data produksi aktual dalam satuan **MBBL (Ribuan Barrel)** untuk masing-masing tahun yang diketahui.
                </p>

                <div id="production-inputs-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <!-- Dynamic production fields will be injected here via JavaScript -->
                </div>

                <div class="form-group" id="reserve-group">
                    <label class="form-label" for="total_reserve" id="reserve-label">Total Cadangan Minyak / Reserve (MBBL)</label>
                    <input type="number" step="any" id="total_reserve" name="total_reserve" class="form-control" value="{{ old('total_reserve') }}" placeholder="Opsional (Wajib jika menggunakan metode depresiasi Unit of Production)" />
                </div>
            </div>

            <!-- STEP 3: FORECAST & DEPRECIATION CONFIG -->
            <div class="wizard-panel" id="panel-3">
                <h3 style="margin-bottom: 1.5rem; color: var(--cyan); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                    <i class="fa-solid fa-sliders"></i> Konfigurasi Prediksi & Depresiasi
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="prediction_years">Target Prediksi Masa Depan (Tahun)</label>
                        <input type="number" id="prediction_years" name="prediction_years" class="form-control" value="{{ old('prediction_years') }}" required min="1" max="100" placeholder="Contoh: 16" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="decline_rate">Laju Penurunan Produksi / Decline Rate (%) <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: none;">(Opsional)</span></label>
                        <input type="number" step="any" id="decline_rate" name="decline_rate" class="form-control" value="{{ old('decline_rate') }}" placeholder="Contoh: 10 (Gunakan Decline Curve)" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="depreciation_years">Durasi Depresiasi Investasi (Tahun)</label>
                        <input type="number" id="depreciation_years" name="depreciation_years" class="form-control" value="{{ old('depreciation_years') }}" required min="1" max="100" placeholder="Contoh: 10" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="custom_depreciation_rate">Laju Depresiasi Kustom (%) <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: none;">(Opsional)</span></label>
                        <input type="number" step="any" id="custom_depreciation_rate" name="custom_depreciation_rate" class="form-control" value="{{ old('custom_depreciation_rate') }}" placeholder="Default otomatis: 1/N atau 2/N" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="depreciation_method">Metode Depresiasi Aset</label>
                    <select id="depreciation_method" name="depreciation_method" class="form-control" required>
                        <option value="straight_line" {{ old('depreciation_method') === 'straight_line' ? 'selected' : '' }}>Straight Line Method (Garis Lurus)</option>
                        <option value="declining_balance" {{ old('depreciation_method') === 'declining_balance' ? 'selected' : '' }}>Declining Balance Method (Saldo Menurun)</option>
                        <option value="double_declining" {{ old('depreciation_method') === 'double_declining' ? 'selected' : '' }}>Double Declining Balance Method</option>
                        <option value="unit_of_production" {{ old('depreciation_method') === 'unit_of_production' ? 'selected' : '' }}>Unit of Production Method</option>
                        <option value="sum_of_year" {{ old('depreciation_method') === 'sum_of_year' ? 'selected' : '' }}>Sum of the Years Digits Method</option>
                    </select>
                    <span id="reserve-warning" style="display: none; color: var(--amber); font-size: 0.8rem; margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> Peringatan: Metode Unit of Production memerlukan input **Total Cadangan (Reserve)** di Step 2!
                    </span>
                </div>
            </div>

            <!-- Wizard Navigation Buttons -->
            <div class="wizard-navigation">
                <button type="button" class="btn btn-secondary" id="btn-prev" style="display: none;">
                    <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                </button>
                <div style="flex-grow: 1;"></div>
                <button type="button" class="btn btn-primary" id="btn-next">
                    Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button type="submit" class="btn btn-success" id="btn-submit" style="display: none;">
                    <i class="fa-solid fa-circle-check"></i> Simpan & Hitung Proyek
                </button>
            </div>
        </form>
    </div>

    <!-- Wizard Javascript Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const totalSteps = 3;
            
            const form = document.getElementById('wizard-form');
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            const btnSubmit = document.getElementById('btn-submit');
            
            const knownYearsInput = document.getElementById('known_years');
            const productionContainer = document.getElementById('production-inputs-container');
            const methodSelect = document.getElementById('depreciation_method');
            const reserveWarning = document.getElementById('reserve-warning');
            const totalReserveInput = document.getElementById('total_reserve');

            // Toggle Unit of Production warning and visual requirement
            function handleMethodChange() {
                if (methodSelect.value === 'unit_of_production') {
                    reserveWarning.style.display = 'block';
                    totalReserveInput.setAttribute('required', 'required');
                    document.getElementById('reserve-label').innerHTML = 'Total Cadangan Minyak / Reserve (MBBL) <span style="color:var(--rose);">*</span>';
                } else {
                    reserveWarning.style.display = 'none';
                    totalReserveInput.removeAttribute('required');
                    document.getElementById('reserve-label').innerHTML = 'Total Cadangan Minyak / Reserve (MBBL)';
                }
            }
            methodSelect.addEventListener('change', handleMethodChange);
            handleMethodChange(); // Init

            // Dynamically generate production inputs on entering Step 2
            function generateProductionFields() {
                const count = parseInt(knownYearsInput.value) || 0;
                
                // Keep track of existing values to not wipe user inputs
                const currentValues = {};
                productionContainer.querySelectorAll('input').forEach(input => {
                    const matches = input.name.match(/production\[(\d+)\]/);
                    if (matches) {
                        currentValues[matches[1]] = input.value;
                    }
                });

                productionContainer.innerHTML = '';
                
                for (let i = 1; i <= count; i++) {
                    const value = currentValues[i] || '';
                    const div = document.createElement('div');
                    div.className = 'form-group';
                    div.innerHTML = `
                        <label class="form-label" for="prod_${i}">Tahun ${i} (MBBL)</label>
                        <input type="number" step="any" id="prod_${i}" name="production[${i}]" class="form-control" value="${value}" required min="0" placeholder="0.0" />
                    `;
                    productionContainer.appendChild(div);
                }
            }

            // Client-side validation per step
            function validateStep(step) {
                let isValid = true;
                const panel = document.getElementById(`panel-${step}`);
                
                // Find all inputs in the current panel
                const inputs = panel.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = 'var(--rose)';
                        input.addEventListener('input', function() {
                            input.style.borderColor = 'var(--border-dim)';
                        }, { once: true });
                    } else if (input.type === 'number' && input.value !== '' && parseFloat(input.value) < 0) {
                        // Avoid negative numbers if appropriate
                        if (input.id !== 'total_reserve' || methodSelect.value === 'unit_of_production') {
                            isValid = false;
                            input.style.borderColor = 'var(--rose)';
                        }
                    }
                });

                return isValid;
            }

            // Update UI displays for active step
            function updateWizardUI() {
                // Panels
                for (let s = 1; s <= totalSteps; s++) {
                    const panel = document.getElementById(`panel-${s}`);
                    const indicator = document.getElementById(`step-indicator-${s}`);
                    
                    if (s === currentStep) {
                        panel.classList.add('active');
                        indicator.classList.add('active');
                    } else {
                        panel.classList.remove('active');
                        indicator.classList.remove('active');
                    }

                    if (s < currentStep) {
                        indicator.classList.add('completed');
                    } else {
                        indicator.classList.remove('completed');
                    }
                }

                // Nav buttons
                if (currentStep === 1) {
                    btnPrev.style.display = 'none';
                    btnNext.style.display = 'inline-flex';
                    btnSubmit.style.display = 'none';
                } else if (currentStep === totalSteps) {
                    btnPrev.style.display = 'inline-flex';
                    btnNext.style.display = 'none';
                    btnSubmit.style.display = 'inline-flex';
                } else {
                    btnPrev.style.display = 'inline-flex';
                    btnNext.style.display = 'inline-flex';
                    btnSubmit.style.display = 'none';
                }
            }

            btnNext.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    if (currentStep === 1) {
                        generateProductionFields();
                    }
                    currentStep++;
                    updateWizardUI();
                } else {
                    alert('Harap isi semua input yang wajib dengan benar di langkah ini sebelum melanjutkan.');
                }
            });

            btnPrev.addEventListener('click', function() {
                currentStep--;
                updateWizardUI();
            });
        });
    </script>
</x-app-layout>
