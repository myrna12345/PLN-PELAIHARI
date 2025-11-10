<?php $__env->startSection('title', 'Tambah Material Keluar - SIMAS-PLN'); ?>

<?php $__env->startSection('content'); ?>
<div class="card-form-container mx-auto">
    <div class="card-form-header">
        <h2>Tambah Material Keluar</h2>
    </div>

    <div class="card-form-body">
        <form action="<?php echo e(route('material_keluar.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            
            <div class="form-group-new">
                <label for="nama_material">Nama Material</label>
                <select name="nama_material" id="nama_material" class="form-control-new" required>
                    <option value="">-- Pilih Material --</option>
                    <?php $__currentLoopData = $materialList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($material->nama_material); ?>"><?php echo e($material->nama_material); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['nama_material'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:red;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-group-new">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" name="nama_petugas" id="nama_petugas" class="form-control-new" placeholder="Masukkan nama petugas" required>
                <?php $__errorArgs = ['nama_petugas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:red;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-group-new">
                <label for="jumlah_material">Jumlah Material Keluar</label>
                <input type="number" name="jumlah_material" id="jumlah_material" class="form-control-new" placeholder="Masukkan jumlah material" required>
                <?php $__errorArgs = ['jumlah_material'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:red;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-group-new">
                <label for="tanggal">Tanggal dan Waktu</label>
                <input type="datetime-local" id="tanggal" name="tanggal" class="form-control-new" required>
            </div>

            
            <div class="form-group-new">
                <label for="foto">Unggah Foto</label>
                <input type="file" name="foto" id="foto" class="form-control-new-file" accept="image/*">
                <?php $__errorArgs = ['foto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:red;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-actions">
                <a href="<?php echo e(route('material_keluar.index')); ?>" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(session('success')): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?php echo e(session('success')); ?>',
    showConfirmButton: false,
    timer: 2000
});
</script>
<?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputTanggal = document.getElementById('tanggal');
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const localDatetime = `${year}-${month}-${day}T${hours}:${minutes}`;
    inputTanggal.value = localDatetime;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/material_keluar/create.blade.php ENDPATH**/ ?>