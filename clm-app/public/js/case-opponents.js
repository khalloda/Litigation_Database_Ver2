/**
 * Case Opponents Management JavaScript
 * 
 * Handles AJAX operations for managing multiple opponents per case:
 * - Add opponents via modal
 * - Remove opponents
 * - Set primary opponent
 * - Reorder opponents
 * - Search opponents
 */

class CaseOpponentsManager {
    constructor(caseId) {
        this.caseId = caseId;
        this.opponentsTable = document.getElementById('opponents-table');
        this.addOpponentModal = document.getElementById('addOpponentModal');
        this.opponentSearchInput = document.getElementById('opponent-search');
        this.opponentResults = document.getElementById('opponent-results');
        this.capacitySelect = document.getElementById('opponent-capacity');
        this.aliasInput = document.getElementById('opponent-alias');
        this.isPrimaryCheckbox = document.getElementById('is-primary');
        
        this.initializeEventListeners();
        this.loadOpponents();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Add opponent button
        document.getElementById('add-opponent-btn')?.addEventListener('click', () => {
            this.showAddOpponentModal();
        });

        // Opponent search
        this.opponentSearchInput?.addEventListener('input', (e) => {
            this.searchOpponents(e.target.value);
        });

        // Add opponent form submission
        document.getElementById('add-opponent-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.addOpponent();
        });

        // Modal close
        document.getElementById('close-modal')?.addEventListener('click', () => {
            this.hideAddOpponentModal();
        });

        // Click outside modal to close
        this.addOpponentModal?.addEventListener('click', (e) => {
            if (e.target === this.addOpponentModal) {
                this.hideAddOpponentModal();
            }
        });
    }

    /**
     * Load opponents for the case
     */
    async loadOpponents() {
        try {
            const response = await fetch(`/cases/${this.caseId}/opponents`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.renderOpponents(data.opponents);
        } catch (error) {
            console.error('Error loading opponents:', error);
            this.showAlert('Error loading opponents', 'error');
        }
    }

    /**
     * Render opponents in the table
     */
    renderOpponents(opponents) {
        if (!this.opponentsTable) return;

        const tbody = this.opponentsTable.querySelector('tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (opponents.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No opponents added yet
                    </td>
                </tr>
            `;
            return;
        }

        opponents.forEach((opponent, index) => {
            const row = this.createOpponentRow(opponent, index);
            tbody.appendChild(row);
        });
    }

    /**
     * Create opponent table row
     */
    createOpponentRow(opponent, index) {
        const row = document.createElement('tr');
        row.setAttribute('data-opponent-id', opponent.id);
        
        const primaryBadge = opponent.is_primary ? 
            '<span class="badge bg-primary"><i class="fas fa-star"></i> Primary</span>' : 
            '<span class="badge bg-secondary">Secondary</span>';

        const capacityBadge = opponent.capacity ? 
            `<span class="badge bg-info">${opponent.capacity.label_en}</span>` : 
            '<span class="text-muted">No capacity</span>';

        const aliasText = opponent.alias_text ? 
            `<small class="text-muted d-block">"${opponent.alias_text}"</small>` : '';

        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="caseOpponentsManager.moveUp(${opponent.id})" ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="caseOpponentsManager.moveDown(${opponent.id})" ${index === this.getOpponentsCount() - 1 ? 'disabled' : ''}>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <span class="badge bg-light text-dark">${index + 1}</span>
                </div>
            </td>
            <td>
                <div>
                    <strong>${opponent.name_en}</strong>
                    ${aliasText}
                </div>
                <small class="text-muted">${opponent.name_ar}</small>
            </td>
            <td>${capacityBadge}</td>
            <td>${primaryBadge}</td>
            <td>
                <div class="btn-group" role="group">
                    ${!opponent.is_primary ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="caseOpponentsManager.setPrimary(${opponent.id})" title="Set as Primary">
                            <i class="fas fa-star"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-outline-danger" onclick="caseOpponentsManager.removeOpponent(${opponent.id})" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    /**
     * Show add opponent modal
     */
    showAddOpponentModal() {
        if (this.addOpponentModal) {
            this.addOpponentModal.style.display = 'block';
            this.addOpponentModal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Focus on search input
            setTimeout(() => {
                this.opponentSearchInput?.focus();
            }, 100);
        }
    }

    /**
     * Hide add opponent modal
     */
    hideAddOpponentModal() {
        if (this.addOpponentModal) {
            this.addOpponentModal.style.display = 'none';
            this.addOpponentModal.classList.remove('show');
            document.body.classList.remove('modal-open');
            this.resetAddOpponentForm();
        }
    }

    /**
     * Reset add opponent form
     */
    resetAddOpponentForm() {
        this.opponentSearchInput.value = '';
        this.opponentResults.innerHTML = '';
        this.capacitySelect.value = '';
        this.aliasInput.value = '';
        this.isPrimaryCheckbox.checked = false;
    }

    /**
     * Search opponents
     */
    async searchOpponents(query) {
        if (query.length < 2) {
            this.opponentResults.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`/opponents/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const opponents = await response.json();
            this.renderOpponentResults(opponents);
        } catch (error) {
            console.error('Error searching opponents:', error);
            this.opponentResults.innerHTML = '<div class="text-danger">Error searching opponents</div>';
        }
    }

    /**
     * Render opponent search results
     */
    renderOpponentResults(opponents) {
        if (!this.opponentResults) return;

        if (opponents.length === 0) {
            this.opponentResults.innerHTML = '<div class="text-muted">No opponents found</div>';
            return;
        }

        this.opponentResults.innerHTML = opponents.map(opponent => `
            <div class="opponent-result-item" onclick="caseOpponentsManager.selectOpponent(${opponent.id}, '${opponent.opponent_name_en}', '${opponent.opponent_name_ar}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${opponent.opponent_name_en}</strong>
                        <br>
                        <small class="text-muted">${opponent.opponent_name_ar}</small>
                    </div>
                    <i class="fas fa-plus text-primary"></i>
                </div>
            </div>
        `).join('');
    }

    /**
     * Select opponent from search results
     */
    selectOpponent(opponentId, nameEn, nameAr) {
        // Store selected opponent ID
        this.selectedOpponentId = opponentId;
        
        // Update search input with selected name
        this.opponentSearchInput.value = nameEn;
        
        // Clear results
        this.opponentResults.innerHTML = '';
        
        // Focus on capacity select
        this.capacitySelect?.focus();
    }

    /**
     * Add opponent to case
     */
    async addOpponent() {
        if (!this.selectedOpponentId) {
            this.showAlert('Please select an opponent', 'warning');
            return;
        }

        const formData = {
            opponent_id: this.selectedOpponentId,
            capacity_id: this.capacitySelect?.value || null,
            alias_text: this.aliasInput?.value || null,
            is_primary: this.isPrimaryCheckbox?.checked || false
        };

        try {
            const response = await fetch(`/cases/${this.caseId}/opponents`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.hideAddOpponentModal();
                this.loadOpponents();
            } else {
                this.showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error adding opponent:', error);
            this.showAlert('Error adding opponent', 'error');
        }
    }

    /**
     * Remove opponent from case
     */
    async removeOpponent(opponentId) {
        if (!confirm('Are you sure you want to remove this opponent?')) {
            return;
        }

        try {
            const response = await fetch(`/cases/${this.caseId}/opponents`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ opponent_id: opponentId })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.loadOpponents();
            } else {
                this.showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error removing opponent:', error);
            this.showAlert('Error removing opponent', 'error');
        }
    }

    /**
     * Set primary opponent
     */
    async setPrimary(opponentId) {
        try {
            const response = await fetch(`/cases/${this.caseId}/opponents/set-primary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ opponent_id: opponentId })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(data.message, 'success');
                this.loadOpponents();
            } else {
                this.showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error setting primary opponent:', error);
            this.showAlert('Error setting primary opponent', 'error');
        }
    }

    /**
     * Move opponent up in order
     */
    async moveUp(opponentId) {
        await this.reorderOpponent(opponentId, 'up');
    }

    /**
     * Move opponent down in order
     */
    async moveDown(opponentId) {
        await this.reorderOpponent(opponentId, 'down');
    }

    /**
     * Reorder opponent
     */
    async reorderOpponent(opponentId, direction) {
        try {
            // Get current order
            const currentOpponents = Array.from(this.opponentsTable.querySelectorAll('tbody tr'))
                .map(row => parseInt(row.getAttribute('data-opponent-id')))
                .filter(id => !isNaN(id));

            // Find current index
            const currentIndex = currentOpponents.indexOf(opponentId);
            if (currentIndex === -1) return;

            // Calculate new index
            let newIndex;
            if (direction === 'up' && currentIndex > 0) {
                newIndex = currentIndex - 1;
            } else if (direction === 'down' && currentIndex < currentOpponents.length - 1) {
                newIndex = currentIndex + 1;
            } else {
                return; // Already at boundary
            }

            // Swap positions
            [currentOpponents[currentIndex], currentOpponents[newIndex]] = 
            [currentOpponents[newIndex], currentOpponents[currentIndex]];

            // Send reorder request
            const response = await fetch(`/cases/${this.caseId}/opponents/reorder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ opponent_ids: currentOpponents })
            });

            const data = await response.json();

            if (data.success) {
                this.loadOpponents();
            } else {
                this.showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error reordering opponent:', error);
            this.showAlert('Error reordering opponent', 'error');
        }
    }

    /**
     * Get current opponents count
     */
    getOpponentsCount() {
        return this.opponentsTable?.querySelectorAll('tbody tr[data-opponent-id]').length || 0;
    }

    /**
     * Show alert message
     */
    showAlert(message, type = 'info') {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Add to page
        document.body.appendChild(alert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get case ID from the page
    const caseId = document.querySelector('[data-case-id]')?.getAttribute('data-case-id');
    
    if (caseId) {
        // Initialize case opponents manager
        window.caseOpponentsManager = new CaseOpponentsManager(caseId);
    }
});

// Export for global access
window.CaseOpponentsManager = CaseOpponentsManager;
