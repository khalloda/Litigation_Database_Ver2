# GoDaddy Deployment Guide

## File Storage Configuration

This application has been configured to work with GoDaddy shared hosting by storing uploaded files directly in the `public/uploads/` directory instead of using Laravel's storage symlinks.

### Changes Made for Production Compatibility

1. **File Upload Location**: Files are now stored in `public/uploads/` instead of `storage/app/public/`
2. **No Symlinks Required**: The application no longer depends on storage symlinks that don't work on shared hosting
3. **Direct Web Access**: Files are directly accessible via web URLs

### Directory Structure

```
public/
├── uploads/
│   └── logos/
│       └── [logo files]
├── build/
├── index.php
└── ...
```

### Database Paths

- **Old Format**: `logos/filename.png` (requires symlink)
- **New Format**: `uploads/logos/filename.png` (direct access)

### Deployment Steps

1. **Upload Files**: Upload all application files to your GoDaddy hosting
2. **Create Directories**: Ensure `public/uploads/logos/` directory exists
3. **Set Permissions**: Set appropriate permissions (755) for upload directories
4. **Configure Environment**: Update `.env` file with production settings
5. **Run Migrations**: Execute `php artisan migrate` if needed

### File Permissions

Ensure the following directories are writable:
- `public/uploads/logos/` (755 or 777)
- `storage/logs/` (755)
- `storage/framework/cache/` (755)
- `storage/framework/sessions/` (755)
- `storage/framework/views/` (755)

### Testing File Uploads

After deployment, test file uploads by:
1. Creating a new client with a logo
2. Verifying the logo displays correctly
3. Checking that files are stored in `public/uploads/logos/`

### Troubleshooting

If file uploads don't work:
1. Check directory permissions
2. Verify `public/uploads/logos/` exists
3. Ensure PHP file upload settings allow the file size
4. Check Laravel logs in `storage/logs/laravel.log`

### Benefits of This Approach

- ✅ Works on GoDaddy shared hosting
- ✅ No symlink dependencies
- ✅ Direct web file access
- ✅ Standard PHP file handling
- ✅ Compatible with most shared hosting providers
