<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['record', 'columns', 'types', 'fkHints', 'title' => 'All Fields']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['record', 'columns', 'types', 'fkHints', 'title' => 'All Fields']); ?>
<?php foreach (array_filter((['record', 'columns', 'types', 'fkHints', 'title' => 'All Fields']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?php echo e($title); ?></h5>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="devToolsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Developer Tools
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="devToolsDropdown">
                <li><a class="dropdown-item" href="#" onclick="showRawJson(event); return false;">View Raw JSON</a></li>
                <li><a class="dropdown-item" href="#" onclick="copyCsv(event); return false;">Copy CSV</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Field</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 60%;">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $columnName = is_array($column) ? $column['column'] : $column;
                            $columnType = $types[$columnName] ?? 'unknown';
                            $isFk = $fkHints[$columnName] ?? false;
                            $value = $record->{$columnName} ?? null;
                            $isNull = $value === null;
                            $isBoolean = in_array(strtolower($columnType), ['boolean', 'tinyint(1)', 'bit']);
                            $isDate = in_array(strtolower($columnType), ['date', 'datetime', 'timestamp']);
                            $isJson = in_array(strtolower($columnType), ['json', 'jsonb']) || is_array($value) || (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '[')));
                            $isLongString = is_string($value) && mb_strlen($value) > 200;
                            $isPath = str_ends_with($columnName, '_path') || str_ends_with($columnName, '_url');
                            
                            // Try to resolve FK relation
                            $fkLabel = null;
                            $relationName = null;
                            if ($isFk && !$isNull) {
                                // Try common relation name patterns
                                $possibleRelationNames = [
                                    str_replace('_id', '', $columnName),
                                    str_replace('_id', 'Ref', $columnName),
                                    lcfirst(str_replace('_', '', ucwords(str_replace('_id', '', $columnName), '_'))),
                                ];
                                
                                foreach ($possibleRelationNames as $relName) {
                                    try {
                                        if (method_exists($record, $relName)) {
                                            $related = $record->{$relName};
                                            if ($related) {
                                                $relationName = $relName;
                                                $fkLabel = $related->name ?? 
                                                          $related->client_name_ar ?? 
                                                          $related->client_name_en ?? 
                                                          $related->matter_name_ar ?? 
                                                          $related->matter_name_en ?? 
                                                          $related->opponent_name_ar ??
                                                          $related->opponent_name_en ??
                                                          $related->court_name_ar ??
                                                          $related->court_name_en ??
                                                          $related->lawyer_name_ar ??
                                                          $related->lawyer_name_en ??
                                                          $related->title ?? 
                                                          $related->label_ar ??
                                                          $related->label_en ??
                                                          (string)$related->id;
                                                if (mb_strlen($fkLabel) > 60) {
                                                    $fkLabel = mb_substr($fkLabel, 0, 60) . '...';
                                                }
                                                break;
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Relation doesn't exist or failed, try next
                                        continue;
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td><code><?php echo e($columnName); ?></code></td>
                            <td>
                                <small class="text-muted"><?php echo e($columnType); ?></small>
                                <?php if($isFk): ?>
                                    <span class="badge bg-info ms-1" title="Foreign Key">FK</span>
                                <?php endif; ?>
                            </td>
                            <td dir="auto">
                                <?php if($isNull): ?>
                                    <span class="text-muted">NULL</span>
                                <?php elseif($isBoolean): ?>
                                    <span class="badge <?php echo e($value ? 'bg-success' : 'bg-secondary'); ?>">
                                        <?php echo e($value ? 'Yes' : 'No'); ?>

                                    </span>
                                <?php elseif($isDate): ?>
                                    <div>
                                        <span><?php echo e($value); ?></span>
                                        <?php if($value): ?>
                                            <small class="text-muted d-block"><?php echo e(\Carbon\Carbon::parse($value)->diffForHumans()); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif($isJson): ?>
                                    <?php
                                        $jsonValue = is_string($value) ? json_decode($value, true) : $value;
                                        $jsonPretty = json_encode($jsonValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                    ?>
                                    <div>
                                        <button class="btn btn-sm btn-outline-secondary mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#json-<?php echo e($loop->index); ?>" aria-expanded="false">
                                            Toggle JSON
                                        </button>
                                        <div class="collapse" id="json-<?php echo e($loop->index); ?>">
                                            <pre class="mb-0 bg-light p-2 rounded" style="max-height: 300px; overflow-y: auto;"><code><?php echo e($jsonPretty); ?></code></pre>
                                        </div>
                                    </div>
                                <?php elseif($isLongString): ?>
                                    <div>
                                        <span id="short-<?php echo e($loop->index); ?>"><?php echo e(mb_substr($value, 0, 200)); ?>...</span>
                                        <button class="btn btn-sm btn-link p-0 ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#full-<?php echo e($loop->index); ?>" aria-expanded="false">
                                            Show more
                                        </button>
                                        <div class="collapse" id="full-<?php echo e($loop->index); ?>">
                                            <div class="mt-2"><?php echo e($value); ?></div>
                                        </div>
                                    </div>
                                <?php elseif($isPath && $value): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e($value); ?></span>
                                        <?php if(file_exists(public_path($value)) || file_exists(storage_path('app/' . $value))): ?>
                                            <a href="<?php echo e(asset($value)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif($isFk && $fkLabel && $relationName): ?>
                                    <div>
                                        <span class="text-muted"><?php echo e($value); ?></span>
                                        <span class="ms-2">→</span>
                                        <?php
                                            $related = $record->{$relationName};
                                            $fullLabel = $related->name ?? 
                                                         $related->client_name_ar ?? 
                                                         $related->client_name_en ?? 
                                                         $related->matter_name_ar ?? 
                                                         $related->matter_name_en ?? 
                                                         $related->opponent_name_ar ??
                                                         $related->opponent_name_en ??
                                                         $related->court_name_ar ??
                                                         $related->court_name_en ??
                                                         $related->lawyer_name_ar ??
                                                         $related->lawyer_name_en ??
                                                         $related->title ?? 
                                                         $related->label_ar ??
                                                         $related->label_en ??
                                                         (string)$related->id;
                                        ?>
                                        <span title="<?php echo e($fullLabel); ?>"><?php echo e($fkLabel); ?></span>
                                    </div>
                                <?php else: ?>
                                    <?php if($value === ''): ?>
                                        <span class="text-muted">(empty string)</span>
                                    <?php else: ?>
                                        <?php echo e($value); ?>

                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Raw JSON Modal -->
<div class="modal fade" id="rawJsonModal" tabindex="-1" aria-labelledby="rawJsonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rawJsonModalLabel">Raw JSON</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre id="rawJsonContent" class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"><code></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function showRawJson(event) {
    event.preventDefault();
    try {
        const record = <?php echo json_encode($record->toArray(), 15, 512) ?>;
        const jsonStr = JSON.stringify(record, null, 2);
        const contentEl = document.getElementById('rawJsonContent');
        if (contentEl) {
            contentEl.textContent = jsonStr;
            const modal = new bootstrap.Modal(document.getElementById('rawJsonModal'));
            modal.show();
        }
    } catch (e) {
        console.error('Error showing raw JSON:', e);
        alert('Error displaying JSON. Check console for details.');
    }
}

function copyCsv(event) {
    event.preventDefault();
    try {
        const columns = <?php echo json_encode($columns, 15, 512) ?>;
        const record = <?php echo json_encode($record->toArray(), 15, 512) ?>;
        
        let csv = 'Field,Value\n';
        columns.forEach(col => {
            const colName = typeof col === 'object' ? col.column : col;
            let value = record[colName] ?? '';
            // Handle null, objects, arrays
            if (value === null) {
                value = '';
            } else if (typeof value === 'object') {
                value = JSON.stringify(value);
            }
            // Escape quotes and newlines for CSV
            value = String(value).replace(/"/g, '""').replace(/\n/g, ' ');
            csv += `"${colName}","${value}"\n`;
        });
        
        navigator.clipboard.writeText(csv).then(() => {
            alert('CSV copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy:', err);
            // Fallback: create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = csv;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('CSV copied to clipboard!');
        });
    } catch (e) {
        console.error('Error copying CSV:', e);
        alert('Error copying CSV. Check console for details.');
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/components/admin/all-fields-table.blade.php ENDPATH**/ ?>