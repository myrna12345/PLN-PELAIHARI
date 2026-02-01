@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .report-card { background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 25px; }
    .report-title { font-weight: 700; color: #333; text-transform: uppercase; margin-bottom: 5px; }
    .table thead th { background-color: #f1f5f9; color: #333; font-weight: 700; padding: 15px; border-bottom: 2px solid #e2e8f0; text-align: center; }
    .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #edf2f7; }
    .img-frame { width: 80px; height: auto; border-radius: 4px; border: 1px solid #ddd; }
    
    /* Tombol Style */
    .btn-pdf { background-color: #e3342f; color: white; border: none; font-weight: 600; padding: 8px 16px; border-radius: 6px; }
    .btn-excel { background-color: #38c172; color: white; border: none; font-weight: 600; padding: 8px 16px; border-radius: 6px; }
    .form-label-sm { font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 4px; display: block; }
</style>

<div class="container py-4">
    <div class="report-card">
        <h3 class="report-title">RIWAYAT PENAMBAHAN MATERIAL</h3>
        <p class="text-muted mb-4">Daftar log aktivitas penambahan stok ke sistem.</p>

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
                        <td class="text-center">{{ $index + 1 }}</td>
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
                        <td colspan="5" class="text-center py-5 text-muted">Belum ada data riwayat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       <div class="pt-4 border-top">
    <form action="{{ route('material-history.download') }}" method="GET">
        <div class="d-flex align-items-center gap-4 py-2">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0 text-nowrap" style="font-size: 0.9rem;">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control" style="width: 180px;">
            </div>

            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0 text-nowrap" style="font-size: 0.9rem;">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control" style="width: 180px;">
            </div>

            <div class="d-flex gap-2 ms-2">
                <button type="submit" name="submit_pdf" class="btn border-0 px-4 py-2" style="background-color: #ffe5e5; color: #d9534f; font-weight: 600; border-radius: 8px;">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh Pdf
                </button>
                <button type="submit" name="submit_excel" class="btn border-0 px-4 py-2" style="background-color: #e6f4ea; color: #2d7d46; font-weight: 600; border-radius: 8px;">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Unduh Excel
                </button>
            </div>
        </div>
    </form>
</div>
    </div>
</div>
@endsection