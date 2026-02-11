@extends('layouts.app')

@section('content')
<style>
/* ===== STYLE TOMBOL & INPUT (Material Stand By) ===== */

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
}

/* Ensure icons inherit color */
.btn-pdf i, .btn-excel i, .btn-foto-download i, .btn-edit i, .btn-hapus i {
    color: inherit !important;
}

/* 2. Tombol PDF, Excel, & Download Foto (BIRU SOFT - Teks Putih) */
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

/* 3. Tombol Edit (HIJAU PASTEL AGAK GELAP - Teks Gelap) */
.btn-edit {
    background-color: #76b596 !important; 
    color: #333333 !important; 
}
.btn-edit:hover {
    background-color: #62a384 !important; 
}

/* 4. Tombol Hapus (MERAH PASTEL AGAK GELAP - Teks Putih) */
.btn-hapus {
    background-color: #cc6666 !important; 
    color: white !important; 
}
.btn-hapus:hover {
    background-color: #b35555 !important; 
}

/* 5. KHUSUS TOMBOL CARI & RESET (SAMAKAN UKURAN & WARNA ABU-ABU) */
.search-form .btn-primary,
.search-form .btn-secondary {
    min-width: 80px !important;
    height: 40px !important;     /* Tinggi 40px */
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
}

.search-form .btn-primary:hover,
.search-form .btn-secondary:hover {
    background-color: #5a6268 !important; 
}

/* 6. INPUT SEARCH & TANGGAL (PERBAIKAN DOUBLE/TUMPUK) */
.search-bar {
    position: relative;              /* Penting untuk posisi ikon absolute */
    height: 40px !important;         
    border: 1px solid #d1d5db;       
    border-radius: 10px;             
    background-color: white;
    box-sizing: border-box;
    width: auto;                     /* Sesuaikan lebar */
    min-width: 200px;                /* Lebar minimal agar tidak gepeng */
}

/* Ikon Kaca Pembesar (Absolute Position agar rapi) */
.search-bar i {
    position: absolute;
    left: 12px;                      /* Jarak dari kiri */
    top: 50%;
    transform: translateY(-50%);     /* Posisi tengah vertikal */
    color: #6c757d;                  /* Warna ikon abu */
    font-size: 14px;
    z-index: 10;
    pointer-events: none;            /* Agar klik tembus ke input */
}

/* Input Text (Padding Kiri Besar agar tidak nabrak ikon) */
.search-bar input {
    border: none !important;
    height: 100%;
    width: 100%;
    outline: none;
    padding-left: 35px !important;   /* PENTING: Memberi ruang untuk ikon */
    padding-right: 10px;
    font-size: 14px;
    background: transparent;
    box-sizing: border-box;
}

/* Menghilangkan ikon 'X' bawaan browser pada input search */
.search-bar input::-webkit-search-decoration,
.search-bar input::-webkit-search-cancel-button,
.search-bar input::-webkit-search-results-button,
.search-bar input::-webkit-search-results-decoration {
    display: none;
}

/* Style Input Tanggal (dd/mm/yy) */
.form-control-tanggal {
    height: 40px !important;         
    border: 1px solid #d1d5db;       
    border-radius: 10px;             
    padding: 0 12px;
    box-sizing: border-box;
    font-size: 14px;
    outline: none;
}


/* ===== STYLE LAINNYA (LAYOUT HALAMAN) ===== */
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

.wrapper-foto-tengah {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

/* ===== CSS MODAL POP-UP ===== */
.thumbnail-img {
    cursor: pointer;
    border-radius: 4px;
    transition: opacity 0.2s;
}
.thumbnail-img:hover {
    opacity: 0.8;
}

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

    {{-- HEADER --}}
    <div class="index-header">
        <h2>LAPORAN MATERIAL STAND BY</h2>

        <form action="{{ route('material-stand-by.index') }}" method="GET" class="search-form">
            {{-- SEARCH BAR (Container) --}}
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search"
                       placeholder="Cari Nama Material"
                       value="{{ request('search') }}">
            </div>

            {{-- INPUT TANGGAL (DD/MM/YY) --}}
            <input type="date" name="tanggal_mulai"
                   class="form-control-tanggal"
                   value="{{ request('tanggal_mulai') }}">

            <input type="date" name="tanggal_akhir"
                   class="form-control-tanggal"
                   value="{{ request('tanggal_akhir') }}">

            {{-- TOMBOL CARI & RESET (ABU-ABU) --}}
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-stand-by.index') }}"
               class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Jumlah</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    
                    {{-- REVISI 1: Sembunyikan Header Aksi untuk Satpam --}}
                    @if(auth()->user()->role !== 'satpam')
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>
                    <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                    <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td>{{ $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                    <td class="text-center">
                        @if($item->foto_path)
                            <div class="wrapper-foto-tengah">
                                <img src="{{ asset('uploads/material_stand_by/' . $item->foto_path) }}"
                                     class="thumbnail-img"
                                     onclick="openModal(this)"
                                     alt="Foto Material"
                                     style="max-width:80px; display:block; margin-bottom: 6px;">
                                
                                {{-- REVISI 2: Sembunyikan Tombol Download Foto untuk Satpam --}}
                                @if(auth()->user()->role !== 'satpam')
                                    <a href="{{ route('material-stand-by.download-foto', $item->id) }}"
                                       class="btn-foto-download">
                                       <i class="fas fa-download"></i> Download
                                    </a>
                                @endif
                            </div>
                        @else
                            -
                        @endif
                    </td>

                    {{-- REVISI 3: Sembunyikan Seluruh Kolom Aksi (Edit/Hapus) untuk Satpam --}}
                    @if(auth()->user()->role !== 'satpam')
                    <td>
                        <div class="table-actions" style="display: flex; gap: 5px; justify-content: center;">
                            {{-- Tombol Edit --}}
                            <a href="{{ route('material-stand-by.edit', $item->id) }}"
                               class="btn-edit">
                               <i class="fas fa-edit"></i> Edit
                            </a>

                            {{-- Tombol Hapus --}}
                            <form action="{{ route('material-stand-by.destroy', $item->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                    {{-- Logika Pesan Kosong --}}
                    {{-- Colspan disesuaikan jika Satpam (5 kolom) vs Admin (6 kolom) --}}
                    <td colspan="{{ auth()->user()->role !== 'satpam' ? 6 : 5 }}" class="text-center" style="padding: 40px 0; color: #6b7280; text-align: center;">
                        @if(request('search') || request('tanggal_mulai') || request('tanggal_akhir'))
                            Data tidak ditemukan.
                        @else
                            Tidak ada data saat ini.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- REVISI 4: Sembunyikan Footer Download PDF & Excel untuk Satpam --}}
    @if(auth()->user()->role !== 'satpam')
    <div class="index-footer-form">
        <form action="{{ route('material-stand-by.pdf') }}"
              method="POST"
              class="form-download">
            @csrf

            <div class="form-group-tanggal">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai"
                       class="form-control-tanggal" required>
            </div>

            <div class="form-group-tanggal">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir"
                       class="form-control-tanggal" required>
            </div>

            <button type="submit" class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>

            <button type="submit"
                    formaction="{{ route('material-stand-by.excel') }}"
                    class="btn-excel">
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

{{-- SCRIPT JAVASCRIPT --}}
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