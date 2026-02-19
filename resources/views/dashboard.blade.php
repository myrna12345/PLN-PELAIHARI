@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    body, html { 
        margin: 0; padding: 0; min-height: 100vh; 
        background-color: #f1f5f9;
    }
    .main-content { padding: 0 !important; }
    .dashboard-wrapper {
        min-height: 100vh; width: 100%; display: flex; flex-direction: column;
        padding: 0 1rem 1rem 1rem; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .main-title-box {
        background: white; padding: 0.6rem; border-radius: 0 0 12px 12px;
        border: 1px solid #e2e8f0; border-top: none; text-align: center; margin-bottom: 0.5rem;
    }
    .main-title-box h2 { font-weight: 800; font-size: 0.9rem; color: #00467f; margin: 0; text-transform: uppercase; }
    
    .section-header { 
        font-size: 0.65rem; font-weight: 800; color: #00467f; margin: 0.5rem 0 0.3rem 0; 
        display: flex; align-items: center; gap: 8px; text-transform: uppercase; 
    }
    .section-header::after { content: ""; height: 1px; flex: 1; background: #cbd5e1; }

    /* --- PERBAIKAN RESPONSIVE GRID --- */
    .stats-grid { 
        display: grid; 
        gap: 0.5rem; 
        margin-bottom: 0.5rem;
        /* Di Laptop tampil 4 kolom, di HP otomatis menyesuaikan */
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
    }
    
    .siaga-grid { 
        display: grid; 
        gap: 0.5rem; 
        margin-bottom: 0.5rem;
        /* Di Laptop tampil 3 kolom, di HP otomatis menumpuk */
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
    }

    .executive-card { 
        background: white; border-radius: 10px; padding: 0.6rem; 
        border: 1px solid #e2e8f0; border-left: 4px solid #00467f; 
        display: flex; flex-direction: column; min-height: 140px;
        transition: transform 0.2s;
    }

    .label-caps { font-size: 0.55rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
    .material-list { flex: 1; max-height: 120px; overflow-y: auto; font-size: 0.58rem; padding-right: 5px; }
    .material-item { 
        display: flex; justify-content: space-between; gap: 8px; 
        margin-bottom: 4px; border-bottom: 1px dashed #f1f5f9; padding-bottom: 2px; 
    }
    .material-name { color: #475569; font-weight: 600; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%; }
    .material-qty { font-weight: 800; color: #0f172a; white-space: nowrap; }
    
    .retur-split { 
        display: flex; justify-content: space-between; font-size: 0.55rem; 
        font-weight: 700; border-top: 1px solid #f1f5f9; padding-top: 4px; margin-top: auto; 
    }

    /* --- PERBAIKAN RESPONSIVE CHART --- */
    .chart-section { 
        display: grid; 
        /* Di HP tampil 1 kolom (tumpuk), di laptop 2 kolom (berjajar) */
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
        gap: 0.5rem; 
        margin-top: 0.5rem; 
    }
    
    .chart-panel { 
        background: white; border-radius: 12px; padding: 0.8rem; 
        border: 1px solid #e2e8f0; display: flex; flex-direction: column; 
    }
    .chart-wrapper { height: 200px; position: relative; width: 100%; }

    /* Scrollbar Styling */
    .material-list::-webkit-scrollbar { width: 4px; }
    .material-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Media Query Tambahan untuk Layar Sangat Kecil (HP) */
    @media (max-width: 480px) {
        .dashboard-wrapper { padding: 0.5rem; }
        .main-title-box h2 { font-size: 0.75rem; }
        .executive-card { min-height: 120px; }
    }
</style>

<div class="dashboard-wrapper">
    <div class="main-title-box">
        <h2>SISTEM INFORMASI PENGELOLAAN MATERIAL STAND BY - PLN</h2>
    </div>

    <div class="section-header">Statistik Inventaris Gudang</div>
    <div class="stats-grid">
        {{-- STOK STANDBY --}}
        <div class="executive-card">
            <span class="label-caps">Material Standby</span>
            <div class="material-list">
                @foreach($detailsStandby as $item)
                    <div class="material-item">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="retur-split"><span>Meter: {{ number_format($totalMeter) }}</span> <span>Buah: {{ number_format($totalBuah) }}</span></div>
        </div>

        {{-- KELUAR --}}
        <div class="executive-card" style="border-left-color: #ef4444;">
            <span class="label-caps text-danger">Material Keluar</span>
            <div class="material-list">
                @forelse($detailsKeluar as $item)
                    <div class="material-item text-danger">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan_material) }}</span>
                    </div>
                @empty
                @endforelse
            </div>
            <div class="retur-split text-danger"><span>Meter: {{ number_format($keluarMeter) }}</span> <span>Buah: {{ number_format($keluarBuah) }}</span></div>
        </div>

        {{-- RINCIAN KEMBALI (Sudah Diperbaiki) --}}
        <div class="executive-card" style="border-left-color: #22c55e;">
            <span class="label-caps text-success">Material Kembali</span>
            <div class="material-list">
                @foreach($detailsKembali as $item)
                    <div class="material-item text-success">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        {{-- Memunculkan kembali jumlah di samping nama --}}
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="retur-split text-success">
                <span>Meter: {{ number_format($kembaliMeter) }}</span> 
                <span>Buah: {{ number_format($kembaliBuah) }}</span>
            </div>
        </div>

        {{-- RINCIAN RETUR --}}
        <div class="executive-card" style="border-left-color: #f59e0b;">
            <span class="label-caps text-warning">Material Retur</span>
            <div class="material-list">
                @foreach($detailsRetur as $item)
                    <div class="material-item text-warning">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="retur-split"><span>Baik: {{ number_format($returAndal) }}</span> <span>Rusak: {{ number_format($returRusak) }}</span></div>
        </div>
    </div>

   <div class="section-header">Monitoring Operasional Siaga</div>
<div class="siaga-grid">
    {{-- SIAGA READY --}}
    <div class="executive-card" style="border-left-color: #00a3e0;">
        <span class="label-caps text-info">Siaga Ready</span>
        <div class="material-list">
            @foreach($listSiagaReady as $k)
                <div class="material-item">
                    <span class="material-name">{{ $k->nama_material }} ({{ $k->nomor_meter }})</span>
                    <span class="material-qty text-info">{{ $k->stand_meter }}</span>
                </div>
            @endforeach
        </div>
        <div class="retur-split text-info"><span>1P: {{ $siaga1P }}</span> <span>3P: {{ $siaga3P }}</span></div>
    </div>

    {{-- SIAGA KELUAR --}}
    <div class="executive-card" style="border-left-color: #ef4444;">
        <span class="label-caps text-danger">Siaga Keluar</span>
        <div class="material-list">
            @foreach($listSiagaKeluar as $k)
                <div class="material-item text-danger">
                    <span class="material-name">{{ $k->nama_material_lengkap }} ({{ $k->nomor_meter }})</span>
                    <span class="material-qty">{{ $k->stand_meter }}</span>
                </div>
            @endforeach
        </div>
        <div class="retur-split text-danger"><span>1P: {{ $siagaKeluar1P }}</span> <span>3P: {{ $siagaKeluar3P }}</span></div>
    </div>

    {{-- SIAGA KEMBALI --}}
    <div class="executive-card" style="border-left-color: #22c55e;">
        <span class="label-caps text-success">Siaga Kembali</span>
        <div class="material-list">
            @foreach($listSiagaKembali as $k)
                <div class="material-item text-success">
                    {{-- Perbaikan: Menggunakan nama_material_lengkap agar muncul KWH SIAGA 1P/3P --}}
                    <span class="material-name">{{ $k->nama_material_lengkap }} ({{ $k->nomor_meter }})</span>
                    <span class="material-qty">{{ $k->stand_meter }}</span>
                </div>
            @endforeach
        </div>
        <div class="retur-split text-success"><span>1P: {{ $siagaKembali1P }}</span> <span>3P: {{ $siagaKembali3P }}</span></div>
    </div>
</div>

    <div class="section-header">Analisis Performa</div>
    <div class="chart-section">
        <div class="chart-panel"><div class="chart-wrapper"><canvas id="gudangChart"></canvas></div></div>
        <div class="chart-panel"><div class="chart-wrapper"><canvas id="siagaChart"></canvas></div></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const lineOpt = { 
        responsive: true, maintainAspectRatio: false, 
        plugins: { legend: { display: false } }, 
        scales: { 
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 8 } } }, 
            x: { grid: { display: false }, ticks: { font: { size: 8, weight: 'bold' } } } 
        },
        elements: { line: { tension: 0.4 }, point: { radius: 3 } }
    };

    new Chart(document.getElementById('gudangChart'), {
        type: 'line',
        data: { 
            labels: ['STB', 'OUT', 'IN', 'OK', 'NOK'],
            datasets: [{ 
                data: [{{$totalStandBy}}, {{$volumeKeluar}}, {{$volumeKembali}}, {{$returAndal}}, {{$returRusak}}], 
                borderColor: '#00467f', backgroundColor: 'rgba(0, 70, 127, 0.1)', fill: true, borderWidth: 2 
            }] 
        },
        options: lineOpt
    });

    new Chart(document.getElementById('siagaChart'), {
        type: 'line',
        data: { 
            labels: ['RDY', 'OUT', 'IN'],
            datasets: [{ 
                data: [{{ count($listSiagaReady) }}, {{ count($listSiagaKeluar) }}, {{ count($listSiagaKembali) }}], 
                borderColor: '#00a3e0', backgroundColor: 'rgba(0, 163, 224, 0.1)', fill: true, borderWidth: 2 
            }] 
        },
        options: lineOpt
    });
</script>
@endsection