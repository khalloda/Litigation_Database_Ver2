
<div class="modal fade" id="fuzzyMatchingModal" tabindex="-1" aria-labelledby="fuzzyMatchingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fuzzyMatchingModalLabel">
                    <i class="fas fa-search me-2"></i>
                    <?php echo e(__('app.fuzzy_matching_choice')); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-info">
                    <strong><?php echo e(__('app.field')); ?>:</strong> <span id="fuzzy-field-name"></span><br>
                    <strong><?php echo e(__('app.search_value')); ?>:</strong> <span id="fuzzy-search-value" dir="auto"></span>
                </div>

                
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    <?php echo e(__('app.select_from_existing')); ?>

                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="existing-choices-container">
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden"><?php echo e(__('app.loading')); ?>...</span>
                                        </div>
                                        <p class="mt-2"><?php echo e(__('app.loading_choices')); ?>...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-plus me-2"></i>
                                    <?php echo e(__('app.create_new_value')); ?>

                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="create-new-container">
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden"><?php echo e(__('app.loading')); ?>...</span>
                                        </div>
                                        <p class="mt-2"><?php echo e(__('app.preparing_create_form')); ?>...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mt-4 text-center">
                    <button type="button" class="btn btn-primary" id="apply-choice-btn" disabled>
                        <i class="fas fa-check me-2"></i>
                        <?php echo e(__('app.apply_choice')); ?>

                    </button>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        <?php echo e(__('app.cancel')); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<form id="fuzzy-choice-form" style="display: none;">
    <input type="hidden" id="fuzzy-field" name="field">
    <input type="hidden" id="fuzzy-search-value-input" name="search_value">
    <input type="hidden" id="fuzzy-choice-type" name="choice_type">
    <input type="hidden" id="fuzzy-choice-data" name="choice_data">
    <input type="hidden" id="fuzzy-import-session-id" name="import_session_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFuzzyData = null;
    let selectedChoice = null;

    // Initialize fuzzy matching modal
    window.initFuzzyMatchingModal = function(field, searchValue, importSessionId) {
        currentFuzzyData = {
            field: field,
            searchValue: searchValue,
            importSessionId: importSessionId
        };

        // Update modal content
        document.getElementById('fuzzy-field-name').textContent = field;
        document.getElementById('fuzzy-search-value').textContent = searchValue;
        document.getElementById('fuzzy-field').value = field;
        document.getElementById('fuzzy-search-value-input').value = searchValue;
        document.getElementById('fuzzy-import-session-id').value = importSessionId;

        // Load choices
        loadFuzzyChoices(field, searchValue, importSessionId);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('fuzzyMatchingModal'));
        modal.show();
    };

    // Load fuzzy matching choices
    function loadFuzzyChoices(field, searchValue, importSessionId) {
        fetch(`<?php echo e(route('fuzzy-matching.choices')); ?>?field=${encodeURIComponent(field)}&search_value=${encodeURIComponent(searchValue)}&import_session_id=${importSessionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderExistingChoices(data.choices.choices);
                    renderCreateForm(data.choices);
                } else {
                    showFuzzyError('Failed to load choices');
                }
            })
            .catch(error => {
                console.error('Error loading fuzzy choices:', error);
                showFuzzyError('Error loading choices: ' + error.message);
            });
    }

    // Render existing choices
    function renderExistingChoices(choices) {
        const container = document.getElementById('existing-choices-container');

        if (choices.length === 0) {
            container.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-search fa-2x text-muted mb-2"></i>
                    <p class="text-muted"><?php echo e(__('app.no_existing_values_found')); ?></p>
                </div>
            `;
            return;
        }

        let html = '<div class="list-group">';
        choices.forEach((choice, index) => {
            html += `
                <label class="list-group-item list-group-item-action cursor-pointer" for="choice-${index}">
                    <input class="form-check-input me-3" type="radio" name="existing-choice" id="choice-${index}" value="${choice.id}" data-choice='${JSON.stringify(choice)}'>
                    <div>
                        <div class="fw-bold" dir="auto">${choice.display || choice.name_ar || choice.label_ar}</div>
                        ${choice.name_en || choice.label_en ? `<small class="text-muted" dir="auto">${choice.name_en || choice.label_en}</small>` : ''}
                        ${choice.email ? `<br><small class="text-muted">${choice.email}</small>` : ''}
                    </div>
                </label>
            `;
        });
        html += '</div>';

        container.innerHTML = html;

        // Add event listeners for radio buttons
        document.querySelectorAll('input[name="existing-choice"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectedChoice = {
                        type: 'existing',
                        data: JSON.parse(this.dataset.choice)
                    };
                    updateApplyButton();
                }
            });
        });
    }

    // Render create form
    function renderCreateForm(choicesData) {
        const container = document.getElementById('create-new-container');

        if (!choicesData.can_create) {
            container.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-ban fa-2x text-muted mb-2"></i>
                    <p class="text-muted"><?php echo e(__('app.cannot_create_new_value')); ?></p>
                </div>
            `;
            return;
        }

        const suggestion = choicesData.create_suggestion;
        let html = '';

        if (suggestion.type === 'lawyer') {
            html = `
                <form id="create-lawyer-form">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.lawyer_name_arabic')); ?></label>
                        <input type="text" class="form-control" name="lawyer_name_ar" value="${suggestion.suggestion.lawyer_name_ar}" dir="auto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.lawyer_name_english')); ?></label>
                        <input type="text" class="form-control" name="lawyer_name_en" value="${suggestion.suggestion.lawyer_name_en}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.email')); ?></label>
                        <input type="email" class="form-control" name="email" value="${suggestion.suggestion.email}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.title')); ?></label>
                        <input type="text" class="form-control" name="title" value="${suggestion.suggestion.title}">
                    </div>
                </form>
            `;
        } else if (suggestion.type === 'court') {
            html = `
                <form id="create-court-form">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.court_name_arabic')); ?></label>
                        <input type="text" class="form-control" name="court_name_ar" value="${suggestion.suggestion.court_name_ar}" dir="auto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.court_name_english')); ?></label>
                        <input type="text" class="form-control" name="court_name_en" value="${suggestion.suggestion.court_name_en}" required>
                    </div>
                </form>
            `;
        } else if (suggestion.type === 'option_value') {
            html = `
                <form id="create-option-form">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.label_arabic')); ?></label>
                        <input type="text" class="form-control" name="label_ar" value="${suggestion.suggestion.label_ar}" dir="auto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('app.label_english')); ?></label>
                        <input type="text" class="form-control" name="label_en" value="${suggestion.suggestion.label_en}" required>
                    </div>
                </form>
            `;
        }

        container.innerHTML = html;

        // Add create button
        const createButton = document.createElement('button');
        createButton.type = 'button';
        createButton.className = 'btn btn-success btn-sm w-100';
        createButton.innerHTML = '<i class="fas fa-plus me-2"></i><?php echo e(__("app.create_new")); ?>';
        createButton.addEventListener('click', function() {
            const form = container.querySelector('form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            selectedChoice = {
                type: 'create',
                data: data
            };
            updateApplyButton();
        });

        container.appendChild(createButton);
    }

    // Update apply button state
    function updateApplyButton() {
        const applyBtn = document.getElementById('apply-choice-btn');
        applyBtn.disabled = !selectedChoice;
    }

    // Apply choice
    document.getElementById('apply-choice-btn').addEventListener('click', function() {
        if (!selectedChoice) return;

        const form = document.getElementById('fuzzy-choice-form');
        form.querySelector('#fuzzy-choice-type').value = selectedChoice.type;
        form.querySelector('#fuzzy-choice-data').value = JSON.stringify(selectedChoice.data);

        const formData = new FormData(form);

        fetch('<?php echo e(route("fuzzy-matching.apply-choice")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFuzzySuccess(data.message);
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('fuzzyMatchingModal'));
                modal.hide();

                // Trigger refresh of validation results
                if (window.refreshValidationResults) {
                    window.refreshValidationResults();
                }
            } else {
                showFuzzyError(data.message);
            }
        })
        .catch(error => {
            console.error('Error applying choice:', error);
            showFuzzyError('Error applying choice: ' + error.message);
        });
    });

    // Show success message
    function showFuzzySuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
    }

    // Show error message
    function showFuzzyError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
    }
});
</script>
<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/partials/_fuzzy-matching-modal.blade.php ENDPATH**/ ?>