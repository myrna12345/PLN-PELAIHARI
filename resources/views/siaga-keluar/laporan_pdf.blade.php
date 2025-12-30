<!DOCTYPE html>
<html>
<head>
<title>Laporan Siaga Keluar</title>
<style>
body { font-family: sans-serif; font-size: 10px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table, th, td { border: 1px solid black; }
th, td { padding: 4px; text-align: left; vertical-align: middle; }
th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
td { text-align: center; }
/* Agar Nama Material, Petugas, dan Keterangan rata kiri */
td:nth-child(2), td:nth-child(3), td:nth-child(5) { text-align: left; }
h2 { text-align: center; margin-bottom: 5px; }
p { text-align: center; margin-top: 0; font-size: 12px; }
.img-report { width: 60px; height: auto; display: block; margin: 0 auto; }
</style>
</head>
<body>
<h2>Laporan Siaga Keluar</h2>
<p>Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_akhir }}</p>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Material & Nomor Meter</th>
            <th>Nama Petugas</th>
            <th>Stand Meter</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Tanggal (WITA)</th>
            <th>Foto Material</th> {{-- TAMBAHAN: Foto Material --}}
            <th>Foto Petugas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dataSiagaKeluar as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                {{ $item->material->nama_material ?? 'N/A' }} 
                @if ($item->nomor_meter) 
                    - {{ $item->nomor_meter }} 
                @endif
            </td>
            <td>{{ $item->nama_petugas }}</td>
            <td>{{ $item->stand_meter ?? '-' }}</td> 
            <td>{{ $item->keterangan }}</td> 
            <td>{{ $item->status }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
            
            {{-- Foto Material --}}
            <td>
                @if($item->foto_path && file_exists(public_path('storage/' . $item->foto_path)))
                    <img src="{{ public_path('storage/' . $item->foto_path) }}" class="img-report">
                @else
                    <span>-</span>
                @endif
            </td>

            {{-- Foto Petugas --}}
            <td>
                @if($item->foto_petugas && file_exists(public_path('storage/' . $item->foto_petugas)))
                    <img src="{{ public_path('storage/' . $item->foto_petugas) }}" class="img-report">
                @else
                    <span>-</span>
                @endif
            </td>
        </tr>
        @empty
            <tr>
                {{-- Colspan disesuaikan jadi 9 karena ada tambahan kolom foto material --}}
                <td colspan="9" style="text-align:center;">Data tidak ditemukan pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>