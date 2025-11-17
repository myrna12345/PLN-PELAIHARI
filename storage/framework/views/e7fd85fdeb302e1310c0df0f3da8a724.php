<?php $__env->startSection('title', 'Tambah Material Retur'); ?>

<?php $__env->startSection('content'); ?>
<div class="card-form-container">
    <div class="card-form-header">
        <h2>Tambah Material Retur</h2>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card-form-body">
        
        <form action="<?php echo e(route('material-retur.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            
            <div class="form-group-new">
                <label for="material_id">Nama Material</label>
                <select name="material_id" id="material_id" class="form-control-new" required>
                    <option value="" disabled selected>Pilih material...</option>
                    <?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($material->id); ?>" <?php echo e(old('material_id') == $material->id ? 'selected' : ''); ?>>
                            <?php echo e($material->nama_material); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" value="<?php echo e(old('nama_petugas')); ?>" required>
            </div>
            
            <div class="form-group-new">
                <label for="jumlah">Jumlah Retur</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control-new" value="<?php echo e(old('jumlah')); ?>" required>
            </div>
            
            <div class="form-group-new">
                <label for="status">Status Material</label>
                <select name="status" id="status" class="form-control-new" required>
                    <option value="" disabled selected>Pilih status...</option>
                    <option value="baik" <?php echo e(old('status') == 'baik' ? 'selected' : ''); ?>>Andal Bagus</option>
                    <option value="rusak" <?php echo e(old('status') == 'rusak' ? 'selected' : ''); ?>>Rusak</option>
                </select>
            </div>

            <!-- Input Tanggal READONLY (Otomatis Waktu Sekarang) -->
            <div class="form-group-new">
                <label>Tanggal dan Jam</label>
                <input type="text" 
                       class="form-control-new" 
                       style="background-color: #e9ecef; cursor: not-allowed;"
                       value="<?php echo e(\Carbon\Carbon::now('Asia/Makassar')->format('d M Y, H:i')); ?>"
                       readonly>
                <small class="text-muted" style="display: block; margin-top: 5px; color: #6c757d;">
                    Waktu akan otomatis terisi saat data disimpan.
                </small>
            </div>

            <div class="form-group-new">
                <label for="keterangan">Keterangan (Opsional)</label>
                <textarea name="keterangan" id="keterangan" class="form-control-new" rows="3"><?php echo e(old('keterangan')); ?></textarea>
            </div>

            <div class="form-group-new">
                <label for="foto">Unggah Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_retur/create.blade.php ENDPATH**/ ?>