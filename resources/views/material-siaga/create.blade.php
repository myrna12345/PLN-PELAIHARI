@extends('layouts.app')

@section('title', 'Tambah Material Siaga Standby')

@section('content')

<style>
    /* CSS UNTUK MENYAMAKAN TAMPILAN: JUDUL DI LUAR, FORM DI DALAM KOTAK */
    .form-main-wrapper {
        max-width: 900px;
        margin: 10px 0 50px 0;
        padding: 0; /* Penting: agar tidak ada layer putih di luar judul */
    }

    .form-title-outside {
        font-weight: 800;
        color: #333;
        font-size: 1.8rem;
        margin-bottom: 30px;
        /* Menggunakan font yang sama dengan dashboard Anda */
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Hanya bagian ini yang berwarna putih (Form Box) */
    .form-content-box {
        background-color: #fff;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Shadow halus sesuai teman Anda */
    }

    .form-group-custom {
        margin-bottom: 25px;
    }

    .form-group-custom label {
        display: block;
        font-weight: 700;
        margin-bottom: 12px;
        color: #444;
        font-size: 0.95rem;
    }

    /* Style input yang memanjang dan bersih */
    .input-style-clean {
        width: 100%;
        padding: 14px 18px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .input-style-clean:focus {
        border-color: #9BC3AE;
        outline: none;
        box-shadow: 0 0 0 4px rgba(155, 195, 174, 0.1);
    }

    .readonly-style {
        background-color: #f1f5f9 !important;
        color: #64748b;
        cursor: not-allowed;
    }

    .btn-submit-green {
        padding: 14px 50px;
        background: #9BC3AE; /* Hijau pastel teman Anda */
        border: none;
        color: #1f2d27;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 800;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-submit-green:hover {
        background: #86B29B;
    }

    .italic-error {
        color: #ef4444;
        font-size: 0.8rem;
        font-style: italic;
        margin-top: 8px;
        display: block;
        font-weight: 600;
    }

    .helper-text {
        font-size: 0.8rem;
        color: #94a3b8;
        margin-top: 8px;
        display: block;
    }
</style>

<div class="form-main-wrapper">
    <div class="form-title-outside">
        Tambah Material Siaga Standby
    </div>

    <div class="form-content-box">
        <form action="{{ route('material-siaga.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group-custom">
                <label>Nama Material</label>
                <select name="nama_material" class="input-style-clean" required>
                    <option value="" disabled selected>Pilih material</option>
                    @foreach ($materials as $mat)
                        <option value="{{ $mat->nama_material }}">{{ $mat->nama_material }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group-custom">
                <label>Nomor Meter</label>
                <input type="text" name="nomor_meter" class="input-style-clean" placeholder="Masukkan Nomor Meter" required>
                <span class="helper-text">Pastikan nomor meter yang dimasukkan sesuai dengan data.</span>
            </div>

            <div class="form-group-custom">
                <label>Stand Meter</label>
                <input type="text" name="stand_meter" class="input-style-clean" placeholder="Masukkan Stand Meter" required>
            </div>

            <div class="form-group-custom">
                <label>Tanggal dan Jam</label>
                <input type="text" name="tanggal" class="input-style-clean readonly-style" 
                    value="{{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i') }}" readonly>
                <span class="helper-text">Waktu akan otomatis terisi saat disimpan.</span>
            </div>

            <div class="form-group-custom">
                <label>Unggah Foto</label>
                <input type="file" name="unggah_foto" class="input-style-clean" required>
                <small class="italic-error">* Unggah foto material adalah wajib.</small>
            </div>

            <div style="margin-top: 40px; text-align: left;">
                <button type="submit" class="btn-submit-green">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection