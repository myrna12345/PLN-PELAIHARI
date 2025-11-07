<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material Keluar - SIMAS-PLN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            height: 100%;
        }
        .container { display: flex; height: 100vh; }

        /* === SIDEBAR === */
        .sidebar {
            width: 260px;
            background-color: #343a40;
            color: #f8f9fa;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #4f565d;
        }
        .sidebar-header .sidebar-logo {
            width: 75px;
            height: 75px;
            margin-right: 15px;
            border-radius: 5px;
            object-fit: contain;
        }
        .sidebar-header h1 {
            font-size: 1.3rem;
            margin: 0;
            font-weight: 600;
        }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li { margin-bottom: 15px; }
        .sidebar-menu a {
            color: #f8f9fa;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar-menu a.active, .sidebar-menu a:hover {
            background-color: #495057;
        }
        .sidebar-menu i {
            width: 30px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* === MAIN CONTENT === */
        .main-content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
        }
        .page-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }

        /* === FORM === */
        .form-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            outline: none;
        }
        input:focus, select:focus {
            border-color: #007bff;
        }

        /* === BUTTONS === */
        .btn-action {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-ok {
            background-color: #28a745;
        }
        .btn-ok:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #dc3545;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-cancel:hover {
            background-color: #c82333;
        }

        .current-photo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .current-photo img {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/logo-pln.png') }}" alt="Logo PLN" class="sidebar-logo">
            <h1>SIMAS-PLN</h1>
        </div>

        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-user-circle"></i> Profil</a></li>
            <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="{{ route('material-stand-by.index') }}"><i class="fas fa-box-open"></i> Material Stand By</a></li>
            <li><a href="#"><i class="fas fa-undo"></i> Material Retur</a></li>
            <li><a href="{{ route('material_keluar.index') }}" class="active"><i class="fas fa-tools"></i> Material Keluar</a></li>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Material Kembali</a></li>
            <li><a href="#"><i class="fas fa-satellite-dish"></i> Material Siaga Stand By</a></li>
            <li><a href="#"><i class="fas fa-history"></i> Siaga Kembali</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h2 class="page-title">Edit Material Keluar</h2>

        <div class="form-container">
            <form action="{{ route('material_keluar.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Nama Material -->
                <div class="form-group">
                    <label for="nama_material">Nama Material</label>
                    <select name="nama_material" id="nama_material" required>
                        <option value="">-- Pilih Material --</option>
                        @foreach($materialList as $material)
                            <option value="{{ $material->nama_material }}" 
                                {{ $material->nama_material == $data->nama_material ? 'selected' : '' }}>
                                {{ $material->nama_material }}
                            </option>
                        @endforeach
                    </select>
                    @error('nama_material') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <!-- Nama Petugas -->
                <div class="form-group">
                    <label for="nama_petugas">Nama Petugas</label>
                    <input type="text" name="nama_petugas" id="nama_petugas" 
                           value="{{ old('nama_petugas', $data->nama_petugas) }}" required>
                    @error('nama_petugas') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <!-- Jumlah Material -->
                <div class="form-group">
                    <label for="jumlah_material">Jumlah Material Keluar</label>
                    <input type="number" name="jumlah_material" id="jumlah_material" 
                           value="{{ old('jumlah_material', $data->jumlah_material) }}" required>
                    @error('jumlah_material') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <!-- Tanggal -->
                <div class="form-group">
                    <label for="tanggal">Tanggal dan Waktu</label>
                    <input type="datetime-local" name="tanggal" id="tanggal"
                           value="{{ \Carbon\Carbon::parse($data->tanggal)->format('Y-m-d\TH:i') }}" required>
                    @error('tanggal') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <!-- Foto -->
                <div class="form-group">
                    <label for="foto">Ubah Foto (opsional)</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    @error('foto') <small style="color:red;">{{ $message }}</small> @enderror

                    @if($data->foto)
                        <div class="current-photo mt-2">
                            <span>Foto Saat Ini:</span>
                            <img src="{{ asset('storage/' . $data->foto) }}" alt="Foto Material" width="100" height="100">
                        </div>
                    @endif
                </div>

                <!-- Tombol -->
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <a href="{{ route('material_keluar.index') }}" class="btn-action btn-cancel">Cancel</a>
                    <button type="submit" class="btn-action btn-ok">OK</button>
                </div>
            </form>
        </div>
    </main>
</div>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    showConfirmButton: false,
    timer: 2000
});
</script>
@endif

<!-- === Script Waktu Lokal Sekarang === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.getElementById('tanggal');
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0,16);
    tanggalInput.value = localISOTime;
});
</script>

</body>
</html>