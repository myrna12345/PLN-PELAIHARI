@extends('layouts.app')

@section('content')
<div class="card-new">
    <div class="index-header">
        <h2>LAPORAN MATERIAL STAND BY</h2>
        <form action="{{ route('material-stand-by.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material" value="{{ request('search') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-stand-by.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Jumlah</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    {{-- Kolom Foto Petugas DIHAPUS --}}
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $items->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                        <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                        <td>{{ $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                        
                        {{-- Foto Material --}}
                        <td style="text-align: center;"> 
                            @if($item->foto_path)
                                <img src="{{ asset('uploads/material_stand_by/' . $item->foto_path) }}" 
                                     class="table-foto" 
                                     style="max-width: 80px; display: block; margin: 0 auto 5px;">
                                <a href="{{ route('material-stand-by.download-foto', $item->id) }}" class="btn-foto-download"><i class="fas fa-download"></i> Download</a>
                            @else
                                -
                            @endif
                        </td>

                        {{-- Data Foto Petugas DIHAPUS --}}

                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material-stand-by.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('material-stand-by.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="index-footer-form">
        <form action="{{ route('material-stand-by.downloadReport') }}" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label>Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>
</div>
@endsection