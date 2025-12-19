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
/* Agar Nama Material & Petugas rata kiri supaya rapi */
td:nth-child(2), td:nth-child(3) { text-align: left; }
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
            {{-- PERBAIKAN HEADER: Nama Material & Nomor Meter --}}
            <th>Nama Material & Nomor Meter</th>
            <th>Nama Petugas</th>
            <th>Stand Meter</th> {{-- STAND METER KEMBALI TERPISAH --}}
            {{-- DIHAPUS: <th>Jumlah Keluar</th> --}}
            {{-- DIHAPUS: <th>Jumlah Masuk</th> --}}
            <th>Status</th>
            <th>Tanggal (WITA)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dataSiagaKeluar as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            {{-- DATA: Menggabungkan Nama Material dan NOMOR METER (field: nomor_unit), tanpa kata 'Unit' --}}
            <td>
                {{ $item->material->nama_material ?? 'N/A' }} 
                @if ($item->nomor_unit) 
                    - {{ $item->nomor_unit }} 
                @endif
            </td>
            <td>{{ $item->nama_petugas }}</td>
            <td>{{ $item->stand_meter ?? '-' }}</td> {{-- STAND METER KEMBALI TERPISAH --}}
            {{-- DIHAPUS: <td>{{ $item->jumlah_siaga_keluar }}</td> --}}
            {{-- DIHAPUS: <td>{{ $item->jumlah_siaga_masuk ?? 0 }}</td> --}}
            <td>{{ $item->status }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


</body>
</html>