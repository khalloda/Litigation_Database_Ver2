# ADR 001: Multi-Opponents Per Case Implementation

**Date**: 2025-10-25  
**Status**: Accepted  
**Decision Makers**: Development Team  

## Context

The Central Litigation Management application currently supports only a single opponent per case through the `cases.opponent_id` field. However, legal cases often involve multiple opposing parties, each with different capacities (e.g., defendant, co-defendant, third-party defendant, etc.).

### Current Limitations

1. **Single Opponent Constraint**: Only one opponent can be associated with each case
2. **No Capacity Tracking**: No way to specify the role/capacity of each opponent
3. **Import Limitations**: Import templates only support one opponent per case
4. **UI Constraints**: No interface for managing multiple opponents
5. **Data Loss Risk**: Complex cases with multiple parties cannot be properly tracked

### Business Requirements

1. **Multiple Opponents**: Support 1-10 opponents per case (configurable)
2. **Capacity Tracking**: Each opponent must have a specific capacity/role
3. **Primary Opponent**: One opponent must be designated as primary for backward compatibility
4. **Import/Export**: Support for importing multiple opponents via templates
5. **UI Management**: Interface for adding, removing, and reordering opponents
6. **Backward Compatibility**: Existing single opponent data must be preserved

## Decision

We will implement a **Multi-Opponents Per Case** system using a pivot table approach with the following architecture:

### Core Components

1. **`case_opponents` Pivot Table** — Stores opponent-case relationships with metadata
2. **`CaseOpponent` Pivot Model** — Eloquent model for pivot operations
3. **`CaseOpponentService`** — Business logic for opponent management
4. **`CaseOpponentController`** — AJAX API for UI operations
5. **UI Components** — Opponents management interface with reordering
6. **Import Templates** — Extended templates supporting multiple opponents

### Database Schema

```sql
CREATE TABLE case_opponents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    opponent_id BIGINT UNSIGNED NOT NULL,
    capacity_id BIGINT UNSIGNED NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT NULL,
    alias_text VARCHAR(191) NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (opponent_id) REFERENCES opponents(id) ON DELETE RESTRICT,
    FOREIGN KEY (capacity_id) REFERENCES option_values(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_case_opponents_case_display (case_id, display_order),
    INDEX idx_case_opponents_opponent (opponent_id),
    INDEX idx_case_opponents_case_primary (case_id, is_primary),
    INDEX idx_case_opponents_case_capacity (case_id, capacity_id)
);
```

### Business Rules

1. **Capacity Multiplicity**: Same opponent can appear with different capacities in same case
2. **Uniqueness**: Enforced at service layer: `(case_id, opponent_id, capacity_id)` must be unique
3. **Primary Constraint**: Exactly one opponent per case must be marked as primary
4. **Soft Deletes**: Support for re-attaching opponents after soft deletion
5. **Legacy Mirror**: `cases.opponent_id` maintained as read-only mirror of primary opponent

### Service Layer Design

```php
class CaseOpponentService
{
    public function attachOpponent(CaseModel $case, int $opponentId, ?int $capacityId = null, bool $isPrimary = false, ?int $order = null, ?string $alias = null): CaseOpponent
    
    public function detachOpponent(CaseModel $case, int $opponentId): bool
    
    public function setPrimary(CaseModel $case, int $opponentId): void
    
    public function reorder(CaseModel $case, array $opponentIdsInOrder): void
    
    public function validateMaxOpponents(CaseModel $case): void
    
    private function syncLegacyOpponentField(CaseModel $case): void
}
```

### Import/Export Strategy

1. **Standard Template**: Single opponent (backward compatible)
2. **Extended Template**: Up to 5 opponents with capacity tracking
3. **Companion Import**: Dedicated `case_opponents` import for unlimited opponents
4. **Preflight Validation**: Max opponents limit enforcement

## Rationale

### Why Pivot Table Approach?

1. **Flexibility**: Supports many-to-many relationships with metadata
2. **Performance**: Indexed queries for opponent lookups
3. **Extensibility**: Easy to add new fields (capacity, alias, order)
4. **Laravel Integration**: Native Eloquent pivot support
5. **Audit Trail**: Built-in created_by/updated_by tracking

### Why Service Layer Uniqueness?

1. **Soft Delete Support**: MySQL unique constraints don't work with soft deletes
2. **Business Logic**: Complex validation rules (capacity multiplicity)
3. **Performance**: Application-level checks are faster than DB constraints
4. **Flexibility**: Can implement custom conflict resolution

### Why Legacy Mirror Field?

1. **Backward Compatibility**: Existing code continues to work
2. **Gradual Migration**: Can deprecate over time
3. **Performance**: Single query for primary opponent lookup
4. **API Compatibility**: Existing APIs don't break

### Why Transaction Safety?

1. **Data Integrity**: Atomic operations prevent inconsistent state
2. **Concurrency**: Handles multi-user scenarios safely
3. **Rollback**: Failed operations don't leave partial data
4. **Audit**: Complete operation logging

## Implementation Details

### Migration Strategy

1. **Create Pivot Table**: New `case_opponents` table with proper indexes
2. **Backfill Data**: Migrate existing single opponents to pivot table
3. **Remove Unique Constraint**: Allow soft delete re-attachment
4. **Update Models**: Add relationships and mark legacy field read-only
5. **Service Implementation**: Business logic with transaction safety

### UI Components

1. **Opponents Table**: Sortable list with capacity badges and primary indicators
2. **Add Opponent Modal**: Search, capacity selection, and alias input
3. **Reorder Interface**: Drag-and-drop or up/down arrows
4. **Primary Selection**: Visual "Set as Primary" actions
5. **Remove Actions**: Confirmation dialogs with impact warnings

### Import Templates

1. **Standard CSV/XLSX**: Single opponent (opponent1_name, opponent1_capacity)
2. **Extended CSV/XLSX**: Up to 5 opponents (opponent1-5_name, opponent1-5_capacity)
3. **Companion Import**: Dedicated `case_opponents` table import
4. **Validation**: Max opponents limit and capacity validation

### API Endpoints

```php
// Case Opponents Management
GET    /cases/{case}/opponents           // List opponents
POST   /cases/{case}/opponents           // Add opponent
DELETE /cases/{case}/opponents           // Remove opponent
POST   /cases/{case}/opponents/set-primary // Set primary
POST   /cases/{case}/opponents/reorder   // Reorder opponents

// Opponent Search (for modal)
GET    /opponents/search?q={query}       // Search opponents
```

## Consequences

### Positive

- **Enhanced Functionality**: Support for complex multi-party cases
- **Better Data Integrity**: Proper capacity tracking and validation
- **Improved UX**: Intuitive opponent management interface
- **Import Flexibility**: Multiple import strategies for different use cases
- **Backward Compatibility**: Existing data and code continue to work
- **Audit Trail**: Complete tracking of opponent changes
- **Performance**: Optimized queries with proper indexing

### Negative

- **Code Complexity**: Additional service layer and UI components
- **Storage Overhead**: Pivot table adds storage requirements
- **Migration Risk**: Data migration from single to multiple opponents
- **UI Complexity**: More complex opponent management interface
- **Testing Overhead**: Additional test coverage required

### Mitigations

- **Gradual Rollout**: Feature flags for controlled deployment
- **Comprehensive Testing**: Unit, integration, and UI tests
- **Data Validation**: Extensive preflight validation
- **User Training**: Documentation and training materials
- **Rollback Plan**: Database migration rollback procedures

## Alternatives Considered

1. **JSON Column**: Store opponents as JSON in cases table
   - **Rejected**: Poor query performance, no referential integrity
   
2. **Separate Opponents Table**: One-to-many relationship
   - **Rejected**: Doesn't support capacity multiplicity
   
3. **No Primary Opponent**: All opponents equal
   - **Rejected**: Breaks backward compatibility
   
4. **Hard Delete Only**: No soft delete support
   - **Rejected**: Loses audit trail and recovery options

## Future Enhancements

- **Bulk Operations**: Select multiple opponents for bulk actions
- **Opponent Templates**: Predefined opponent sets for common case types
- **Advanced Search**: Filter opponents by capacity, case type, etc.
- **Export Options**: Export opponents to various formats
- **API Integration**: External system integration for opponent data
- **Analytics**: Reporting on opponent patterns and case complexity

## Deprecation Timeline

### Phase 1 (Current): Legacy Field Read-Only
- `cases.opponent_id` becomes read-only mirror
- All writes go through `CaseOpponentService`
- Existing code continues to work

### Phase 2 (v2.1): Deprecation Warnings
- Add deprecation warnings for direct `opponent_id` access
- Update documentation with migration guidance
- Provide migration tools

### Phase 3 (v3.0): Legacy Field Removal
- Remove `cases.opponent_id` field entirely
- Update all code to use pivot table
- Complete migration to multi-opponent system

## References

- **Implementation Plan**: `/docs/plans/PLAN_Multi_Opponents_Per_Case.md`
- **Database Schema**: `/database/migrations/2025_10_25_104215_create_case_opponents_table.php`
- **Service Implementation**: `/app/Services/CaseOpponentService.php`
- **UI Components**: `/resources/views/cases/partials/_opponents.blade.php`
- **Import Templates**: `/app/Console/Commands/GenerateCasesTemplate.php`

---

## Change Log

| Date | Version | Changes |
|---|---|---|
| 2025-10-25 | 1.0 | Initial ADR - Multi-opponents system approved |

**Author**: AI Agent  
**Reviewers**: Pending human review
