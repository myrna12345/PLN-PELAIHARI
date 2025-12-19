@extends('layouts.app')

@section('title', 'Laporan Material Stand By')

@section('content')

<div class="card-new">

<div class="index-header">
    <h2>LAPORAN MATERIAL STAND BY</h2>
    
    <form action="{{ route('material-stand-by.index') }}" method="GET" class="search-form">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            {{-- Pencarian hanya berdasarkan Nama Material dan Satuan --}}
            <input type="text" name="search" placeholder="Cari Nama Material..." value="{{ request('search') }}">
        </div>
        <div class="form-group-tanggal-filter">
            <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}" title="Tanggal Mulai">
        </div>
        <div class="form-group-tanggal-filter">
            <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}" title="Tanggal Akhir">
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
                <th>Jumlah & Satuan</th>
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
                    
                    {{-- Menggabungkan Jumlah dan Satuan --}}
                    <td>{{ $item->jumlah }} {{ $item->satuan ?? '' }}</td> 

                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                    
                    <td style="text-align: center; vertical-align: top;"> 
                        @if($item->foto_path)
                            <img src="{{ route('material-stand-by.show-foto', $item->id) }}" 
                                    alt="Foto Material" 
                                    class="table-foto" 
                                    style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;" 
                                    title="Klik untuk memperbesar">
                            
                            <a href="{{ route('material-stand-by.download-foto', $item->id) }}" class="btn-foto-download" title="Download Foto">
                                <i class="fas fa-download"></i> Download Foto
                            </a>
                        @else
                            <span>-</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('material-stand-by.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('material-stand-by.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                        
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-hapus">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                {{-- Total 6 kolom --}}
                <tr>
                    <td colspan="6" style="text-align:center;">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    {{ $items->appends(request()->query())->links() }}
</div>

<div class="index-footer-form">
    <form action="{{ route('material-stand-by.download-report') }}" method="GET" class="form-download" target="_blank">
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