<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Keluar - SIMAS-PLN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body, html {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            height: 100%;
        }
        .container { display: flex; height: 100vh; }

        /* Sidebar */
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
            width: 75px; height: 75px;
            margin-right: 15px;
            border-radius: 5px;
            object-fit: contain;
        }
        .sidebar-header h1 {
            font-size: 1.3rem; font-weight: 600; margin: 0;
        }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li { margin-bottom: 15px; }
        .sidebar-menu a {
            color: #f8f9fa;
            text-decoration: none;
            font-size: 1rem;
            display: flex; align-items: center;
            padding: 12px; border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar-menu a.active, .sidebar-menu a:hover {
            background-color: #495057;
        }
        .sidebar-menu i {
            width: 30px; text-align: center;
            margin-right: 10px; font-size: 1.2rem;
        }

        /* Main content */
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
        .search-box {
            margin-bottom: 15px;
            text-align: right;
        }
        .search-box input {
            width: 250px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
            font-size: 0.95rem;
        }
        th {
            background-color: #e9ecef;
            font-weight: 600;
        }
        tr:hover { background-color: #f8f9fa; }

        /* Buttons */
        .btn {
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-edit, .btn-hapus { padding: 5px 15px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; font-size: 0.9rem; font-weight: 500; color: white; display: inline-block; font-family: 'Poppins', sans-serif; }
        .btn-edit { background-color: #198754; } .btn-hapus { background-color: #dc3545; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-pdf { background: #d63384; color: white; margin-top: 20px; padding: 8px 15px; border-radius: 6px; }
        .btn-excel { background: #198754; color: white; margin-top: 20px; padding: 8px 15px; border-radius: 6px; }

        img.material-photo {
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
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

    <!-- Main content -->
<main class="main-content">
    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">

        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; color: #333; text-align: left;">SALDO MATERIAL STAND BY</h2>

        @if(session('success'))
            @endif

        <div style="text-align: right; margin-bottom: 20px;">
            <input type="text" placeholder="Search" style="width: 250px; padding: 10px 15px; border-radius: 5px; border: 1px solid #ccc; font-size: 1rem;">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah/Unit</th> 
                    <th>Tanggal (Jam Lokal)</th>
                    <th>Foto</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialKeluar as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nama_material }}</td>
                        <td>{{ $item->nama_petugas }}</td>
                        <td>{{ $item->jumlah_material }}</td> 
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($item->foto)
                                <a href="{{ asset('storage/' . $item->foto) }}" target="_blank" download>
                                    <img src="{{ asset('storage/' . $item->foto) }}" 
                                        alt="Foto Material" width="70" height="70" 
                                        style="border-radius:8px;object-fit:cover;cursor:pointer;">
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('material_keluar.edit', $item->id) }}" class="btn btn-edit">✏ Edit</a>
                            <form action="{{ route('material_keluar.destroy', $item->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus data ini?')">🗑 Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6c757d; padding: 50px 0;">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 20px;">
            <button class="btn btn-pdf" style="background: #dc3545;">📄 Unduh Pdf</button>
            <button class="btn btn-excel">📊 Unduh Excel</button>
        </div>

    </div>
</main>