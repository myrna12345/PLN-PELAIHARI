@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .report-card { background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 25px; }
    .report-title { font-weight: 700; color: #333; text-transform: uppercase; margin-bottom: 0; }
    
    .search-form { display: flex; align-items: center; gap: 10px; }
    .search-bar { position: relative; }
    .search-bar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-bar input { padding-left: 35px !important; border-radius: 6px; border: 1px solid #e2e8f0; height: 38px; }
    .form-control-tanggal { border-radius: 6px; border: 1px solid #e2e8f0; height: 38px; padding: 0 10px; }

    .table thead th { background-color: #f1f5f9; color: #333; font-weight: 700; padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: center; }
    .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #edf2f7; }
    .img-frame { width: 80px; height: auto; border-radius: 4px; border: 1px solid #ddd; }
    
    .btn-cari { background-color: #00467f; color: white; border: none; font-weight: 600; padding: 0 20px; border-radius: 6px; height: 38px; }
    .btn-reset { background-color: #64748b; color: white; border: none; font-weight: 600; padding: 0 20px; border-radius: 6px; height: 38px; line-height: 38px; text-decoration: none; display: inline-block; }

    /* --- PERBAIKAN BAGIAN UNDUH SESUAI PERMINTAAN --- */
    .download-section-custom {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .download-flex-container {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end; /* Membuat tombol dan input sejajar di bawah */
        gap: 15px;
    }

    .date-input-group {
        display: flex;
        flex-direction: column; /* Label di atas input */
        gap: 8px;
    }

    .date-input-group label {
        font-weight: 700;
        color: #000;
        font-size: 1rem;
        margin-bottom: 0;
    }

    .date-input-group input {
        width: 200px;
        height: 40px;
        border: 1.5px solid #000; /* Border hitam tipis sesuai gambar */
        border-radius: 8px;
        padding: 0 12px;
    }

    .btn-pdf-pastel {
        background-color: #ffe8e8 !important; /* Pink pastel */
        color: #e35d5d !important;
        border: none;
        font-weight: 700;
        height: 40px;
        padding: 0 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-excel-pastel {
        background-color: #e6f4ea !important; /* Hijau pastel */
        color: #2d7d46 !important;
        border: none;
        font-weight: 700;
        height: 40px;
        padding: 0 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<div class="container py-4">
    <div class="report-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="report-title">RIWAYAT PENAMBAHAN MATERIAL</h3>
            
            <form action="{{ route('material-history.index') }}" method="GET" class="search-form">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari Nama Material" value="{{ request('search') }}">
                </div>
                <div class="form-group-tanggal-filter">
                    <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="form-group-tanggal-filter">
                    <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}">
                </div>
                <button type="submit" class="btn-cari">Cari</button>
                <a href="{{ route('material-history.index') }}" class="btn-reset">Reset</a>
            </form>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th class="text-start">Nama Material</th>
                        <th>Jumlah</th>
                        <th>Tanggal (WITA)</th>
                        <th>Foto Material</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $index => $h)
                    <tr>
                        <td class="text-center">
                            {{ $histories instanceof \Illuminate\Pagination\LengthAwarePaginator ? $histories->firstItem() + $index : $index + 1 }}
                        </td>
                        <td class="fw-bold text-uppercase">{{ $h->nama_material }}</td>
                        <td class="text-center"><span class="fw-bold">{{ $h->jumlah }}</span> {{ $h->satuan }}</td>
                        <td class="text-center text-nowrap">
                            {{ \Carbon\Carbon::parse($h->tanggal_input)->format('d M Y, H:i') }}
                        </td>
                        <td class="text-center">
                            @if($h->foto_path)
                                <img src="{{ asset('uploads/material_stand_by/' . $h->foto_path) }}" class="img-frame">
                            @else
                                <span class="text-muted small">No Photo</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Data tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center">
                {{ $histories->appends(request()->query())->links() }}
            </div>
        @endif

        {{-- BAGIAN UNDUH YANG DIRAPIKAN SESUAI PERMINTAAN --}}
        <div class="download-section-custom">
            <form action="{{ route('material-history.download') }}" method="GET">
                <div class="download-flex-container">
                    
                    <div class="date-input-group">
                        <label>Dari Tanggal:</label>
                        <input type="date" name="tanggal_mulai_dl" required>
                    </div>

                    <div class="date-input-group">
                        <label>Sampai Tanggal:</label>
                        <input type="date" name="tanggal_akhir_dl" required>
                    </div>

                    <button type="submit" name="submit_pdf" class="btn-pdf-pastel">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Unduh Pdf
                    </button>

                    <button type="submit" name="submit_excel" class="btn-excel-pastel">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Unduh Excel
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection