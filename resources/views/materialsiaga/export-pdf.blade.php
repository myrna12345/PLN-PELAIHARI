<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Material Siaga Standby</title>
    <style>
        body{
            font-family: sans-serif;
            font-size: 12px;
        }
        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td{
            border: 1px solid black;
        }
        th{
            background: #eaeaea;
            padding: 6px;
        }
        td{
            padding: 5px;
            text-align: center;
        }
        h2, p{
            text-align: center;
            margin: 0;
        }
        .foto{
            width: 60px;
            height: auto;
        }
    </style>
</head>
<body>

    <h2>LAPORAN MATERIAL SIAGA STANDBY</h2>
    <p>Periode: {{ $start_date }} s/d {{ $end_date }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Material</th>
                <th>Nama Petugas</th>
                <th>Stand Meter</th>
                <th>Jumlah Material Siaga Standby</th>
                <th>Tanggal</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index+1 }}</td>
                <td>{{ $item->material?->nama_material }}</td>
                <td>{{ $item->nama_petugas }}</td>
                <td>{{ $item->stand_meter }}</td>
                <td>{{ $item->jumlah_material }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>
                    @if($item->foto)
                        <img src="{{ public_path('storage/' . $item->foto) }}" class="foto">
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
