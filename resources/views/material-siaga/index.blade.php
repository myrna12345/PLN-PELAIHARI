@extends('layouts.app')

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
    box-sizing: border-box !important;
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
    {{-- HEADER + FILTER --}}
    <div class="index-header">
        <h2>LAPORAN MATERIAL SIAGA STAND BY</h2>

        <form action="{{ route('material-siaga.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material" value="{{ request('search') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="start_date" class="form-control-tanggal" value="{{ request('start_date') }}" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="end_date" class="form-control-tanggal" value="{{ request('end_date') }}" title="Tanggal Akhir">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-siaga.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material & Nomor Meter</th>
                    <th>Stand Meter</th>
                    <th>Tanggal (WITA)</th>
                    <th>Status</th>
                    <th>Foto</th>
                    @if(strtolower(auth()->user()->role) !== 'satpam')
                    <th style="min-width: 150px; text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse ($dataSiaga as $data)
                <tr>
                    <td align="center">{{ $loop->iteration + ($dataSiaga->firstItem() - 1) }}</td>
                    <td>{{ strtoupper($data->nama_material) }} - {{ $data->nomor_meter }}</td>
                    <td align="center">{{ $data->stand_meter }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($data->tanggal)->format('d M Y, H:i') }}</td>
                    <td align="center">
                        <span style="font-weight:bold; color: {{ $data->status === 'Ready' ? '#2e7d32' : '#c62828' }}">
                            {{ strtoupper($data->status) }}
                        </span>
                    </td>

                    <td style="text-align: center;">
                        @if($data->unggah_foto && Storage::disk('public')->exists($data->unggah_foto))
                            <img src="{{ route('material-siaga.show-foto', $data->id) }}"
                                 class="table-foto"
                                 onclick="openModal(this)"
                                 alt="Foto Material">
                            
                            @if(strtolower(auth()->user()->role) !== 'satpam')
                            <a href="{{ route('material-siaga.download-foto', $data->id) }}" class="btn-foto-download">
                                <i class="fas fa-download"></i> Download
                            </a>
                            @endif
                        @else
                            <span style="font-size:11px;color:#aaa;">-</span>
                        @endif
                    </td>

                    @if(strtolower(auth()->user()->role) !== 'satpam')
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('material-siaga.edit', $data->id) }}" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <form action="{{ route('material-siaga.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-hapus">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    {{-- PERBAIKAN: Posisi teks di tengah vertikal (padding) dan horizontal (text-align) --}}
                    <td colspan="{{ strtolower(auth()->user()->role) !== 'satpam' ? 7 : 6 }}" 
                        style="text-align: center; vertical-align: middle; padding: 100px 0; color: #6b7280; font-weight: 500; font-size: 16px;">
                        Data tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $dataSiaga->appends(request()->query())->links() }}
    </div>

    {{-- FOOTER EXPORT --}}
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