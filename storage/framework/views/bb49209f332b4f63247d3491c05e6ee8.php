

<?php $__env->startSection('title', 'Lihat Material Stand By'); ?>

<?php $__env->startSection('content'); ?>
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Detail Material Stand By</h2>
    </div>

    <div class="card-form-body">
        
            <div class="form-group-new">
                <label>Nama Material</label>
                <input type="text" class="form-control-new" value="<?php echo e($item->material->nama_material ?? 'N/A'); ?>" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Nama Petugas</label>
                <input type="text" class="form-control-new" value="<?php echo e($item->nama_petugas); ?>" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Jumlah/Unit</label>
                <input type="number" class="form-control-new" value="<?php echo e($item->jumlah); ?>" disabled>
            </div>
            
            <div class="form-group-new">
                <label>Tanggal dan Jam (WITA)</label>
                <input type="text" class="form-control-new" value="<?php echo e($item->tanggal->format('d M Y, H:i')); ?>" disabled>
            </div>

            <div class="form-group-new">
                <label>Foto</label>
                <?php if($item->foto_path): ?>
                    <img src="<?php echo e(asset('storage/' . $item->foto_path)); ?>" alt="Foto Material" style="width: 100%; max-width: 400px; border-radius: 10px; display:block;">
                <?php else: ?>
                    <p>Tidak ada foto yang diunggah.</p>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="justify-content: flex-start;">
                <a href="<?php echo e(route('material-stand-by.index')); ?>" class="btn-batal">Kembali</a>
            </div>
        
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_stand_by/show.blade.php ENDPATH**/ ?>