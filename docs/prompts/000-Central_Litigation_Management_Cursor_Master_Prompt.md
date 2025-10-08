# Central Litigation Management — Cursor Master Prompt

You are an expert full‑stack Laravel engineer and project manager tasked to **design, plan, scaffold, and iteratively implement** a bilingual (English/Arabic) web application named **Central Litigation Management** using the provided Excel exports in folder \Access_Data_Export\ (from MS Access) as the source of truth for the domain model.

---

## 1) Source Data (Excel → MySQL)
Use the following **inferred data dictionary** as a starting point. Confirm all types and relationships by inspecting the Excel content, then refine during migration. Normalize where appropriate (3NF+), add foreign keys, indexes, and constraints, and capture **all assumptions** in ADRs.

# Data Dictionary (Inferred from Excel)
Below is an inferred schema from the uploaded Excel exports (sheet-by-sheet). Types are inferred and may need adjustment during migration.
## admin_work_subtasks.xlsx — Sheet: إجراءات_المهام
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| Lawyer_ID | integer | 0.0% | 6 |
| Subtask_ID | integer | 0.0% | 2797 |
| Task_ID | integer | 1.0% | 10421 |
| القائم بالعمل | string | 0.0% | ** |
| الموعد القادم | date | 99.8% | 2019-10-28 |
| النتيجة | string | 0.7% | تم تحديد جلسة في 23-05-2022 (موقع محكمة النقض) |
| تاريخ الإجراء | date | 2.5% | 2022-04-05 |
| تقرير | boolean | 0.0% | True |

## admin_work_tasks.xlsx — Sheet: admin_work_table
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| Task_ID | integer | 0.0% | 271 |
| matter_ID | integer | 0.0% | 450 |
| آخر متابعة | string | 63.2% | صالح |
| آخر موعد | date | 100.0% | 2019-10-27 |
| الجهة | string | 0.3% | مجلس الدولة بالجيزة |
| الحالة | string | 58.3% | منجزة |
| الدائرة | string | 11.8% | 7 استثمار |
| العمل المطلوب | string | 1.2% | متابعة قرار المحكمة. |
| القائم بالعمل | string | 46.1% | ** |
| القرار السابق | string | 47.0% | أول جلسة |
| المحكمة | string | 0.3% | الإدارية العليا |
| النتيجة | string | 42.7% | تم إيداع مذكرة الطعن، ولم يتم تحديد جلسة حتى تاريخه. |
| تاريخ الإنشاء | datetime | 49.0% | 2019-03-10 00:00:00 |
| تاريخ التنفيذ | datetime | 43.8% | 2013-06-17 00:00:00 |
| تنبيه | boolean | 0.0% | False |

## cases.xlsx — Sheet: الدعاوى
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| circutSecretary | string | 99.4% | فارس السيد أحمد |
| client&Cap | string | 0.9% | نافتو جاز أوف أوكرانيا_x000D_
_x000D_
"مدعى عليها" |
| clientBranch | string | 67.1% | تويوتا إيجيبت |
| client_id | integer | 0.0% | 222 |
| contractID | integer | 96.6% | 197.0 |
| courtFloor | integer | 100.0% | 3 |
| courtHall | integer | 100.0% | 2 |
| lawyerA | string | 48.8% | محمد عبد العزيز، أحمد سعيد |
| lawyerB | string | 86.9% | أحمد إسماعيل |
| matteEvaluation | string | 87.2% | ضد |
| matterAskedAmount | float | 96.3% | 86000.0 |
| matterCategory | string | 12.6% | جنايات |
| matterCircut | string | 86.4% | جنائي |
| matterCourt | string | 83.5% | النقض |
| matterDegree | string | 14.6% | نقض |
| matterDistination | string | 88.7% | دار القضاء العالي |
| matterEndDate | date | 89.4% | 2020-07-07 |
| matterImportance | string | 30.3% | حرجة |
| matterJudgedAmount | float | 96.4% | 0.0 |
| matterNotes1 | string | 92.3% | أحمد عبد الله: من أول جلسة حتى جلسة 1-9-2015.
ناجي رمضان: من جلسة 12-1-2016 ح... |
| matterNotes2 | string | 93.7% | مبلغ المطالبة: 86.000 جنيه تقريباً._x000D_
المبلغ المقضي به: لا يوجد. |
| matterPartner | string | 30.9% | د. هاني سري الدين |
| matterSelect | boolean | 0.0% | True |
| matterShelf | string | 28.8% | EJ9548 |
| matterStartDate | date | 4.9% | 2017-01-10 |
| matter_description | string | 2.5% | دعوى عمالية بطلب إلزام بمكافأة نهاية خدمة. |
| matter_id | integer | 0.0% | 1 |
| matter_name_ar | string | 0.0% | (نافتو جاز) 732 / 2016 |
| matter_name_en | string | 0.0% | (نافتو جاز) 732 / 2016 |
| matter_status | string | 0.8% | منتهية |
| opponent&Cap | string | 3.1% | يوسف محمد عبد الرحمن_x000D_
_x000D_
"مدعي" |
| secretaryRoom | float | 100.0% |  |
| الرأي القانوني | string | 99.5% | لم يتم التحقيق مع الموظفة الشاكية، ولم يتم التحقيق مع الغير بما يفيد إثبات ال... |
| المخصص المالي | string | 93.2% | لا تحتاج إلى مخصص مالي. |
| الموقف الحالي | string | 93.2% | بجلسة 2014/05/31 قررت المحكمة: قبول التظلم شكلاً ورفضه موضوعاً. |
| خطاب الأتعاب | float | 75.9% | 25098.0 |
| فريق العمل | integer | 3.1% | 1 |
| نوع العميل | string | 54.8% | ربحي |

## clients.xlsx — Sheet: العملاء
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| Cash/probono | string | 0.6% | Probono |
| ClientName_ar | string | 0.0% | سري الدين وشركاه -مستشارون قانونيون |
| ClientName_en | string | 26.6% | Sarie Eldin & Partners |
| ClientPrintName | string | 0.0% | سري الدين وشركاه -مستشارون قانونيون |
| Status | string | 0.0% | Active |
| clientEnd | date | 98.7% | 2019-07-17 |
| clientStart | date | 69.8% | 2009-01-01 |
| client_ID | integer | 0.0% | 1 |
| contactLawyer | string | 62.7% | د. هاني سري الدين |
| logo | string | 82.5% | Sarie El-Din new logo.png |
| مكان التوكيل | string | 0.0% | الخزينة |
| مكان المستندات | string | 95.8% | الأرشيف |

## clients_matters_documents.xlsx — Sheet: المستندات
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| client_ID | integer | 0.0% | 277 |
| document_id | integer | 0.0% | 1 |
| العميل | string | 14.9% | هيرمس |
| المحامي/الموظف المسئول | string | 31.4% | نيرمين حجازي |
| بطاقة الحركة | boolean | 0.0% | False |
| بيان المستند | string | 0.0% | حافظة تسليم مستندات + 4 صور البطاقة الشخصية (هشام محمد ممدوح - محمد حمدي مجمد... |
| تاريخ الإيداع | date | 0.0% | 2022-02-22 |
| تاريخ المستند | date | 9.4% | 2022-02-22 |
| رقم الدعوى | string | 49.0% | 10427 لسنة 2012 |
| عدد الأوراق | string | 0.2% | 12 |
| ملاحظات | string | 86.6% | تم تقديمها في الدعوى رقم 288 / 2010 بجلسة 23/5/2017 |

## contacts.xlsx — Sheet: Contacts
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| Address | string | 33.0% | 79 El Geish Street |
| Attachments | float | 100.0% |  |
| Business Phone | string | 53.2% | 050-2310966 |
| City | string | 32.4% | Mansoura |
| Contact1 | string | 3.2% | Ahmed Shawki Montasser |
| Contact_ID | integer | 0.0% | 1 |
| Country/Region | string | 34.6% | Egypt |
| E-mail Address | string | 25.0% | montassir@mahaseel.com |
| Fax Number | string | 75.0% | 050-2311170 |
| Full_name | string | 89.9% | شركة أبو زعبل للأسمدة والمواد الكيماوية |
| Home Phone | float | 99.5% | 1222222167.0 |
| Job Title | string | 39.9% | Deputy Manager |
| Mobile Phone | string | 26.6% | 01223118809 |
| State/Province | string | 35.1% | Dakahlia |
| Web Page | string | 85.6% | www.ey.com/me#http://www.ey.com/me# |
| ZIP/Postal Code | string | 93.6% | Post Code: 11835 P.O.Box341 |
| clientID | integer | 0.0% | 187 |

## engagement_letters.xlsx — Sheet: خطابات_الأتعاب
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| Client | string | 35.0% | الإسلامية للاستثمار الخليجي |
| Cont-Date | datetime | 7.3% | 2011-09-15 00:00:00 |
| Cont-Details | string | 1.2% | مباشرة الجنحة رقم 644 / 2012 جرائم اقتصادية. |
| Cont-Structure | string | 14.6% | 1) 75 ألف دولار دفعة (1 من 2) من الأتعاب._x000D_
2) 50 ألف دولار دفعة (2 من 2... |
| Cont-Type | string | 6.4% | Lump-Sum |
| Matter | string | 40.7% | 644 / 2012_x000D_
حصر جرائم إقتصادي_x000D_
98 / 2013 حصر تحقيق مالية _x000D_
... |
| Status | string | 99.1% | Issuing invoices postpond till finishing the case. |
| client_ID | integer | 0.0% | 88 |
| contract_ID | integer | 0.0% | 1 |
| mfiles_ID | integer | 7.6% | 1 |

## hearings.xlsx — Sheet: الجلسات
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| date | date | 4.0% | 2010-04-07 |
| decision | string | 1.2% | أول جلسة -قررت المحكمة التأجيل لجلسة 19-5-2010 لسند الكالة عن الشركة والاطلاع. |
| hearings_id | integer | 0.0% | 1 |
| lastDecision | string | 61.1% | أول جلسة |
| matter_id | integer | 0.0% | 64 |
| nextHearing | date | 80.8% | 2024-01-21 |
| report | boolean | 0.0% | False |
| shortDecision | string | 79.2% | أول جلسة |
| إخطار العميل بالقرار | boolean | 0.0% | False |
| الإجراء | string | 2.4% | محكمة |
| الجهة | string | 1.5% | القاهرة |
| الحاضر | string | 36.8% | أحمد سعيد |
| الدائرة | string | 1.9% | 40 عمال |
| المحكمة | string | 0.6% | شمال القاهرة |
| حاضر 1 | string | 69.8% | محمد الغرابلي |
| حاضر 2 | string | 98.4% | محمود شعبان |
| حاضر 3 | string | 99.9% | أحمد إسماعيل |
| حاضر 4 | string | 100.0% | إيهاب حمدي |
| حضور الجلسة القادمة | string | 99.9% | أ. أحمد إسماعيل |
| صالح/ضد | string | 94.2% | ضد |
| ملاحظات | string | 99.0% | الدعوى 1026 / 2010 مستأنفة حالياً برقم 6162 / 22ق، وما زالت سارية |

## lawyers.xlsx — Sheet: lawyers
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| AttTrack | boolean | 0.0% | False |
| Lawyer_ID | integer | 0.0% | 15 |
| lawyer_email | string | 4.3% | aismail@sarieldin.com |
| lawyer_name_ar | string | 0.0% | المحامي غير محدد |
| lawyer_name_en | string | 0.0% | Undefined Lawyer |
| lawyer_name_title | string | 4.3% | Mr. |

## power_of_attorneys.xlsx — Sheet: التوكيلات
| Column | Inferred Type | NULL % | Example |
|---|---|---:|---|
| ClientPrintName | string | 14.3% | شريف محمد علي |
| client_ID | integer | 0.0% | 119 |
| اسم الموكل | string | 0.0% | محمد أسامة أحمد شوقي مهران جابر |
| السنة | integer | 0.6% | 2021 |
| الصفة | string | 0.1% | شخصي |
| المحامون الصادر لهم التوكيل | string | 0.4% | حمدي عبد العزيز، أحمد عبد الله محمد، محمد عبد العزيز عبد الحافظ، شريف أبو الم... |
| تاريخ الإصدار | date | 0.3% | 2021-07-03 |
| جرد | boolean | 0.0% | True |
| جهة الإصدار | string | 0.3% | نادي هليوبوليس |
| حرف | string | 1.2% | ل |
| رقم التوكيل | integer | 0.6% | 778 |
| صفة الموكل بالتوكيل | string | 21.2% | شخصي وبأي صفة كانت |
| عدد النسخ | integer | 0.7% | 2 |
| مسلسل | string | 17.7% | B |
| ملاحظات | string | 48.2% | تم إيداعة بمحكمة القاهرة الجديدة |



**Actions:**
1. Generate an **ERD** (tools allowed: Mermaid, dbdiagram.io syntax, or Laravel‑ide‑helper style).  
2. Propose the final **MySQL 9.1.0** schema (utf8mb4, collation `utf8mb4_unicode_ci`), with:
   - Tables, columns, types, defaults, constraints, FKs
   - Junction tables for many‑to‑many relations
   - Composite indexes where needed
   - Soft deletes where appropriate (`deleted_at`)
   - Audit columns (`created_by`, `updated_by`, `created_at`, `updated_at`, `ip_address`, `user_agent`)
3. Provide a **data migration plan** (ETL) from the Excel files to MySQL:
   - Column mapping & transformations
   - Date parsing rules (for columns inferred as "datetime (string, parseable)")
   - Value normalization (enums, booleans, reference tables)
   - Scripts/seeders to import
   - Idempotency & rollback strategy

---

## 2) Target Stack & Environment

**Database**
- Server: MySQL **9.1.0**
- Name: `litigation_db_ver2`
- Host: `localhost`
- Port: `3306`
- Username: `root`
- Password: `1234`
- Collation/Charset: `utf8mb4_unicode_ci` / `utf8mb4`

**Development Host**
- OS: Windows 11
- WAMP 3.3.7 (Apache/2.4.62, PHP 8.4.0)
- PhpMyAdmin 5.2.1
- Local domain: `litigation.local`

**Laravel / PHP**
- Laravel **10.49.0** (or latest compatible with PHP 8.4)
- PHP **8.4**

**Frontend**
- Bootstrap **5**
- Responsive, RTL support

**Localization**
- English + Arabic
- RTL layout; localized date/time/currency

**Security & Quality**
- Role‑Based Access Control (RBAC)
- Audit logging
- Secure file storage
- PSR standards
- Unit tests (coverage goals ≥ 60% critical modules)
- API documentation (OpenAPI) & **Architecture Decision Records (ADRs)**
- Git workflow, code review required

---

## 3) Initial Provisioning Tasks (Small, Commit‑Sized)

> Work in **small, atomic tasks**. After each task, **commit with a descriptive message**, then **ask me to continue**. For any new feature/bug/fix, propose a **branch name** before you start (e.g., `feature/auth-seeders`, `fix/migration-date-parse`, `chore/ci-workflow`).

### T‑01: Laravel Project Setup
- Create Laravel project with config for MySQL above.
- Set `APP_URL=http://litigation.local`, locale defaults (`en` with `ar` fallback properly configured), timezone (`Africa/Cairo`).
- Configure `LOG_CHANNEL=daily`, `LOG_LEVEL=info`.
- Install packages:
  - `laravel/ui` or Breeze/Jetstream (your call; justify in ADR)
  - `spatie/laravel-permission` for RBAC
  - `spatie/laravel-activitylog` (or custom) for audit logging
  - `laravel/sail` optional for parity scripts
  - `barryvdh/laravel-ide-helper` (dev), `larastan/larastan` (dev), `pestphp/pest` (or phpunit)
- Create **ADRs** for: Auth choice, RBAC choice, Activity Log choice, i18n scheme, storage strategy.
- Commit: `chore(bootstrap): init laravel app, env, packages, and ADR skeletons`

### T‑02: Authentication & Super Admin
- Implement email/password auth.
- Seed **Super Admin**:
  - Email: `khelmy@sarieldin.com`
  - Password: `P@ssw0rd` (**hash with Laravel `Hash::make`**; never store plain text)
- Create roles: `super_admin`, `admin`, `lawyer`, `staff`, `client_portal` (adjust later).
- Commit: `feat(auth): seed super admin and base roles`

### T‑03: RBAC & Policies
- Configure roles/permissions via Spatie.
- Map high‑level features to permissions (e.g., `cases.view`, `cases.edit`, `hearings.manage`, `documents.upload`, `admin.users.manage`, etc.).
- Add Gate/Policy scaffolds for core models.
- Commit: `feat(rbac): base permissions and policies`

### T‑04: Core Domain Models & Migrations
- From ERD, create migrations/models for: **Clients, Contacts, Lawyers, Matters/Cases, Hearings, EngagementLetters, POAs, AdminTasks/Subtasks, ClientDocuments** (rename models as needed).
- Include soft deletes & audit columns.
- Commit: `feat(db): core tables + indexes + soft deletes + audit columns`

### T‑05: ETL Importers
- Build console commands to import from Excel files in `/storage/app/imports`:
  - Validations & transforms (date parsing, enums, booleans)
  - Idempotent upserts keyed by natural/legacy keys
  - Logging of rejects with reason CSV
- Commit: `feat(etl): excel importers with validations and idempotent upserts`

### T‑06: Audit Logging
- Log create/update/delete for all core models (with `causer_id`, IP, UA).
- Add activity feed view for admins.
- Commit: `feat(audit): activity logging and admin feed`

### T‑07: Secure File Storage
- Store `clients_matters_documents` and related files under `storage/app/secure` with access guards.
- Generate signed download URLs; enforce permission checks.
- Commit: `feat(files): secure storage with signed URLs and permission checks`

### T‑08: Localization & RTL
- Implement i18n using `resources/lang/en/*.php` and `resources/lang/ar/*.php`.
- Middleware to switch locale via user preference; persist in DB.
- RTL aware layouts; date/time (Cairo timezone), currency helpers.
- Commit: `feat(i18n): bilingual UI + RTL support + locale middleware`

### T‑09: Bootstrap 5 UI
- AdminLTE‑like layout or custom Bootstrap dashboard.
- Navigation for Cases, Clients, Hearings, Documents, Admin Work.
- Global search (Scout optional; otherwise SQL LIKE with indexes).
- Commit: `feat(ui): bootstrap dashboard, nav, and global search`

### T‑10: API & Docs
- Add API routes for core entities (CRUD with policies).
- Generate **OpenAPI** (Swagger) docs.
- Commit: `docs(api): OpenAPI spec and endpoints`

### T‑11: Testing
- Unit tests for policies, ETL validation, and core services.
- Feature tests for critical flows (auth, case create, hearing schedule).
- Commit: `test(core): unit and feature tests for critical flows`

> After completing each task, **ask for confirmation to proceed**. If context window becomes full, **propose a minimal next task** and request opening a new agent with reference to the **Tasks/Subtasks Index**.

---

## 4) Tasks / Subtasks Index (Living Document)
Maintain a numbered index. Example (expand as needed):

- **1. Bootstrap**
  - 1.1 Laravel app init
  - 1.2 Packages & ADRs
- **2. Auth & RBAC**
  - 2.1 Auth scaffolding
  - 2.2 Super Admin seeder
  - 2.3 Roles/Permissions policies
- **3. Domain & DB**
  - 3.1 ERD & migrations
  - 3.2 ETL importers
- **4. Audit & Files**
  - 4.1 Activity logs
  - 4.2 Secure storage
- **5. UX/UI**
  - 5.1 Layout & RTL
  - 5.2 Global search
- **6. API & Tests**
  - 6.1 OpenAPI
  - 6.2 Unit/Feature tests

Each task/subtask must include:
- **Definition of Done (DoD)**
- **Validation steps**
- **Rollback steps**
- **Commit message**
- **Suggested branch name**

---

## 5) Non‑Functional Requirements (NFRs)

- **Security**: Enforce RBAC at route/policy layer. Validate uploads (max 10 MB), MIME checks, antivirus hook (optional), signed URLs, and access logs. Secrets in `.env`, never in code.
- **Performance**: Use eager‑loading, DB indexes, pagination. Monitor slow queries. Cache frequently used lookups.
- **Usability**: Mobile‑first Bootstrap 5; Arabic fonts & RTL correctness; date pickers localized.
- **Reliability**: Migrations versioned; ETL idempotent; backups; audit trail for all critical actions.
- **Maintainability**: Layered architecture (Controllers → Services → Repos), DTOs/Requests, SOLID, PSR‑12, ADRs, docblocks.

---

## 6) Deliverables After Each Iteration

1. **Commit** with descriptive title and body, referencing task ID.  
2. **Short status note** documenting: action list, decisions, issues, fixes, test evidence.  
3. **Prompt to proceed** and **proposed next branch name**.

---

## 7) Working Protocol in Agent Mode

- Work in **tiny, independent chunks** to avoid context overflow.
- Before any new feature/bug/fix: **ask me to create a new branch** and **suggest a name**.
- Keep **Step Log** in `/docs/worklogs/{yyyy-mm-dd}/step-N.md`:
  - Command run, code changes, file paths
  - Errors/bugs faced
  - Fix description & rationale
  - Test steps & outcomes
- After crossing ~60–80 messages or when context is tight, **stop and instruct me** to spawn a new agent. Include the next step summary + branch name.
- Always maintain/update **Tasks/Subtasks Index** and **ADRs**.

---

## 8) Implementation Notes & Hints

- Seeders should hash the Super Admin password using `Hash::make('P@ssw0rd')`.
- Use `spatie/laravel-permission` tables (`roles`, `permissions`, `model_has_roles`, etc.) and seed base permissions.
- For documents, store metadata in DB and the binary in storage with strict policies; generate short‑lived signed routes for downloads.
- Prefer form requests for validation. Centralize date parsing for ETL.
- Use `enum` or config maps for status fields; localize via lang files.
- Add `Observer`s for auditing hooks if not using activitylog.

---

**Now begin with Task T‑01. Propose the branch name and confirm before executing.**