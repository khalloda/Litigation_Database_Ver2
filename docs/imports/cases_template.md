# Cases Import Templates

## Overview

Schema-driven import templates for the `cases` table, generated from the live database schema. Two variants available: **Standard** (core fields) and **Extended** (all fields).

## Template Types

### Standard Template
- **Purpose**: Core fields needed for basic case creation
- **Columns**: ~25 essential fields
- **Use Case**: Most imports, new case creation
- **Files**: `Cases_Import_Template_Standard.csv`, `Cases_Import_Template_Standard.xlsx`

### Extended Template  
- **Purpose**: All fields including legacy/optional
- **Columns**: ~60 fields including advanced options
- **Use Case**: Complete data migration, legacy system imports
- **Files**: `Cases_Import_Template_Extended.csv`, `Cases_Import_Template_Extended.xlsx`

## File Formats

### CSV Templates
- **Encoding**: UTF-8 with BOM for Excel compatibility
- **Sample Data**: 2 rows (Arabic + English examples)
- **Headers**: Exact database column names

### XLSX Templates
- **Sheets**: 3 sheets (Template, Lookups, README)
- **Validation**: Dropdown lists for short enums (<50 values)
- **Named Ranges**: For data validation
- **Documentation**: Comprehensive README with usage instructions

## Column Specifications

### Standard Template Columns
Core fields for basic case import:
- `client_id`, `client_name`, `client_in_case_name`
- `matter_name_ar`, `matter_name_en`, `matter_description`
- `matter_status_id`, `matter_status`, `matter_category_id`, `matter_category`
- `matter_degree_id`, `matter_degree`, `matter_importance_id`, `matter_importance`
- `court_id`, `court_name`, `opponent_id`, `opponent_name`
- `client_capacity_id`, `opponent_capacity_id`, `matter_partner_id`, `matter_partner_name`
- `matter_start_date`, `matter_end_date`, `matter_asked_amount`, `matter_judged_amount`
- `notes_1`

### Extended Template Columns
All importable fields including:
- All Standard columns
- Legacy fields: `legacy_id`, `legacy_case_number`
- Advanced options: `matter_branch_id`, `circuit_name_id`, `circuit_shift_id`
- Additional notes: `notes_2`, `notes_3`, `notes_4`
- Fee fields: `fee_amount`, `fee_currency`, `fee_payment_status`
- And ~30 more optional fields

## Foreign Key Resolution

### ID vs Name Precedence
**Rule**: For any foreign key (client, court, opponent, partner), you can provide EITHER:
- **ID column** (e.g., `client_id`): Direct lookup, fastest
- **Name column** (e.g., `client_name`): Fuzzy match with AR/EN normalization

**Precedence**: If BOTH provided, ID wins (Name ignored)
**Conflict Warning**: If ID and Name disagree, preflight logs a WARNING

### Supported Foreign Keys
- `client_id` / `client_name` → `clients` table
- `court_id` / `court_name` → `courts` table  
- `opponent_id` / `opponent_name` → `opponents` table
- `matter_partner_id` / `matter_partner_name` → `lawyers` table
- `matter_destination_id` / `matter_destination` → `courts` table

## Data Validation

### Short Enums (Dropdowns)
XLSX templates include dropdown validation for:
- `matter_status_id` → Case status options
- `matter_category_id` → Case category options  
- `matter_degree_id` → Case degree options
- `matter_importance_id` → Case importance options
- `client_capacity_id` / `opponent_capacity_id` → Capacity type options

### Large Lists (Free Text + Preflight)
- **Courts**: Free text input, preflight suggests closest matches
- **Opponents**: Bilingual fuzzy matching with existing OpponentSuggestionService
- **Lawyers**: Partner-level lawyers only (filtered by title)

## Usage Instructions

### 1. Download Template
- Navigate to Import → Upload
- Select "Cases" as target table
- Download Standard (recommended) or Extended template

### 2. Fill Data
- **Required**: `client_id` OR `client_name` (at least one)
- **Required**: `matter_name_ar`, `matter_name_en`
- **Optional**: All other fields as needed
- **Date Format**: YYYY-MM-DD (ISO 8601)
- **Amounts**: Decimal format (e.g., 125000.50)

### 3. Upload & Import
- Upload filled template
- Map columns (auto-mapped for exact matches)
- Run preflight validation
- Execute import

## Regeneration

### Command Line
```bash
# Generate all templates
php artisan templates:generate-cases --mode=all

# Generate specific template
php artisan templates:generate-cases --mode=standard
php artisan templates:generate-cases --mode=extended
```

### Admin UI
- Admin users can regenerate templates via UI button
- Run after schema changes to update templates

## Technical Details

### Schema Introspection
- Queries `INFORMATION_SCHEMA.COLUMNS` for column metadata
- Retrieves foreign key relationships
- Calculates schema signature for version tracking

### File Generation
- **CSV**: UTF-8 BOM, 2 sample rows, exact column headers
- **XLSX**: 3 sheets, named ranges, data validation, comprehensive README
- **Memory**: Optimized for large datasets

### Error Handling
- Graceful handling of missing templates
- Permission checks for downloads
- Schema change detection

## Limitations

- Dropdowns only for short enumerations (<50 values)
- Large vocabularies rely on preflight name matching
- Excel row limit: 1,048,576 (sufficient for most imports)
- Memory usage optimized for large template generation

## Troubleshooting

### Template Not Found
- Run: `php artisan templates:generate-cases --mode=all`
- Check file permissions in `storage/app/templates/`

### Permission Denied
- Ensure user has `import.view_template` permission
- Check role assignments

### Schema Changes
- Regenerate templates after database schema changes
- Templates include schema signature for version tracking
