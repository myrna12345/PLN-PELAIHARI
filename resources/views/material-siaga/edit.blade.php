@extends('layouts.app')

@section('title', 'Edit Material Siaga Stand By')

@section('content')

<style>
    body {
        background: #E7E8EA !important;
    }

    .page-title {
        text-align: left;
        margin: 25px 0 15px 20px;
        font-weight: 700;
        font-size: 22px;
    }

    .form-wrapper {
        width: 700px;
        margin: 0;
        margin-left: 20px;
        background: #fff;
        border-radius: 14px;
        padding: 30px 40px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    label {
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
    }

    input[type="text"],
    input[type="datetime-local"],
    select {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        margin-bottom: 18px;
    }

    button {
        padding: 12px 32px;
        background: #9BC3AE;
        border: none;
        color: #1f2d27;
        border-radius: 14px;
        cursor: pointer;
        font-weight: 700;
        font-size: 16px;
    }

    button:hover {
        background: #86B29B;
    }

    .image-preview {
        margin: 10px 0 20px 0;
    }

    .image-preview img {
        width: 150px; /* Ukuran sedikit diperbesar agar lebih jelas */
        height: auto;
        border-radius: 8px;
        border: 1px solid #ddd;
        display: block;
        margin-bottom: 5px;
    }

    .text-muted {
        font-size: 12px;
        color: #666;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }
</style>

<h2 class="page-title">EDIT MATERIAL SIAGA STAND BY</h2>

<div class="form-wrapper">

    <form action="{{ route('material-siaga.update', $material->id) }}"
          method="POST" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- Error Validasi --}}
        @if ($errors->any())
            <div class="alert alert-danger"
                 style="color:red; margin-bottom:20px; border:1px solid red; padding:10px; border-radius:5px;">
                <ul style="margin:0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <label>Nama Material</label>
        <input type="text" name="nama_material"
               value="{{ old('nama_material', $material->nama_material) }}" required>

        <label>No. Meter</label>
        <input type="text" name="nomor_meter"
               value="{{ old('nomor_meter', $material->nomor_meter) }}" required>

        <label>Stand Meter</label>
        <input type="text" name="stand_meter"
               value="{{ old('stand_meter', $material->stand_meter) }}" required>

        <label>Tanggal</label>
        <input type="datetime-local" name="tanggal"
               value="{{ \Carbon\Carbon::parse($material->tanggal)->format('Y-m-d\TH:i') }}"
               readonly>

        <label>Status</label>
        <select name="status" required>
            <option value="Ready" {{ old('status', $material->status) == 'Ready' ? 'selected' : '' }}>Ready</option>
            <option value="Terpakai" {{ old('status', $material->status) == 'Terpakai' ? 'selected' : '' }}>Terpakai</option>
        </select>

        <label>Foto Saat Ini</label>
        <div class="image-preview">
            @if($material->unggah_foto)
                {{-- Menggunakan Storage::url untuk keamanan path --}}
                <img src="{{ Storage::url($material->unggah_foto) }}" alt="Foto Lama">
                <span class="text-muted">File: {{ basename($material->unggah_foto) }}</span>
            @else
                <p class="text-muted" style="font-style: italic;">Belum ada foto yang diunggah.</p>
            @endif
        </div>

        <label>Ganti Foto</label>
        <input type="file" name="unggah_foto" accept="image/*">
        

        <div class="form-actions">
            <button type="submit">Update</button>
        </div>

    </form>

</div>

@endsection