@extends('layouts.app')

@section('title', 'Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-new">

    <div class="index-header">
        <h2>LAPORAN MATERIAL KEMBALI</h2>

        <form action="{{ route('material_kembali.index') }}" method="GET" class="search-form">
            
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
            </div>

            <div class="date-filter-group">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal"
                    value="{{ request('tanggal_mulai') }}" placeholder="Dari Tanggal">
                
                <input type="date" name="tanggal_akhir" class="form-control-tanggal"
                    value="{{ request('tanggal_akhir') }}" placeholder="Sampai Tanggal">
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material_kembali.index') }}" class="btn btn-secondary btn-sm">Reset</a>
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
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah</th>
                    <th>Saldo Saat Ini</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Foto Petugas</th>
                    <th>Aksi</th>
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
                                    style="max-width:80px; margin-bottom:5px; cursor:pointer;">

                                <a href="{{ route('material_kembali.download-foto', $item->id) }}"
                                    class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            @else
                                <span class="text-danger">Tidak ada</span>
                            @endif
                        </td>

                        <td style="text-align:center; vertical-align:top;">
                            @if($item->foto_petugas)
                                <img src="{{ route('material_kembali.show-foto-petugas', $item->id) }}"
                                    class="table-foto zoomable"
                                    style="max-width:80px; margin-bottom:5px; cursor:pointer;">

                                <a href="{{ route('material_kembali.download-foto-petugas', $item->id) }}"
                                    class="btn-foto-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            @else
                                <span class="text-danger">Tidak ada</span>
                            @endif
                        </td>

                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material_kembali.edit', $item->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('material_kembali.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini akan mengurangi stok Stand By.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6c757d; padding:50px 0;">
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

    <div class="index-footer-form">
        <form action="{{ route('material_kembali.download') }}" method="POST" class="form-download">
            @csrf

            <div class="form-group-tanggal">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" required>
            </div>

            <div class="form-group-tanggal">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" required>
            </div>

            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>

            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>

</div>

<style>
.search-form { display:flex; align-items:center; gap:15px; }
.date-filter-group { display:flex; gap:10px; }
.date-filter-group input {
    padding:5px 10px;
    border:1px solid #ccc;
    border-radius:4px;
    width:130px;
}

.text-nowrap {
    white-space:nowrap;
    text-align:center;
}

/* ===== WARNA TOMBOL DOWNLOAD ===== */

.btn-foto-download,
.btn-pdf,
.btn-excel {
    background-color:#5a8dee !important;
    color:white !important;
    border:none !important;
}

/* icon putih */
.btn-foto-download i,
.btn-pdf i,
.btn-excel i {
    color:#ffffff !important;
}

/* hover */
.btn-foto-download:hover,
.btn-pdf:hover,
.btn-excel:hover {
    background-color:#4a7bd1 !important;
}

/* ===== UKURAN TOMBOL PDF & EXCEL ===== */

.btn-pdf,
.btn-excel {
    padding:6px 12px !important;
    font-size:13px !important;
    border-radius:6px !important;
}

.btn-pdf i,
.btn-excel i {
    font-size:12px !important;
}

/* ===== MODAL FOTO ===== */

.image-modal {
    display:none;
    position:fixed;
    z-index:9999;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.9);
}

.modal-content {
    margin:auto;
    display:block;
    max-width:90%;
    max-height:90%;
    margin-top:5%;
}

.close-modal {
    position:absolute;
    top:20px;
    right:30px;
    color:white;
    font-size:40px;
    cursor:pointer;
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
