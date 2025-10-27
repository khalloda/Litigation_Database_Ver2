
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
    <?php echo csrf_field(); ?>
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

        // Show modal (with Bootstrap fallback)
        const modalElement = document.getElementById('fuzzyMatchingModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            // Fallback: show modal manually
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            modalElement.setAttribute('aria-hidden', 'false'); // Fix accessibility issue
            document.body.classList.add('modal-open');

            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'fuzzy-modal-backdrop';
            document.body.appendChild(backdrop);
        }

        // Add close button event listeners
        addCloseButtonListeners();
    };

    // Load fuzzy matching choices
    function loadFuzzyChoices(field, searchValue, importSessionId) {
        fetch(`<?php echo e(route('fuzzy-matching.choices')); ?>?field=${encodeURIComponent(field)}&search_value=${encodeURIComponent(searchValue)}&import_session_id=${importSessionId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Fuzzy choices response:', data);
                if (data.success) {
                    console.log('Choices data:', data.choices);
                    console.log('Choices array:', data.choices.choices);
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

        // Safety check: ensure choices is an array
        if (!Array.isArray(choices)) {
            console.error('Choices is not an array:', choices);
            container.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <p class="text-warning">Invalid choices data received</p>
                </div>
            `;
            return;
        }

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
                    console.log('Existing choice selected:', this.dataset.choice);
                    selectedChoice = {
                        type: 'existing',
                        data: JSON.parse(this.dataset.choice)
                    };
                    updateApplyButton();
                    showFuzzySuccess('Existing value selected. Click "Apply Choice" to use it.');
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
            console.log('Create New button clicked');
            const form = container.querySelector('form');
            if (form) {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                console.log('Form data:', data);

                selectedChoice = {
                    type: 'create',
                    data: data
                };
                updateApplyButton();

                // Show success message
                showFuzzySuccess('New value prepared. Click "Apply Choice" to create it.');
            } else {
                console.error('Form not found');
                showFuzzyError('Form not found. Please refresh and try again.');
            }
        });

        container.appendChild(createButton);
    }

    // Update apply button state
    function updateApplyButton() {
        const applyBtn = document.getElementById('apply-choice-btn');
        applyBtn.disabled = !selectedChoice;
    }

    // Apply choice
    document.getElementById('apply-choice-btn').addEventListener('click', async function() {
        console.log('Apply Choice button clicked');
        console.log('Selected choice:', selectedChoice);

        if (!selectedChoice) {
            console.error('No choice selected');
            showFuzzyError('Please select a choice first');
            return;
        }

        const form = document.getElementById('fuzzy-choice-form');
        form.querySelector('#fuzzy-choice-type').value = selectedChoice.type;
        form.querySelector('#fuzzy-choice-data').value = JSON.stringify(selectedChoice.data);

        const formData = new FormData(form);

        // Debug: Log all form data
        console.log('Form data contents:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Debug: Check CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF token from meta:', csrfToken);
        console.log('CSRF token from form:', formData.get('_token'));

        // Check if CSRF tokens match
        if (csrfToken !== formData.get('_token')) {
            console.warn('CSRF token mismatch detected!');
            console.log('Meta token:', csrfToken);
            console.log('Form token:', formData.get('_token'));

            // Update the form data with the current meta token
            formData.set('_token', csrfToken);
            console.log('Updated form token to match meta token');
        }

        // Always refresh CSRF token before making the request
        console.log('Refreshing CSRF token before request...');
        const refreshResponse = await fetch('<?php echo e(route("fuzzy-matching.choices")); ?>?refresh_csrf=1');
        const refreshData = await refreshResponse.json();

        if (refreshData.success && refreshData.csrf_token) {
            // Update the meta tag and form data with the fresh token
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', refreshData.csrf_token);
            formData.set('_token', refreshData.csrf_token);
            console.log('CSRF token refreshed before request:', refreshData.csrf_token);
        }

        console.log('Sending choice data:', {
            type: selectedChoice.type,
            data: selectedChoice.data
        });

        const url = '<?php echo e(route("fuzzy-matching.apply-choice")); ?>';
        console.log('Request URL:', url);

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            console.log('Response URL:', response.url);

            // Check if response is HTML instead of JSON
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);

            if (contentType && contentType.includes('text/html')) {
                console.error('❌ Server returned HTML instead of JSON!');
                return response.text().then(html => {
                    console.error('HTML Response:', html.substring(0, 200) + '...');
                    throw new Error('Server returned HTML instead of JSON');
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showFuzzySuccess(data.message);
                // Close modal
                closeFuzzyModal();

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

            // Check if it's a CSRF token mismatch
            if (error.message.includes('Server returned HTML instead of JSON')) {
                console.warn('Possible CSRF token mismatch - trying to refresh token');

                // Try to refresh the CSRF token first
                fetch('<?php echo e(route("fuzzy-matching.choices")); ?>?refresh_csrf=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.csrf_token) {
                            // Update the meta tag with the new token
                            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
                            console.log('CSRF token refreshed:', data.csrf_token);
                            showFuzzyError('CSRF token refreshed. Please try again.');
                        } else {
                            throw new Error('Could not refresh CSRF token');
                        }
                    })
                    .catch(refreshError => {
                        console.error('Failed to refresh CSRF token:', refreshError);
                        showFuzzyError('CSRF token expired. Please refresh the page and try again.');
                        // Close modal and suggest refresh
                        setTimeout(() => {
                            closeFuzzyModal();
                            if (confirm('Your session has expired. Would you like to refresh the page?')) {
                                window.location.reload();
                            }
                        }, 2000);
                    });
            } else {
                showFuzzyError('Error applying choice: ' + error.message);
            }
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

    // Close modal function with Bootstrap fallback
    function closeFuzzyModal() {
        const modalElement = document.getElementById('fuzzyMatchingModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        } else {
            // Fallback: hide modal manually
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true'); // Fix accessibility issue
            document.body.classList.remove('modal-open');

            // Remove backdrop
            const backdrop = document.getElementById('fuzzy-modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }

    // Add close button event listeners when modal is shown
    function addCloseButtonListeners() {
        // Close button in modal header
        document.querySelectorAll('#fuzzyMatchingModal [data-bs-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', closeFuzzyModal);
        });

        // Cancel button
        const cancelBtn = document.querySelector('#fuzzyMatchingModal .btn-secondary');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeFuzzyModal);
        }
    }
});
</script>
<?php /**PATH D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app\resources\views/import/partials/_fuzzy-matching-modal.blade.php ENDPATH**/ ?>