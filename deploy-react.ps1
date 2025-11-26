# Deploy React to Laravel Public Folder
# This script builds the React app and copies it to Laravel's public directory

Write-Host "🚀 Deploying React to Laravel..." -ForegroundColor Cyan

# Step 1: Build React app
Write-Host "`n📦 Building React app..." -ForegroundColor Yellow
Set-Location "AiStudio-CLMS2"
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Build failed!" -ForegroundColor Red
    exit 1
}

Set-Location ".."

# Step 2: Remove old files
Write-Host "`n🧹 Cleaning old files..." -ForegroundColor Yellow
Remove-Item -Path "clm-app\public\index.html" -Force -ErrorAction SilentlyContinue
Remove-Item -Path "clm-app\public\assets" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "clm-app\public\locales" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "clm-app\public\vite.svg" -Force -ErrorAction SilentlyContinue

# Step 3: Copy new build
Write-Host "`n📋 Copying build files..." -ForegroundColor Yellow
Copy-Item -Path "AiStudio-CLMS2\dist\*" -Destination "clm-app\public\" -Recurse -Force

# Step 4: Verify
if (Test-Path "clm-app\public\index.html") {
    Write-Host "`n✅ Deployment successful!" -ForegroundColor Green
    Write-Host "React app is now available at: http://litigation.local" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Deployment failed - index.html not found!" -ForegroundColor Red
    exit 1
}
