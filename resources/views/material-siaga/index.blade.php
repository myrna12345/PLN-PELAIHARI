@extends('layouts.app')

@section('content')

<style>
/* 1. Layout Dasar Tombol */
.btn-pdf, .btn-excel, .btn-foto-download, .btn-edit, .btn-hapus {
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

.btn-pdf i, .btn-excel i, .btn-foto-download i, .btn-edit i, .btn-hapus i { color: inherit !important; }

/* 2. Tombol Warna */
.btn-pdf, .btn-excel, .btn-foto-download { background-color: #5a8dee !important; color: white !important; }
.btn-pdf:hover, .btn-excel:hover, .btn-foto-download:hover { background-color: #4a77ce !important; }
.btn-edit { background-color: #76b596 !important; color: #333333 !important; }
.btn-edit:hover { background-color: #62a384 !important; }
.btn-hapus { background-color: #cc6666 !important; color: white !important; }
.btn-hapus:hover { background-color: #b35555 !important; }

/* 3. Tombol Cari & Reset */
.search-form .btn-primary, .search-form .btn-secondary {
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

/* 4. INPUT SEARCH & TANGGAL */
.search-bar {
    position: relative; height: 40px !important; border: 1px solid #d1d5db; border-radius: 10px; background-color: white; box-sizing: border-box; width: auto; min-width: 200px; 
}
.search-bar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: 14px; z-index: 10; pointer-events: none; }
.search-bar input { border: none !important; height: 100%; width: 100%; outline: none; padding-left: 35px !important; padding-right: 10px; font-size: 14px; background: transparent; box-sizing: border-box; }

/* Perbaikan Label HP agar tidak double */
@media (max-width: 991.98px) { .label-manual { display: none !important; } }
.label-manual { display: none; } /* Default sembunyi di desktop */

.form-control-tanggal { height: 40px !important; border: 1px solid #d1d5db; border-radius: 10px; padding: 0 12px; box-sizing: border-box; font-size: 14px; outline: none; width: 100%; }

.table-actions { display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center; justify-content: center; gap: 8px; width: 100%; }
.table-foto { max-width: 80px; display: block; margin: 0 auto 5px; border-radius: 4px; cursor: pointer; transition: opacity 0.2s; border: 1px solid #ddd; }

/* MODAL STYLE */
.image-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); justify-content: center; align-items: center; }
.image-modal-content { max-width: 90%; max-height: 90vh; border-radius: 4px; }
.close-modal { position: absolute; top: 20px; right: 35px; color: #fff; font-size: 40px; font-weight: bold; cursor: pointer; }
</style>

<div class="card-new">
    <div class="index-header">
        <h2>LAPORAN MATERIAL SIAGA STAND BY</h2>

        <form action="{{ route('material-siaga-stand-by.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material" value="{{ request('search') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <label class="label-manual">Dari Tanggal:</label>
                <input type="date" name="start_date" class="form-control-tanggal" value="{{ request('start_date') }}" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <label class="label-manual">Sampai Tanggal:</label>
                <input type="date" name="end_date" class="form-control-tanggal" value="{{ request('end_date') }}" title="Tanggal Akhir">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-siaga-stand-by.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material & Nomor Meter</th>
                    <th>Stand Meter</th>
                    <th>Tanggal (WITA)</th>
                    <th>Status</th>
                    <th style="text-align: center;">Foto Material</th>
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
                        @if($data->unggah_foto && File::exists(public_path('uploads/material_siaga/' . $data->unggah_foto)))
                            <img src="{{ asset('uploads/material_siaga/' . $data->unggah_foto) }}"
                                 class="table-foto"
                                 onclick="openModal(this)"
                                 alt="Foto Material">
                            
                            @if(strtolower(auth()->user()->role) !== 'satpam')
                            <a href="{{ route('material-siaga-stand-by.download-foto', $data->id) }}" class="btn-foto-download">
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
                            <a href="{{ route('material-siaga-stand-by.edit', $data->id) }}" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <form action="{{ route('material-siaga-stand-by.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                    <td colspan="{{ strtolower(auth()->user()->role) !== 'satpam' ? 7 : 6 }}" 
                        style="text-align: center; vertical-align: middle; padding: 100px 0; color: #6b7280; font-weight: 500; font-size: 16px;">
                        Data tidak ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PERBAIKAN: Pagination sekarang akan otomatis menggunakan simple mode --}}
    <div style="margin-top: 20px;">
        {{ $dataSiaga->appends(request()->query())->links() }}
    </div>

    @if(!in_array(strtolower(auth()->user()->role), ['satpam', 'harmet']))
    <div class="index-footer-form">
        <form action="{{ route('material-siaga-stand-by.export') }}" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label>Dari Tanggal:</label>
                <input type="date" name="start_date" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label>Sampai Tanggal:</label>
                <input type="date" name="end_date" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="export" value="pdf" class="btn btn-pdf"><i class="fas fa-file-pdf"></i> Unduh PDF</button>
            <button type="submit" name="export" value="excel" class="btn btn-excel"><i class="fas fa-file-excel"></i> Unduh Excel</button>
        </form>
    </div>
    @endif
</div>

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
    function closeModal() { document.getElementById("imageModal").style.display = "none"; }
    window.onclick = function(event) {
        var modal = document.getElementById("imageModal");
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

@endsection