{{-- resources/views/material_retur/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Laporan Material Retur')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>LAPORAN MATERIAL RETUR</h2>
        
        <form action="{{ route('material-retur.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material/Petugas..." value="{{ request('search') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}" title="Tanggal Akhir">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="{{ route('material-retur.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah Retur</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $items->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah }}</td>
                        
                        {{-- 💡 PERBAIKAN: Menggunakan Accessor $item->status --}}
                        <td>
                            {{-- Accessor di Model MaterialRetur.php akan mengubah 'bekas_andal' menjadi 'Baik' --}}
                            @if($item->status == 'Baik')
                                <span style="color: #198754; font-weight: 500;">{{ $item->status }}</span>
                            @elseif($item->status == 'rusak' || $item->status == 'Rusak')
                                <span style="color: #d06368ff; font-weight: 500;">Rusak</span>
                            @else
                                <span>{{ $item->status }}</span>
                            @endif
                        </td>
                        
                        <td>{{ \Illuminate\Support\Str::limit($item->keterangan, 30) ?? '-' }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                        
                        <td style="text-align: center; vertical-align: top;"> 
                            @if($item->foto_path)
                                {{-- Menggunakan route show-foto --}}
                                <img src="{{ route('material-retur.show-foto', $item->id) }}" 
                                      alt="Foto Material" 
                                      class="table-foto" 
                                      style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;" 
                                      title="Klik untuk memperbesar">
                                
                                <a href="{{ route('material-retur.download-foto', $item->id) }}" class="btn-foto-download" title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material-retur.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('material-retur.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $items->appends(request()->query())->links() }}
    </div>

    <div class="index-footer-form">
        <form action="{{ route('material-retur.download-report') }}" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
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