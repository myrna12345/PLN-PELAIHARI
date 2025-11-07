<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Material Keluar - SIMAS-PLN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

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
        }
        .main-content h2 {
            font-size: 1.6rem;
            color: #333;
            margin-bottom: 30px;
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
        input[type="date"],
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
        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        input[type="datetime-local"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            outline: none;
        }

        input[type="datetime-local"]:focus {
        border-color: #007bff;
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
            <li><a href="{{ route('material_keluar.create') }}" class="active"><i class="fas fa-tools"></i> Material Keluar</a></li>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Material Kembali</a></li>
            <li><a href="#"><i class="fas fa-satellite-dish"></i> Material Siaga Stand By</a></li>
            <li><a href="#"><i class="fas fa-history"></i> Siaga Kembali</a></li>
        </ul>
    </aside>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <!-- Main Content -->
    <main class="main-content">
         <h2 class="page-title">Tambah Material Keluar</h2>

        <div class="form-container">
            <form action="{{ route('material_keluar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="nama_material">Nama Material</label>
                    <select name="nama_material" id="nama_material" required>
                        <option value="">-- Pilih Material --</option>
                    @foreach($materialList as $material)
                        <option value="{{ $material->nama_material }}">{{ $material->nama_material }}</option>
                    @endforeach
                    </select>

                    @error('nama_material') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="nama_petugas">Nama Petugas</label>
                    <input type="text" name="nama_petugas" id="nama_petugas" placeholder="Masukkan nama petugas" required>
                    @error('nama_petugas') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="jumlah_material">Jumlah Material Keluar</label>
                    <input type="number" name="jumlah_material" id="jumlah_material" placeholder="Masukkan jumlah material" required>
                    @error('jumlah_material') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
    <label for="tanggal" class="form-label">Tanggal dan Waktu</label>
    <input type="datetime-local" id="tanggal" name="tanggal" class="form-control" required>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil elemen input tanggal
        const inputTanggal = document.getElementById('tanggal');

        // Ambil waktu sekarang dari komputer user
        const now = new Date();

        // Konversi ke format yang bisa diterima oleh input datetime-local (YYYY-MM-DDTHH:MM)
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        // Gabungkan format
        const localDatetime = `${year}-${month}-${day}T${hours}:${minutes}`;

        // Set nilai input agar otomatis tampil di form
        inputTanggal.value = localDatetime;
    });
</script>


                <div class="form-group">
                    <label for="foto">Unggah Foto</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    @error('foto') <small style="color:red;">{{ $message }}</small> @enderror
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-submit">Simpan</button>
                </div>
                @if (session('success'))
                <div style="background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 5px; margin-bottom: 15px;">
                    <strong>{{ session('success') }}</strong>
                </div>
                @endif
            </form>
        </div>
    </main>
</div>
</body>
</html>
