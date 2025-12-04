<!DOCTYPE html>

    <html>
        <head>
            <title>Laporan Material Stand By</title>
        <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; font-size: 11px; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        td { vertical-align: top; }
        .text-center { text-align: center; }
        </style>
        </head>
    <body>
    <h2>LAPORAN MATERIAL STAND BY</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}</p>
    <table>
<thead>
    <tr>
        <th style="width: 5%;">No</th>
        <th>Nama Material</th>
        <th>Jumlah & Satuan</th>
        <th>Tanggal (WITA)</th>
    </tr>
</thead>
    <tbody>
    @forelse ($items as $index => $item)
    <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $item->material->nama_material ?? 'N/A' }}</td>
        <td class="text-center">{{ $item->jumlah }} {{ $item->satuan ?? '' }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center">Data tidak ditemukan pada periode ini.</td>
    </tr>
@endforelse
</tbody>
</table>
</body>
</html>