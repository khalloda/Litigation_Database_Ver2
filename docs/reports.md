# Client Case PDF Report

This document explains how to generate the Toyota-style client case report that mirrors the provided sample.

## Overview

- **Route (API)**: `POST /api/reports/client-cases/pdf`
- **Permission**: `reports.view`
- **PDF Engine**: [`barryvdh/laravel-snappy`](https://github.com/barryvdh/laravel-snappy) using `wkhtmltopdf`
- **Front-end**: React `ReportsPage` exposes a client selector, column toggles, and generates the PDF via Axios.

## Server Requirements

1. **Install wkhtmltopdf on the host OS:**
   - **Windows**: Download from [wkhtmltopdf.org](https://wkhtmltopdf.org/downloads.html) and install. Add the installation directory (typically `C:\Program Files\wkhtmltopdf\bin`) to your system PATH, or set the binary path in `.env`.
   - **Linux**: `sudo apt-get install wkhtmltopdf` (Debian/Ubuntu) or `sudo yum install wkhtmltopdf` (RHEL/CentOS).
   - **macOS**: `brew install wkhtmltopdf` (via Homebrew).

2. **Set the binaries in `.env` if they differ from defaults:**
   ```env
   WKHTMLTOPDF_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe
   WKHTMLTOIMAGE_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe
   ```
   On Linux/macOS, use Unix-style paths:
   ```env
   WKHTMLTOPDF_BINARY=/usr/local/bin/wkhtmltopdf
   WKHTMLTOIMAGE_BINARY=/usr/local/bin/wkhtmltoimage
   ```

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```
   This will pull `barryvdh/laravel-snappy` and its dependencies.

4. **Grant permissions:**
   ```bash
   php artisan db:seed --class=PermissionsSeeder
   ```
   This ensures the `reports.view` permission exists and is assigned to `super_admin`.

## Usage Steps

1. Ensure the authenticated user has the `reports.view` permission (seed via `PermissionsSeeder` or assign manually).
2. Open **Reports** page in the SPA. The "Client Case Report" widget should appear at the top of the page.
3. Select a client, optionally toggle columns, and click **Generate PDF**. The browser automatically downloads the PDF.
4. The backend fetches the client's cases, latest hearing decisions, evaluation, and financial provisions, then renders the PDF using `resources/views/reports/client_cases_pdf.blade.php`.

## API Example

```bash
curl -X POST /api/reports/client-cases/pdf \
     -H "Authorization: Bearer <token>" \
     -H "Accept: application/pdf" \
     -d '{"client_id":123,"columns":{"serial":true,"subject":false}}' \
     --output client-report.pdf
```

## Troubleshooting

- **Blank PDF / 500**: Confirm wkhtmltopdf is installed and executable by the web user.
- **403 Forbidden**: Assign the `reports.view` permission to the user/role.
- **Font rendering issues**: Install an Arabic font like `Cairo` or `Noto Kufi Arabic` on the server; wkhtmltopdf leverages system fonts.

## Files Touched

- `app/Http/Controllers/Api/ReportController.php`
- `resources/views/reports/client_cases_pdf.blade.php`
- `public/pages/ReportsPage.tsx`
- `config/snappy.php`
- `docs/reports.md` (this file)

