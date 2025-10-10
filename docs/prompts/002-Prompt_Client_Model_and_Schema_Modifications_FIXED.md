
# Prompt — Client Model & Schema Modifications (Corrected Field Names)

You are working on **Central Litigation Management** (Laravel 10, PHP 8.4, MySQL 9.1, Bootstrap 5, EN/AR + RTL).  
Implement the following **Client** model and database changes with **managed dropdowns** and **import compatibility**, using these exact field names from the current database table:

- `cash_or_probono`
- `status`
- `power_of_attorney_location`
- `documents_location`

> All references below use these names consistently. Store **IDs** (FKs) in the client table and render localized labels in the UI.

---

## Goals

1) Convert the four fields above into **managed dropdowns** (admin can add/rename values without breaking historical data).
2) Store **foreign keys** to option values, not raw text.
3) Ensure **import compatibility** with the future `clients.xlsx` (EN/AR headers supported).

---

## Data Model Changes (Migrations)

### A. Generic Option Sets
Create reusable tables:

**`option_sets`**
- `id` (pk), `key` (unique, e.g., `client.cash_or_probono`), `name_en`, `name_ar`, timestamps.

**`option_values`**
- `id` (pk), `set_id` (fk→option_sets), `code` (unique within set), `label_en`, `label_ar`, `position` (int), `is_active` (bool), timestamps.  
- Unique index: (`set_id`,`code`).

Seed sets (keys and codes must be stable):
- `client.cash_or_probono` → (`cash`,`probono`,`unknown`)
- `client.status` → (`disabled`,`active`,`potential`)
- `client.power_of_attorney_location` → (`archive`,`safe`,`handed_to_client`)
- `client.documents_location` → (`archive`,`safe`,`handed_to_client`)

### B. Client Table Adjustments
Add nullable FKs (keep legacy text columns temporarily only if they exist, for data migration):
- `cash_or_probono_id` (fk→option_values)
- `status_id` (fk→option_values)
- `power_of_attorney_location_id` (fk→option_values)
- `documents_location_id` (fk→option_values)

Indexes on each FK; `onDelete('set null')`.

### C. Data Migration
One-off command/migration to map any legacy text values to FK IDs:
- Map headers/values (case/space-insensitive; trim; tolerate AR and synonyms).  
- Unknown values → map to `unknown` where applicable and log to a report.

After verification, optionally drop the legacy text columns.

---

## Model & Relations

**App\Models\Client**
- Relations:
  - `cashOrProbono()` → `belongsTo(OptionValue::class, 'cash_or_probono_id')`
  - `statusRef()` → `belongsTo(OptionValue::class, 'status_id')`  *(name `statusRef` to avoid shadowing attribute `status`)*
  - `powerOfAttorneyLocation()` → `belongsTo(OptionValue::class, 'power_of_attorney_location_id')`
  - `documentsLocation()` → `belongsTo(OptionValue::class, 'documents_location_id')`
- Accessors for localized labels (based on current locale):
  - `getCashOrProbonoLabelAttribute()`
  - `getStatusLabelAttribute()`
  - `getPowerOfAttorneyLocationLabelAttribute()`
  - `getDocumentsLocationLabelAttribute()`
- Optional scopes: `scopeStatus($q, array $codes)`, etc.

**App\Models\OptionValue**
- `belongsTo(OptionSet::class, 'set_id')`

Policies: admin-only management for option sets/values.

---

## Admin UI (Managed Dropdowns)

- CRUD screens for **Option Sets** and **Option Values** (add/edit/rename/order/toggle active).  
- Show **where-used** counts before disabling a value.
- Client create/edit forms use **selects** backed by the four sets above.
- AJAX endpoint: `/admin/options/{setKey}` → returns (id, code, label).

Validation: ensure selected option value **belongs** to the correct set and is **active**.

---

## Import Compatibility (clients.xlsx)

**Mapping to new FKs**
- Source → DB:
  - `Cash/probono` → `cash_or_probono_id`
  - `Status` → `status_id`
  - `مكان التوكيل` → `power_of_attorney_location_id`
  - `مكان المستندات` → `documents_location_id`

**Lookup strategy during preflight/import**
1) Try label match in current locale (EN/AR), case-insensitive.  
2) Fallback to `code`.  
3) Normalize common synonyms (e.g., `pro bono` → `probono`).  
4) If unresolved → **reject row** with a clear reason.

**Sample template**
- The **Clients** sample XLSX includes the four columns above with human-readable values and a “Notes” sheet listing allowed values (EN/AR).

---

## Localization (EN/AR)

- Add language lines for set/value labels (`resources/lang/en/options.php`, `resources/lang/ar/options.php`).  
- UI displays localized labels; DB stores IDs only.

---

## Tests (Pest)

- **Unit**: option lookup by AR/EN label, by code; normalization synonyms.  
- **Feature**: client create/update with selects; reject inactive/wrong-set values.  
- **Import preflight**: map spreadsheet values to IDs; reject unknowns; report.
- **Migration**: legacy text mapped to IDs; counts verified.

---

## Branch & Commits

1. `feat/options-core` — option_sets/option_values migrations, seeders, models.  
2. `feat/client-managed-dropdowns` — client FKs, relations, form changes, validation.  
3. `feat/import-mapping-clients-options` — importer mapping for Clients; sample template.  
4. `test/options-and-client-mapping` — unit/feature tests.  
5. `docs/options-managed-dropdowns` — ERD/forms/import runbook updates.

**Conventional commit example**
```
feat(client): add managed dropdowns for cash_or_probono, status, power_of_attorney_location, documents_location
```

---

## Definition of Done

- Client form uses managed dropdowns for `cash_or_probono`, `status`, `power_of_attorney_location`, `documents_location`.  
- Admins can rename labels safely; stored data uses **FK IDs**.  
- Import preflight maps EN/AR text to IDs and rejects unknowns.  
- Data migration migrates legacy values to FKs and passes tests.  
- EN/AR labels display correctly across UI and exports.
