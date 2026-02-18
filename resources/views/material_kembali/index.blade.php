@extends('layouts.app')

@section('title', 'Material Kembali - SIMAS-PLN')

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

    <div class="index-header">
        <h2>LAPORAN MATERIAL KEMBALI</h2>

        <form action="{{ route('material_kembali.index') }}" method="GET" class="search-form">
            
            {{-- SEARCH BAR --}}
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" 
                       placeholder="Cari Nama Material" 
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
            <a href="{{ route('material_kembali.index') }}" 
               class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah</th>
                    <th>Saldo Saat Ini</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Foto Petugas</th>
                    @if(auth()->user()->role !== 'satpam')
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($materialKembali as $item)
                    <tr>
                        <td>{{ $materialKembali->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? 'Material Dihapus' }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td class="text-nowrap">{{ $item->jumlah_material }} {{ $item->satuan }}</td>
                        <td class="text-nowrap">{{ $item->stok_saat_ini }}</td>

                        <td class="text-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}
                        </td>

                        <td style="text-align:center; vertical-align:top;">
                            @if($item->foto)
                                <img src="{{ route('material_kembali.show-foto', $item->id) }}"
                                    class="table-foto zoomable"
                                    style="max-width:80px; margin-bottom:5px; cursor:pointer;"
                                    alt="Foto Material">

                                @if(auth()->user()->role !== 'satpam')
                                <a href="{{ route('material_kembali.download-foto', $item->id) }}"
                                    class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span class="text-danger">Tidak ada</span>
                            @endif
                        </td>

                        <td style="text-align:center; vertical-align:top;">
                            @if($item->foto_petugas)
                                <img src="{{ route('material_kembali.show-foto-petugas', $item->id) }}"
                                    class="table-foto zoomable"
                                    style="max-width:80px; margin-bottom:5px; cursor:pointer;"
                                    alt="Foto Petugas">

                                @if(auth()->user()->role !== 'satpam')
                                <a href="{{ route('material_kembali.download-foto-petugas', $item->id) }}"
                                    class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @endif
                            @else
                                <span class="text-danger">Tidak ada</span>
                            @endif
                        </td>

                        @if(auth()->user()->role !== 'satpam')
                        <td>
                            <div class="table-actions" style="display: flex; gap: 5px; justify-content: center;">
                                <a href="{{ route('material_kembali.edit', $item->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('material_kembali.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini akan mengurangi stok Stand By.')">
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
                        <td colspan="{{ auth()->user()->role !== 'satpam' ? 9 : 8 }}" style="text-align:center; color:#6c757d; padding:50px 0;">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $materialKembali->appends(request()->query())->links() }}
    </div>

    @if(auth()->user()->role !== 'satpam')
    <div class="index-footer-form" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
        <form action="{{ route('material_kembali.download') }}" method="POST" class="form-download" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
            @csrf

            <div class="form-group-tanggal">
                <label style="font-weight: 600; margin-bottom: 6px; display: block;">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" required>
            </div>

            <div class="form-group-tanggal">
                <label style="font-weight: 600; margin-bottom: 6px; display: block;">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" required>
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

{{-- MODAL FOTO --}}
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
            modal.style.display = "flex";
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