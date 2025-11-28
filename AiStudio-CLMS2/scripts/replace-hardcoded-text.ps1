# i18n Hard-Coded Text Replacement Script
# This script replaces all hard-coded "Loading..." and "Error:" text with translation keys

$pagesPath = "d:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\AiStudio-CLMS2\pages"

# Get all TypeScript files in the pages directory
$files = Get-ChildItem -Path $pagesPath -Filter "*.tsx" -Recurse

$totalReplacements = 0

foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    $originalContent = $content
    
    # Replace "Loading..." with {t('common.loading')}
    $content = $content -replace '<p className="text-gray-600">Loading\.\.\.</p>', '<p className="text-gray-600">{t(''common.loading'')}</p>'
    
    # Replace "Loading dashboard..." with {t('common.loading_dashboard')}
    $content = $content -replace '<p className="text-gray-600">Loading dashboard\.\.\.</p>', '<p className="text-gray-600">{t(''common.loading_dashboard'')}</p>'
    
    # Replace "Error: {error}" with {t('common.error')}: {error}
    $content = $content -replace '<p className="text-red-600">Error: \{error\}</p>', '<p className="text-red-600">{t(''common.error'')}: {error}</p>'
    
    # Replace "Error: {error || 'XXX not found'}" patterns
    $content = $content -replace "Error: \{error \|\| '([^']+) not found'\}", "{t('common.error')}: {error || t('common.not_found').replace('{item}', '$1')}"
    
    # If content changed, write it back
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $replacements = ($originalContent.Length - $content.Length)
        Write-Host "Updated: $($file.Name) - Made replacements" -ForegroundColor Green
        $totalReplacements++
    }
}

Write-Host "`nTotal files updated: $totalReplacements" -ForegroundColor Cyan
Write-Host "i18n replacement complete!" -ForegroundColor Green
