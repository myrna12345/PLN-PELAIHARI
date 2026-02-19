@extends('layouts.app')

@section('title', 'Laporan Siaga Keluar')

@section('content')

<style>
/* ===== STYLE TOMBOL & INPUT (STANDARISASI) ===== */

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

/* Ensure icons inherit color */
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

/* 5. KHUSUS TOMBOL CARI & RESET (ABU-ABU & UKURAN SAMA) */
/* PERBAIKAN: Menambahkan box-sizing agar ukuran identik di tampilan HP */
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
    
    /* WARNA KEDUANYA ABU-ABU */
    background-color: #6c757d !important; 
    color: white !important;
    text-decoration: none !important;
    box-sizing: border-box !important; /* PENTING: Menyamakan perilaku lebar elemen link dan button di mobile */
}
.search-form .btn-primary:hover,
.search-form .btn-secondary:hover {
    background-color: #5a6268 !important; 
}

/* 6. INPUT SEARCH & TANGGAL (STYLE) */
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

/* Hide default browser search icons */
.search-bar input::-webkit-search-decoration,
.search-bar input::-webkit-search-cancel-button,
.search-bar input::-webkit-search-results-button,
.search-bar input::-webkit-search-results-decoration {
    display: none;
}

/* Style Input Tanggal */
.form-control-tanggal {
    height: 40px !important;          
    border: 1px solid #d1d5db;       
    border-radius: 10px;             
    padding: 0 12px;
    box-sizing: border-box;
    font-size: 14px;
    outline: none;
}

/* 7. LAYOUT TOMBOL AKSI BERSEBELAHAN */
.table-actions {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}
.table-actions form {
    margin: 0 !important;
    padding: 0 !important;
    display: inline-flex !important;
}

/* Layout Foto di Tabel */
.table-foto {
    max-width: 80px; 
    display: block; 
    margin: 0 auto 5px; 
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
}
.table-foto:hover {
    opacity: 0.8;
}

/* Layout Footer Form */
.index-footer-form {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}
.form-download {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}
.form-group-tanggal label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

/* CSS MODAL POP-UP */
.image-modal {
    display: none; 
    position: fixed; 
    z-index: 9999; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: hidden; 
    background-color: rgba(0,0,0,0.85); 
    justify-content: center;
    align-items: center;
}
.image-modal-content {
    margin: auto;
    display: block;
    width: auto; 
    height: auto;
    max-width: 90%;  
    max-height: 90vh; 
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}
.close-modal {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10001;
}
.close-modal:hover {
    color: #ccc;
}
</style>

<div class="card-new">

    <div class="index-header">
        <h2>LAPORAN SIAGA KELUAR</h2>
        
        <form action="{{ route('siaga-keluar.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material" value="{{ request('search') }}">
            </div>
            
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}" title="Tanggal Akhir">
            </div>
            
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('siaga-keluar.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material & Nomor Meter</th> 
                    <th>Nama Petugas</th>
                    <th>Stand Meter</th>
                    <th>Keterangan</th> 
                    <th>Status</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Foto Petugas</th>
                    {{-- Header Aksi untuk Admin --}}
                    @if(strtolower(auth()->user()->role) !== 'satpam')
                    <th style="min-width: 150px; text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($dataSiagaKeluar as $item)
                    <tr>
                        <td>{{ $dataSiagaKeluar->firstItem() + $loop->index }}</td>
                        
                        <td>
                            {{ $item->material->nama_material ?? 'N/A' }} 
                            @if ($item->nomor_meter) 
                                - {{ $item->nomor_meter }} 
                            @endif
                        </td>
                        
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->stand_meter ?? '-' }}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                        
                        {{-- Foto Material --}}
                        <td style="text-align: center;"> 
                            @if($item->foto_path)
                                <img src="{{ asset('uploads/siaga_keluar/' . $item->foto_path) }}" 
                                     class="table-foto" 
                                     onclick="openModal(this)"
                                     alt="Foto Material">
                                
                                {{-- Tombol Download Foto untuk Admin --}}
                                @if(strtolower(auth()->user()->role) !== 'satpam')
                                <a href="{{ route('siaga-keluar.download-foto', $item->id) }}" class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        {{-- Foto Petugas --}}
                        <td style="text-align: center;"> 
                            @if($item->foto_petugas)
                                <img src="{{ asset('uploads/siaga_keluar/' . $item->foto_petugas) }}" 
                                     class="table-foto" 
                                     onclick="openModal(this)"
                                     alt="Foto Petugas">
                                
                                {{-- Tombol Download Foto Petugas untuk Admin --}}
                                @if(strtolower(auth()->user()->role) !== 'satpam')
                                <a href="{{ route('siaga-keluar.download-foto-petugas', $item->id) }}" class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        {{-- Kolom Aksi (Edit/Hapus) untuk Admin --}}
                        @if(strtolower(auth()->user()->role) !== 'satpam')
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('siaga-keluar.edit', $item->id) }}" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('siaga-keluar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ strtolower(auth()->user()->role) !== 'satpam' ? 10 : 9 }}" class="text-center" style="text-align: center; vertical-align: middle; padding: 40px 0; font-weight: 500; color: #6b7280;">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $dataSiagaKeluar->appends(request()->query())->links() }}
    </div>

    {{-- Footer Download Laporan untuk Admin --}}
    @if(strtolower(auth()->user()->role) !== 'satpam')
    <div class="index-footer-form">
        <form action="{{ route('siaga-keluar.download-report') }}" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
            </div>
            
            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>
    @endif
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
        var modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("imageModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

@endsection