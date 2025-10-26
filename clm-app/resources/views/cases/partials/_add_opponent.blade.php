{{-- Add Opponent Modal --}}
{{-- Modal for adding new opponents to a case with search, capacity selection, and alias input --}}

<div class="modal fade" id="addOpponentModal" tabindex="-1" aria-labelledby="addOpponentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOpponentModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('app.add_opponent') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addOpponentForm">
                <div class="modal-body">
                    {{-- Opponent Search --}}
                    <div class="mb-3">
                        <label for="opponentSearch" class="form-label">
                            {{ __('app.search_opponent') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="opponentSearch"
                                   placeholder="{{ __('app.type_to_search_opponents') }}"
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="opponentSearchResults" class="mt-2" style="display: none;">
                            {{-- Search results will be populated here --}}
                        </div>
                        <div class="form-text">
                            {{ __('app.opponent_search_help') }}
                        </div>
                    </div>

                    {{-- Selected Opponent Display --}}
                    <div id="selectedOpponent" class="mb-3" style="display: none;">
                        <label class="form-label">{{ __('app.selected_opponent') }}</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold" id="selectedOpponentName"></div>
                                        <small class="text-muted" id="selectedOpponentDetails"></small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearSelection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Capacity Selection --}}
                    <div class="mb-3">
                        <label for="opponentCapacity" class="form-label">
                            {{ __('app.capacity') }}
                        </label>
                        <select class="form-select" id="opponentCapacity" name="capacity_id">
                            <option value="">{{ __('app.no_capacity') }}</option>
                            @foreach(\App\Models\OptionValue::where('set_id', 3)->get() as $capacity)
                                <option value="{{ $capacity->id }}">
                                    {{ $capacity->label_en }}
                                    @if($capacity->label_ar && $capacity->label_ar !== $capacity->label_en)
                                        ({{ $capacity->label_ar }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            {{ __('app.capacity_help') }}
                        </div>
                    </div>

                    {{-- Alias Text --}}
                    <div class="mb-3">
                        <label for="opponentAlias" class="form-label">
                            {{ __('app.alias_text') }}
                        </label>
                        <input type="text"
                               class="form-control"
                               id="opponentAlias"
                               name="alias_text"
                               placeholder="{{ __('app.optional_alias_text') }}"
                               maxlength="191">
                        <div class="form-text">
                            {{ __('app.alias_help') }}
                        </div>
                    </div>

                    {{-- Set as Primary --}}
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="setAsPrimary" name="is_primary">
                            <label class="form-check-label" for="setAsPrimary">
                                {{ __('app.set_as_primary_opponent') }}
                            </label>
                        </div>
                        <div class="form-text">
                            {{ __('app.primary_opponent_help') }}
                        </div>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" id="selectedOpponentId" name="opponent_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="addOpponentBtn" disabled>
                        <i class="fas fa-plus me-1"></i>
                        {{ __('app.add_opponent') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addOpponentModal');
    const form = document.getElementById('addOpponentForm');
    const searchInput = document.getElementById('opponentSearch');
    const searchResults = document.getElementById('opponentSearchResults');
    const selectedOpponent = document.getElementById('selectedOpponent');
    const selectedOpponentId = document.getElementById('selectedOpponentId');
    const selectedOpponentName = document.getElementById('selectedOpponentName');
    const selectedOpponentDetails = document.getElementById('selectedOpponentDetails');
    const addOpponentBtn = document.getElementById('addOpponentBtn');
    const clearSearchBtn = document.getElementById('clearSearch');
    const clearSelectionBtn = document.getElementById('clearSelection');

    let searchTimeout;
    let currentOpponent = null;

    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            searchOpponents(query);
        }, 300);
    });

    // Clear search
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchResults.style.display = 'none';
    });

    // Clear selection
    clearSelectionBtn.addEventListener('click', function() {
        clearSelection();
    });

    // Modal reset on show
    modal.addEventListener('show.bs.modal', function() {
        resetForm();
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        addOpponent();
    });

    function searchOpponents(query) {
        fetch(`{{ route('opponents.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="text-danger">{{ __("app.error_searching_opponents") }}</div>';
                searchResults.style.display = 'block';
            });
    }

    function displaySearchResults(opponents) {
        if (opponents.length === 0) {
            searchResults.innerHTML = '<div class="text-muted">{{ __("app.no_opponents_found") }}</div>';
        } else {
            searchResults.innerHTML = opponents.map(opponent => `
                <div class="search-result-item p-2 border-bottom cursor-pointer"
                     data-opponent-id="${opponent.id}"
                     data-opponent-name-en="${opponent.opponent_name_en}"
                     data-opponent-name-ar="${opponent.opponent_name_ar || ''}">
                    <div class="fw-bold" dir="auto">${opponent.opponent_name_en}</div>
                    ${opponent.opponent_name_ar && opponent.opponent_name_ar !== opponent.opponent_name_en ?
                        `<div class="text-muted small" dir="auto">${opponent.opponent_name_ar}</div>` : ''}
                </div>
            `).join('');
        }
        searchResults.style.display = 'block';

        // Add click handlers to search results
        searchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                selectOpponent({
                    id: this.dataset.opponentId,
                    name_en: this.dataset.opponentNameEn,
                    name_ar: this.dataset.opponentNameAr
                });
            });
        });
    }

    function selectOpponent(opponent) {
        currentOpponent = opponent;
        selectedOpponentId.value = opponent.id;
        selectedOpponentName.textContent = opponent.name_en;
        selectedOpponentDetails.textContent = opponent.name_ar && opponent.name_ar !== opponent.name_en ?
            opponent.name_ar : '';

        selectedOpponent.style.display = 'block';
        searchResults.style.display = 'none';
        searchInput.value = '';
        addOpponentBtn.disabled = false;
    }

    function clearSelection() {
        currentOpponent = null;
        selectedOpponentId.value = '';
        selectedOpponent.style.display = 'none';
        addOpponentBtn.disabled = true;
    }

    function resetForm() {
        form.reset();
        clearSelection();
        searchInput.value = '';
        searchResults.style.display = 'none';
    }

    function addOpponent() {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        addOpponentBtn.disabled = true;
        addOpponentBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("app.adding") }}...';

        fetch('{{ route("case-opponents.store", $case) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reload page
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                location.reload();
            } else {
                showAlert('danger', data.message || '{{ __("app.error_adding_opponent") }}');
                addOpponentBtn.disabled = false;
                addOpponentBtn.innerHTML = '<i class="fas fa-plus me-1"></i>{{ __("app.add_opponent") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', '{{ __("app.error_adding_opponent") }}');
            addOpponentBtn.disabled = false;
            addOpponentBtn.innerHTML = '<i class="fas fa-plus me-1"></i>{{ __("app.add_opponent") }}';
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.insertBefore(alertDiv, modalBody.firstChild);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endpush
