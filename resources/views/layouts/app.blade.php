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
        /* === CSS DASAR & SIDEBAR === */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            height: 100%;
        }
        .container-fluid { display: flex; min-height: 100vh; }
        .sidebar {
            min-width: 260px;
            max-width: 260px;
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
            width: 50px;
            height: 50px;
            margin-right: 15px;
            border-radius: 5px;
            object-fit: contain;
        }
        .sidebar-header h1 {
            font-size: 1.5rem;
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
        .sidebar-menu a.active, .sidebar-menu a:hover { background-color: #495057; }
        .sidebar-menu i {
            width: 30px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* === MAIN CONTENT & KARTU LAMA === */
        .main-content {
            flex-grow: 1;
            padding: 40px;
            background-color: #E9ECEF; 
        }
        .card { 
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h2 { margin: 0; font-size: 1.5rem; }
        .card-body { padding: 1.5rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        
        /* === CSS FORM KUSTOM === */
        .card-form-container { max-width: 800px; }
        .card-form-header { padding: 0; margin-bottom: 2rem; }
        .card-form-header h2 { margin: 0; font-size: 1.8rem; color: #333; font-weight: 600; }
        .card-form-body { background-color: #ffffff; border-radius: 12px; padding: 2.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group-new { margin-bottom: 2rem; }
        .form-group-new label { display: block; margin-bottom: 10px; font-weight: 600; font-size: 1rem; color: #495057; }
        .form-control-new { width: 100%; padding: 14px 16px; border: 1px solid #ced4da; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins', sans-serif; font-size: 1rem; transition: border-color 0.2s, box-shadow 0.2s; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; background-size: 16px 12px; }
        input.form-control-new { appearance: none; background-image: none; }
        .form-control-new:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.15); }
        .form-control-new-file { width: 100%; padding: 14px 16px; border: 1px solid #ced4da; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins', sans-serif; font-size: 1rem; }
        .form-control-new-file::file-selector-button { display: none; }
        .form-actions { margin-top: 2.5rem; text-align: right; }
        .btn-simpan { padding: 12px 24px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: transform 0.2s; background-color: #198754; color: white; margin-left: 10px; }
        .btn-simpan:hover { transform: translateY(-2px); }
        .btn-batal { padding: 12px 24px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: transform 0.2s; background-color: #e9ecef; color: #495057; }
        .btn-batal:hover { transform: translateY(-2px); }


        /* === CSS HALAMAN INDEX (LAPORAN) === */
        .card-new { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 2.5rem; }
        .index-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .index-header h2 { margin: 0; font-size: 1.8rem; color: #333; font-weight: 600; }
        .search-bar { position: relative; }
        .search-bar i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-bar input { width: 300px; padding: 12px 15px 12px 40px; border-radius: 10px; border: 1px solid #ddd; font-size: 1rem; font-family: 'Poppins', sans-serif; }
        .search-bar input:focus { outline: none; border-color: #007bff; }
        .table-container { overflow-x: auto; }
        .table { border-top: 1px solid #ddd; width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .table thead { background-color: #f4f6f9; }
        .table th { font-weight: 600; }
        .table-foto { width: 100px; height: auto; border-radius: 5px; object-fit: cover; }
        .table-actions { display: flex; gap: 5px; }
        .btn-edit, .btn-hapus { padding: 5px 15px; border: none; border-radius: 5px; text-decoration: none; cursor: pointer; font-size: 0.9rem; font-weight: 500; color: white; display: inline-block; font-family: 'Poppins', sans-serif; }
        .btn-edit { background-color: #198754; } .btn-hapus { background-color: #dc3545; }
        .index-footer { margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-pdf, .btn-excel { padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .btn-pdf { background-color: #fce8e6; color: #dc3545; border: 1px solid #f5c6cb; }
        .btn-excel { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        /* === CSS DROPDOWN SIDEBAR === */
        .sidebar-menu .menu-item-has-dropdown > a { position: relative; }
        .sidebar-menu .arrow-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 0.8rem; transition: transform 0.2s ease; }
        .sidebar-menu .menu-item-has-dropdown.open > .dropdown-toggle .arrow-icon { transform: translateY(-50%) rotate(90deg); }
        .submenu { list-style: none; padding: 0 0 0 40px; margin: 0; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .menu-item-has-dropdown.open > .submenu { max-height: 200px; padding-top: 10px; }
        .submenu a { font-size: 0.95rem !important; padding: 10px 12px !important; color: #ced4da !important; }
        .submenu a:hover, .submenu a.sub-active { color: #ffffff !important; background-color: transparent !important; }

    </style>
</head>
<body>
<div class="container-fluid">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/logo-pln.png') }}" alt="Logo PLN" class="sidebar-logo">
            <h1>SIMAS-PLN</h1>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-user-circle"></i> Profil</a></li>
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> Dashboard</a></li>
            
            <li class="menu-item-has-dropdown {{ request()->routeIs('material-stand-by.*') ? 'active open' : '' }}">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-box-open"></i>
                    <span>Material Stand By</span>
                    <i class="fas fa-chevron-right arrow-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="{{ route('material-stand-by.index') }}" class="{{ request()->routeIs('material-stand-by.index') ? 'sub-active' : '' }}">Lihat Material</a></li>
                    <li><a href="{{ route('material-stand-by.create') }}" class="{{ request()->routeIs('material-stand-by.create') ? 'sub-active' : '' }}">Tambah Material</a></li>
                </ul>
            </li>
            
            <li><a href="#"><i class="fas fa-undo"></i> Material Retur</a></li>
            <li><a href="#"><i class="fas fa-tools"></i> Material Keluar</a></li>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Material Kembali</a></li>
            <li><a href="#"><i class="fas fa-satellite-dish"></i> Material Siaga Stand By</a></li>
            <li><a href="#"><i class="fas fa-history"></i> Siaga Kembali</a></li>
        </ul>
    </aside>

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Script untuk Dropdown Sidebar ---
    var dropdownToggles = document.querySelectorAll('.sidebar-menu .dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(event) {
            event.preventDefault(); 
            var parentLi = this.parentElement;
            parentLi.classList.toggle('open');
        });
    });
});
</script>

@stack('scripts')

</body>
</html>