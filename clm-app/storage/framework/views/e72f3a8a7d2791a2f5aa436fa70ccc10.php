<div class="card">
    <div class="card-body">
        <table class="table table-borderless table-sm">
            <tbody>
                <?php $__currentLoopData = \App\Support\Cases\FieldMap::bySection($section); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('cases.partials._field_row', ['field' => $field], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/cases/partials/_section_content.blade.php ENDPATH**/ ?>