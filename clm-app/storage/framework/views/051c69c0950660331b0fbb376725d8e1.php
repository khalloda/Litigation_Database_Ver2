<?php
    $fieldConfig = \App\Support\Cases\FieldMap::get($field);
    $label = app()->getLocale() === 'ar' ? ($fieldConfig['label_ar'] ?? $fieldConfig['label_en']) : $fieldConfig['label_en'];
    $value = $case->$field ?? null;
    $format = $fieldConfig['format'] ?? 'text';
?>

<tr>
    <td class="fw-bold" style="width: 30%;"><?php echo e($label); ?></td>
    <td style="width: 70%;">
        <?php if($format === 'raw'): ?>
            <?php echo \App\Support\View\Format::raw($value); ?>

        <?php elseif($format === 'text'): ?>
            <?php echo \App\Support\View\Format::text($value); ?>

        <?php elseif($format === 'date'): ?>
            <?php echo \App\Support\View\Format::date($value); ?>

        <?php elseif($format === 'datetime'): ?>
            <?php echo \App\Support\View\Format::datetime($value); ?>

        <?php elseif($format === 'money'): ?>
            <?php echo \App\Support\View\Format::money($value); ?>

        <?php elseif($format === 'boolean'): ?>
            <?php echo \App\Support\View\Format::boolean($value); ?>

        <?php elseif($format === 'person'): ?>
            <?php echo \App\Support\View\Format::person($value); ?>

        <?php elseif($format === 'longtext'): ?>
            <?php echo \App\Support\View\Format::longtext($value); ?>

        <?php elseif(str_starts_with($format, 'fk:')): ?>
            <?php
                $formatParts = explode(':', $format);
                $relation = $formatParts[1] ?? null;
                if (isset($formatParts[2]) && $formatParts[2] === 'option') {
                    $relation = $formatParts[3] ?? $relation;
                }
            ?>
            <?php echo \App\Support\View\Format::fk($case, $relation, $value, $format); ?>

        <?php else: ?>
            <?php echo \App\Support\View\Format::text($value); ?>

        <?php endif; ?>
    </td>
</tr>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views\cases\partials\_field_row.blade.php ENDPATH**/ ?>