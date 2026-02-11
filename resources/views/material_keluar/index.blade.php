@extends('layouts.app')

@section('title', 'Material Keluar - SIMAS-PLN')

@section('content')

<div class="card-new">

<div class="index-header">
    <h2>LAPORAN MATERIAL KELUAR</h2>

    <form action="{{ route('material_keluar.index') }}" method="GET" class="search-form">
        
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
        </div>
        
        <div class="date-filter-group">
            <input type="date" name="tanggal_mulai" 
                class="form-control-tanggal" 
                value="{{ request('tanggal_mulai') }}" 
                placeholder="Dari Tanggal">
            
            <input type="date" name="tanggal_akhir" 
                class="form-control-tanggal" 
                value="{{ request('tanggal_akhir') }}" 
                placeholder="Sampai Tanggal">
        </div>

        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <a href="{{ route('material_keluar.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success text-center">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger text-center">{{ session('error') }}</div>
@endif

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
                        <div class="table-actions">
                            <a href="{{ route('material_keluar.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('material_keluar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini akan mengembalikan stok!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-hapus">Hapus</button>
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
        <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
            <i class="fas fa-file-pdf"></i> Unduh PDF
        </button>
        <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
            <i class="fas fa-file-excel"></i> Unduh Excel
        </button>
    </form>
</div>
@endif

</div>

<style>
.search-form { display: flex; align-items: center; gap: 15px; }
.date-filter-group { display: flex; gap: 10px; }
.date-filter-group input { padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px; width: 130px; }

.text-nowrap { white-space: nowrap; text-align: center; }

.col-keterangan { max-width: 150px; word-wrap: break-word; }

.table th.col-jumlah, 
.table th.col-stok { min-width: 80px; }

.table th.col-no { width: 50px; }

/* ===== WARNA TOMBOL DOWNLOAD ===== */

.btn-foto-download,
.btn-pdf,
.btn-excel {
    background-color: #5a8dee !important;
    color: white !important;
    border: none !important;
}

/* ===== UKURAN TOMBOL PDF & EXCEL ===== */

.btn-pdf,
.btn-excel {
    padding: 6px 12px !important;
    font-size: 13px !important;
    border-radius: 6px !important;
}

.btn-pdf i,
.btn-excel i {
    font-size: 12px !important;
}

.btn-foto-download i,
.btn-pdf i,
.btn-excel i {
    color: #ffffff !important;
}

/* ===== MODAL POPUP FOTO ===== */

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
    modal.onclick = () => modal.style.display = "none";
});
</script>

@endsection