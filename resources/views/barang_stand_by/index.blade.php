@extends('layouts.app')
@section('title', 'Laporan Barang Stand By')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Laporan Barang Stand By</h2>
        <a href="{{ route('barang-stand-by.create') }}" class="btn btn-primary">Tambah Data</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th><th>Kode Barang</th><th>Nama Barang</th><th>Jumlah</th>
                    <th>Tanggal</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td><td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->nama_barang }}</td><td>{{ $item->jumlah }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                        <td>{{ $item->status }}</td>
                        <td>
                            <a href="{{ route('barang-stand-by.edit', $item->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('barang-stand-by.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 20px;">{{ $items->links() }}</div>
    </div>
</div>
@endsection