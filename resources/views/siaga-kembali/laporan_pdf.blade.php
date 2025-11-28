<!DOCTYPE html>
<html>
<head>
    <title>Laporan Siaga Kembali</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        td { text-align: center; }
        /* Mengubah indeks rata kiri karena kolom sudah digabung */
        td:nth-child(2), td:nth-child(3) { text-align: left; } 
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; margin-top: 0; font-size: 12px; }
    </style>
</head>
<body>
    <h2>Laporan Siaga Kembali</h2>
    {{-- ✅ PERBAIKAN 1: Memastikan variabel periode yang diakses ada dan diformat --}}
    <p>Periode: {{ $tanggal_mulai->format('d M Y') }} s/d {{ $tanggal_akhir->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Material & Unit</th> {{-- ✅ PERBAIKAN 2: Menggabungkan Nama Material dan Unit --}}
                <th>Nama Petugas</th>
                <th>Stand Meter</th>
                <th>Jumlah Siaga Keluar</th>
                <th>Jumlah Siaga Kembali</th>
                <th>Status</th>
                <th>Tanggal (WITA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                
                {{-- ✅ PERBAIKAN 3: Menggabungkan data Nama Material dan Nomor Unit --}}
                <td>
                    {{ $item->material->nama_material ?? 'N/A' }}
                    @if ($item->nomor_unit)
                        - {{ $item->nomor_unit }}
                    @endif
                </td>
                
                <td>{{ $item->nama_petugas }}</td>
                <td>{{ $item->stand_meter ?? '-' }}</td>
                <td>{{ $item->jumlah_siaga_keluar }}</td>
                <td>{{ $item->jumlah_siaga_kembali }}</td>
                <td>{{ $item->status ?? 'Kembali' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Data tidak ditemukan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>