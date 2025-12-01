<div class="report-header" style="width: 100%; display: table; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; font-family: 'Cairo', 'Noto Kufi Arabic', 'Tahoma', 'Arial', sans-serif;">
    <div style="display: table-cell; vertical-align: middle; width: 22%; <?php echo e(($locale ?? 'ar') === 'ar' ? 'text-align: right;' : 'text-align: left;'); ?>">
        <?php if(!empty($firmLogoPath) && file_exists($firmLogoPath)): ?>
            <img src="<?php echo e($firmLogoPath); ?>" alt="Firm Logo" style="max-height: 60px; width: auto; max-width: 100%;">
        <?php endif; ?>
    </div>
    <div style="display: table-cell; vertical-align: middle; text-align: center; <?php echo e(!empty($clientName) ? 'color: #b91c1c; font-weight: 700; font-size: 18px;' : ''); ?>">
        <?php echo e($clientName ?? ''); ?>

    </div>
    <div style="display: table-cell; vertical-align: middle; width: 22%; <?php echo e(($locale ?? 'ar') === 'ar' ? 'text-align: left;' : 'text-align: right;'); ?>">
        <?php if(!empty($clientLogoPath) && file_exists($clientLogoPath)): ?>
            <img src="<?php echo e($clientLogoPath); ?>" alt="Client Logo" style="max-height: 60px; width: auto; max-width: 100%;">
        <?php endif; ?>
    </div>
</div>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/partials/header.blade.php ENDPATH**/ ?>