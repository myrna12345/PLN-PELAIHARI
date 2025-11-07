<!DOCTYPE html>
<html>
<head>
    <title>Laporan Material Retur</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; font-size: 11px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        td { vertical-align: top; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>LAPORAN MATERIAL RETUR</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Material</th>
                <th>Nama Petugas</th>
                <th>Jumlah Retur</th>
                <th>Jumlah Keluar</th>
                <th>Jumlah Kembali</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Tanggal (WITA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
                    <td>{{ $item->nama_petugas }}</td>
                    <td class="text-center">{{ $item->jumlah }}</td>
                    <td class="text-center">{{ $item->material_keluar ?? 0 }}</td>
                    <td class="text-center">{{ $item->material_kembali ?? 0 }}</td>
                    <td>{{ $item->status == 'baik' ? 'Baik' : 'Rusak' }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>{{ $item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Data tidak ditemukan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>