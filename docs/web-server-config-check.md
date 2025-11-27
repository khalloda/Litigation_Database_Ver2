# Web Server Configuration Check

## Routes Are Registered ✅

Test script confirms all 66 API routes are registered correctly, including:
- `GET api/options/{setKey}` ✅
- `GET api/cases` ✅
- All other routes ✅

## Problem: 404 Errors Despite Routes Being Registered

If routes are registered but returning 404, the issue is likely:

### 1. Web Server Not Routing to Laravel

**Check**: Is your web server configured to serve from `clm-app/public/`?

**Apache Configuration** (httpd.conf or virtual host):
```apache
<VirtualHost *:80>
    ServerName litigation.local
    DocumentRoot "D:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/clm-app/public"
    
    <Directory "D:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/clm-app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx Configuration**:
```nginx
server {
    listen 80;
    server_name litigation.local;
    root D:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/clm-app/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 2. .htaccess Not Working

**Check**: Is mod_rewrite enabled in Apache?

```bash
# Check if mod_rewrite is enabled
php -m | grep rewrite
```

**Enable mod_rewrite** (if needed):
```apache
# In httpd.conf
LoadModule rewrite_module modules/mod_rewrite.so
```

### 3. Test Direct PHP Access

Create a test file: `clm-app/public/test.php`
```php
<?php
phpinfo();
```

Access: `http://litigation.local/test.php`

If this doesn't work, PHP isn't configured correctly.

### 4. Test Laravel Bootstrap

Access: `http://litigation.local/` 

Should serve React SPA's `index.html`. If you see Laravel's welcome page or an error, Laravel isn't bootstrapping correctly.

### 5. Check Laravel Logs

```powershell
Get-Content clm-app\storage\logs\laravel.log -Tail 50
```

Look for route matching errors or exceptions.

---

## Quick Test Commands

```powershell
# Test if PHP is working
curl http://litigation.local/test.php

# Test if Laravel is bootstrapping
curl http://litigation.local/

# Test API route directly
curl -v http://litigation.local/api/options/case.status

# Check web server error logs
# Apache: logs/error.log
# Nginx: logs/error.log
```

---

## Expected Behavior

### ✅ Working Setup
- `http://litigation.local/` → React SPA index.html
- `http://litigation.local/api/options/case.status` → JSON response (200 OK)
- `http://litigation.local/api/cases` → 401 Unauthorized (not 404!)

### ❌ Current Issue
- All `/api/*` routes → 404 Not Found

This suggests requests aren't reaching Laravel's router at all.

---

## Next Steps

1. **Verify web server root** points to `clm-app/public/`
2. **Check mod_rewrite** is enabled (Apache)
3. **Test PHP** is working (`test.php`)
4. **Check Laravel logs** for errors
5. **Verify .htaccess** is being read (check Apache error logs)

