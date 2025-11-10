<?php $__env->startSection('title', 'Dashboard - SIMAS-PLN'); ?>

<?php $__env->startSection('content'); ?>
    <h2>Sistem Informasi Pengelolaan Material Stand By di Gudang Kecil-PLN</h2>
        
    <div class="widget-grid">
        <div class="widget-card salmon-red"> 
            <div class="widget-icon"><i class="fas fa-box-open"></i></div>
            <div class="widget-info">
                <h3>Material Stand By</h3>
                <p>Material stand by di gudang kecil : <?php echo e($totalStandBy ?? 0); ?> unit</p>
            </div>
        </div>

        <div class="widget-card salmon-red"> 
            <div class="widget-icon"><i class="fas fa-tools"></i></div>
            <div class="widget-info">
                <h3>Material Keluar</h3>
                <p>Pemasangan Hari ini : <?php echo e($materialKeluarHariIni ?? 0); ?> Lokasi</p>
            </div>
        </div>

        <div class="widget-card salmon-red"> 
            <div class="widget-icon"><i class="fas fa-undo"></i></div>
            <div class="widget-info">
                <h3>Material Retur</h3>
                <div class="retur-list">
                    Bekas Andal : <?php echo e($returAndal ?? 0); ?><br>
                    Rusak : <?php echo e($returRusak ?? 0); ?>

                </div>
            </div>
        </div>
        
        <div class="widget-card blue"> 
            <div class="widget-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="widget-info">
                <h3>Material Kembali</h3>
                <p>Material Kembali: <?php echo e($totalMaterialKembali ?? 0); ?> unit</p>
            </div>
        </div>

        <div class="widget-card purple"> 
            <div class="widget-icon"><i class="fas fa-box-archive"></i></div>
            <div class="widget-info">
                <h3> Siaga Stand By</h3>
                <p>Material Kembali: <?php echo e($totalSiagaStandBy ?? 0); ?> unit</p>
            </div>
        </div>

        <div class="widget-card green"> 
            <div class="widget-icon"><i class="fas fa-truck"></i></div>
            <div class="widget-info">
                <h3>Siaga Keluar</h3>
                <p>Material Kembali: <?php echo e($totalSiagaKeluar ?? 0); ?> unit</p>
            </div>
        </div>
        
        <div class="widget-card mauve"> 
            <div class="widget-icon"><i class="fas fa-sync-alt"></i></div>
            <div class="widget-info">
                <h3>Siaga Kembali</h3>
                <p>Material Kembali: <?php echo e($totalSiagaKembali ?? 0); ?> unit</p>
            </div>
        </div>
        
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\KULIAH\PKL\GITHUB\PLN PELAIHARI\PLN-PELAIHARI\resources\views/dashboard.blade.php ENDPATH**/ ?>