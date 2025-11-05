@extends('layouts.app')
@section('title', 'Edit Barang Stand By')

@section('content')
<div class="card">
    <div class="card-header"><h2>Edit Barang Stand By</h2></div>
    <div class="card-body">
        <form action="{{ route('barang-stand-by.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="kode_barang">Kode Barang</label>
                <input type="text" name="kode_barang" id="kode_barang" class="form-control" value="{{ $item->kode_barang }}" required>
            </div>
            <div class="form-group">
                <label for="nama_barang">Nama/Tipe Barang</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" value="{{ $item->nama_barang }}" required>
            </div>
            <div class="form-group">
                <label for="jumlah">Jumlah/Unit</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ $item->jumlah }}" required>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $item->tanggal }}" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control" value="{{ $item->status }}" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('barang-stand-by.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection