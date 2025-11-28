# Playwright Test Runner Script
Set-Location "D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\clm-app"

Write-Host "=== Starting Playwright Tests ===" -ForegroundColor Cyan
Write-Host ""

# Check if node_modules exists
if (-not (Test-Path "node_modules")) {
    Write-Host "Installing npm dependencies..." -ForegroundColor Yellow
    npm install
}

# Check if Playwright is installed
if (-not (Test-Path "node_modules\@playwright")) {
    Write-Host "Playwright not found. Installing..." -ForegroundColor Yellow
    npm install @playwright/test
}

# Install browsers
Write-Host "Installing Playwright browsers..." -ForegroundColor Yellow
npx playwright install chromium

# Run tests
Write-Host ""
Write-Host "=== Running CRUD Tests ===" -ForegroundColor Green
Write-Host ""

npx playwright test --reporter=list

Write-Host ""
Write-Host "=== Tests Complete ===" -ForegroundColor Cyan
Write-Host "Check playwright-report folder for HTML report"
Write-Host "Check tests/e2e/screenshots folder for screenshots"

# Open the report
npx playwright show-report

