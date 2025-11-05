<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMAS-PLN</title>
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
            width: 75px;     /* Ukuran diperbesar */
            height: 75px;    /* Ukuran diperbesar */
            margin-right: 15px;
            border-radius: 5px; 
            object-fit: contain; 
        }
        .sidebar-header h1 { 
            font-size: 1.3rem; /* Ukuran teks diperbesar */
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
        .main-content { flex-grow: 1; padding: 40px; }
        .main-content h2 { font-size: 1.8rem; color: #333; margin-top: 0; margin-bottom: 30px; }
        .widget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .widget-card {
            padding: 25px;
            border-radius: 12px;
            color: #212529; 
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .widget-card.red { background-color: #f8d7da; border-left: 5px solid #dc3545; }
        .widget-card.yellow { background-color: #fff3cd; border-left: 5px solid #ffc107; }
        .widget-card.blue { background-color: #d1ecf1; border-left: 5px solid #17a2b8; }
        .widget-icon { font-size: 3rem; margin-right: 25px; opacity: 0.7; }
        .widget-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 600; }
        .widget-info p { margin: 0; font-size: 0.95rem; }
        
        .widget-info .retur-list {
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/logo-pln.png') }}" alt="Logo PLN" class="sidebar-logo">
            <h1>SIMAS-PLN</h1>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-user-circle"></i> Profil</a></li>
            <li><a href="{{ route('dashboard') }}" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="{{ route('material-stand-by.index') }}" class="{{ request()->routeIs('material-stand-by.*') ? 'active' : '' }}"><i class="fas fa-box-open"></i> Material Stand By</a></li>
            <li><a href="#"><i class="fas fa-undo"></i> Material Retur</a></li>
            <li><a href="#"><i class="fas fa-tools"></i> Material Keluar</a></li>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Material Kembali</a></li>
            <li><a href="#"><i class="fas fa-satellite-dish"></i> Material Siaga Stand By</a></li>
            <li><a href="#"><i class="fas fa-history"></i> Siaga Kembali</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Sistem Informasi Pengelolaan Material Stand By di Gudang Kecil-PLN</h2>
        
        <div class="widget-grid">
            <div class="widget-card red">
                <div class="widget-icon"><i class="fas fa-box-check"></i></div>
                <div class="widget-info">
                    <h3>Material Stand By</h3>
                    <p>Material stand by di gudang kecil : {{ $totalStandBy ?? 0 }} unit</p>
                </div>
            </div>

            <div class="widget-card yellow">
                <div class="widget-icon"><i class="fas fa-tools"></i></div>
                <div class="widget-info">
                    <h3>Material Keluar</h3>
                    <p>Pemasangan Hari ini : {{ $materialKeluarHariIni ?? 0 }} Lokasi</p>
                </div>
            </div>

            <div class="widget-card yellow">
                <div class="widget-icon"><i class="fas fa-box-recycle"></i></div>
                <div class="widget-info">
                    <h3>Material Retur</h3>
                    <div class="retur-list">
                        Bekas Andal : {{ $returAndal ?? 0 }}<br>
                        Rusak : {{ $returAndal ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="widget-card blue">
                <div class="widget-icon"><i class="fas fa-wave-square"></i></div>
                <div class="widget-info">
                    <h3>Material Kembali</h3>
                    <p>Material Kembali: {{ $totalMaterialKembali ?? 0 }} unit</p>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>