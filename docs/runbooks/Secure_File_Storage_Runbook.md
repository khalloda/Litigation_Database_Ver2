# Secure File Storage Runbook

## Overview

The Central Litigation Management system implements a comprehensive document management system with secure file storage, upload validation, and access control. All documents are stored securely with proper permissions and audit logging.

## Features

### ✅ **Secure File Storage**
- **Private storage disk** - Files stored in `storage/app/secure/` directory
- **UUID-based filenames** - Prevents directory traversal and guessing
- **Access control** - Permission-based file access
- **Signed URLs** - Temporary secure access for previews
- **File validation** - Size, type, and MIME validation

### ✅ **Document Management**
- **Upload interface** - Drag-and-drop file upload with validation
- **Metadata management** - Client, matter, type, and description
- **Search and filtering** - By client, matter, type, and content
- **Preview support** - PDF and image preview in browser
- **Download control** - Secure download with proper headers

### ✅ **Security Features**
- **File type validation** - Only allowed file types (PDF, Office, Images)
- **Size limits** - Maximum 10MB per file
- **MIME type checking** - Prevents file extension spoofing
- **Permission-based access** - RBAC integration
- **Audit logging** - All file operations logged
- **Secure storage** - Files not accessible via direct URL

## Supported File Types

### **Documents**
- **PDF** - `.pdf` (application/pdf)
- **Word** - `.doc`, `.docx` (application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document)
- **Excel** - `.xls`, `.xlsx` (application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)
- **PowerPoint** - `.ppt`, `.pptx` (application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation)
- **Text** - `.txt` (text/plain)

### **Images**
- **JPEG** - `.jpg`, `.jpeg` (image/jpeg)
- **PNG** - `.png` (image/png)
- **GIF** - `.gif` (image/gif)

## Access Control

### **Permissions Required**
- **`documents.view`** - View document listings and details
- **`documents.upload`** - Upload new documents
- **`documents.download`** - Download documents
- **`documents.edit`** - Edit document metadata
- **`documents.delete`** - Delete documents

### **Security Features**
- **Authentication required** - Must be logged in
- **Permission-based access** - Each operation requires specific permission
- **No direct file access** - Files served through secure routes only
- **Signed URLs** - Temporary access with expiration

## Usage Guide

### 1. **Accessing Document Management**

```
URL: http://litigation.local/documents
Login: khelmy@sarieldin.com
Password: P@ssw0rd
```

### 2. **Uploading Documents**

#### **Via Web Interface**
1. Click **"📤 Upload Document"** button
2. Select file (max 10MB, supported formats only)
3. Choose client (required)
4. Select matter (optional)
5. Enter document type and description
6. Click **"Upload Document"**

#### **File Requirements**
- **Maximum size**: 10MB
- **Supported formats**: PDF, Word, Excel, PowerPoint, Text, Images
- **Client selection**: Required
- **Document type**: Required

### 3. **Managing Documents**

#### **Document Listing**
- **Filter by**: Client, matter, document type
- **Search**: Document names and descriptions
- **Sort**: By upload date, name, size
- **Pagination**: 20 documents per page

#### **Document Actions**
- **👁️ View Details** - See metadata and preview
- **📥 Download** - Secure file download
- **✏️ Edit Metadata** - Update client, matter, type, description
- **🗑️ Delete** - Remove document (requires confirmation)

### 4. **Document Preview**

#### **Supported Previews**
- **PDF files** - Inline browser preview
- **Images** - Direct image display
- **Other files** - Download only

#### **Preview Security**
- **Signed URLs** - Temporary access (1 hour expiration)
- **Permission checking** - Only authorized users
- **Audit logging** - All preview access logged

## Technical Details

### **Database Schema**
```sql
-- client_documents table (enhanced for file management)
client_documents:
- id (bigint, primary key)
- client_id (foreign key to clients)
- matter_id (foreign key to cases, nullable)
- document_name (varchar) - Original filename
- document_type (varchar) - Document category
- file_path (varchar) - Secure storage path
- file_size (bigint) - File size in bytes
- mime_type (varchar) - MIME type
- document_description (text) - User description
- deposit_date (date) - Upload date
- created_by (foreign key to users)
- updated_by (foreign key to users)
- created_at, updated_at, deleted_at
```

### **Storage Configuration**
```php
// config/filesystems.php
'secure' => [
    'driver' => 'local',
    'root' => storage_path('app/secure'),
    'visibility' => 'private',
    'throw' => false,
],
```

### **File Upload Process**
1. **Validation** - File size, type, MIME validation
2. **Secure naming** - Generate UUID filename
3. **Storage** - Save to `storage/app/secure/documents/`
4. **Database record** - Create metadata record
5. **Audit logging** - Log upload activity

### **Download Process**
1. **Permission check** - Verify `documents.download` permission
2. **File existence** - Check file exists in secure storage
3. **Secure download** - Serve file with proper headers
4. **Audit logging** - Log download activity

## Security Considerations

### **File Upload Security**
- **MIME type validation** - Prevents file extension spoofing
- **File size limits** - Prevents DoS attacks
- **Type restrictions** - Only business-relevant file types
- **Virus scanning** - Consider adding ClamAV integration

### **Storage Security**
- **Private storage** - Files not accessible via web server
- **UUID filenames** - Prevents directory traversal
- **Permission checks** - All access requires proper permissions
- **Audit trails** - Complete activity logging

### **Access Security**
- **Signed URLs** - Temporary access with expiration
- **Permission-based** - RBAC integration
- **HTTPS only** - Secure transmission (in production)
- **No caching** - Sensitive documents not cached

## Troubleshooting

### **Common Issues**

#### **1. Upload Fails**
```bash
# Check file size
ls -la /path/to/file

# Check MIME type
file --mime-type /path/to/file

# Check storage permissions
ls -la storage/app/secure/
```

#### **2. Download Fails**
```bash
# Check file exists
ls -la storage/app/secure/documents/

# Check permissions
php artisan tinker
>>> Storage::disk('secure')->exists('documents/filename.pdf')
```

#### **3. Preview Not Working**
- Check if file type supports preview (PDF, images only)
- Verify signed URL generation
- Check browser console for errors

### **Storage Maintenance**

#### **Cleanup Old Files**
```bash
# Find orphaned files (database records deleted)
php artisan documents:cleanup

# Archive old documents
php artisan documents:archive --days=365
```

#### **Storage Monitoring**
```bash
# Check storage usage
du -sh storage/app/secure/

# Count documents
php artisan tinker
>>> ClientDocument::count()
```

## Best Practices

### **1. File Management**
- **DO** use descriptive document types
- **DO** add meaningful descriptions
- **DON'T** upload unnecessary files
- **DON'T** upload files larger than needed

### **2. Security**
- **DO** regularly review document access logs
- **DO** use strong passwords for user accounts
- **DON'T** share download links publicly
- **DON'T** upload sensitive files without proper permissions

### **3. Performance**
- **DO** compress large images before upload
- **DO** use appropriate file formats
- **DON'T** upload duplicate files
- **DON'T** upload files unnecessarily large

### **4. Compliance**
- **DO** maintain proper audit trails
- **DO** follow data retention policies
- **DO** ensure proper access controls
- **DON'T** store files longer than required

## Integration Points

### **With Audit Logging**
- All file operations are automatically logged
- Track who uploaded, downloaded, modified documents
- Monitor file access patterns

### **With RBAC System**
- Document permissions integrated with role system
- Granular permission control
- User-specific access rights

### **With Trash System**
- Deleted documents go to trash
- Full document restoration possible
- Secure file deletion

## Monitoring & Alerts

### **Key Metrics to Monitor**
- **Storage usage** - Total disk space consumed
- **Upload volume** - Documents uploaded per day
- **Download activity** - File access patterns
- **Error rates** - Failed uploads/downloads

### **Alert Conditions**
- **Storage threshold** - >80% disk usage
- **Upload failures** - >5% failure rate
- **Unauthorized access** - Failed permission checks
- **Large files** - Files >5MB uploaded

---

## Quick Reference

### **URLs**
- **Document List**: `/documents`
- **Upload Form**: `/documents/create`
- **Download**: `/documents/{id}/download`
- **Preview**: `/documents/{id}/signed-url`

### **Commands**
```bash
# Check storage usage
du -sh storage/app/secure/

# Clean up orphaned files
php artisan documents:cleanup

# Generate storage report
php artisan documents:report
```

### **Permissions**
- **View**: `documents.view`
- **Upload**: `documents.upload`
- **Download**: `documents.download`
- **Edit**: `documents.edit`
- **Delete**: `documents.delete`

### **Configuration Files**
- **Storage**: `config/filesystems.php`
- **Model**: `app/Models/ClientDocument.php`
- **Controller**: `app/Http/Controllers/DocumentController.php`
- **Views**: `resources/views/documents/`
- **Routes**: `routes/web.php`

---

*Last Updated: 2025-10-09*
*Version: 1.0*
