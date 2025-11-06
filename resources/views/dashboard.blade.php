@extends('layouts.app')

@section('title', 'Dashboard - SIMAS-PLN')

@section('content')
    <h2>Sistem Informasi Pengelolaan Material Stand By di Gudang Kecil-PLN</h2>
        
    <div class="widget-grid">
        <div class="widget-card salmon-red"> {{-- KELAS BARU UNTUK WARNA D06368FF --}}
            <div class="widget-icon"><i class="fas fa-box-open"></i></div>
            <div class="widget-info">
                <h3>Material Stand By</h3>
                <p>Material stand by di gudang kecil : {{ $totalStandBy ?? 0 }} unit</p>
            </div>
        </div>

        <div class="widget-card salmon-red"> {{-- KELAS BARU UNTUK WARNA D06368FF --}}
            <div class="widget-icon"><i class="fas fa-tools"></i></div>
            <div class="widget-info">
                <h3>Material Keluar</h3>
                <p>Pemasangan Hari ini : {{ $materialKeluarHariIni ?? 0 }} Lokasi</p>
            </div>
        </div>

        <div class="widget-card salmon-red"> {{-- KELAS BARU UNTUK WARNA D06368FF --}}
            <div class="widget-icon"><i class="fas fa-undo"></i></div>
            <div class="widget-info">
                <h3>Material Retur</h3>
                <div class="retur-list">
                    Bekas Andal : {{ $returAndal ?? 0 }}<br>
                    Rusak : {{ $returRusak ?? 0 }}
                </div>
            </div>
        </div>
        
        <div class="widget-card blue"> 
            <div class="widget-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="widget-info">
                <h3>Material Kembali</h3>
                <p>Material Kembali: {{ $totalMaterialKembali ?? 0 }} unit</p>
            </div>
        </div>

        <div class="widget-card purple"> 
            <div class="widget-icon"><i class="fas fa-box-archive"></i></div>
            <div class="widget-info">
                <h3>Material Siaga Stand By</h3>
                <p>Material Kembali: {{ $totalSiagaStandBy ?? 0 }} unit</p>
            </div>
        </div>

        <div class="widget-card green"> 
            <div class="widget-icon"><i class="fas fa-truck"></i></div>
            <div class="widget-info">
                <h3>Siaga Keluar</h3>
                <p>Material Kembali: {{ $totalSiagaKeluar ?? 0 }} unit</p>
            </div>
        </div>
        
        <div class="widget-card mauve"> 
            <div class="widget-icon"><i class="fas fa-sync-alt"></i></div>
            <div class="widget-info">
                <h3>Siaga Kembali</h3>
                <p>Material Kembali: {{ $totalSiagaKembali ?? 0 }} unit</p>
            </div>
        </div>
        
    </div>
@endsection