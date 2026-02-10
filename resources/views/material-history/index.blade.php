@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .report-card { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; padding: 24px; }
    .img-frame { width: 80px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; }
    .table th { background-color: #f1f5f9; vertical-align: middle; font-size: 0.9rem; color: #334155; }
    .table td { vertical-align: middle; }

    .filter-row-top { display: flex; flex-direction: row; align-items: center; gap: 10px; flex-wrap: nowrap; margin-bottom: 12px; }
    .search-wrapper-custom { position: relative; width: 300px; flex-shrink: 0; }
    .search-wrapper-custom i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-wrapper-custom input { padding-left: 35px; height: 38px; border-radius: 10px; border: 1px solid #cbd5e1; }
    .input-date-custom { height: 38px; border-radius: 10px; border: 1px solid #cbd5e1; width: 150px; }
    .btn-action-custom { height: 38px; border-radius: 10px; font-weight: 600; padding: 0 18px; display: flex; align-items: center; justify-content: center; }
    .btn-cari-outline { background-color: #ffffff; color: #334155; border: 1px solid #cbd5e1; }
    .btn-reset-grey { background-color: #64748b; color: #ffffff; border: none; }

    .bottom-action-row { display: flex; flex-direction: row; align-items: center; gap: 12px; flex-wrap: nowrap; }
    .bottom-action-row label { font-size: 0.85rem; font-weight: 600; margin-bottom: 0; white-space: nowrap; }
    .bottom-action-row input { height: 34px; width: 140px; border-radius: 8px; border: 1px solid #cbd5e1; }
    .btn-pdf { background-color: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2; font-weight: 600; white-space: nowrap; }
    .btn-excel { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; font-weight: 600; white-space: nowrap; }

    .custom-pagination { display: flex; justify-content: flex-start; align-items: center; gap: 4px; margin-top: 10px; margin-bottom: 10px; }
    .page-btn { padding: 4px 10px; border: 1px solid #dee2e6; background: white; color: #00467f; text-decoration: none; border-radius: 5px; font-size: 0.75rem; font-weight: 600; }
    .page-btn:hover { background-color: #f8fafc; }
    .page-btn.active { background-color: #00467f; color: white; border-color: #00467f; }
    .page-btn.disabled { color: #ccc; pointer-events: none; background-color: #f9f9f9; }
</style>

<div class="container py-3">
    <div class="report-card">
        <h3 class="fw-bold text-dark text-uppercase">Riwayat Penambahan Material</h3>

        {{-- FILTER ATAS --}}
        <form action="{{ route('material-history.index') }}" method="GET" class="filter-row-top">
            <div class="search-wrapper-custom">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari Nama Material..." value="{{ request('search') }}">
            </div>
            <input type="date" name="tanggal_mulai" class="form-control input-date-custom" value="{{ request('tanggal_mulai') }}">
            <input type="date" name="tanggal_akhir" class="form-control input-date-custom" value="{{ request('tanggal_akhir') }}">
            <button type="submit" class="btn btn-action-custom btn-cari-outline">Cari</button>
            <a href="{{ route('material-history.index') }}" class="btn btn-action-custom btn-reset-grey text-decoration-none">Reset</a>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="text-center">
                    <tr>
                        <th width="50">No</th>
                        <th class="text-start">Nama Material</th>
                        <th width="100">Jumlah</th> {{-- Kolom Dipisah --}}
                        <th width="100">Satuan</th> {{-- Kolom Dipisah --}}
                        <th>Tanggal (WITA)</th>
                        <th width="120">Foto Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $index => $h)
                        <tr>
                            <td class="text-center">{{ $histories->firstItem() + $index }}</td>
                            <td class="fw-bold text-uppercase">{{ $h->nama_material }}</td>
                            {{-- Menghapus tanda + --}}
                            <td class="text-center">{{ ltrim($h->jumlah, '+ ') }}</td>
                            <td class="text-center text-uppercase">{{ $h->satuan }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($h->tanggal_input)->format('d/m/Y H:i') }} WITA</td>
                            <td class="text-center">
                                @if($h->foto_path)
                                    <img src="{{ asset('uploads/material_stand_by/' . $h->foto_path) }}" class="img-frame">
                                @else
                                    <div class="img-frame bg-light d-flex align-items-center justify-content-center mx-auto text-muted"><small>No Photo</small></div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- Colspan diubah menjadi 6 --}}
                        <tr><td colspan="6" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- INFO DATA --}}
        <div class="mt-2 small text-muted">
            Menampilkan {{ $histories->firstItem() ?? 0 }} sampai {{ $histories->lastItem() ?? 0 }} dari {{ $histories->total() }} data
        </div>

        {{-- PAGINATION MANUAL --}}
        <div class="custom-pagination">
            @if ($histories->onFirstPage())
                <span class="page-btn disabled">« Previous</span>
            @else
                <a href="{{ $histories->previousPageUrl() }}" class="page-btn">« Previous</a>
            @endif

            @foreach ($histories->getUrlRange(1, $histories->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page == $histories->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if ($histories->hasMorePages())
                <a href="{{ $histories->nextPageUrl() }}" class="page-btn">Next »</a>
            @else
                <span class="page-btn disabled">Next »</span>
            @endif
        </div>

        {{-- TOOLBAR BAWAH --}}
        <div class="p-2 bg-light rounded-3 border mt-3">
            <form action="{{ route('material-history.index') }}" method="GET" class="bottom-action-row">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
                <a href="{{ route('material-history.pdf', request()->query()) }}" class="btn btn-pdf btn-sm d-flex align-items-center px-3 text-decoration-none"><i class="fas fa-file-pdf me-2"></i> Unduh PDF</a>
                <a href="{{ route('material-history.excel', request()->query()) }}" class="btn btn-excel btn-sm d-flex align-items-center px-3 text-decoration-none"><i class="fas fa-file-excel me-2"></i> Unduh Excel</a>
            </form>
        </div>
    </div>
</div>
@endsection