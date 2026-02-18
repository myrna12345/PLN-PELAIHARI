@extends('layouts.app')

@section('title', 'Material Keluar - SIMAS-PLN')

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
    background-color: #6c757d !important; 
    color: white !important;
    text-decoration: none !important;
    cursor: pointer;
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
    min-width: 250px; 
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

/* ===== STYLE TABEL & LAINNYA ===== */
.text-nowrap { white-space: nowrap; text-align: center; }
.col-keterangan { max-width: 150px; word-wrap: break-word; }
.table th.col-jumlah, .table th.col-stok { min-width: 80px; }
.table th.col-no { width: 50px; }

/* TOMBOL DOWNLOAD & AKSI */
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

/* Tombol Edit & Hapus Original */
.btn-edit { background-color: #76b596 !important; color: #333 !important; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; }
.btn-hapus { background-color: #cc6666 !important; color: white !important; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px; }

/* MODAL POPUP FOTO */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 50px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 35px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}
</style>

<div class="card-new">

    <div class="index-header">
        <h2>LAPORAN MATERIAL KELUAR</h2>

        <form action="{{ route('material_keluar.index') }}" method="GET" class="search-form">
            {{-- SEARCH BAR --}}
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" 
                       placeholder="Cari Nama Material / Petugas..." 
                       value="{{ request('search') }}">
            </div>
            
            {{-- INPUT TANGGAL --}}
            <input type="date" name="tanggal_mulai" 
                   class="form-control-tanggal" 
                   value="{{ request('tanggal_mulai') }}">
            
            <input type="date" name="tanggal_akhir" 
                   class="form-control-tanggal" 
                   value="{{ request('tanggal_akhir') }}">

            {{-- TOMBOL CARI & RESET --}}
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material_keluar.index') }}" 
               class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th class="col-jumlah">Jumlah</th>
                    <th class="col-stok">Saldo saat Ini</th>
                    <th>Keterangan</th> 
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Foto Petugas</th>
                    @if(auth()->user()->role !== 'satpam')
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($materialKeluar as $item)
                    <tr>
                        <td>{{ $materialKeluar->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? '-' }}</td> 
                        <td>{{ $item->nama_petugas }}</td>
                        
                        <td class="text-nowrap">{{ $item->jumlah_material }} {{ $item->satuan_material }}</td>
                        <td class="text-nowrap">{{ $item->stok_saat_ini }}</td>
                        
                        <td class="col-keterangan">{{ $item->keterangan }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto)
                                <img src="{{ route('material_keluar.show-foto', $item->id) }}" 
                                    alt="Foto Material" 
                                    class="table-foto zoomable"
                                    style="max-width: 80px; margin-bottom:5px; cursor:pointer;">

                                @if(auth()->user()->role !== 'satpam')
                                <a href="{{ route('material_keluar.download-foto', $item->id) }}" 
                                    class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto_petugas)
                                <img src="{{ route('material_keluar.show-foto-petugas', $item->id) }}"
                                    alt="Foto Petugas" 
                                    class="table-foto zoomable"
                                    style="max-width: 80px; margin-bottom:5px; cursor:pointer;">

                                @if(auth()->user()->role !== 'satpam')
                                <a href="{{ route('material_keluar.download-foto-petugas', $item->id) }}"
                                class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span>-</span>
                            @endif
                        </td>

                        @if(auth()->user()->role !== 'satpam')
                        <td>
                            <div class="table-actions" style="display: flex; gap: 5px; justify-content: center;">
                                <a href="{{ route('material_keluar.edit', $item->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('material_keluar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini akan mengembalikan stok!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role !== 'satpam' ? 10 : 9 }}" style="text-align:center; color:#6c757d; padding:50px 0;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $materialKeluar->appends(request()->query())->links() }}
    </div>

    @if(auth()->user()->role !== 'satpam')
    <div class="index-footer-form">
        <form action="{{ route('material_keluar.download') }}" method="POST" class="form-download">
            @csrf
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="submit_pdf" value="1" class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>
    @endif

</div>

{{-- MODAL IMAGE --}}
<div id="imageModal" class="image-modal">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const closeBtn = document.querySelector(".close-modal");

    document.querySelectorAll(".zoomable").forEach(img => {
        img.addEventListener("click", function () {
            modal.style.display = "block";
            modalImg.src = this.src;
        });
    });

    closeBtn.onclick = () => modal.style.display = "none";
    modal.onclick = (e) => {
        if (e.target === modal) modal.style.display = "none";
    };
});
</script>

@endsection