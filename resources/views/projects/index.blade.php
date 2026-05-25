<x-app-layout>
    <x-slot name="title">Daftar Proyek</x-slot>

    <div class="page-title">
        <span>Proyek Investasi Saya</span>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Proyek Baru
        </a>
    </div>

    @if($projects->isEmpty())
        <div class="glass-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h2>Belum Ada Proyek Analisis</h2>
                <p style="color: var(--text-secondary); max-width: 460px; margin: 0.5rem 0 1.5rem;">
                    Mulai analisis investasi lapangan minyak dan gas bumi Anda dengan membuat proyek perhitungan baru. Sistem kami akan menghitung proyeksi produksi dan kelayakan keekonomiannya.
                </p>
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-circle-plus"></i> Buat Proyek Pertama Anda
                </a>
            </div>
        </div>
    @else
        <div class="projects-grid">
            @foreach($projects as $project)
                <div class="glass-card glow-cyan" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="project-card-header">
                            <div>
                                <a href="{{ route('projects.show', $project) }}" class="project-title">
                                    {{ $project->name }}
                                </a>
                                <div class="project-date">
                                    Dibuat pada {{ $project->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <span class="badge badge-cyan">
                                {{ str_replace('_', ' ', $project->depreciation_method) }}
                            </span>
                        </div>

                        <div class="project-metrics">
                            <div class="metric-row">
                                <span class="metric-label">Harga Minyak:</span>
                                <span class="metric-val">${{ number_format($project->oil_price, 2) }} / bbl</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Total NCF:</span>
                                <span class="metric-val {{ $project->total_ncf >= 0 ? 'positive' : 'negative' }}">
                                    ${{ number_format($project->total_ncf, 2) }} M
                                </span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">NPV (r = {{ $project->discount_rate }}%):</span>
                                <span class="metric-val {{ $project->npv >= 0 ? 'positive' : 'negative' }}">
                                    ${{ number_format($project->npv, 2) }} M
                                </span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Durasi Depresiasi:</span>
                                <span class="metric-val">{{ $project->depreciation_years }} Tahun</span>
                            </div>
                        </div>
                    </div>

                    <div class="project-card-footer">
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini? Seluruh data produksi dan perhitungan akan terhapus permanen.');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;" title="Hapus Proyek">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.85rem;">
                            <i class="fa-solid fa-chart-column"></i> Detail & Grafik
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
