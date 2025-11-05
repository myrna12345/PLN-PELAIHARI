@extends('layouts.app')
@section('title', 'Tambah Barang Stand By')

@section('content')
<div class="card">
    <div class="card-header"><h2>Tambah Barang Stand By</h2></div>
    <div class="card-body">
        <form action="{{ route('barang-stand-by.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="kode_barang">Kode Barang</label>
                <input type="text" name="kode_barang" id="kode_barang" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nama_barang">Nama/Tipe Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="jumlah">Jumlah/Unit</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('barang-stand-by.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection