<?php $__env->startSection('title', 'Laporan Material Retur'); ?>

<?php $__env->startSection('content'); ?>
<div class="card-new">
    
    <div class="index-header">
        <h2>LAPORAN MATERIAL RETUR</h2>
        
        <form action="<?php echo e(route('material-retur.index')); ?>" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material/Petugas..." value="<?php echo e(request('search')); ?>">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_mulai" class="form-control-tanggal" value="<?php echo e(request('tanggal_mulai')); ?>" title="Tanggal Mulai">
            </div>
            <div class="form-group-tanggal-filter">
                <input type="date" name="tanggal_akhir" class="form-control-tanggal" value="<?php echo e(request('tanggal_akhir')); ?>" title="Tanggal Akhir">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="<?php echo e(route('material-retur.index')); ?>" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah Retur</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($items->firstItem() + $loop->index); ?></td>
                        <td><?php echo e($item->material->nama_material ?? 'N/A'); ?></td>
                        <td><?php echo e($item->nama_petugas); ?></td>
                        <td><?php echo e($item->jumlah); ?></td>
                        <td>
                            <?php if($item->status == 'baik'): ?>
                                <span style="color: #198754; font-weight: 500;">Baik</span>
                            <?php else: ?>
                                <span style="color: #d06368ff; font-weight: 500;">Rusak</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($item->keterangan, 30) ?? '-'); ?></td>
                        
                        <!-- Format tanggal WITA -->
                        <td><?php echo e(\Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i')); ?></td>
                        
                        <td style="text-align: center; vertical-align: top;"> 
                            <?php if($item->foto_path): ?>
                                <!-- Gunakan ltrim agar path aman -->
                                <img src="<?php echo e(asset('storage/' . ltrim($item->foto_path, '/'))); ?>" alt="Foto Material" class="table-foto" style="cursor: pointer;" title="Klik untuk memperbesar">
                                <a href="<?php echo e(route('material-retur.download-foto', $item->id)); ?>" class="btn-foto-download" title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <!-- Tombol Lihat SUDAH DIHAPUS -->
                                <a href="<?php echo e(route('material-retur.edit', $item->id)); ?>" class="btn btn-edit">Edit</a>
                                <form action="<?php echo e(route('material-retur.destroy', $item->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        <?php echo e($items->appends(request()->query())->links()); ?>

    </div>

    <div class="index-footer-form">
        <form action="<?php echo e(route('material-retur.download-report')); ?>" method="GET" class="form-download">
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh Pdf
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_retur/index.blade.php ENDPATH**/ ?>