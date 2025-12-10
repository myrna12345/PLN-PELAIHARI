@extends('layouts.app')

@section('title', 'Material Keluar - SIMAS-PLN')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>LAPORAN MATERIAL KELUAR</h2>

        <form action="{{ route('material_keluar.index') }}" method="GET" class="search-form">
            
            {{-- Bagian Pencarian Teks --}}
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
            </div>
            
            {{-- 🟢 Input Tanggal Mulai dan Akhir untuk Filter 🟢 --}}
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
                    <th class="col-jumlah">Jumlah</th> {{-- Ditambah class untuk styling --}}
                    <th class="col-stok">Stok</th> {{-- Ditambah class untuk styling --}}
                    <th>Keterangan</th>                    
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialKeluar as $item)
                    <tr>
                        <td>{{ $materialKeluar->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? '-' }}</td> 
                        <td>{{ $item->nama_petugas }}</td>
                        
                        {{-- Data Jumlah (diberi style untuk mencegah wrap) --}}
                        <td class="text-nowrap">{{ $item->jumlah_material }} {{ $item->satuan_material }}</td>
                        
                        {{-- Data Stok (diberi style untuk mencegah wrap) --}}
                        <td class="text-nowrap">{{ $item->stok_saat_ini }}</td>
                        
                        <td class="col-keterangan">{{ $item->keterangan }}</td> {{-- Ditambah class untuk styling --}}
                        
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto)
                                <img src="{{ route('material_keluar.show-foto', $item->id) }}" 
                                    alt="Foto Material" 
                                    class="table-foto zoomable"
                                    style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;"
                                    title="Klik untuk memperbesar">

                                <a href="{{ route('material_keluar.download-foto', $item->id) }}" 
                                    class="btn-foto-download" 
                                    title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>

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
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6c757d; padding:50px 0;">Data tidak ditemukan.</td> {{-- Kolom disesuaikan menjadi 9 --}}
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $materialKeluar->appends(request()->query())->links() }}
    </div>

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
</div>

{{-- CSS Tambahan untuk tata letak tabel yang lebih rapi --}}
<style>
    /* CSS sebelumnya */
    .search-form {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .date-filter-group {
        display: flex;
        gap: 10px;
    }
    .date-filter-group input {
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 130px;
    }
    
    /* 🛠️ CSS BARU UNTUK MERAPIKAN KOLOM JUMLAH DAN STOK 🛠️ */
    
    /* Mencegah teks memecah baris pada kolom Jumlah dan Stok */
    .text-nowrap {
        white-space: nowrap; /* Mencegah pemisahan kata */
        text-align: center; /* Merapikan konten di tengah */
    }

    /* Memastikan Keterangan tetap bisa memecah baris jika panjang */
    .col-keterangan {
        max-width: 150px; /* Batasi lebar */
        word-wrap: break-word; /* Biarkan teks memecah baris jika terlalu panjang */
    }
    
    /* Mengatur lebar minimum untuk kolom tertentu agar data Jumlah/Stok tidak terlalu sempit */
    .table th.col-jumlah, 
    .table th.col-stok {
        min-width: 80px; 
    }
    .table th.col-no {
        width: 50px;
    }
</style>
@endsection