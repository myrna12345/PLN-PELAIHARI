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
        .img-report { width: 60px; height: auto; display: block; margin: 0 auto; }
        </style>
    </head>
    <body>
    <h2>LAPORAN SIAGA KEMBALI</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M Y') }}</p>
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
        <th>Foto Material</th>
        <th>Foto Petugas</th>
    </tr>
</thead>
    <tbody>
    @forelse ($items as $index => $item)
    <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>
            {{ $item->material->nama_material ?? 'N/A' }} 
            @if ($item->nomor_meter)
                - {{ $item->nomor_meter }}
            @endif
        </td>
        <td>{{ $item->nama_petugas }}</td>
        <td>{{ $item->stand_meter ?? '-' }}</td>
        <td>{{ $item->keterangan }}</td>
        <td>{{ $item->status ?? 'Kembali' }}</td>
        <td>{{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</td>
        
        {{-- Foto Material --}}
        <td>
            @if($item->foto_path && file_exists(public_path('uploads/siaga_kembali/' . $item->foto_path)))
                <img src="{{ public_path('uploads/siaga_kembali/' . $item->foto_path) }}" class="img-report">
            @else
                <div class="text-center">-</div>
            @endif
        </td>

        {{-- Foto Petugas --}}
        <td>
            @if($item->foto_petugas && file_exists(public_path('uploads/siaga_kembali/' . $item->foto_petugas)))
                <img src="{{ public_path('uploads/siaga_kembali/' . $item->foto_petugas) }}" class="img-report">
            @else
                <div class="text-center">-</div>
            @endif
        </td>
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