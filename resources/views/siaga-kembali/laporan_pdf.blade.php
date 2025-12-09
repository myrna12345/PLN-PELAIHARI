<!DOCTYPE html>
<html>
    <head>
        <title>Laporan Siaga Kembali</title>
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
    <h2>LAPORAN SIAGA KEMBALI</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}</p>
    <table>
<thead>
    <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 20%;">Nama Material & Nomor Meter</th> 
        <th style="width: 15%;">Nama Petugas</th>
        <th style="width: 10%;">Stand Meter</th>
        <th style="width: 25%;">Keterangan</th> {{-- <--- KOLOM HEADER KETERANGAN BARU --}}
        <th>Status</th>
        <th style="width: 15%;">Tanggal (WITA)</th>
    </tr>
</thead>
    <tbody>
    @forelse ($items as $index => $item)
    <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>
            {{ $item->material->nama_material ?? 'N/A' }} 
            @if ($item->nomor_meter) {{-- MENGGUNAKAN nomor_meter SESUAI PERBAIKAN --}}
                - {{ $item->nomor_meter }}
            @endif
        </td>
        <td>{{ $item->nama_petugas }}</td>
        <td>{{ $item->stand_meter ?? '-' }}</td>
        <td>{{ $item->keterangan }}</td> {{-- <--- DATA KETERANGAN BARU --}}
        
        <td>{{ $item->status ?? 'Kembali' }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
    </tr>
@empty
    <tr>
        {{-- COLSPAN diubah dari 6 menjadi 7: (No, Mat&Meter, Petugas, Stand, Keterangan, Status, Tgl) --}}
        <td colspan="7" class="text-center">Data tidak ditemukan pada periode ini.</td>
    </tr>
@endforelse
</tbody>
</table>
</body>
</html>