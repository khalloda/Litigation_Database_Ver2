# Delayed Ideas & Plans

This document tracks features and enhancements that have been identified but postponed for future implementation. Each item includes context about why it was delayed and what dependencies need to be resolved first.

---

## Format Template

```
### DELAYED-XXX: [Title]
- **Description**: Brief description of the feature/enhancement
- **Reason for Delay**: Why this was postponed
- **Dependencies**: What needs to be completed first
- **Related Models/Components**: Affected parts of the system
- **Priority**: Low / Medium / High
- **Estimated Effort**: Small / Medium / Large
- **Proposed By**: User/Developer name
- **Date Added**: YYYY-MM-DD
```

---

## Active Delayed Items

### DELAYED-001: Full Hearings Integration in Court View
- **Description**: Display all hearings related to a specific court in the court detail page, with filtering, sorting, and pagination capabilities.
- **Reason for Delay**: The Hearings model is still in development and requires improvements and modifications before it can be fully integrated.
- **Dependencies**: 
  - Finalize Hearings model structure
  - Establish proper relationships between Hearings and Courts (via Cases)
  - Implement hearing status and type management
- **Related Models/Components**: 
  - `Hearing` model
  - `CaseModel` (relationship)
  - `CourtsController::show()` method
  - `courts/show.blade.php` view
- **Priority**: High
- **Estimated Effort**: Medium
- **Proposed By**: User
- **Date Added**: 2025-10-13

---

### DELAYED-002: Tasks and Subtasks Hierarchy in Court View
- **Description**: Display all tasks related to cases in a specific court, with expandable subtasks in an accordion/tree structure. Include task status, assignees, and due dates.
- **Reason for Delay**: Tasks and Subtasks models are still in development and need improvements and modifications.
- **Dependencies**:
  - Finalize AdminTask and AdminSubtask models
  - Establish proper relationships between Tasks and Courts (via Cases)
  - Implement task-subtask hierarchy logic
  - Design expandable UI component for task/subtask display
- **Related Models/Components**:
  - `AdminTask` model
  - `AdminSubtask` model
  - `CaseModel` (relationship)
  - `CourtsController::show()` method
  - `courts/show.blade.php` view
- **Priority**: High
- **Estimated Effort**: Large
- **Proposed By**: User
- **Date Added**: 2025-10-13

---

### DELAYED-003: Advanced Court Statistics and Analytics Dashboard
- **Description**: Create a comprehensive analytics dashboard for each court showing:
  - Total cases by status
  - Average case duration
  - Win/loss ratios
  - Upcoming hearings timeline
  - Case load trends over time
  - Lawyer performance metrics for cases in this court
- **Reason for Delay**: Requires stable data models and sufficient historical data. Should be implemented after core CRUD operations are complete and data is populated.
- **Dependencies**:
  - Complete data migration and validation
  - Finalize all related models (Cases, Hearings, Tasks)
  - Implement proper status tracking across all models
  - Choose and integrate charting library (e.g., Chart.js, ApexCharts)
- **Related Models/Components**:
  - `CaseModel`
  - `Hearing`
  - `AdminTask`
  - `Lawyer`
  - `CourtsController` (new analytics method)
  - New view: `courts/analytics.blade.php`
- **Priority**: Medium
- **Estimated Effort**: Large
- **Proposed By**: System Architect
- **Date Added**: 2025-10-13

---

### DELAYED-004: Court-Specific Document Management
- **Description**: Allow filtering and viewing all documents related to cases in a specific court, with advanced search and categorization.
- **Reason for Delay**: Document management system needs to be fully implemented and tested first.
- **Dependencies**:
  - Complete ClientDocument model implementation
  - Implement document categorization and tagging
  - Establish document-court relationship via cases
- **Related Models/Components**:
  - `ClientDocument` model
  - `CaseModel`
  - `CourtsController`
  - `courts/show.blade.php`
- **Priority**: Low
- **Estimated Effort**: Medium
- **Proposed By**: System Architect
- **Date Added**: 2025-10-13

---

### DELAYED-005: Court Calendar Integration
- **Description**: Display a calendar view showing all hearings for a specific court, with day/week/month views and filtering options.
- **Reason for Delay**: Requires stable Hearing model and calendar UI component selection/implementation.
- **Dependencies**:
  - Finalize Hearing model
  - Choose calendar library (e.g., FullCalendar, Tui Calendar)
  - Implement calendar API endpoints
- **Related Models/Components**:
  - `Hearing` model
  - `CourtsController` (new calendar method)
  - New view: `courts/calendar.blade.php`
- **Priority**: Medium
- **Estimated Effort**: Medium
- **Proposed By**: System Architect
- **Date Added**: 2025-10-13

---

## Completed (Moved from Delayed)

_Items that were previously delayed but have now been implemented will be listed here for reference._

---

## Notes

- Review this list quarterly to re-prioritize items
- Update dependencies as related models/features are completed
- Link to relevant ADRs when architectural decisions are made
- Move items to "Completed" section when implemented, with completion date and commit reference

