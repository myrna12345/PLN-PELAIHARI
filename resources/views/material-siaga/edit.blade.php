@extends('layouts.app')

@section('title', 'Edit Material Siaga Stand By')

@section('content')
<style>
body { background: #E7E8EA !important; }
.page-title { text-align:center; margin:28px 0; font-weight:700; font-size:20px; }
.form-wrapper {
    width:820px; margin:0 auto 50px; background:#FFFFFF; border-radius:16px;
    padding:34px 42px; box-shadow:0 6px 20px rgba(0,0,0,0.06);
}
.form-label { display:block; font-weight:700; margin-bottom:10px; color:#111; font-size:15px; }
.form-control, .custom-select, .input-file {
    display:block !important; width:100% !important; height:52px !important;
    padding:12px 18px !important; border-radius:14px !important;
    border:1.6px solid #222 !important; background:#fff !important;
    font-size:15px !important; box-sizing:border-box !important;
}
.custom-select {
    appearance:none;
    background-image:url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'><path fill='%23222' d='M7 10l5 5 5-5z'/></svg>");
    background-repeat:no-repeat; background-position:right 16px center; background-size:16px;
    padding-right:44px !important;
}
.input-file { padding:6px 14px; height:auto !important; min-height:52px !important; }
.field { margin-bottom:18px; }

/* Tombol */
.btn-area {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    margin-top: 26px;
}
.btn-submit {
    background:#138A36;
    color:#fff;
    border-radius:12px;
    padding:12px 32px;
    font-weight:700;
    border:none;
    box-shadow:0 6px 14px rgba(19,138,54,0.18);
}

@media(max-width:900px){
    .form-wrapper{ width:92%; padding:20px; }
}
</style>

<div class="page-title">Edit Material Siaga Stand By</div>

<div class="form-wrapper">
    <form action="{{ route('material-siaga.update', $data->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="field">
            <label class="form-label">Nama Material</label>
            <select id="nama_material" class="custom-select" required>
                <option value="">Pilih Material</option>
                <option value="Kwh Siaga 1P" {{ str_contains($data->nama_material, 'Kwh Siaga 1P') ? 'selected' : '' }}>Kwh Siaga 1P</option>
                <option value="Kwh Siaga 3P" {{ str_contains($data->nama_material, 'Kwh Siaga 3P') ? 'selected' : '' }}>Kwh Siaga 3P</option>
            </select>
            <input type="hidden" name="nama_material" id="nama_material_real" value="{{ $data->nama_material }}">
        </div>

        @php
            $unit = preg_replace('/[^0-9]/', '', $data->nama_material);
        @endphp

        <div class="field" id="dropdown1p" style="{{ str_contains($data->nama_material, '1P') ? '' : 'display:none;' }}">
            <select id="unit1p" class="custom-select">
                <option value="">Pilih Nomor</option>
                @for($i=1; $i<=50; $i++)
                    <option value="{{ $i }}" {{ $unit == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="field" id="dropdown3p" style="{{ str_contains($data->nama_material, '3P') ? '' : 'display:none;' }}">
            <select id="unit3p" class="custom-select">
                <option value="">Pilih Nomor</option>
                @for($i=1; $i<=10; $i++)
                    <option value="{{ $i }}" {{ $unit == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="field">
            <label class="form-label">Nama Petugas</label>
            <input type="text" name="nama_petugas" class="form-control" value="{{ $data->nama_petugas }}" required>
        </div>

        <div class="field">
            <label class="form-label">Stand Meter</label>
            <input type="number" name="stand_meter" class="form-control" value="{{ $data->stand_meter }}" required>
        </div>

        <div class="field">
            <label class="form-label">Jumlah Siaga Standby</label>
            <input type="number" name="jumlah_siaga_standby" class="form-control" value="{{ $data->jumlah_siaga_standby }}" required>
        </div>

        {{-- === TANGGAL TANPA KALENDAR (FORMAT CREATE) === --}}
        <div class="field">
            <label class="form-label">Tanggal</label>
            <input 
                type="text" 
                name="tanggal" 
                class="form-control"
                value="{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d H:i') }}"
                required>
        </div>

        <div class="field">
            <label class="form-label">Status</label>
            <select name="status" class="custom-select" required>
                <option value="Ready" {{ $data->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                <option value="Terpakai" {{ $data->status == 'Terpakai' ? 'selected' : '' }}>Terpakai</option>
            </select>
        </div>

        <div class="field">
            <label class="form-label">Foto Saat Ini</label>
            @if($data->foto)
                {{-- 🟢 KODE PERBAIKAN: Menggunakan route showFoto 🟢 --}}
                <img src="{{ route('material-siaga-stand-by.show-foto', $data->id) }}" 
                     width="160" 
                     style="border-radius:10px;border:1.6px solid #222;margin-bottom:14px;">
                {{-- Tambahkan link download jika perlu (saat ini dihilangkan mengikuti format Siaga Kembali Edit) --}}
            @endif

            <label class="form-label">Unggah Foto Baru</label>
            <div class="input-file">
                <input type="file" name="unggah_foto" accept="image/*">
            </div>
        </div>

        <div class="btn-area">
            <button type="submit" class="btn-submit">Update</button>
        </div>

    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const material = document.getElementById("nama_material");
    const d1 = document.getElementById("dropdown1p");
    const d3 = document.getElementById("dropdown3p");
    const u1 = document.getElementById("unit1p");
    const u3 = document.getElementById("unit3p");
    const real = document.getElementById("nama_material_real");

    function update() {
        let v = material.value;
        if (v === "Kwh Siaga 1P" && u1.value) v += " - " + u1.value;
        if (v === "Kwh Siaga 3P" && u3.value) v += " - " + u3.value;
        real.value = v;
    }

    material.addEventListener("change", function(){
        d1.style.display = material.value === "Kwh Siaga 1P" ? "block" : "none";
        d3.style.display = material.value === "Kwh Siaga 3P" ? "block" : "none";
        u1.value = ""; 
        u3.value = "";
        update();
    });

    u1.addEventListener("change", update);
    u3.addEventListener("change", update);
    
    // Initial update on load to ensure current value is correctly formatted if needed
    update();
});
</script>

@endsection