@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .container-fluid, .main-content { margin: 0 !important; padding: 0 !important; }
    .dashboard-wrapper {
        height: 100vh; width: 100%; display: flex; flex-direction: column;
        padding: 0 1rem 0.5rem 1rem; box-sizing: border-box; gap: 0.3rem;
        background-color: #f1f5f9; overflow: hidden; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .main-title-box {
        background: white; padding: 0.5rem; border-radius: 0 0 12px 12px;
        border: 1px solid #e2e8f0; border-top: none; text-align: center; margin-bottom: 0.1rem;
    }
    .main-title-box h2 { font-weight: 800; font-size: 0.9rem; color: #00467f; margin: 0; text-transform: uppercase; }
    
    .notif-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.2rem; }
    .notif-item { 
        background: white; border-left: 4px solid #00467f; padding: 0.4rem 0.6rem; 
        border-radius: 8px; display: flex; align-items: center; gap: 8px;
        border: 1px solid #e2e8f0; animation: slideIn 0.5s ease;
    }
    .notif-item.siaga { border-left-color: #22c55e; }
    .badge-new { background: #00467f; color: white; padding: 1px 5px; border-radius: 4px; font-size: 0.5rem; font-weight: 800; }
    .notif-item.siaga .badge-new { background: #22c55e; }
    .notif-text { font-size: 0.6rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .notif-time { font-size: 0.5rem; color: #94a3b8; margin-left: auto; }

    .section-header { font-size: 0.6rem; font-weight: 800; color: #00467f; margin: 0.1rem 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
    .section-header::after { content: ""; height: 1px; flex: 1; background: #cbd5e1; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
    .siaga-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    
    .executive-card { background: white; border-radius: 10px; padding: 0.5rem 0.8rem; border: 1px solid #e2e8f0; border-left: 4px solid #00467f; }
    .label-caps { font-size: 0.5rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
    .value-main { font-size: 1.1rem; font-weight: 800; color: #0f172a; line-height: 1; margin: 2px 0; }
    .unit-text { font-size: 0.6rem; margin-left: 3px; color: #64748b; }
    .retur-split { display: flex; justify-content: space-between; margin-top: 3px; padding-top: 3px; border-top: 1px solid #f1f5f9; font-size: 0.55rem; font-weight: 700; }
    
    .chart-section { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; flex: 1; min-height: 0; }
    .chart-panel { background: white; border-radius: 12px; padding: 0.5rem; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
    .chart-wrapper { flex: 1; min-height: 0; position: relative; }

    @keyframes slideIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
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
                <span class="notif-time">{{ $recentStandby->created_at->format('H:i') }}</span>
            @else
                <span class="notif-text">Tidak ada data.</span>
            @endif
        </div>

        <div class="notif-item siaga">
            <span class="badge-new">SIAGA TERBARU</span>
            @if($recentSiaga)
                <span class="notif-text"><strong>{{ $recentSiaga->nama_material }}</strong> (Ready)</span>
                <span class="notif-time">{{ $recentSiaga->created_at->format('H:i') }}</span>
            @else
                <span class="notif-text">Tidak ada data.</span>
            @endif
        </div>
    </div>

    <div class="section-header">Statistik Inventaris Gudang</div>
    <div class="stats-grid">
        <div class="executive-card">
            <span class="label-caps">Total Stok Standby</span>
            <div class="value-main">{{ number_format($totalStandBy) }}<span class="unit-text">Unit</span></div>
            <div class="retur-split"><span>Meter: {{ $totalMeter }}</span> <span>Buah: {{ $totalBuah }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #ef4444;">
            <span class="label-caps text-danger">Material Keluar</span>
            <div class="value-main text-danger">{{ number_format($volumeKeluar) }}<span class="unit-text">Unit</span></div>
            <div class="retur-split text-danger"><span>Meter: {{ $keluarMeter }}</span> <span>Buah: {{ $keluarBuah }}</span></div>
        </div>

        <div class="executive-card" style="border-left-color: #22c55e;">
            <span class="label-caps text-success">Total Kembali</span>
            <div class="value-main text-success">{{ number_format($volumeKembali) }}<span class="unit-text">Unit</span></div>
            <div class="retur-split text-success">
                <span>Meter: {{ $kembaliMeter }}</span> 
                <span>Buah: {{ $kembaliBuah }}</span>
            </div>
        </div>

        <div class="executive-card" style="border-left-color: #f59e0b;">
            <span class="label-caps text-warning">Material Retur</span>
            <div class="value-main text-warning">{{ number_format($totalRetur) }}<span class="unit-text">Unit</span></div>
            <div class="retur-split">
                <span class="text-success">Bekas Andal: {{ $returAndal }}</span> <span class="text-danger">Rusak: {{ $returRusak }}</span>
            </div>
        </div>
    </div>

    <div class="section-header">Monitoring Operasional Siaga</div>
    <div class="siaga-grid">
        <div class="executive-card" style="border-left-color: #00a3e0;">
            <span class="label-caps">Stok Siaga Ready</span>
            <div class="value-main text-info">{{ $siagaReady }}<span class="unit-text">Unit</span></div>
            <div class="retur-split text-info"><span>Kwh Siaga 1P: {{ $siaga1P }}</span> <span>Kwh Siaga 3P: {{ $siaga3P }}</span></div>
        </div>
        <div class="executive-card" style="border-left-color: #ef4444;">
            <span class="label-caps text-danger">Stok Siaga Keluar</span>
            <div class="value-main text-danger">{{ $siagaKeluar }}<span class="unit-text">Unit</span></div>
            <div class="retur-split text-danger"><span>Kwh Siaga 1P: {{ $siagaKeluar1P }}</span> <span>Kwh Siaga 3P: {{ $siagaKeluar3P }}</span></div>
        </div>
        <div class="executive-card" style="border-left-color: #22c55e;">
            <span class="label-caps text-success">Stok Siaga Kembali</span>
            <div class="value-main text-success">{{ $siagaKembali }}<span class="unit-text">Unit</span></div>
            <div class="retur-split text-success"><span>Kwh Siaga 1P: {{ $siagaKembali1P }}</span> <span>Kwh Siaga 3P: {{ $siagaKembali3P }}</span></div>
        </div>
    </div>

    <div class="section-header">Analisis Performa</div>
    <div class="chart-section">
        <div class="chart-panel">
            <span class="label-caps mb-1">Aktivitas Gudang</span>
            <div class="chart-wrapper"><canvas id="gudangChart"></canvas></div>
        </div>
        <div class="chart-panel">
            <span class="label-caps mb-1">Aktivitas Siaga</span>
            <div class="chart-wrapper"><canvas id="siagaChart"></canvas></div>
        </div>
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
            labels: ['STANDBY', 'KELUAR', 'MASUK', 'ANDAL', 'RUSAK'],
            datasets: [{ 
                data: [{{$totalStandBy}}, {{$volumeKeluar}}, {{$volumeKembali}}, {{$returAndal}}, {{$returRusak}}], 
                borderColor: '#00467f', backgroundColor: 'rgba(0, 70, 127, 0.1)', fill: true, borderWidth: 3
            }] 
        },
        options: lineOpt
    });

    new Chart(document.getElementById('siagaChart'), {
        type: 'line',
        data: { 
            labels: ['READY', 'KELUAR', 'KEMBALI'],
            datasets: [{ 
                data: [{{$siagaReady}}, {{$siagaKeluar}}, {{$siagaKembali}}], 
                borderColor: '#00a3e0', backgroundColor: 'rgba(0, 163, 224, 0.1)', fill: true, borderWidth: 3
            }] 
        },
        options: lineOpt
    });
</script>
@endsection