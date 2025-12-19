<!DOCTYPE html>

<html>
<head>
<title>Laporan Siaga Keluar</title>
<style>
body { font-family: sans-serif; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table, th, td { border: 1px solid black; }
th, td { padding: 6px; text-align: left; }
th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
td { text-align: center; }
/* Agar Nama Material, Petugas, dan Keterangan rata kiri supaya rapi */
td:nth-child(2), td:nth-child(3), td:nth-child(5) { text-align: left; }
h2 { text-align: center; margin-bottom: 5px; }
p { text-align: center; margin-top: 0; font-size: 12px; }
</style>
</head>
<body>
<h2>Laporan Siaga Keluar</h2>
<p>Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_akhir }}</p>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 20%;">Nama Material & Nomor Meter</th>
            <th style="width: 15%;">Nama Petugas</th>
            <th style="width: 10%;">Stand Meter</th>
            <th style="width: 30%;">Keterangan</th> {{-- <--- KOLOM HEADER KETERANGAN BARU --}}
            <th>Status</th>
            <th style="width: 15%;">Tanggal (WITA)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dataSiagaKeluar as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                {{ $item->material->nama_material ?? 'N/A' }} 
                @if ($item->nomor_meter) {{-- MENGGUNAKAN nomor_meter SESUAI PERBAIKAN --}}
                    - {{ $item->nomor_meter }} 
                @endif
            </td>
            <td>{{ $item->nama_petugas }}</td>
            <td>{{ $item->stand_meter ?? '-' }}</td> 
            <td>{{ $item->keterangan }}</td> {{-- <--- DATA KETERANGAN BARU --}}
            <td>{{ $item->status }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
        </tr>
        @empty
            <tr>
                {{-- COLSPAN Disesuaikan: 7 kolom total (No, Mat&Meter, Petugas, Stand, Keterangan, Status, Tgl) --}}
                <td colspan="7" style="text-align:center;">Data tidak ditemukan pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>