{{-- File: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPBO-PLN')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        /* (Salin semua CSS dari file dashboard.blade.php Anda ke sini agar konsisten) */
        body, html { margin:0; padding:0; font-family:'Poppins', sans-serif; background-color:#f4f6f9; height:100%; }
        .container-fluid { display:flex; min-height:100vh; }
        .sidebar { min-width:260px; max-width:260px; background-color:#343a40; color:#f8f9fa; padding:20px; display:flex; flex-direction:column; }
        .sidebar-header { display:flex; align-items:center; margin-bottom:30px; padding-bottom:20px; border-bottom:1px solid #4f565d; }
        .sidebar-header img { width:40px; margin-right:15px; }
        .sidebar-header h1 { font-size:1.3rem; margin:0; }
        .sidebar-menu { list-style:none; padding:0; }
        .sidebar-menu li { margin-bottom:15px; }
        .sidebar-menu a { color:#f8f9fa; text-decoration:none; font-size:1rem; display:flex; align-items:center; padding:12px; border-radius:5px; transition:background-color 0.3s; }
        .sidebar-menu a.active, .sidebar-menu a:hover { background-color:#495057; }
        .sidebar-menu i { width:30px; text-align:center; margin-right:10px; font-size:1.2rem; }
        .main-content { flex-grow:1; padding:40px; }
        .card { background-color:#fff; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.08); overflow:hidden; }
        .card-header { padding:1.25rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
        .card-header h2 { margin:0; font-size:1.5rem; }
        .card-body { padding:1.5rem; }
        .btn { padding:10px 20px; border:none; border-radius:5px; text-decoration:none; cursor:pointer; font-size:0.9rem; font-weight:500; }
        .btn-primary { background-color:#007bff; color:white; } .btn-success { background-color:#28a745; color:white; } .btn-danger { background-color:#dc3545; color:white; }
        .btn-warning { background-color:#ffc107; color:black; } .btn-secondary { background-color:#6c757d; color:white; }
        .form-group { margin-bottom:1.5rem; }
        .form-group label { display:block; margin-bottom:8px; font-weight:600; }
        .form-control { width:100%; padding:12px; border:1px solid #ccc; border-radius:5px; box-sizing:border-box; }
        .table { width:100%; border-collapse:collapse; }
        .table th, .table td { padding:12px; border:1px solid #ddd; text-align:left; }
        .table thead { background-color:#f4f6f9; }
        .alert { padding:1rem; margin-bottom:1rem; border-radius:5px; }
        .alert-success { background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    </style>
</head>
<body>
<div class="container-fluid">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/97/Logo_PLN.png" alt="Logo PLN">
            <h1>SIPBO-PLN</h1>
        </div>
        <ul class="sidebar-menu">
    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="{{ route('barang-stand-by.index') }}" class="{{ request()->routeIs('barang-stand-by.*') ? 'active' : '' }}"><i class="fas fa-box-open"></i> Barang Stand By</a></li>
</ul>
    </aside>
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>