<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIPBO-PLN</title>
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
        .sidebar-header img { width: 40px; margin-right: 15px; }
        .sidebar-header h1 { font-size: 1.3rem; margin: 0; }
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
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }
        .widget-card {
            padding: 25px;
            border-radius: 12px;
            color: #212529; /* Warna teks default untuk semua card */
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        /* Warna latar sesuai desain */
        .widget-card.red { background-color: #f8d7da; border-left: 5px solid #dc3545; }
        .widget-card.yellow { background-color: #fff3cd; border-left: 5px solid #ffc107; }
        .widget-card.blue { background-color: #d1ecf1; border-left: 5px solid #17a2b8; }
        .widget-icon { font-size: 3rem; margin-right: 25px; opacity: 0.7; }
        .widget-info h3 { margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 600; }
        .widget-info p { margin: 0; font-size: 0.95rem; }
    </style>
</head>
<body>

<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/97/Logo_PLN.png" alt="Logo PLN">
            <h1>SIPBO-PLN</h1>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-user-circle"></i> Profil</a></li>
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="{{ route('barang-stand-by.index') }}" class="{{ request()->routeIs('barang-stand-by.*') ? 'active' : '' }}"><i class="fas fa-box-open"></i> Barang Stand By</a></li>
            <li><a href="#"><i class="fas fa-undo"></i> Barang Retur</a></li>
            <li><a href="#"><i class="fas fa-tools"></i> Monitoring Pemasangan</a></li>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Monitoring Barang Sisa</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Sistem Informasi Pengelolaan Barang Operasional PLN</h2>
        
        <div class="widget-grid">
            <div class="widget-card red">
                {{-- ICON DIUBAH DI SINI --}}
                <div class="widget-icon"><i class="fas fa-box-open"></i></div>
                <div class="widget-info">
                    <h3>Barang Stand By</h3>
                    <p>Barang stand by di ruang server : {{ $totalStandBy ?? 0 }} unit</p>
                </div>
            </div>

            <div class="widget-card yellow">
                {{-- ICON DIUBAH DI SINI --}}
                <div class="widget-icon"><i class="fas fa-tools"></i></div>
                <div class="widget-info">
                    <h3>Monitoring Pemasangan</h3>
                    <p>Pemasangan Hari Ini : {{ $pemasanganHariIni ?? 0 }} Lokasi</p>
                </div>
            </div>

            <div class="widget-card yellow">
                {{-- ICON DIUBAH DI SINI --}}
                <div class="widget-icon"><i class="fas fa-undo"></i></div>
                <div class="widget-info">
                    <h3>Barang Retur</h3>
                    <p>Barang Rusak : {{ $returRusak ?? 0 }}<br>Barang Baik : {{ $returBaik ?? 0 }}</p>
                </div>
            </div>

            <div class="widget-card blue">
                {{-- ICON DIUBAH DI SINI --}}
                <div class="widget-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="widget-info">
                    <h3>Monitoring Barang Sisa</h3>
                    <p>Barang Sisa: {{ $totalBarangSisa ?? 0 }} unit</p>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>