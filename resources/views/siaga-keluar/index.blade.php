@extends('layouts.app')

@section('title', 'Laporan Siaga Keluar')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>LAPORAN MATERIAL SIAGA KELUAR</h2>
        
        <form action="{{ route('siaga-keluar.index') }}" method="GET" class="search-form">
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
            <a href="{{ route('siaga-keluar.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material & Unit</th> {{-- ⬅️ Kolom Nama Material diganti namanya --}}
                    {{-- ❌ KOLOM NOMOR UNIT DIHAPUS DARI HEADER ❌ --}}
                    <th>Nama Petugas</th>
                    <th>Stand Meter</th>
                    <th>Jumlah Siaga Keluar</th>
                    <th>Jumlah Siaga Kembali</th>
                    <th>Status</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataSiagaKeluar as $item)
                    <tr>
                        <td>{{ $dataSiagaKeluar->firstItem() + $loop->index }}</td>
                        
                        {{-- 🟢 PERBAIKAN: Menggabungkan Nama Material dan Nomor Unit 🟢 --}}
                        <td>
                            {{ $item->material->nama_material ?? 'N/A' }} 
                            @if ($item->nomor_unit)
                                - {{ $item->nomor_unit }}
                            @endif
                        </td>
                        {{-- ❌ CELL NOMOR UNIT DIHAPUS DARI BODY ❌ --}}
                        
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->stand_meter ?? '-' }}</td>
                        
                        <td>{{ $item->jumlah_siaga_keluar }}</td>
                        
                        <td>{{ $item->jumlah_siaga_masuk ?? 0 }}</td>

                        <td>{{ $item->status }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                        
                        <td style="text-align: center; vertical-align: top;"> 
                            @if($item->foto_path)
                                <img src="{{ route('siaga-keluar.show-foto', $item->id) }}" 
                                        alt="Foto Siaga Keluar" 
                                        class="table-foto" 
                                        style="max-width: 80px; height: auto; object-fit: cover; display: block; margin: 0 auto 5px; cursor: pointer;" 
                                        title="Klik untuk memperbesar">
                                
                                <a href="{{ route('siaga-keluar.download-foto', $item->id) }}" class="btn-foto-download" title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('siaga-keluar.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('siaga-keluar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- ⚠️ PERBAIKAN: Colspan dihitung ulang. (10 Kolom tersisa) ⚠️ --}}
                    <tr>
                        <td colspan="10" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $dataSiagaKeluar->appends(request()->query())->links() }}
    </div>

    <div class="index-footer-form">
        <form action="{{ route('siaga-keluar.download-report') }}" method="GET" class="form-download" target="_blank">
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