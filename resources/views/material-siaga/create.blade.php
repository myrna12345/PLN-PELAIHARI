@extends('layouts.app')

@section('title', 'Tambah Material Siaga Stand By')

@section('content')

<style>
    /* Style untuk kontainer utama form */
    .form-card {
        width: 700px; /* Lebar diperbesar menjadi 700px */
        margin: 0 0 50px 0; 
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    
    /* Style untuk judul */
    h2 {
        text-align: left; 
        margin-top: 15px; 
        margin-bottom: 30px; 
        font-weight: 700;
        color: #333;
        width: 700px; /* Lebar disamakan dengan form-card */
        margin-left: 0; 
        font-size: 1.5rem; 
    }

    /* Style untuk setiap grup form */
    .form-group {
        margin-bottom: 20px;
    }

    /* Memastikan label terlihat rapi dan memiliki ukuran standar */
    label {
        display: block;
        margin-bottom: .5rem;
        font-size: 1rem; 
        font-weight: 500;
    }

    /* Memastikan input, select, dan file terlihat rapi */
    .form-control, 
    .form-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        box-sizing: border-box; 
        font-size: 1rem; 
    }

    /* Style khusus untuk readonly field (Tanggal) */
    .form-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }

    /* Style untuk tombol Simpan */
    button {
    padding: 12px 32px;
    background: #9BC3AE;      /* hijau pastel seperti gambar */
    border: none;
    color: #1f2d27;           /* teks gelap */
    border-radius: 14px;
    cursor: pointer;
    font-weight: 700;
    font-size: 16px;
}

button:hover {
    background: #86B29B;      /* lebih gelap saat hover */
}

    /* Style untuk keterangan field (optional) */
    .form-text {
        font-size: 0.85em; 
        color: #6c757d;
        margin-top: 5px;
        display: block;
    }

    /* Wrapper tambahan (jika diperlukan) untuk menjaga form-card tetap di kiri */
    .form-wrapper {
        display: flex; 
        justify-content: flex-start; 
    }
</style>

{{-- Wrapper ditambahkan agar form-card menempel ke kiri --}}
<div class="form-wrapper">

    <div>
        {{-- JUDUL: Tambah Material Siaga Standby --}}
        <h2>TAMBAH MATERIAL SIAGA STANDBY</h2>

        <div class="form-card">

            <form action="{{ route('material-siaga.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- 1. Nama Material (Dropdown) --}}
                <div class="form-group">
                    <label for="nama_material">Nama Material</label>
                    <select name="nama_material" id="nama_material" class="form-select" required>
                        <option value="">-- Pilih Material --</option>
                        @foreach ($materials as $mat)
                            <option value="{{ $mat->nama_material }}">{{ $mat->nama_material }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- 2. Nomor Meter --}}
                <div class="form-group">
                    <label for="nomor_meter">Nomor Meter</label>
                    <input type="text" name="nomor_meter" id="nomor_meter" class="form-control" placeholder="Masukkan Nomor Meter" required>
                    <span class="form-text">Pastikan nomor meter yang dimasukkan sesuai dengan data.</span>
                </div>

                {{-- 3. Stand Meter --}}
                <div class="form-group">
                    <label for="stand_meter">Stand Meter</label>
                    <input type="text" name="stand_meter" id="stand_meter" class="form-control" placeholder="Masukkan Stand Meter" required>
                </div>

                {{-- 4. Tanggal & Jam (Readonly) --}}
                <div class="form-group">
                    <label for="tanggal">Tanggal dan Jam</label>
                    <input type="text" name="tanggal" id="tanggal" class="form-control" value="{{ now()->format('d M Y, H:i') }}" readonly>
                    <span class="form-text">Waktu akan otomatis terisi saat disimpan.</span>
                </div>

                {{-- 5. Status (Default Ready) --}}
                <div class="form-group" style="display: none;">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Ready" selected>Ready</option>
                    </select>
                </div>

                {{-- 6. Unggah Foto --}}
                <div class="form-group">
                    <label for="unggah_foto">Unggah Foto</label>
                    <input type="file" name="unggah_foto" id="unggah_foto" class="form-control" required>
                    <span class="form-text text-danger">*Unggah foto material adalah wajib.</span>
                </div>

                {{-- Tombol Simpan --}}
                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="btn-submit">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection