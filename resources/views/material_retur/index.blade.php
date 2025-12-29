@extends('layouts.app')

@section('content')
<div class="card-new">
    <div class="index-header">
        <h2>LAPORAN MATERIAL RETUR</h2>
        <form action="{{ route('material-retur.index') }}" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material/Petugas" value="{{ request('search') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="{{ request('tanggal_mulai') }}">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="{{ request('tanggal_akhir') }}">
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
                    <th>Keterangan</th> {{-- Kolom Baru Ditambahkan --}}
                    <th>Tanggal (WITA)</th>
                    <th>Foto Material</th>
                    <th>Foto Petugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $items->firstItem() + $loop->index }}</td>
                        <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                        <td>{{ $item->status }}</td>
                        <td>{{ $item->keterangan }}</td> {{-- Data Keterangan Ditampilkan Disini --}}
                        <td>{{ $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                        <td style="text-align: center;"> 
                            @if($item->foto_path)
                                <img src="{{ route('material-retur.show-foto', $item->id) }}" class="table-foto" style="max-width: 80px; display: block; margin: 0 auto 5px;">
                                <a href="{{ route('material-retur.download-foto', $item->id) }}" class="btn-foto-download"><i class="fas fa-download"></i> Download</a>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($item->foto_petugas)
                                <img src="{{ asset('storage/' . $item->foto_petugas) }}" class="table-foto" style="max-width: 80px; display: block; margin: 0 auto 5px;">
                                <a href="{{ route('material-retur.download-foto-petugas', $item->id) }}" class="btn-foto-download"><i class="fas fa-download"></i> Download</a>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('material-retur.edit', $item->id) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('material-retur.destroy', $item->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Colspan diubah jadi 10 karena kolom bertambah 1 --}}
                    <tr><td colspan="10" style="text-align:center;">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="index-footer-form">
        <form action="{{ route('material-retur.download-report') }}" method="GET" class="form-download">
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