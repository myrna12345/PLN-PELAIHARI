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
    <p>Periode: <?php echo e(\Carbon\Carbon::parse($tanggal_mulai)->format('d M Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($tanggal_akhir)->format('d M Y')); ?></p>

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
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td><?php echo e($item->material->nama_material ?? 'N/A'); ?></td>
                    <td><?php echo e($item->nama_petugas); ?></td>
                    <td class="text-center"><?php echo e($item->jumlah); ?></td>
                    <td class="text-center"><?php echo e($item->material_keluar ?? 0); ?></td>
                    <td class="text-center"><?php echo e($item->material_kembali ?? 0); ?></td>
                    <td><?php echo e($item->status == 'baik' ? 'Baik' : 'Rusak'); ?></td>
                    <td><?php echo e($item->keterangan); ?></td>
                    <td><?php echo e($item->tanggal->setTimezone('Asia/Makassar')->format('d M Y, H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="text-center">Data tidak ditemukan pada periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_retur/laporan_pdf.blade.php ENDPATH**/ ?>