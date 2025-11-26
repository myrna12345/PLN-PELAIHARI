@extends('layouts.app')

@section('title', 'Tambah Material Siaga Stand By')

@section('content')
<style>
/* page background */
body {
    background: #E7E8EA !important;
}

/* title ke kiri */
.page-title {
    text-align: left;
    margin: 28px 0 28px 20px;
    font-weight: 700;
    font-size: 20px;
}

/* main card: pindah ke kiri */
.form-wrapper {
    width: 750px;
    margin-left: 20px; /* pindah ke kiri */
    margin-bottom: 50px;
    background: #FFFFFF;
    border-radius: 16px;
    padding: 34px 42px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}

/* label */
.form-label {
    display:block;
    font-weight:700;
    margin-bottom: 10px;
    color: #111;
    font-size: 15px;
}

/* input dan select */
.form-control,
.custom-select {
    display: block !important;
    width: 100% !important;
    max-width: 100%;
    height: 52px !important;
    padding: 12px 16px !important;
    border-radius: 14px !important;
    border: 1.6px solid #222 !important;
    background: #fff !important;
    font-size: 15px !important;
    box-sizing: border-box !important;
}

/* select custom arrow */
.custom-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'><path fill='%23222' d='M7 10l5 5 5-5z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 16px;
    padding-right: 44px !important;
}

/* file input */
.input-file {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    padding: 10px 14px !important;
    border-radius: 14px !important;
    border: 1.6px solid #222 !important;
    background: #fff !important;
    display: flex !important;
    align-items: center;
    min-height: 52px !important;
}

/* spacing antar field */
.field {
    margin-bottom: 18px;
}

/* tombol simpan */
.btn-submit {
    background: #138A36;
    color: #fff;
    border-radius: 12px;
    padding: 12px 28px;
    font-weight: 700;
    border: none;
    margin-top: 20px;
    box-shadow: 0 6px 14px rgba(19,138,54,0.18);
    display: block;
    margin-left: auto;
}

@media (max-width: 900px) {
    .form-wrapper {
        width: 92%;
        padding: 20px;
        margin-left: 10px;
    }
}
</style>

<div class="page-title">Tambah Material Siaga Stand By</div>

<div class="form-wrapper">
    <form action="{{ route('material-siaga.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="field">
            <label class="form-label">Nama Material</label>
            <select id="nama_material" class="custom-select" required>
                <option value="">Pilih Material</option>
                <option value="Kwh Siaga 1P">Kwh Siaga 1P</option>
                <option value="Kwh Siaga 3P">Kwh Siaga 3P</option>
            </select>
            <input type="hidden" name="nama_material" id="nama_material_real">
        </div>

        <div class="field" id="dropdown1p" style="display: none;">
            <select id="unit1p" class="custom-select">
                <option value="">Pilih Nomor</option>
                @for($i=1; $i<=50; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="field" id="dropdown3p" style="display: none;">
            <select id="unit3p" class="custom-select">
                <option value="">Pilih Nomor</option>
                @for($i=1; $i<=10; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="field">
            <label class="form-label">Nama Petugas</label>
            <input type="text" name="nama_petugas" class="form-control" placeholder="Masukkan nama petugas" required>
        </div>

        <div class="field">
            <label class="form-label">Stand Meter</label>
            <input type="number" name="stand_meter" class="form-control" placeholder="Masukkan stand meter" required>
        </div>

        <div class="field">
            <label class="form-label">Jumlah Siaga Standby</label>
            <input type="number" name="jumlah_siaga_standby" class="form-control" placeholder="Masukkan jumlah" required>
        </div>

        <div class="field">
            <label class="form-label">Tanggal</label>
            <input type="text" name="tanggal" class="form-control" readonly required>
        </div>

        <div class="field">
            <label class="form-label">Unggah Foto</label>
            <div class="input-file">
                <input type="file" name="unggah_foto" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn-submit">Simpan</button>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const material = document.getElementById("nama_material");
    const dropdown1p = document.getElementById("dropdown1p");
    const dropdown3p = document.getElementById("dropdown3p");
    const unit1p = document.getElementById("unit1p");
    const unit3p = document.getElementById("unit3p");
    const realInput = document.getElementById("nama_material_real");

    function updateValue() {
        let value = material.value;
        if (value === "Kwh Siaga 1P" && unit1p.value) value += " - " + unit1p.value;
        if (value === "Kwh Siaga 3P" && unit3p.value) value += " - " + unit3p.value;
        realInput.value = value;
    }

    material.addEventListener("change", function() {
        dropdown1p.style.display = material.value === "Kwh Siaga 1P" ? "block" : "none";
        dropdown3p.style.display = material.value === "Kwh Siaga 3P" ? "block" : "none";
        unit1p.value = "";
        unit3p.value = "";
        updateValue();
    });

    unit1p.addEventListener("change", updateValue);
    unit3p.addEventListener("change", updateValue);

    // ============================
    // TANGGAL OTOMATIS (FORMAT VALID LARAVEL)
    // ============================
    const tanggalInput = document.querySelector('input[name="tanggal"]');

    function pad(num) { return num.toString().padStart(2, '0'); }

    const now = new Date();
    const year = now.getFullYear();
    const month = pad(now.getMonth() + 1);
    const day = pad(now.getDate());
    const hours = pad(now.getHours());
    const minutes = pad(now.getMinutes());

    // Format wajib Laravel: YYYY-MM-DD HH:MM
    tanggalInput.value = `${year}-${month}-${day} ${hours}:${minutes}`;
});
</script>

    </form>
</div>
@endsection
