# Product Requirements Document (PRD)
## Centralized Litigation Management System (CLMS)

**Document Version**: 1.0 (Reverse-Engineered)  
**Analysis Date**: November 25, 2025  
**Product Owner**: Law Firm Management  
**Technical Lead**: Development Team  

---

## 1. Product Overview

### 1.1 Product Vision

**Vision Statement**: To provide law firms with a comprehensive, bilingual CLMS that streamlines litigation workflows, enhances team collaboration, ensures compliance, and enables data-driven decision-making.

### 1.2 Product Goals

1. **Efficiency**: Reduce administrative overhead by 40% through automation and centralized data
2. **Compliance**: Maintain 100% audit trail for regulatory requirements
3. **Accessibility**: Support bilingual (EN/AR) users with RTL interface
4. **Reliability**: 99.9% uptime with automated backups and disaster recovery
5. **Scalability**: Support growth from 10 to 1000+ users without architecture changes

### 1.3 Target Audience

**Primary Users**:
- Law firm partners (strategic oversight, client management)
- Senior associates and lawyers (day-to-day case handling)
- Paralegals and legal assistants (administrative tasks, document management)
- Administrative staff (system configuration, user management)

**Secondary Users**:
- Clients (future: portal for case status viewing)
- External counsel (future: collaboration features)

---

## 2. Functional Requirements

### 2.1 Client Management (Module 1)

**Priority**: **P0 (Critical)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| CM-001 | As a partner, I want to create client profiles with bilingual names so I can serve both English-speaking and Arabic-speaking clients | - Support client_name_ar and client_name_en fields<br>- Auto-generate unique client_code<br>- Set default status to "Active" | ✅ Complete |
| CM-002 | As a lawyer, I want to assign a primary contact lawyer to each client so ownership is clear | - Dropdown of active lawyers<br>- Support for reassignment<br>- Display in client detail view | ✅ Complete |
| CM-003 | As a staff member, I want to track client relationships with start/end dates so I can identify active clients | - Date pickers for client_start and client_end<br>- Filter/sort by status<br>- Show duration in months/years | ✅ Complete |
| CM-004 | As an admin, I want to classify clients as Cash or Pro Bono so I can generate financial reports | - Dropdown with options from option_values (set_id=26)<br>- Include in reporting filters | ✅ Complete |
| CM-005 | As a lawyer, I want to view all cases associated with a client in one screen so I can understand the full relationship | - Client detail page shows linked cases table<br>- Sortable by status, date<br>- Click to navigate to case detail | ✅ Complete |

**Data Model**:
```typescript
interface Client {
  id: number;
  client_code: string; // Unique, auto-generated
  client_name_ar: string; // Required
  client_name_en?: string; // Optional
  client_print_name: string; // Required (how to display on documents)
  status: 'Active' | 'Inactive';
  cash_or_probono_id?: number; // FK to option_values
  client_start?: Date;
  client_end?: Date;
  contact_lawyer_id?: number; // FK to lawyers
  logo?: string; // Path to uploaded logo file
  power_of_attorney_location?: string;
  documents_location?: string;
  // Audit fields...
}
```

**API Endpoints**:
- `GET /api/clients` - List with pagination, filters
- `GET /api/clients/:id` - Detail view with related entities
- `POST /api/clients` - Create new client
- `PUT /api/clients/:id` - Update client
- `DELETE /api/clients/:id` - Soft delete (creates deletion bundle)

---

### 2.2 Case Management (Module 2)

**Priority**: **P0 (Critical)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| CA-001 | As a lawyer, I want to create a case linked to a client so I can track litigation | - Select client from dropdown<br>- Enter matter_name_ar and matter_name_en<br>- Set status, importance, category from option values<br>- Assign partner from lawyers list | ✅ Complete |
| CA-002 | As a partner, I want to assign multiple opponents to a case with different capacities so I accurately represent multi-party litigation | - Add opponent with capacity (plaintiff, defendant, etc.)<br>- Support adding same opponent multiple times with different capacities<br>- Mark one opponent as primary<br>- Display order customizable | ✅ Complete (Multi-opponent PP feature) |
| CA-003 | As a lawyer, I want to record court and circuit details so hearings can be properly scheduled | - Select court from courts table<br>- Select circuit name, serial, shift from option values<br>- Enter circuit secretary, floor, hall<br>- Validate completeness | ✅ Complete |
| CA-004 | As a partner, I want to track financial aspects of cases (asked amount, judged amount, fees) so I can manage firm revenue | - Decimal fields for amounts<br>- Fee letter field<br>- Allocated budget text field<br>- Display on reports/dashboards | ✅ Complete |
| CA-005 | As a lawyer, I want to assign a team to a case so multiple lawyers can collaborate | - Select team from teams dropdown<br>- Team members auto-associated<br>- Override with lawyer_a, lawyer_b if needed | ✅ Complete |

**Data Model** (Key Fields):
```typescript
interface Case {
  id: number;
  client_id: number; // FK to clients (required)
  matter_name_ar: string; // Required (often case number)
  matter_name_en: string; // Required
  matter_description?: string; // Long text
  matter_status_id?: number; // FK to option_values (set_id=15)
  matter_importance_id?: number; // FK to option_values (set_id=16)
  matter_category_id?: number; // FK to option_values (set_id=17)
  court_id?: number; // FK to courts
  circuit_name_id?: number; // FK to option_values (set_id=19)
  matter_partner_id?: number; // FK to lawyers
  team_id?: number; // FK to teams
  matter_start_date?: Date;
  matter_end_date?: Date;
  // ... 60+ fields covering all aspects
}
```

**API Endpoints**:
- `GET /api/cases` - List with filters (client, status, partner, importance)
- `GET /api/cases/:id` - Detail with opponents, hearings, tasks, documents
- `POST /api/cases` - Create case
- `PUT /api/cases/:id` - Update case
- `DELETE /api/cases/:id` - Soft delete with cascade deletion bundle

---

### 2.3 Hearing Management (Module 3)

**Priority**: **P0 (Critical)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| HE-001 | As a lawyer, I want to record hearing dates and decisions so I can track case progress | - Link to case (matter_id)<br>- Date picker for hearing date<br>- Rich text for decision field<br>- Next hearing date<br>- Status/procedure dropdown | ✅ Complete |
| HE-002 | As a paralegal, I want to track which lawyers attended hearings so we can manage billable hours | - Select lawyer_id from lawyers<br>- Support attendee, attendee_1..attendee_4 fields<br>- Track next_attendee for scheduling | ✅ Complete |
| HE-003 | As a lawyer, I want to flag hearings that require client notification so admin staff can send updates | - Boolean notify_client checkbox<br>- Show on dashboard/task list for admin<br>- Link to client contact info | ✅ Complete |
| HE-004 | As a lawyer, I want to see a chronological history of all hearings for a case so I can prepare for court | - Case detail page shows hearings table<br>- Sort by date (desc by default)<br>- Show short_decision in list, full decision in detail | ✅ Complete |

**API Endpoints**:
- `GET /api/hearings` - List with filters (case, lawyer, date range)
- `GET /api/cases/:caseId/hearings` - Hearings for specific case
- `POST /api/hearings` - Create hearing
- `PUT /api/hearings/:id` - Update hearing
- `DELETE /api/hearings/:id` - Soft delete

---

### 2.4 Document Management (Module 4)

**Priority**: **P0 (Critical)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| DO-001 | As a paralegal, I want to upload documents linked to clients or cases so they are centrally stored | - File upload with drag-drop<br>- Select client (required) and case (optional)<br>- Auto-capture file metadata (size, MIME type)<br>- Storage type: physical/digital/both | ✅ Complete |
| DO-002 | As a lawyer, I want to categorize documents by type so I can find them quickly | - Document type dropdown from option_values (set_id=23)<br>- Searchable/filterable by type<br>- Bulk categorization support | ✅ Complete |
| DO-003 | As an admin, I want to track document movements (check-out/check-in) so we know who has what | - Movement form: from_location, to_location, status<br>- History table on document detail<br>- Movement card boolean flag | ✅ Complete (Movement system) |
| DO-004 | As a lawyer, I want to analyze document content using AI so I can quickly understand key points | - "Analyze" button on document detail<br>- Call Google Gemini API via backend<br>- Show summary, entities, potential arguments<br>- Bilingual support (EN/AR) | ✅ Complete (AI integration) |

**API Endpoints**:
- `GET /api/documents` - List with filters
- `POST /api/documents` - Upload with multipart/form-data
- `GET /api/documents/:id` - Download with signed URL
- `PUT /api/documents/:id` - Update metadata
- `DELETE /api/documents/:id` - Soft delete
- `POST /api/documents/:id/movements` - Create movement record
- `POST /api/documents/:id/analyze` - AI analysis

---

### 2.5 Administrative Task Management (Module 5)

**Priority**: **P1 (High)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| AT-001 | As a partner, I want to create tasks for non-hearing work so all case activities are tracked | - Link to case<br>- Assign lawyer<br>- Set required_work description<br>- Status tracking<br>- Alert flag for urgent items | ✅ Complete |
| AT-002 | As a paralegal, I want to break tasks into subtasks so complex work is manageable | - Create subtask under parent task<br>- Assign performer<br>- Track next_date and procedure_date<br>- Result field for outcome | ✅ Complete |
| AT-003 | As a lawyer, I want to see all my assigned tasks in one dashboard so I can prioritize work | - My Tasks view filtered by lawyer_id<br>- Sort by status, due date<br>- Show case name for context | ⏳ Planned (Dashboard enhancement) |

**API Endpoints**:
- `GET /api/admin-tasks` - List with filters
- `GET /api/cases/:caseId/tasks` - Tasks for case
- `POST /api/admin-tasks` - Create task
- `PUT /api/admin-tasks/:id` - Update task
- `POST /api/admin-tasks/:taskId/subtasks` - Create subtask

---

### 2.6 User & Access Management (Module 6)

**Priority**: **P0 (Critical)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| UM-001 | As an admin, I want to create user accounts with role assignments so access is controlled | - User form with name, email, password<br>- Role dropdown (5 roles)<br>- Active/inactive status<br>- Email verification (optional) | ✅ Complete |
| UM-002 | As an admin, I want to define roles with specific permissions so I can implement least-privilege access | - Role management UI<br>- Permission checkboxes (22 permissions in 4 groups)<br>- Preview which users have each role | ✅ Complete |
| UM-003 | As a user, I want to login with email/password and stay authenticated across sessions so I don't have to login repeatedly | - Laravel Sanctum session-based auth<br>- Remember me option<br>- Secure token storage | ✅ Complete (LoginPage exists) |
| UM-004 | As an admin, I want to see an audit log of all user actions so I can investigate issues | - Activity log table (Spatie integration)<br>- Filter by user, date, action type<br>- Show subject (what was changed) | ✅ Complete (Spatie ActivityLog) |

**RBAC Matrix**:

| Permission | Super Admin | Admin | Lawyer | Staff |
|-----------|-------------|-------|--------|-------|
| case:create | ✅ | ✅ | ✅ | ❌ |
| case:view | ✅ | ✅ | ✅ | ✅ |
| case:edit | ✅ | ✅ | ✅ | ❌ |
| case:delete | ✅ | ✅ | ❌ | ❌ |
| user:manage | ✅ | ✅ | ❌ | ❌ |
| trash:purge | ✅ | ❌ | ❌ | ❌ |

**API Endpoints**:
- `POST /api/login` - Authenticate user
- `POST /api/logout` - End session
- `GET /api/user` - Current user profile
- `GET /api/users` - List users (admin only)
- `POST /api/users` - Create user (admin only)
- `GET /api/roles` - List roles with permissions
- `PUT /api/roles/:id` - Update role permissions

---

### 2.7 Trash & Recovery System (Module 7)

**Priority**: **P1 (High)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| TR-001 | As an admin, I want to view deleted items in a trash bin so I can recover accidental deletions | - Trash UI showing deletion bundles<br>- Filter by root_type, deleted_by, date<br>- Show cascade_count (how many related items)<br>- Status badges (trashed/restored/purged) | ✅ Complete |
| TR-002 | As an analyst, I want to restore deleted entities with conflict resolution so I can fix mistakes | - Restore button on bundle detail<br>- Conflict strategy selection (skip/overwrite/rename)<br>- Dry-run mode to preview<br>- Restore report showing results | ✅ Complete |
| TR-003 | As a partner, I want automatic purging of old trash items so storage doesn't grow indefinitely | - TTL field on bundles (default 90 days)<br>- Scheduled task to purge expired bundles<br>- Configurable TTL per bundle | ✅ Complete |

**API Endpoints**:
- `GET /api/trash/bundles` - List deletion bundles
- `GET /api/trash/bundles/:id` - Bundle detail
- `POST /api/trash/bundles/:id/restore` - Restore with conflict strategy
- `DELETE /api/trash/bundles/:id` - Permanently purge
- `POST /api/trash/bundles/:id/dry-run` - Test restore

---

### 2.8 Data Import System (Module 8)

**Priority**: **P1 (High)**

**User Stories**:

| ID | User Story | Acceptance Criteria | Status |
|----|-----------|-------------------|--------|
| IM-001 | As an admin, I want to import cases from Excel so I can migrate from MS Access | - Upload Excel file<br>- Map columns to database fields<br>- Preflight validation with error reporting<br>- Import with progress tracking | ✅ Complete (ETL pipeline) |
| IM-002 | As a data manager, I want to see import statistics so I can identify data quality issues | - Dashboard showing import sessions<br>- Success/failure counts<br>- Error logs downloadable<br>- Orphaned record detection | ✅ Complete (Quality dashboard) |
| IM-003 | As an admin, I want idempotent imports so I can re-run imports without duplicates | - Check for existing records by business key<br>- Update instead of insert if exists<br>- Log skipped/updated counts | ✅ Complete |

**API Endpoints**:
- `POST /api/imports/upload` - Upload file, create import session
- `POST /api/imports/:sessionId/map` - Save column mapping
- `POST /api/imports/:sessionId/validate` - Run preflight
- `POST /api/imports/:sessionId/execute` - Run import
- `GET /api/imports/:sessionId/status` - Check progress

---

## 3. Non-Functional Requirements

### 3.1 Performance

| Requirement | Target | Measurement Method |
|------------|--------|-------------------|
| Page load time (initial) | < 3 seconds | Lighthouse score |
| Page load time (subsequent) | < 1 second | Browser DevTools |
| API response time (95th percentile) | < 500ms | APM (Laravel Telescope) |
| Search results | < 2 seconds | Manual testing |
| Large file upload (10 MB) | < 30 seconds | Upload progress tracking |
| Case with 500+ hearings detail load | < 5 seconds | React DevTools Profiler |

### 3.2 Scalability

| Metric | Current | 6 Months | 12 Months |
|--------|---------|----------|-----------|
| Concurrent users | 50 | 500 | 2000 |
| Total cases | 2,000 | 20,000 | 100,000 |
| Documents stored | 500 | 10,000 | 50,000 |
| Database size | 500 MB | 5 GB | 25 GB |
| API requests/minute | 1,000 | 10,000 | 50,000 |

**Scalability Strategy**:
- Horizontal scaling via load balancer (planned)
- Database read replicas for reporting queries
- Redis caching for frequently accessed data
- CDN for static frontend assets

### 3.3 Availability

| Requirement | Target | Strategy |
|------------|--------|----------|
| Uptime | 99.9% | Redundant servers, health checks |
| Planned maintenance window | < 2 hours/month | Off-peak hours (weekend nights) |
| Database backup frequency | Every 6 hours | Automated backups to cloud storage |
| Disaster recovery time (RTO) | < 4 hours | Documented runbook, tested quarterly |
| Data loss tolerance (RPO) | < 1 hour | Point-in-time recovery capability |

### 3.4 Security

| Requirement | Implementation |
|------------|----------------|
| Authentication | Laravel Sanctum (session + token-based) |
| Authorization | Spatie Permission (RBAC with 22 permissions) |
| Password policy | Min 8 chars, 1 uppercase, 1 number, 1 special |
| Session timeout | 2 hours of inactivity |
| Data encryption (in transit) | TLS 1.3 (HTTPS enforced) |
| Data encryption (at rest) | Database-level encryption (planned) |
| API rate limiting | 60 requests/minute per user |
| Audit logging | All CRUD operations logged (Spatie ActivityLog) |
| Vulnerability scanning | Quarterly penetration testing (planned) |

### 3.5 Usability

| Requirement | Implementation |
|------------|----------------|
| Language support | English and Arabic (full bilingual) |
| RTL layout | CSS direction switching based on language |
| Mobile responsiveness | Bootstrap-based responsive design (Tailwind CSS) |
| Accessibility (WCAG) | WCAG 2.1 Level AA compliance (target) |
| Search functionality | Global search across cases, clients, documents |
| User manual | English and Arabic user guides (planned) |
| Inline help | Tooltips and contextual help (planned) |

### 3.6 Maintainability

| Requirement | Implementation |
|------------|----------------|
| Code coverage | 70% unit + integration tests (target, currently 45%) |
| Documentation | Technical dossier, ADRs, runbooks, API docs |
| Version control | Git with feature branches and pull requests |
| CI/CD | Automated testing and deployment (planned) |
| Monitoring | Laravel Telescope (dev), APM (prod planned) |
| Error tracking | Centralized logging (Sentry/Bugsnag planned) |

---

## 4. Technical Constraints

### 4.1 Technology Stack (Fixed)

| Component | Technology | Version | Reason |
|-----------|-----------|---------|--------|
| Backend | Laravel | 10.49.1 | Existing codebase, extensive ecosystem |
| Frontend | React | 19.2.0 | Modern, maintainable, TypeScript support |
| Database | MySQL | 9.1.0 | Existing schema, relational data |
| Server | Apache (WAMP) | 2.4 (dev) | Development environment |
| Language | PHP | 8.4 | Laravel 10 requirement |

### 4.2 Browser Support

| Browser | Minimum Version | Notes |
|---------|----------------|-------|
| Chrome | Latest - 2 | Primary browser |
| Firefox | Latest - 2 | Secondary |
| Safari | Latest - 2 | Mac users |
| Edge | Latest - 2 | Windows users |
| Mobile Safari | iOS 14+ | Responsive design |
| Chrome Mobile | Android 10+ | Responsive design |

**No Internet Explorer support** (end of life)

### 4.3 Integration Requirements

| System | Purpose | Status |
|--------|---------|--------|
| MFiles | Document management system | ⏳ Planned (fields present) |
| Google Gemini | AI document analysis | ✅ Integrated (backend proxy needed) |
| Email (SMTP) | Notifications | ✅ Configured (Laravel Mail) |
| Cloud Storage | File uploads | ⏳ Planned (S3/MinIO) |

---

## 5. User Interface Requirements

### 5.1 Key Screens

| Screen | Purpose | Priority |
|--------|---------|----------|
| Dashboard | Overview with statistics and recent activity | P0 |
| Cases List | Filterable, sortable list of cases | P0 |
| Case Detail | Complete case information with tabs (hearings, documents, tasks) | P0 |
| Clients List | Client directory with search | P0 |
| Client Detail | Client profile with related entities | P0 |
| Hearings Calendar | Upcoming hearings across all cases | P1 |
| Document Library | Searchable document repository | P0 |
| My Tasks | Lawyer's assigned tasks dashboard | P1 |
| Reports | Pre-built and custom reports | P1 |
| Settings | System configuration and user management | P0 |

### 5.2 Navigation

**Primary Navigation** (Sidebar):
- Dashboard
- Cases
- Clients
- Lawyers
- Courts
- Hearings
- Documents
- Tasks
- Reports
- Settings (admin only)

**Secondary Navigation** (Breadcrumbs):
- Show navigation path: e.g., "Home > Cases > Case #983/11ق > Edit"

**Global Actions** (Header):
- Quick search
- Language toggle (EN/AR)
- User profile menu
- Notifications (future)

### 5.3 Responsive Design

**Breakpoints**:
- Mobile: < 768px (1 column layout)
- Tablet: 768px - 1024px (2 column layout)
- Desktop: > 1024px (multi-column, sidebar always visible)

**Mobile-specific** considerations:
- Hamburger menu for navigation
- Collapsible sections on detail pages
- Swipe gestures for lists
- Optimized form inputs (date pickers, dropdowns)

---

## 6. Data Requirements

### 6.1 Data Volume

**Current**:
- 308 clients
- 1,695 cases
- 369 hearings
- 404 documents
- 4,077 tasks
- 14 lawyers

**Projected** (12 months):
- 1,000 clients
- 10,000 cases
- 50,000 hearings
- 25,000 documents
- 50,000 tasks
- 50 lawyers

### 6.2 Data Retention

| Data Type | Retention Policy | Rationale |
|-----------|-----------------|-----------|
| Active cases | Indefinite | Legal requirement |
| Closed cases | 10 years | Statute of limitations |
| Documents | 10 years | Legal requirement |
| Audit logs | 7 years | Compliance |
| Deletion bundles | 90 days (default) | Recovery window |
| Import logs | 1 year | Troubleshooting |

### 6.3 Data Migration

**Source**: MS Access Database  
**Volume**: ~2,000 cases, 300+ clients, 400+ documents

**Migration Status**:
- ✅ Clients: 100% migrated
- ✅ Cases: 99.65% migrated
- ✅ Admin Tasks: 98.79% migrated
- ⏳ Hearings: 3.5% migrated (orphaned FKs expected)
- ✅ Documents: 100% migrated

**Data Quality**:
- Fuzzy matching for Arabic opponent names (trigram-based)
- Option value resolution for dropdowns
- FK validation with orphan detection
- Preflight error reporting

---

## 7. Compliance & Regulatory Requirements

### 7.1 Data Privacy (Applicable Regulations)

| Regulation | Requirement | Implementation |
|-----------|-------------|----------------|
| GDPR (if EU clients) | Right to erasure | Deletion bundles + manual purge |
| GDPR | Data portability | Export functionality (planned) |
| Local data protection laws | Audit trail | Activity logging (Spatie) |
| Legal ethics | Confidentiality | RBAC, encryption, access logs |

### 7.2 Audit Requirements

- All CRUD operations logged with user, timestamp, and changes
- Immutable audit log (activity_log table)
- Audit log retention: 7 years
- Ability to generate audit reports on demand

### 7.3 Backup & Recovery

- Daily automated backups to secure location
- Backup retention: 30 days (daily), 12 months (monthly)
- Quarterly disaster recovery drills
- Documented recovery procedures (runbook)

---

## 8. Success Metrics (KPIs)

### 8.1 User Adoption

| Metric | Target (6 months) | Measurement |
|--------|------------------|-------------|
| Active users (monthly) | 100% of firm | Login analytics |
| Cases created in system vs. offline | 90% in system | Manual audit |
| Documents uploaded vs. physical only | 80% digital | Document counts |
| User satisfaction score | >= 4.0/5.0 | Quarterly survey |

### 8.2 Operational Efficiency

| Metric | Baseline | Target (6 months) | Measurement |
|--------|----------|------------------|-------------|
| Time to find case information | 10 min | 2 min | User survey |
| Time to prepare for hearing | 2 hours | 1 hour | User survey |
| Administrative overhead (hours/week) | 20 hours | 12 hours | Time tracking |
| Document retrieval time | 30 min | 5 min | User survey |

### 8.3 System Health

| Metric | Target | Measurement |
|--------|--------|-------------|
| Uptime | >= 99.9% | Monitoring (uptime.com) |
| API error rate | < 0.1% | Application logs |
| Page load time (P95) | < 3 sec | Real User Monitoring |
| Support tickets/month | < 10 | Issue tracker |

---

## 9. Release Plan

### 9.1 Version 1.0 (Current Status)

**Status**: ✅ Development Complete (pending API integration)

**Features**:
- Client management (CRUD)
- Case management with multi-opponent support
- Hearing tracking
- Document management with movement tracking
- Task management
- User/role management (RBAC)
- Trash & recovery system
- Data import pipeline (ETL)
- AI document analysis (Gemini)
- Bilingual EN/AR support with RTL

**Known Limitations**:
- Frontend using mock data (migration to API in progress)
- No real-time notifications
- Limited reporting capabilities
- Mobile experience needs optimization

### 9.2 Version 1.1 (Planned - Q1 2026)

**Theme**: API Integration & Production Readiness

**Features**:
- ✅ Complete React-Laravel API integration
- ✅ Enhanced error handling and loading states
- ✅ Redis caching layer
- ✅ Cloud file storage (S3/MinIO)
- ✅ Comprehensive test coverage (70%+)
- ✅ CI/CD pipeline
- ✅ Production deployment
- ⏳ OpenAPI documentation

### 9.3 Version 1.2 (Planned - Q2 2026)

**Theme**: Advanced Features & Performance

**Features**:
- ⏳ Real-time notifications (WebSockets)
- ⏳ Advanced reporting and analytics
- ⏳ Full-text search (Meilisearch)
- ⏳ Calendar view for hearings
- ⏳ Email integration (automated notifications)
- ⏳ Mobile app (React Native)

### 9.4 Version 2.0 (Future Vision)

**Theme**: AI-Powered Legal Assistant

**Features**:
- Predictive case outcome analytics
- Automated legal research integration
- Natural language search
- Document classification (ML-based)
- Intelligent task suggestions
- Client self-service portal

---

## 10. Assumptions & Dependencies

### 10.1 Assumptions

1. Law firm has stable internet connectivity (minimum 10 Mbps)
2. Users have basic computer literacy
3. Firm will provide training for new users
4. Existing data from MS Access is reasonably clean
5. Arabic language support is critical (not optional)

### 10.2 Dependencies

| Dependency | Owner | Risk | Mitigation |
|-----------|-------|------|------------|
| Gemini API availability | Google | Medium | Fallback to manual analysis |
| MFiles integration | Third-party | Low | Optional feature, not critical |
| Cloud storage (S3) | AWS | Low | Local fallback if needed |
| SSL certificate | IT department | Low | Free Let's Encrypt |

---

## 11. Out of Scope (for Version 1.0)

- Mobile native apps (iOS/Android)
- Third-party API integrations (beyond MFiles, Gemini)
- Multi-tenancy (separate instances per firm)
- Blockchain-based audit trail
- Video conferencing integration
- E-signature integration
- Time tracking and billing
- Email client integration (Outlook/Gmail plugins)

---

## Conclusion

This PRD captures the requirements for the Centralized Litigation Management System as reverse-engineered from the existing codebase. The system is well-architected to support the complex workflows of litigation management with strong foundations in bilingual support, comprehensive data modeling, and enterprise-grade features like RBAC and audit logging.

**Key Strengths**:
- Comprehensive domain coverage
- Bilingual-first design
- Innovative features (multi-opponent, deletion bundles)
- Solid technical implementation

**Primary Gap**:
- Frontend-backend integration completion (in progress)

With successful completion of API migration and deployment, this system is ready for production use by law firms requiring bilingual litigation management capabilities.

---

**Document End**  
*Generated by Antigravity AI - Reverse-Engineered PRD*
