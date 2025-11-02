<div class="longtext-container">
    <div id="preview-<?php echo e($id); ?>" dir="auto" class="text-wrap text-break">
        <?php echo e(nl2br(e($preview))); ?>...
        <a href="#" class="show-more-link" data-target="full-<?php echo e($id); ?>" data-preview="preview-<?php echo e($id); ?>">
            <?php echo e(__('app.show_more')); ?>

        </a>
    </div>
    <div id="full-<?php echo e($id); ?>" dir="auto" class="text-wrap text-break" style="display: none;">
        <?php echo e(nl2br(e($full))); ?>

        <a href="#" class="show-less-link" data-target="full-<?php echo e($id); ?>" data-preview="preview-<?php echo e($id); ?>">
            <?php echo e(__('app.show_less')); ?>

        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.show-more-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const previewId = this.getAttribute('data-preview');
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(targetId).style.display = 'block';
        });
    });

    document.querySelectorAll('.show-less-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const previewId = this.getAttribute('data-preview');
            document.getElementById(targetId).style.display = 'none';
            document.getElementById(previewId).style.display = 'block';
        });
    });
});
</script>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/partials/_longtext.blade.php ENDPATH**/ ?>