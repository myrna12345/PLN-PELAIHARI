@extends('layouts.app')

@section('title', 'Material Kembali - SIMAS-PLN')

@section('content')
<div class="card-new">

    <div class="index-header">
        <h2>LAPORAN MATERIAL KEMBALI</h2>

        <form action="{{ route('material_kembali.index') }}" method="GET" class="search-form">
            
            {{-- Bagian Pencarian Teks --}}
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="{{ request('search') }}">
            </div>

            {{-- 🟢 PENAMBAHAN: Input Tanggal Mulai dan Akhir untuk Filter 🟢 --}}
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
            <a href="{{ route('material_kembali.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    
    {{-- Tambahkan error alert untuk laporan --}}
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
                    <th>Stok</th> {{-- 🟢 TAMBAH KOLOM STOK 🟢 --}}
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialKembali as $item)
                    <tr>
                        <td>{{ $materialKembali->firstItem() + $loop->index }}</td>
                        
                        {{-- Mengambil nama material melalui relasi 'material' --}}
                        <td>{{ $item->material->nama_material ?? 'Material Dihapus' }}</td>
                        
                        <td>{{ $item->nama_petugas }}</td>
                        
                        {{-- Menggabungkan jumlah dan satuan --}}
                        <td>{{ $item->jumlah_material }} {{ $item->satuan }}</td> 
                        
                        {{-- 🟢 TAMPILKAN DATA STOK 🟢 --}}
                        <td>{{ $item->stok_saat_ini }}</td>


                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>

                        <td style="text-align: center; vertical-align: top;">
                            @if($item->foto)
                                {{-- Menggunakan route showFoto --}}
                                <img src="{{ route('material_kembali.show-foto', $item->id) }}" 
                                    alt="Foto Material" 
                                    class="table-foto zoomable"
                                    style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;"
                                    title="Klik untuk memperbesar">

                                {{-- Menggunakan route downloadFoto --}}
                                <a href="{{ route('material_kembali.download-foto', $item->id) }}" 
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
                                <a href="{{ route('material_kembali.edit', $item->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('material_kembali.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini akan mengurangi stok Stand By.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- Colspan 8 untuk 8 kolom --}}
                        <td colspan="8" style="text-align:center; color:#6c757d; padding:50px 0;">Data tidak ditemukan.</td>
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
            
            @error('tanggal_mulai')
                <div class="text-danger small mb-2" style="margin-left: 10px;">{{ $message }}</div>
            @enderror
            @error('tanggal_akhir')
                <div class="text-danger small mb-2" style="margin-left: 10px;">{{ $message }}</div>
            @enderror
            
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" value="{{ old('tanggal_mulai') }}" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" value="{{ old('tanggal_akhir') }}" required>
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

{{-- 🟢 PENAMBAHAN: Tambahkan CSS untuk date-filter-group agar layout pencarian berfungsi --}}
<style>
    .search-form {
        display: flex;
        align-items: center;
        gap: 15px; /* Jarak antar elemen */
    }
    .date-filter-group {
        display: flex;
        gap: 10px;
    }
    .date-filter-group input {
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 130px; /* Atur lebar agar tidak terlalu panjang */
    }
</style>
@endsection