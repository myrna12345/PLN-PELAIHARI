@extends('layouts.app')

@section('title', 'Edit Material Siaga - SIMAS-PLN')

@section('content')

<style>
/* 1. JUDUL - Pojok kiri luar card */
.page-title-container {
    width: 100%;
    margin: 20px 0 25px 0;
    text-align: left;
}

.page-title-container h2 {
    font-size: 24px; 
    font-weight: 600; 
    color: #333;
    text-transform: none; 
    margin: 0;
}

/* 2. LAYOUT CARD - Lurus Satu Kolom */
.card-edit-container {
    background: #ffffff;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    max-width: 850px; 
    margin-left: 0;
    margin-bottom: 40px;
    font-family: 'Segoe UI', sans-serif;
}

/* 3. FORM GROUP & LABEL */
.form-group-new {
    margin-bottom: 25px;
}

.form-group-new label {
    display: block;
    font-weight: 500; 
    font-size: 15px;
    color: #333;
    margin-bottom: 12px;
}

/* 4. INPUT FIELD */
.form-control-new {
    width: 100%;
    height: 50px !important; 
    border: 1px solid #d1d5db;
    border-radius: 10px !important; 
    padding: 0 15px;
    font-size: 15px;
    box-sizing: border-box;
    outline: none;
    background-color: #ffffff;
    color: #333;
    display: flex;
    align-items: center;
}

.form-control-new:focus {
    border-color: #5a8dee;
}

/* 5. PERBAIKAN INPUT FILE - Teks dinamis di tengah */
.file-input-wrapper {
    position: relative;
    width: 100%;
    height: 50px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #fff;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0; 
    cursor: pointer;
    z-index: 2;
}

.file-input-wrapper .file-custom-text {
    color: #666;
    font-size: 15px;
    font-weight: 400;
    z-index: 1;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 0 15px;
}

/* 6. TOMBOL AKSI - Pojok Kanan */
.form-actions {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end; 
}

.btn-simpan {
    background-color: #76b596 !important; 
    color: #333 !important;
    border: none;
    padding: 12px 35px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s;
}

.btn-batal {
    background-color: #6c757d !important; 
    color: white !important;
    padding: 12px 35px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    margin-left: 12px;
    transition: 0.2s;
}

.btn-simpan:hover, .btn-batal:hover {
    opacity: 0.85;
}

/* 7. PREVIEW FOTO */
.foto-preview-box {
    margin-top: 15px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    display: inline-block;
}

.foto-preview-box img {
    max-width: 250px;
    border-radius: 8px;
}

/* 8. PESAN ERROR VALIDASI */
.alert-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 25px;
    border: 1px solid #f5c6cb;
    font-size: 14px;
}
</style>

<div class="page-title-container">
    <h2>Edit Data Siaga Standby</h2>
</div>

<div class="card-edit-container">
    {{-- Menampilkan pesan error jika validasi gagal --}}
    @if ($errors->any())
        <div class="alert-error">
            <strong>Gagal Memperbarui:</strong>
            <ul style="margin-top: 5px; margin-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('material-siaga.update', $materialSiaga->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group-new">
            <label for="nama_material">Nama Material</label>
            <input type="text" name="nama_material" id="nama_material" class="form-control-new" 
                   value="{{ old('nama_material', $materialSiaga->nama_material) }}" required>
        </div>

        <div class="form-group-new">
            <label for="nomor_meter">Nomor Meter</label>
            <input type="text" name="nomor_meter" id="nomor_meter" class="form-control-new" 
                   value="{{ old('nomor_meter', $materialSiaga->nomor_meter) }}" required>
        </div>
        
        <div class="form-group-new">
            <label for="stand_meter">Stand Meter</label>
            <input type="text" name="stand_meter" id="stand_meter" class="form-control-new" 
                   value="{{ old('stand_meter', $materialSiaga->stand_meter) }}" required>
        </div>

        <div class="form-group-new">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control-new" required>
                {{-- Value disesuaikan dengan validasi di Controller (Case Sensitive) --}}
                <option value="Ready" {{ old('status', $materialSiaga->status) == 'Ready' ? 'selected' : '' }}>READY</option>
                <option value="Terpakai" {{ old('status', $materialSiaga->status) == 'Terpakai' ? 'selected' : '' }}>TERPAKAI</option>
            </select>
        </div>

        <div class="form-group-new">
            <label>Tanggal dan Jam (WITA)</label>
            <input type="text" class="form-control-new" 
                   style="background-color: #f3f4f6; cursor: not-allowed;"
                   value="{{ \Carbon\Carbon::parse($materialSiaga->tanggal)->format('d M Y, H:i') }}" readonly>
            
            <input type="hidden" name="tanggal" value="{{ $materialSiaga->tanggal }}">
        </div>

        <div class="form-group-new">
            <label>Foto Material Saat Ini</label>
            @if($materialSiaga->unggah_foto)
                <div class="foto-preview-box">
                    <img src="{{ route('material-siaga.show-foto', $materialSiaga->id) }}" alt="Foto Material">
                </div>
            @endif
            <div style="margin-top: 15px;">
                <label style="font-size: 13px; color: #666;">Unggah Foto Material Baru(Opsional)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="unggah_foto" id="unggah_foto" onchange="updateFileName(this)">
                    <div class="file-custom-text" id="file-name-text">No file chosen</div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-simpan">Update</button>
            <a href="{{ route('material-siaga.index') }}" class="btn-batal">Batal</a>
        </div>
    </form>
</div>

<script>
    function updateFileName(input) {
        const textDisplay = document.getElementById('file-name-text');
        if (input.files && input.files.length > 0) {
            textDisplay.innerText = input.files[0].name;
            textDisplay.style.color = "#333";
            textDisplay.style.fontWeight = "500";
        } else {
            textDisplay.innerText = "Klik untuk memilih foto baru...";
            textDisplay.style.color = "#666";
            textDisplay.style.fontWeight = "400";
        }
    }
</script>

@endsection