@extends('layouts.app')

@section('title', 'Laporan Siaga Kembali')

@section('content')

<div class="card-new">

<div class="index-header">
    <h2>LAPORAN SIAGA KEMBALI</h2>
    
    <form action="{{ route('siaga-kembali.index') }}" method="GET" class="search-form">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            {{-- PERBAIKAN: Mengganti "Nomor Unit" di placeholder menjadi "Nomor Meter" --}}
            <input type="text" name="search" placeholder="Cari Material/Petugas/Nomor Meter..." value="{{ request('search') }}">
        </div>
        <div class="form-group-tanggal-filter">
            <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}" title="Tanggal Mulai">
        </div>
        <div class="form-group-tanggal-filter">
            <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}" title="Tanggal Akhir">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <a href="{{ route('siaga-kembali.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Material & Nomor Meter</th>
                <th>Nama Petugas</th>
                <th>Stand Meter</th>
                {{-- HAPUS KOLOM JUMLAH SIAGA KELUAR --}}
                {{-- <th>Jumlah Siaga Keluar</th> --}}
                {{-- HAPUS KOLOM JUMLAH SIAGA KEMBALI --}}
                {{-- <th>Jumlah Siaga Kembali</th> --}}
                <th>Status</th>
                <th>Tanggal (WITA)</th>
                <th>Foto & Download</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>
                    
                    <td>
                        {{ $item->material->nama_material ?? 'N/A' }} 
                        {{-- PERBAIKAN: Mengganti $item->nomor_unit menjadi $item->nomor_meter --}}
                        @if ($item->nomor_meter)
                            - {{ $item->nomor_meter }} 
                        @endif
                    </td>
                    
                    <td>{{ $item->nama_petugas }}</td>
                    <td>{{ $item->stand_meter ?? '-' }}</td>
                    
                    {{-- HAPUS DATA JUMLAH SIAGA KELUAR --}}
                    {{-- <td>{{ $item->jumlah_siaga_keluar }}</td> --}}
                    
                    {{-- HAPUS DATA JUMLAH SIAGA KEMBALI --}}
                    {{-- <td>{{ $item->jumlah_siaga_kembali }}</td> --}}
                    
                    <td>{{ $item->status ?? 'Kembali' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                    
                    <td style="text-align: center; vertical-align: top;"> 
                        @if($item->foto_path)
                            <img src="{{ route('siaga-kembali.show-foto', $item->id) }}" 
                                            alt="Foto Siaga Kembali" 
                                            class="table-foto" 
                                            style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;" 
                                            title="Klik untuk memperbesar">

                            <a href="{{ route('siaga-kembali.download-foto', $item->id) }}" class="btn-foto-download" title="Download Foto">
                                <i class="fas fa-download"></i> Download Foto
                            </a>
                        @else
                            <span>-</span>
                        {{-- PERBAIKAN: Menghapus tag penutup button yang tidak perlu --}}
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('siaga-kembali.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('siaga-kembali.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-hapus">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                {{-- COLSPAN dihitung ulang menjadi 8 --}}
                <tr><td colspan="8" style="text-align:center;">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">{{ $items->appends(request()->query())->links() }}</div>

<div class="index-footer-form">
    <form action="{{ route('siaga-kembali.download-report') }}" method="GET" class="form-download" target="_blank">
        <div class="form-group-tanggal">
            <label>Dari Tanggal:</label>
            <input type="date" name="tanggal_mulai" class="form-control-tanggal" required>
        </div>
        <div class="form-group-tanggal">
            <label>Sampai Tanggal:</label>
            <input type="date" name="tanggal_akhir" class="form-control-tanggal" required>
        </div>
        <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf"><i class="fas fa-file-pdf"></i> Unduh Pdf</button>
        <button type="submit" name="submit_excel" value="1" class="btn btn-excel"><i class="fas fa-file-excel"></i> Unduh Excel</button>
    </form>
</div>


</div>
@endsection