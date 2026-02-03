@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Reset CSS */
    body, .container-fluid, .main-content { margin: 0 !important; padding: 0 !important; }
    
    /* Layout Utama */
    .dashboard-wrapper {
        /* PERBAIKAN: Menggunakan min-height agar bisa bertambah tinggi saat zoom */
        min-height: 100vh; 
        width: 100%; 
        display: flex; 
        flex-direction: column;
        padding: 0 1rem 1rem 1rem; 
        box-sizing: border-box; 
        gap: 0.3rem;
        background-color: #f1f5f9; 
        
        /* PERBAIKAN: Mengaktifkan scroll otomatis agar tampilan bisa digeser saat zoom */
        overflow-y: auto; 
        overflow-x: auto; 
        
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Header Judul */
    .main-title-box {
        background: white; 
        padding: 0.4rem; 
        border-radius: 0 0 12px 12px;
        border: 1px solid #e2e8f0; 
        border-top: none; 
        text-align: center; 
    }
    .main-title-box h2 { font-weight: 800; font-size: 0.85rem; color: #00467f; margin: 0; text-transform: uppercase; }
    
    /* Bar Notifikasi */
    .notif-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .notif-item { 
        background: white; border-left: 4px solid #00467f; padding: 0.3rem 0.6rem; 
        border-radius: 8px; display: flex; align-items: center; gap: 8px;
        border: 1px solid #e2e8f0;
    }
    .notif-item.siaga { border-left-color: #22c55e; }
    .badge-new { background: #00467f; color: white; padding: 1px 5px; border-radius: 4px; font-size: 0.5rem; font-weight: 800; }
    .notif-text { font-size: 0.6rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .notif-time { font-size: 0.5rem; color: #94a3b8; margin-left: auto; }

    /* Header Section */
    .section-header { font-size: 0.6rem; font-weight: 800; color: #00467f; margin: 0.1rem 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
    .section-header::after { content: ""; height: 1px; flex: 1; background: #cbd5e1; }
    
    /* Grid Statistik */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.4rem; }
    .siaga-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; }
    
    /* Executive Card */
    .executive-card { 
        background: white; 
        border-radius: 10px; 
        padding: 0.5rem 0.7rem; 
        border: 1px solid #e2e8f0; 
        border-left: 4px solid #00467f; 
        display: flex; 
        flex-direction: column; 
        height: 140px; 
        min-height: 140px; 
    }

    .label-caps { font-size: 0.55rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 3px; }
    
    /* List Material dengan Scroll Internal */
    .material-list { 
        flex: 1; 
        overflow-y: auto; 
        margin-bottom: 4px; 
        padding-right: 4px;
        font-size: 0.55rem; 
    }
    .material-item { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 2px; border-bottom: 1px dashed #f1f5f9; }
    .material-name { color: #475569; font-weight: 600; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%; }
    .material-qty { font-weight: 800; color: #0f172a; white-space: nowrap; }

    .retur-split { display: flex; justify-content: space-between; margin-top: auto; font-size: 0.55rem; font-weight: 700; border-top: 1px solid #f1f5f9; padding-top: 2px; }
    
    /* Area Grafik */
    .chart-section { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 0.5rem; 
        flex: 1; 
        /* PERBAIKAN: Beri tinggi minimal agar tidak gepeng saat zoom */
        min-height: 300px; 
        margin-bottom: 1rem;
    }
    .chart-panel { background: white; border-radius: 12px; padding: 0.6rem; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
    .chart-wrapper { flex: 1; min-height: 0; position: relative; }

    /* Custom Scrollbar Minimalis */
    .material-list::-webkit-scrollbar { width: 3px; }
    .material-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* MEDIA QUERY: AGAR RAPI DI HP */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            height: auto; 
            overflow-y: auto; 
        }
        .stats-grid, .siaga-grid, .chart-section {
            grid-template-columns: 1fr; 
        }
        .executive-card {
            height: auto; 
            min-height: 130px;
        }
        .chart-section {
            height: 500px; 
        }
    }
</style>

<div class="dashboard-wrapper">
    <div class="main-title-box">
        <h2>SISTEM INFORMASI PENGELOLAAN MATERIAL STAND BY DI GUDANG KECIL – PLN</h2>
    </div>

    <div class="notif-bar">
        <div class="notif-item">
            <span class="badge-new">STANDBY TERBARU</span>
            @if($recentStandby)
                <span class="notif-text"><strong>{{ $recentStandby->nama_material }}</strong> (+{{ $recentStandby->jumlah }})</span>
                <span class="notif-time">{{ $recentStandby->created_at ? $recentStandby->created_at->format('H:i') : '--:--' }}</span>
            @else
                <span class="notif-text">Tidak ada data.</span>
            @endif
        </div>
        <div class="notif-item siaga">
            <span class="badge-new">SIAGA TERBARU</span>
            @if($recentSiaga)
                <span class="notif-text"><strong>{{ $recentSiaga->nama_material }}</strong> (Ready)</span>
                <span class="notif-time">{{ $recentSiaga->created_at ? $recentSiaga->created_at->format('H:i') : '--:--' }}</span>
            @else
                <span class="notif-text">Tidak ada data.</span>
            @endif
        </div>
    </div>

    <div class="section-header">Statistik Inventaris Gudang</div>
    <div class="stats-grid">
        <div class="executive-card">
            <span class="label-caps">Rincian Stok Standby</span>
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

        <div class="executive-card" style="border-left-color: #ef4444;">
            <span class="label-caps text-danger">Rincian Keluar (Hari Ini)</span>
            <div class="material-list">
                @forelse($detailsKeluar as $item)
                    <div class="material-item text-danger">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan_material) }}</span>
                    </div>
                @empty
                    <div class="notif-text text-muted" style="font-size: 0.5rem; font-style: italic;">Tidak ada data</div>
                @endforelse
            </div>
            <div class="retur-split text-danger"><span>Meter: {{ number_format($keluarMeter) }}</span> <span>Buah: {{ number_format($keluarBuah) }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #22c55e;">
            <span class="label-caps text-success">Rincian Kembali</span>
            <div class="material-list">
                @foreach($detailsKembali as $item)
                    <div class="material-item text-success">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="retur-split text-success"><span>Buah: {{ number_format($volumeKembali) }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #f59e0b;">
            <span class="label-caps text-warning">Rincian Retur</span>
            <div class="material-list">
                @foreach($detailsRetur as $item)
                    <div class="material-item text-warning">
                        <span class="material-name">{{ $item->nama_material }}</span>
                        <span class="material-qty">{{ number_format($item->total) }} {{ strtoupper($item->satuan) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="retur-split">
                <span class="text-success">Bekas andal: {{ number_format($returAndal) }}</span> 
                <span class="text-danger">Rusak: {{ number_format($returRusak) }}</span>
            </div>
        </div>
    </div>

    <div class="section-header">Monitoring Operasional Siaga</div>
    <div class="siaga-grid">
        <div class="executive-card" style="border-left-color: #00a3e0;">
            <span class="label-caps">Siaga Ready (Detail)</span>
            <div class="material-list">
                @forelse($listSiagaReady as $k)
                    <div class="material-item">
                        <span class="material-name">{{ $k->nama_material }} - ({{ $k->nomor_meter }})</span>
                        <span class="material-qty text-info">{{ $k->stand_meter }}</span>
                    </div>
                @empty
                    <div class="notif-text text-muted" style="font-size: 0.5rem;">Kosong</div>
                @endforelse
            </div>
            <div class="retur-split text-info"><span>1P: {{ $siaga1P }}</span> <span>3P: {{ $siaga3P }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #ef4444;">
            <span class="label-caps text-danger">Siaga Keluar (Detail)</span>
            <div class="material-list">
                @forelse($listSiagaKeluar as $k)
                    <div class="material-item text-danger">
                        <span class="material-name">{{ $k->nama_material_lengkap }} - ({{ $k->nomor_meter }})</span>
                        <span class="material-qty">{{ $k->stand_meter }}</span>
                    </div>
                @empty
                    <div class="notif-text text-muted" style="font-size: 0.5rem;">Kosong</div>
                @endforelse
            </div>
            <div class="retur-split text-danger"><span>1P: {{ $siagaKeluar1P }}</span> <span>3P: {{ $siagaKeluar3P }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #22c55e;">
            <span class="label-caps text-success">Siaga Kembali (Detail)</span>
            <div class="material-list">
                @forelse($listSiagaKembali as $k)
                    <div class="material-item text-success">
                        <span class="material-name">{{ $k->nama_material_lengkap }} - ({{ $k->nomor_meter }})</span>
                        <span class="material-qty">{{ $k->stand_meter }}</span>
                    </div>
                @empty
                    <div class="notif-text text-muted" style="font-size: 0.5rem;">Kosong</div>
                @endforelse
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
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { legend: { display: false } }, 
        scales: { 
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 8 } } }, 
            x: { grid: { display: false }, ticks: { font: { size: 8, weight: 'bold' } } } 
        },
        elements: { line: { tension: 0.4 }, point: { radius: 4 } }
    };
    new Chart(document.getElementById('gudangChart'), {
        type: 'line',
        data: { 
            labels: ['STANDBY', 'KELUAR', 'KEMBALI', 'BAIK', 'RUSAK'],
            datasets: [{ data: [{{$totalStandBy}}, {{$volumeKeluar}}, {{$volumeKembali}}, {{$returAndal}}, {{$returRusak}}], borderColor: '#00467f', backgroundColor: 'rgba(0, 70, 127, 0.1)', fill: true, borderWidth: 3 }] 
        },
        options: lineOpt
    });
    new Chart(document.getElementById('siagaChart'), {
        type: 'line',
        data: { 
            labels: ['READY', 'KELUAR', 'KEMBALI'],
            datasets: [{ data: [{{ count($listSiagaReady) }}, {{ count($listSiagaKeluar) }}, {{ count($listSiagaKembali) }}], borderColor: '#00a3e0', backgroundColor: 'rgba(0, 163, 224, 0.1)', fill: true, borderWidth: 3 }] 
        },
        options: lineOpt
    });
</script>
@endsection