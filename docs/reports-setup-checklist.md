# Client Case PDF Report — Setup Checklist

## ✅ Completed Steps

1. ✅ **Composer Package Installed**: `barryvdh/laravel-snappy` has been installed via `composer update`
2. ✅ **Configuration Published**: `config/snappy.php` is in place
3. ✅ **Permission Seeded**: `reports.view` permission created and assigned to `super_admin` role
4. ✅ **React App Built**: Front-end changes compiled from `AiStudio-CLMS2` to `AiStudio-CLMS2/dist` and copied to `clm-app/public`
5. ✅ **Backend Controller**: `App\Http\Controllers\Api\ReportController` created
6. ✅ **PDF View Template**: `resources/views/reports/client_cases_pdf.blade.php` created
7. ✅ **API Route**: `POST /api/reports/client-cases/pdf` registered

## 📝 Build Process

**Important**: The React app source is in `AiStudio-CLMS2/`, not `clm-app/public/`.

To rebuild after making changes:
1. Make changes in `AiStudio-CLMS2/pages/ReportsPage.tsx` (or other source files)
2. Build: `cd AiStudio-CLMS2 && npm run build` (outputs to `AiStudio-CLMS2/dist`)
3. Copy to Laravel: Copy `AiStudio-CLMS2/dist/*` to `clm-app/public/`

## ⚠️ Required: Install wkhtmltopdf

**For Windows:**

1. Download wkhtmltopdf from: https://wkhtmltopdf.org/downloads.html
   - Choose the Windows 64-bit installer
   - Current stable version recommended

2. Install the downloaded `.msi` file
   - Default installation path: `C:\Program Files\wkhtmltopdf\bin\`

3. **Option A** (Recommended): Add to System PATH
   - Add `C:\Program Files\wkhtmltopdf\bin` to your Windows PATH environment variable
   - Restart your terminal/IDE after adding to PATH

4. **Option B**: Set in `.env` file
   ```env
   WKHTMLTOPDF_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe
   WKHTMLTOIMAGE_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe
   ```

5. Verify installation:
   ```powershell
   wkhtmltopdf --version
   ```
   Should output version information if installed correctly.

## 🧪 Testing the Report

1. **Start your Laravel development server** (if not already running):
   ```bash
   php artisan serve
   ```

2. **Login** to the application with a user that has `reports.view` permission (super_admin by default)

3. **Navigate to Reports page** in the React SPA

4. **Select a client** from the dropdown (e.g., "Toyota Egypt" / "تويوتا إيجيبت")

5. **Toggle columns** as desired (all are enabled by default)

6. **Click "Generate PDF"** — the browser should download the PDF file

## 🔍 Troubleshooting

### "wkhtmltopdf not found" error
- Ensure wkhtmltopdf is installed and accessible
- Check that the binary path in `.env` is correct (Windows paths need double backslashes or forward slashes)
- Verify the binary is executable by your web server user

### PDF is blank or malformed
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure Arabic fonts are available on the system (wkhtmltopdf uses system fonts)
- Try generating a simple test PDF first to verify wkhtmltopdf works

### 403 Forbidden error
- Verify your user has the `reports.view` permission:
  ```bash
  php artisan tinker
  >>> auth()->user()->hasPermissionTo('reports.view')
  ```
- If false, assign the permission:
  ```bash
  php artisan db:seed --class=PermissionsSeeder
  ```

### React UI not showing new form
- Rebuild the React app: `cd clm-app/public && npm run build`
- Clear browser cache and hard refresh (Ctrl+Shift+R)

## 📝 Next Steps

Once wkhtmltopdf is installed, you should be able to:
- Generate PDF reports for any client
- Customize which columns appear in the report
- Download reports with proper RTL Arabic text rendering

For detailed documentation, see `docs/reports.md`.

