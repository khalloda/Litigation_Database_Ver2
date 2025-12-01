<div class="footer">
    <div>
        <?php echo e(($locale ?? 'ar') === 'ar' ? 'تاريخ الإصدار' : 'Generated At'); ?>: 
        <?php echo e($generatedAt->format(($locale ?? 'ar') === 'ar' ? 'Y-m-d H:i' : 'Y-m-d H:i')); ?>

    </div>
    <?php if(isset($pageNumbers) && $pageNumbers): ?>
        <div>
            <?php echo e(($locale ?? 'ar') === 'ar' ? 'الصفحة' : 'Page'); ?> <span class="page"></span> 
            <?php echo e(($locale ?? 'ar') === 'ar' ? 'من' : 'of'); ?> <span class="topage"></span>
        </div>
    <?php endif; ?>
</div>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/reports/partials/footer.blade.php ENDPATH**/ ?>