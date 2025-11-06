@extends('layouts.app')

@section('title', 'Saldo Material Stand By')

@section('content')
<div class="card-new">
    
    <div class="index-header">
        <h2>SALDO MATERIAL STAND BY</h2>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search">
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah/Unit</th>
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
                        
                        <td>{{ $item->tanggal->format('d M Y, H:i') }}</td>
                        
                        <td style="text-align: center; vertical-align: top;"> 
                            @if($item->foto_path)
                                <img src="{{ asset('storage/' . $item->foto_path) }}" alt="Foto Material" class="table-foto">
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
                    <tr>
                        <td colspan="7" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        {{ $items->links() }}
    </div>

    <div class="index-footer-form">
        <form action="{{ route('material-stand-by.download-pdf') }}" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label for="tanggal_mulai">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control-tanggal" required>
            </div>
            <button type="submit" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>
        </form>
        <a href="#" class="btn btn-excel"><i class="fas fa-file-excel"></i> Unduh Excel</a>
    </div>

</div>
@endsection