@extends('layouts.app')


@section('content')

<style>
/* ===== STYLE PENCARIAN (MENYAMAKAN MATERIAL STAND BY) ===== */

/* 1. Layout Dasar Form Pencarian */
.search-form { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    flex-wrap: wrap;
}

/* 2. KHUSUS TOMBOL CARI & RESET (WARNA ABU-ABU) */
.search-form .btn-primary,
.search-form .btn-secondary {
    min-width: 80px !important;
    height: 40px !important; 
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 15px !important;
    border-radius: 10px !important; 
    font-size: 14px !important;
    border: none !important;
    outline: none !important;
    box-sizing: border-box !important;
    background-color: #6c757d !important; 
    color: white !important;
    text-decoration: none !important;
}

.search-form .btn-primary:hover,
.search-form .btn-secondary:hover {
    background-color: #5a6268 !important; 
}

/* 3. INPUT SEARCH & TANGGAL (IDENTIK MATERIAL STAND BY) */
.search-bar {
    position: relative; 
    height: 40px !important; 
    border: 1px solid #d1d5db; 
    border-radius: 10px; 
    background-color: white;
    box-sizing: border-box;
    width: auto; 
    min-width: 200px; 
}

.search-bar i {
    position: absolute;
    left: 12px; 
    top: 50%;
    transform: translateY(-50%); 
    color: #6c757d; 
    font-size: 14px;
    z-index: 10;
    pointer-events: none; 
}

/* CSS PERBAIKAN: Sembunyikan label manual di HP karena app.blade sudah punya otomatis */
@media (max-width: 991.98px) {
    .form-group-tanggal-filter label {
        display: none !important;
    }
}

/* Sembunyikan label manual di desktop secara default */
.form-group-tanggal-filter label {
    display: none;
}

.search-bar input {
    border: none !important;
    height: 100%;
    width: 100%;
    outline: none;
    padding-left: 35px !important; 
    padding-right: 10px;
    font-size: 14px;
    background: transparent;
    box-sizing: border-box;
}

.search-bar input::-webkit-search-decoration,
.search-bar input::-webkit-search-cancel-button,
.search-bar input::-webkit-search-results-button,
.search-bar input::-webkit-search-results-decoration {
    display: none;
}

.form-control-tanggal {
    height: 40px !important; 
    border: 1px solid #d1d5db; 
    border-radius: 10px; 
    padding: 0 12px;
    box-sizing: border-box;
    font-size: 14px;
    outline: none;
    width: 100%;
}

/* ===== STYLE TABEL & LAINNYA ===== */
.text-nowrap { white-space: nowrap; text-align: center; }

/* Tombol Download */
.btn-foto-download, .btn-pdf, .btn-excel {
    background-color: #5a8dee !important;
    color: white !important;
    border: none !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    border-radius: 6px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    transition: background-color 0.2s;
}

.btn-foto-download:hover, .btn-pdf:hover, .btn-excel:hover {
    background-color: #4a77ce !important;
}

.btn-foto-download i, .btn-pdf i, .btn-excel i {
    color: #ffffff !important;
}

/* Tombol Edit & Hapus Pastel Style */
.btn-edit { 
    background-color: #76b596 !important; 
    color: #333333 !important; 
    padding: 8px 12px; 
    border-radius: 5px; 
    text-decoration: none; 
    font-size: 13px; 
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}
.btn-hapus { 
    background-color: #cc6666 !important; 
    color: white !important; 
    border: none; 
    padding: 8px 12px; 
    border-radius: 5px; 
    cursor: pointer; 
    font-size: 13px; 
    font-weight: 500;
}

/* MODAL FOTO */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.9);
    justify-content: center;
    align-items: center;
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}
</style>

<div class="card-new">
    {{-- HEADER --}}
    <div class="index-header">
        <h2>HISTORY MATERIAL STAND BY</h2>

        {{-- FILTER ATAS --}}
        <form action="{{ route('material-history.index') }}" method="GET" class="search-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search"
                       placeholder="Cari Nama Material"
                       value="{{ request('search') }}">
            </div>

            <div class="form-group-tanggal-filter">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai"
                       class="form-control-tanggal"
                       value="{{ request('tanggal_mulai') }}">
            </div>

            <div class="form-group-tanggal-filter">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir"
                       class="form-control-tanggal"
                       value="{{ request('tanggal_akhir') }}">
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-stand-by.index') }}"
               class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="table-container" style="margin-top: 20px;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th style="text-align: left; padding-left: 15px;">Nama Material</th>
                    <th width="100">Jumlah</th>
                    <th width="100">Satuan</th>
                    <th width="180">Tanggal (WITA)</th>
                    <th width="120">Foto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $index => $h)
                <tr>
                    <td align="center">{{ $histories->firstItem() + $index }}</td>
                    <td style="font-weight:600; text-transform:uppercase; padding-left: 15px;">{{ $h->nama_material }}</td>
                    <td align="center">{{ ltrim($h->jumlah, '+ ') }}</td>
                    <td align="center" style="text-transform:uppercase;">{{ $h->satuan }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($h->tanggal_input)->format('d/m/Y H:i') }}</td>
                    <td align="center">
                        @if($h->foto_path)
                            <img src="{{ asset('uploads/material_stand_by/' . $h->foto_path) }}" 
                                 class="table-foto" 
                                 onclick="openModal(this)">
                        @else
                            <span style="font-size:11px;color:#aaa;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- DATA TIDAK DITEMUKAN POSISI TENGAH TOTAL --}}
                    <td colspan="6" style="text-align: center; vertical-align: middle; padding: 100px 0; color: #6b7280; font-weight: 500; font-size: 16px;">
                        Data tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER EXPORT --}}
    <div class="index-footer-form" style="margin-top:30px; padding-top:20px; border-top:1px solid #eee;">
        <form action="{{ route('material-history.index') }}" method="GET" style="display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap;">
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-weight:700; font-size: 13px;">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}">
            </div>

            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-weight:700; font-size: 13px;">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}">
            </div>

            <button type="submit" name="export" value="pdf" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>

            <button type="submit" name="export" value="excel" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>
</div>

{{-- MODAL POP-UP IMAGE --}}
<div id="imageModal" class="image-modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img class="image-modal-content" id="img01">
</div>

<script>
    function openModal(element) {
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("img01");
        modal.style.display = "flex"; 
        modalImg.src = element.src;   
    }

    function closeModal() {
        document.getElementById("imageModal").style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("imageModal");
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

@endsection