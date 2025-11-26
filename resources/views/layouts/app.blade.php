<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIMAS-PLN')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
        /* === 1. CSS DASAR & SIDEBAR (TAMPILAN DESKTOP DEFAULT) === */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9; 
            height: 100vh; 
            overflow: hidden; 
        }
        .container-fluid { display: flex; height: 100%; }

        /* Sidebar Desktop */
        .sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: #414141;
            color: #f8f9fa;
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0; 
            overflow-y: auto; 
            transition: all 0.3s ease-in-out;
            z-index: 1001; 
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #4f565d;
            justify-content: space-between; 
        }
        .sidebar-header .logo-section {
            display: flex; align-items: center;
        }
        .sidebar-header .sidebar-logo {
            width: 40px; height: 40px; margin-right: 10px; border-radius: 5px; object-fit: contain;
        }
        .sidebar-header h1 {
            font-size: 1.4rem; margin: 0; font-weight: 600;
        }
        /* Tombol close sidebar desktop: DISEMBUNYIKAN */
        .sidebar-close-btn {
            display: none; 
        }

        /* Sidebar Menu Styles */
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
            /* Tambahan: Pastikan tidak membungkus teks */
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-menu a.active, .sidebar-menu a:hover { 
            background-color: #495057; 
        }
        .sidebar-menu i {
            width: 30px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.2rem;
            flex-shrink: 0; /* Mencegah icon menyusut */
        }
        /* Tambahan: Pastikan span mengambil sisa ruang */
        .sidebar-menu a span {
            flex-grow: 1; /* Membuat span mengambil ruang */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* === 2. CSS MAIN CONTENT & HEADER === */
        .main-content {
            flex-grow: 1;
            background-color: #E9ECEF; 
            overflow: auto; /* PENTING: Scroll vertikal & horizontal */
            -webkit-overflow-scrolling: touch;
            display: flex;
            flex-direction: column;
            width: 100%; 
        }
        .top-header {
            display: none; 
            background-color: #ffffff;
            padding: 15px 25px;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .toggle-sidebar-btn {
            background: none; border: none; font-size: 1.5rem; color: #333; cursor: pointer; margin-right: 20px;
        }
        .page-title { margin: 0; font-size: 1.3rem; color: #333; font-weight: 600; }
        .main-content-inner { padding: 30px; flex-grow: 1; min-width: 0; }

        /* Alert Styles */
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* === 3. CSS FORM KUSTOM === */
        .card-form-container { max-width: 800px; }
        .card-form-header { padding: 0; margin-bottom: 2rem; }
        .card-form-header h2 { margin: 0; font-size: 1.8rem; color: #333; font-weight: 600; }
        .card-form-body { background-color: #ffffff; border-radius: 12px; padding: 2.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group-new { margin-bottom: 2rem; }
        .form-group-new label { display: block; margin-bottom: 10px; font-weight: 600; font-size: 1rem; color: #495057; }
        .form-control-new { width: 100%; padding: 14px 16px; border: 1px solid #ced4da; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins', sans-serif; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; background-size: 16px 12px; }
        input.form-control-new { appearance: none; background-image: none; }
        .form-control-new:focus { outline: none; border-color: #198754; box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15); }
        .form-control-new-file { width: 100%; padding: 14px 16px; border: 1px solid #ced4da; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins', sans-serif; font-size: 1rem; }
        .form-control-new-file::file-selector-button { display: none; }
        .form-actions { margin-top: 2.5rem; text-align: right; }
        .btn-simpan { padding: 12px 24px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: transform 0.2s; background-color: #92C1A9; color: #212529; margin-left: 10px; }
        .btn-batal { padding: 12px 24px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: transform 0.2s; background-color: #e9ecef; color: #495057; }

        /* === 4. CSS HALAMAN INDEX (LAPORAN) === */
        .card-new { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 2.5rem; width: 100%; box-sizing: border-box; }
        .index-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .index-header h2 { margin: 0; font-size: 1.8rem; color: #333; font-weight: 600; }
        .search-form { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
        .search-bar { position: relative; }
        .search-bar i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; z-index: 2; }
        .search-bar input { width: 250px; padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #ddd; font-size: 0.9rem; font-family: 'Poppins', sans-serif; }
        .search-bar input:focus { outline: none; border-color: #007bff; }
        .form-group-tanggal-filter input.form-control-tanggal { padding: 8px 10px; border-radius: 10px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; font-size: 0.9rem; height: 38px; }
        .btn-sm { padding: 9px 15px; font-size: 0.9rem; border-radius: 10px; }
        .btn-secondary { background-color: #6c757d; color: white; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }

        .table-container { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; border: 1px solid #eee; border-radius: 8px; display: block; }
        .table { width: 100%; min-width: 800px; border-collapse: collapse; }
        .table th, .table td { padding: 12px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        .table thead { background-color: #f4f6f9; }
        .table th { font-weight: 600; white-space: nowrap; }
        .table-foto { width: 100px; height: auto; border-radius: 5px; object-fit: cover; display: block; margin: 0 auto; cursor: pointer; transition: transform 0.2s; }
        .table-actions { display: flex; flex-wrap: wrap; gap: 5px; }
        .btn-lihat, .btn-edit, .btn-hapus { padding: 5px 15px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; font-size: 0.9rem; font-weight: 500; display: inline-block; font-family: 'Poppins', sans-serif; }
        .btn-lihat { background-color: #6c757d; color: white; }
        .btn-edit { background-color: #92C1A9; color: #212529; } 
        .btn-hapus { background-color: #d06368ff; color: white; }
        .btn-foto-download { display: inline-flex; align-items: center; gap: 4px; margin-top: 8px; padding: 4px 8px; border-radius: 5px; background-color: #88c7d2ff; color: #212529; font-size: 0.8rem; font-weight: 500; text-decoration: none; transition: background-color 0.2s; }
        .btn-foto-download i { font-size: 0.8rem; color: #212529; }

        .index-footer-form { margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem; }
        .form-download { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
        .form-group-tanggal label { font-size: 0.9rem; font-weight: 500; display: block; margin-bottom: 5px; }
        .form-control-tanggal { padding: 8px 10px; border-radius: 5px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; }
        .btn-pdf, .btn-excel { padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; font-family: 'Poppins', sans-serif; }
        .btn-pdf { background-color: #fce8e6; color: #dc3545; border: 1px solid #f5c6cb; }
        .btn-excel { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        /* =========================================
           5. CSS DROPDOWN SIDEBAR (PERBAIKAN FOKUS DI SINI)
           ========================================= */
        .sidebar-menu .menu-item-has-dropdown > a { 
            position: relative; 
            cursor: pointer; 
        }
        .sidebar-menu .arrow-icon {
            /* MENGHAPUS 'position: absolute' */
            margin-left: auto; /* Mendorong panah ke paling kanan */
            padding-left: 5px; /* Jarak aman dari teks */
            font-size: 0.8rem;
            transition: transform 0.2s ease;
            flex-shrink: 0; /* Mencegah panah menyusut */
        }
        .sidebar-menu .menu-item-has-dropdown.open > .dropdown-toggle .arrow-icon { 
            transform: rotate(90deg); /* Hanya memutar */
        }
        .submenu { list-style: none; padding: 0 0 0 40px; margin: 0; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .menu-item-has-dropdown.open > .submenu { max-height: 500px; padding-top: 10px; }
        .submenu a { font-size: 0.95rem !important; padding: 10px 12px !important; color: #ced4da !important; }
        .submenu a:hover, .submenu a.sub-active { color: #ffffff !important; background-color: transparent !important; }
        /* =========================================
           PERBAIKAN DROPDOWN SELESAI
           ========================================= */

        /* === 6. CSS WIDGET DASHBOARD === */
        .widget-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .widget-card { padding: 25px; border-radius: 12px; display: flex; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-left: none; }
        .widget-card.salmon-red { background-color: #d06368ff; color: white; }
        .widget-card.blue { background-color: #88c7d2ff; color: #212529; }
        .widget-card.purple { background-color: #88c7d2ff; color: #212529; }
        .widget-card.green { background-color: #88c7d2ff; color: #212529; }
        .widget-card.mauve { background-color: #dad664ff; color: #212529; }
        .widget-icon { font-size: 3rem; margin-right: 25px; opacity: 0.7; }
        .widget-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 600; }
        .widget-info p { margin: 0; font-size: 0.95rem; }

        /* === 7. CSS MODAL FOTO === */
        .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; }
        .modal-content { position: relative; margin: auto; padding: 0; max-width: 90%; max-height: 90%; }
        .modal-image { width: 100%; height: auto; max-width: 80vw; max-height: 80vh; object-fit: contain; }
        .modal-close { position: absolute; top: -15px; right: 0px; color: #fff; font-size: 40px; font-weight: bold; transition: 0.3s; cursor: pointer; }

        /* =========================================
           8. RESPONSIVE (HP & TABLET)
           ========================================= */
        .sidebar-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;
        }
        .sidebar-overlay.active { display: block; }

        @media (max-width: 991.98px) {
            body, html { overflow: auto; height: auto; min-height: 100vh; }
            .container-fluid { display: block; height: auto; }
            .sidebar { position: fixed; height: 100%; transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .sidebar-close-btn { display: block; background: none; border: none; color: #f8f9fa; font-size: 1.8rem; cursor: pointer; }
            .top-header { display: flex; }
            .main-content { height: auto; overflow: visible; }
            .main-content-inner { padding: 15px; }
            .card-new { padding: 1.5rem; overflow-x: hidden; }
            
            /* FIX FORM SEARCH HP (Vertical & Seragam) */
            .index-header { flexDirection: column; align-items: flex-start; gap: 15px; }
            .index-header h2 { font-size: 1.5rem; white-space: normal; line-height: 1.3; width: 100%; }

            .search-form, .form-download {
                flex-direction: column; align-items: stretch; width: 100%; gap: 15px;
            }
            
            /* LABEL OTOMATIS DI HP */
            .form-group-tanggal-filter:nth-of-type(2)::before { content: "Dari Tanggal:"; display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; color: #333; }
            .form-group-tanggal-filter:nth-of-type(3)::before { content: "Sampai Tanggal:"; display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; color: #333; }
            .form-download .form-group-tanggal label { font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; color: #333; }

            /* UKURAN SERAGAM MUTLAK (50px) UNTUK SEMUA */
            .search-bar input[type="text"],
            .form-group-tanggal-filter input[type="date"],
            .form-group-tanggal input[type="date"],
            .search-form button.btn-primary,
            .search-form a.btn-secondary,
            .form-download button.btn-pdf,
            .form-download button.btn-excel {
                width: 100% !important; max-width: 100% !important; box-sizing: border-box !important;
                height: 50px !important; min-height: 50px !important;
                font-size: 1rem !important;
                border-radius: 10px !important; margin: 0 !important;
            }
            .search-form button, .search-form a.btn-secondary, .form-download button {
                display: flex !important; align-items: center !important; justify-content: center !important;
                padding: 0 15px !important;
            }
            
            /* --- PERBAIKAN SPESIFIK UNTUK INPUT (SEARCH & TANGGAL) --- */
            .search-bar input[type="text"] {
                display: block !important;
                line-height: normal !important; 
                padding: 0 15px 0 40px !important; /* padding-left: 40px untuk ikon search */
                background-image: none !important; /* Matikan background-image di HP */
            }
            .form-group-tanggal-filter input[type="date"], 
            .form-group-tanggal input[type="date"] {
                display: flex !important; /* Ubah ke flex agar align-items berfungsi */
                align-items: center !important; /* Teks tanggal di tengah vertikal */
                padding: 0 15px !important;
            }
            /* --- AKHIR PERBAIKAN SPESIFIK --- */

            .search-bar, .form-group-tanggal-filter, .form-group-tanggal { width: 100% !important; margin-bottom: 0 !important; }
            .search-form a.btn-secondary { background-color: #6c757d; color: white !important; font-weight: 600; margin-top: 5px !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo-pln.png') }}" alt="Logo" class="sidebar-logo">
                <h1>SIMAS-PLN</h1>
            </div>
            <button class="sidebar-close-btn" id="sidebarCloseBtn">&times;</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-user-circle"></i> <span>Profil</span></a></li>
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            
            <li class="menu-item-has-dropdown {{ request()->routeIs('material-stand-by.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-box-open"></i> <span>Material Stand By</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <li><a href="{{ route('material-stand-by.index') }}" class="{{ request()->routeIs('material-stand-by.index') ? 'sub-active' : '' }}">Laporan Material</a></li>
                    <li><a href="{{ route('material-stand-by.create') }}" class="{{ request()->routeIs('material-stand-by.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
                </ul>
            </li>
            <li class="menu-item-has-dropdown {{ request()->routeIs('material-retur.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-undo"></i> <span>Material Retur</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <li><a href="{{ route('material-retur.index') }}" class="{{ request()->routeIs('material-retur.index') ? 'sub-active' : '' }}">Laporan Material</a></li>
                    <li><a href="{{ route('material-retur.create') }}" class="{{ request()->routeIs('material-retur.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
                </ul>
            </li>
             <li class="menu-item-has-dropdown {{ request()->routeIs('material-keluar.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-tools"></i> <span>Material Keluar</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <li><a href="{{ route('material_keluar.index') }}" class="{{ request()->routeIs('material_keluar.index') ? 'sub-active' : '' }}">Laporan Material</a></li>
                    <li><a href="{{ route('material_keluar.create') }}" class="{{ request()->routeIs('material_keluar.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
                </ul>
            </li>
             <li class="menu-item-has-dropdown {{ request()->routeIs('material-kembali.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-chart-pie"></i> <span>Material Kembali</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <li><a href="{{ route('material_kembali.index') }}" class="{{ request()->routeIs('material_kembali.index') ? 'sub-active' : '' }}">Laporan Material</a></li>
                    <li><a href="{{ route('material_kembali.create') }}" class="{{ request()->routeIs('material_kembali.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
                </ul>
            </li>
             <li class="menu-item-has-dropdown {{ request()->routeIs('material-siaga-stand-by.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-box-archive"></i> <span>Siaga Stand By</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
<<<<<<< HEAD
                    <li><a href="{{ route('material-siaga.index') }}">Laporan Material</a></li>
                    <li><a href="{{ route('material-siaga.create') }}">Tambah Material</a></li>
=======
                    <!-- INI YANG DIPERBAIKI (href="#") -->
                    <li><a href="{{ route('material-siaga-stand-by.index') }}" class="{{ request()->routeIs('material-siaga-stand-by.index') ? 'sub-active' : '' }}">Laporan Material</a></li>
                    <li><a href="{{ route('material-siaga-stand-by.create') }}" class="{{ request()->routeIs('material-siaga-stand-by.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
>>>>>>> f62d50ac3e2f3ff52ca7d3e4ef9ac52831175ff7
                </ul>
            </li>
             <li class="menu-item-has-dropdown {{ request()->routeIs('siaga-keluar.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-truck"></i> <span>Siaga Keluar</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <!-- INI YANG DIPERBAIKI (href="#") -->
                    <li><a href="{{ route('siaga-keluar.index') }}" class="{{ request()->routeIs('siaga-keluar.index') ? 'sub-active' : '' }}">Laporan Siaga</a></li>
                    <li><a href="{{ route('siaga-keluar.create') }}" class="{{ request()->routeIs('siaga-keluar.create') ? 'sub-active' : '' }}">Tambah Siaga</a></li>
                </ul>
            </li>
             <li class="menu-item-has-dropdown {{ request()->routeIs('siaga-kembali.*') ? 'active open' : '' }}">
                <a class="dropdown-toggle"><i class="fas fa-sync-alt"></i> <span>Siaga Kembali</span><i class="fas fa-chevron-right arrow-icon"></i></a>
                <ul class="submenu">
                    <!-- INI YANG DIPERBAIKI (href="#") -->
                    <li><a href="{{ route('siaga-kembali.index') }}" class="{{ request()->routeIs('siaga-kembali.index') ? 'sub-active' : '' }}">Laporan Siaga</a></li>
                    <li><a href="{{ route('siaga-kembali.create') }}" class="{{ request()->routeIs('siaga-kembali.create') ? 'sub-active' : '' }}">Tambah Siaga</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <button class="toggle-sidebar-btn" id="toggleSidebarBtn"><i class="fas fa-bars"></i></button>
            <h2 class="page-title">@yield('title', 'SIMAS-PLN')</h2>
        </header>
        <div class="main-content-inner">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div id="fotoModal" class="modal-overlay"><span class="modal-close" id="modalCloseButton">&times;</span><div class="modal-content"><img id="modalImage" class="modal-image" src="" alt="Foto Material"></div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    function toggleSidebar() { sidebar.classList.toggle('active'); overlay.classList.toggle('active'); }
    if(toggleBtn) toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleSidebar(); });
    if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);
    window.addEventListener('resize', () => { if (window.innerWidth > 991.98 && sidebar.classList.contains('active')) { sidebar.classList.remove('active'); overlay.classList.remove('active'); } });

    document.querySelectorAll('.sidebar-menu .dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement.closest('.menu-item-has-dropdown'); // Target li
            if (!parent) return; // Keluar jika tidak ditemukan
            
            // Tutup dropdown lain
            document.querySelectorAll('.sidebar-menu .menu-item-has-dropdown.open').forEach(open => {
                if (open !== parent) open.classList.remove('open');
            });
            parent.classList.toggle('open');
        });
    });

    const modal = document.getElementById('fotoModal');
    const modalImg = document.getElementById('modalImage');
    const modalClose = document.getElementById('modalCloseButton');
    document.body.addEventListener('click', e => { if (e.target.classList.contains('table-foto')) { modal.style.display = 'flex'; modalImg.src = e.target.src; } });
    if(modalClose) modalClose.addEventListener('click', () => modal.style.display = 'none');
    if(modal) modal.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });
});
</script>
</body>
</html>