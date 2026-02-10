<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Material</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .text-left { text-align: left; }
        .img-pdf { width: 60px; height: auto; border: 0.5px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PT PLN (PERSERO) ULP PELAIHARI</h2>
        <p>LAPORAN RIWAYAT PENAMBAHAN MATERIAL STANDBY</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="25%">NAMA MATERIAL</th>
                <th width="10%">JUMLAH</th> {{-- Kolom Dipisah --}}
                <th width="10%">SATUAN</th> {{-- Kolom Dipisah --}}
                <th width="25%">TANGGAL INPUT</th>
                <th width="25%">FOTO MATERIAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $h)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ strtoupper($h->nama_material) }}</td>
                {{-- Menghapus tanda + --}}
                <td>{{ ltrim($h->jumlah, '+ ') }}</td>
                <td>{{ strtoupper($h->satuan) }}</td>
                <td>{{ \Carbon\Carbon::parse($h->tanggal_input)->format('d/m/Y H:i') }} WITA</td>
                <td>
                    @php
                        $path = public_path('uploads/material_stand_by/' . $h->foto_path);
                    @endphp
                    @if($h->foto_path && file_exists($path))
                        <img src="{{ $path }}" class="img-pdf">
                    @else
                        <i style="color: #999; font-size: 10px;">Tidak Ada Foto</i>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>