# Central Litigation Management

**Version**: 1.1  
**Status**: Active Development  
**Framework**: Laravel 10.49.1 | PHP 8.4 | MySQL 9.1.0  

---

## Overview

Central Litigation Management (CLM) is a comprehensive bilingual (English/Arabic) web application for managing legal cases, clients, court hearings, documents, and administrative workflows. Built with enterprise-grade security and data recovery features.

---

## Key Features

- 🏢 **Client Management**: Organizations and individuals with full relationship tracking
- ⚖️ **Case Management**: Legal matters with lifecycle management
- 📅 **Hearing Management**: Court sessions, dates, decisions, attendees
- 📄 **Document Management**: Secure file storage with access control
- 📋 **Administrative Workflows**: Tasks and subtasks with lawyer assignments
- 🗑️ **Restorable Trash System**: Enterprise-grade data recovery with snapshot bundles ← **New**
- 🔐 **Role-Based Access Control**: 5 roles, 22 permissions, policy-based authorization
- 📊 **Audit Logging**: Complete activity trail for compliance
- 🌐 **Bilingual Interface**: English/Arabic with RTL support

---

## Quick Start

### Prerequisites
- PHP 8.4+
- MySQL 9.1.0
- Composer 2.x
- Node.js 18+ & npm

### Installation

```bash
# Clone repository
git clone <repository-url>
cd clm-app

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install && npm run build

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Generate application key
php artisan key:generate

# Run migrations and seed database
php artisan migrate:fresh --seed

# Start development server
php artisan serve
```

### Default Credentials

**Super Admin**:
- Email: `khelmy@sarieldin.com`
- Password: `P@ssw0rd`

⚠️ **Change password immediately after first login!**

---

## Trash / Recycle Bin System ← **New Feature**

### What It Does
Automatically captures snapshots of deleted entities with their entire relationship graph, allowing complete restoration later.

### Quick Usage

#### Delete (Auto-creates Bundle)
```php
$client->delete(); // Bundle automatically created
```

#### List Bundles
```bash
php artisan trash:list --type=Client
```

#### Restore
```bash
# Test first (dry-run)
php artisan trash:restore abc-123-uuid --dry-run

# Actual restore
php artisan trash:restore abc-123-uuid
```

#### Web UI
Visit `/trash` as admin to view and manage deletion bundles.

### Supported Models
All core models create bundles: Client, Case, Document, Hearing, AdminTask, AdminSubtask, EngagementLetter, PowerOfAttorney, Contact, Lawyer.

### Learn More
📚 **[Trash Restore Runbook](../docs/runbooks/Trash_Restore_Runbook.md)** — Complete operational guide

---

## Project Structure

```
clm-app/
├── app/
│   ├── Console/Commands/           # CLI commands (trash:*, etc.)
│   ├── Http/
│   │   ├── Controllers/            # Web controllers
│   │   ├── Middleware/             # Custom middleware
│   │   └── Requests/               # Form validation
│   ├── Models/                     # Eloquent models (21 total)
│   ├── Policies/                   # Authorization policies
│   ├── Services/                   # Business logic (DeletionBundleService, etc.)
│   └── Support/
│       └── DeletionBundles/        # Trash system (trait, collectors)
├── config/
│   ├── trash.php                   # Trash system configuration ← New
│   ├── permission.php              # Spatie Permission config
│   └── ...
├── database/
│   ├── migrations/                 # Database schema (23 migrations)
│   └── seeders/                    # Data seeders
├── resources/
│   ├── views/
│   │   ├── trash/                  # Trash UI views ← New
│   │   └── ...
│   └── lang/                       # Translations (planned)
├── routes/
│   ├── web.php                     # Web routes (includes trash routes)
│   └── api.php                     # API routes
└── tests/
    └── Feature/                    # Feature tests (20 tests, 124 assertions)
```

---

## Database Schema (23 Tables)

### Core Domain (10)
- clients, cases, hearings, lawyers, contacts
- engagement_letters, power_of_attorneys
- admin_tasks, admin_subtasks, client_documents

### System Features (13)
- users, roles, permissions (RBAC)
- **deletion_bundles, deletion_bundle_items** (Trash) ← New
- activity_log (Audit)
- password_resets, failed_jobs, etc.

---

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Auth tests
php artisan test --filter=SuperAdminSeederTest

# Trash tests
php artisan test --filter=TrashSystemTest

# Permission tests
php artisan test --filter=PermissionAssignmentTest
```

### Current Coverage
- **Total**: 20 tests, 124 assertions
- **Success Rate**: 100%
- **Coverage**: ~30% (foundational phase)

---

## CLI Commands

### Trash Management ← **New**
```bash
# List deletion bundles
php artisan trash:list [--type=Client] [--status=trashed] [--limit=20]

# Restore bundle
php artisan trash:restore {bundle-uuid} [--dry-run] [--resolve-conflicts=skip]

# Purge bundles
php artisan trash:purge {bundle-uuid}
php artisan trash:purge --older-than=90 [--force]
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh install with seed data
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback
```

### Development
```bash
# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Documentation

📖 **[Master Plan](../docs/master-plan.md)** — Project roadmap and phases  
📋 **[Tasks Index](../docs/tasks-index.md)** — Task tracker with DoD  
🏗️ **[ERD](../docs/erd.md)** — Entity Relationship Diagram  
📚 **[ADRs](../docs/adr/)** — Architecture Decision Records (6 ADRs)  
🔧 **[Runbooks](../docs/runbooks/)** — Operational guides  
🔒 **[Security Controls](../docs/security/Security-Controls.md)** — Security documentation  

---

## Contributing

### Development Workflow
1. Create feature branch (`feat/`, `fix/`, `chore/`)
2. Implement changes
3. Write tests (maintain ≥60% coverage)
4. Update documentation
5. Commit with Conventional Commits format
6. Open PR for review

### Commit Format
```
feat(domain): add case reassignment feature
fix(trash): handle orphaned documents on restore
docs(adr): update ADR-006 with TTL policy
test(trash): add bulk purge test
```

---

## License

Proprietary — All rights reserved.

---

## Support

**Documentation**: `/docs/`  
**Issues**: Internal tracker  
**Contact**: khelmy@sarieldin.com  

---

**Built with ❤️ using Laravel**  
**Last Updated**: 2025-10-08
