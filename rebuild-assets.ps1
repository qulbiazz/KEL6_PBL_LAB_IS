# Post-pull script untuk rebuild assets (Windows PowerShell)

Write-Host "`n🔄 Rebuilding assets after pull..." -ForegroundColor Cyan

# Check if node_modules exists
if (-not (Test-Path "node_modules")) {
    Write-Host "📦 Installing npm dependencies..." -ForegroundColor Yellow
    npm install
}

# Rebuild Tailwind CSS
Write-Host "🎨 Building Tailwind CSS..." -ForegroundColor Yellow
npm run build:css

# Check if composer vendor exists
if (-not (Test-Path "vendor")) {
    Write-Host "📦 Installing composer dependencies..." -ForegroundColor Yellow
    composer install --no-dev --ignore-platform-reqs
}

Write-Host "`n✅ Assets rebuilt successfully!" -ForegroundColor Green
Write-Host "💡 Tip: Run this script after every git pull`n" -ForegroundColor Gray
