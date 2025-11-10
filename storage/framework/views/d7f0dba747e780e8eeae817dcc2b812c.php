<?php $__env->startSection('title', 'Material Keluar - SIMAS-PLN'); ?>

<?php $__env->startSection('content'); ?>
<div class="card-new">
    
    <div class="index-header">
        <h2>MATERIAL KELUAR</h2>

        <!-- 🔍 FORM SEARCH -->
        <form action="<?php echo e(route('material_keluar.index')); ?>" method="GET" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari Nama Material / Petugas..." value="<?php echo e(request('search')); ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <a href="<?php echo e(route('material_keluar.index')); ?>" class="btn btn-secondary btn-sm">Reset</a>
        </form>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success text-center"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <!-- 📋 TABEL DATA -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Material</th>
                    <th>Nama Petugas</th>
                    <th>Jumlah/Unit</th>
                    <th>Tanggal (WITA)</th>
                    <th>Foto & Download</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $materialKeluar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($materialKeluar->firstItem() + $loop->index); ?></td>
                        <td><?php echo e($item->nama_material); ?></td>
                        <td><?php echo e($item->nama_petugas); ?></td>
                        <td><?php echo e($item->jumlah_material); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Makassar')->format('d M Y, H:i')); ?></td>

                        <!-- FOTO + DOWNLOAD -->
                        <td style="text-align: center; vertical-align: top;">
                            <?php if($item->foto): ?>
                                <img src="<?php echo e(asset('storage/' . $item->foto)); ?>" 
                                     alt="Foto Material" 
                                     class="table-foto"
                                     title="Klik untuk memperbesar">

                                <a href="<?php echo e(asset('storage/' . $item->foto)); ?>" 
                                   download 
                                   class="btn-foto-download" 
                                   title="Download Foto">
                                    <i class="fas fa-download"></i> Download Foto
                                </a>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>

                        <!-- AKSI -->
                        <td>
                            <div class="table-actions">
                                <a href="<?php echo e(route('material_keluar.lihat', $item->id)); ?>" class="btn-lihat">Lihat</a>
                                <a href="<?php echo e(route('material_keluar.edit', $item->id)); ?>" class="btn btn-edit">Edit</a>
                                <form action="<?php echo e(route('material_keluar.destroy', $item->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6c757d; padding:50px 0;">Data tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div style="margin-top: 20px;">
        <?php echo e($materialKeluar->appends(request()->query())->links()); ?>

    </div>

    <!-- 📥 FOOTER: UNDUH PDF / EXCEL -->
    <div class="index-footer-form">
        <form action="<?php echo e(route('material_keluar.download')); ?>" method="POST" class="form-download">
            <?php echo csrf_field(); ?>
            <div class="form-group-tanggal">
                <label for="tanggal_mulai_pdf">Dari Tanggal:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_pdf" class="form-control-tanggal" required>
            </div>
            <div class="form-group-tanggal">
                <label for="tanggal_akhir_pdf">Sampai Tanggal:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir_pdf" class="form-control-tanggal" required>
            </div>
            <button type="submit" name="submit_pdf" value="1" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </button>
            <button type="submit" name="submit_excel" value="1" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_keluar/index.blade.php ENDPATH**/ ?>