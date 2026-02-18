@extends('layouts.app')

@section('title', 'Riwayat Penambahan Material - SIMAS-PLN')

@section('content')

<style>

/* 1. Layout Dasar Tombol */
.btn-pdf, 
.btn-excel, 
.btn-foto-download, 
.btn-edit, 
.btn-hapus {
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 13px;
    cursor: pointer;
    transition: background-color 0.2s, color 0.2s;
    white-space: nowrap;
}

/* Icons inherit color */
.btn-pdf i, .btn-excel i, .btn-foto-download i, .btn-edit i, .btn-hapus i {
    color: inherit !important;
}

/* 2. Tombol PDF, Excel, & Download Foto (BIRU SOFT) */
.btn-pdf, 
.btn-excel,
.btn-foto-download {
    background-color: #5a8dee !important;
    color: white !important;
}
.btn-pdf:hover, 
.btn-excel:hover,
.btn-foto-download:hover {
    background-color: #4a77ce !important; 
}

/* 3. Tombol Edit (HIJAU PASTEL AGAK GELAP) */
.btn-edit {
    background-color: #76b596 !important; 
    color: #333333 !important; 
}
.btn-edit:hover {
    background-color: #62a384 !important; 
}

/* 4. Tombol Hapus (MERAH PASTEL AGAK GELAP) */
.btn-hapus {
    background-color: #cc6666 !important; 
    color: white !important; 
}
.btn-hapus:hover {
    background-color: #b35555 !important; 
}

/* 5. TOMBOL CARI & RESET (ABU-ABU) */
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
    background-color: #6c757d !important; 
    color: white !important;
    text-decoration: none !important;
}
.search-form .btn-primary:hover,
.search-form .btn-secondary:hover {
    background-color: #5a6268 !important; 
}

/* 6. INPUT SEARCH & TANGGAL */
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

.form-control-tanggal {
    height: 40px !important;         
    border: 1px solid #d1d5db;       
    border-radius: 10px;             
    padding: 0 12px;
    box-sizing: border-box;
    font-size: 14px;
    outline: none;
}

/* 7. LAYOUT AKSI */
.table-actions {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

.table-foto {
    max-width: 80px; 
    display: block; 
    margin: 0 auto 5px; 
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
    border: 1px solid #ddd;
}

/* MODAL STYLE */
.image-modal {
    display: none; 
    position: fixed; 
    z-index: 9999; 
    left: 0; top: 0; width: 100%; height: 100%; 
    background-color: rgba(0,0,0,0.85); 
    justify-content: center; align-items: center;
}
.image-modal-content {
    max-width: 90%; max-height: 90vh; border-radius: 4px;
}
.close-modal {
    position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; cursor: pointer;
}
</style>

<div class="card-new">
    {{-- HEADER --}}
    <div class="index-header">
        <h2>RIWAYAT PENAMBAHAN MATERIAL</h2>

        {{-- FILTER ATAS --}}
        <form action="{{ route('material-history.index') }}" method="GET" class="search-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material..." value="{{ request('search') }}">
            </div>
            
            <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}" title="Tanggal Mulai">
            <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}" title="Tanggal Akhir">
            
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('material-history.index') }}" class="btn btn-secondary">Reset</a>
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