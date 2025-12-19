# ============================================================================
# QUICK FIX SCRIPT - Repair Visitors Table on Server (Windows)
# ============================================================================
# Purpose: Fix duplicate key constraint error on visitors table
# Usage: .\scripts\fix_visitors_server.ps1
# ============================================================================

# Configuration (UPDATE THESE VALUES)
$SSH_HOST = "pbl-inlet.alwaysdata.net"
$SSH_USER = "pbl-inlet"
$DB_HOST = "postgresql-pbl-inlet.alwaysdata.net"
$DB_USER = "pbl-inlet"
$DB_NAME = "pbl-inlet_inlet_db"
$REMOTE_PATH = "www/inlet-web"

Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  INLET - Fix Visitors Table on Server" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

# Step 1: Upload migration file
Write-Host "[1/4] Uploading migration file to server..." -ForegroundColor Green
scp database/migrations/fix_visitors_unique_constraint.sql `
    "${SSH_USER}@${SSH_HOST}:~/${REMOTE_PATH}/database/migrations/"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error uploading migration file!" -ForegroundColor Red
    exit 1
}

# Step 2: Upload updated VisitorModel.php
Write-Host "[2/4] Uploading updated VisitorModel.php..." -ForegroundColor Green
scp models/VisitorModel.php `
    "${SSH_USER}@${SSH_HOST}:~/${REMOTE_PATH}/models/"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error uploading VisitorModel.php!" -ForegroundColor Red
    exit 1
}

# Step 3: Run migration on server
Write-Host "[3/4] Running migration on server database..." -ForegroundColor Green
$migrationCommand = @"
cd www/inlet-web && \
echo 'Applying fix_visitors_unique_constraint.sql...' && \
psql -h postgresql-pbl-inlet.alwaysdata.net \
     -U pbl-inlet \
     -d pbl-inlet_inlet_db \
     -f database/migrations/fix_visitors_unique_constraint.sql
"@

ssh "${SSH_USER}@${SSH_HOST}" $migrationCommand

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error running migration!" -ForegroundColor Red
    Write-Host "You may need to run it manually via SSH" -ForegroundColor Yellow
}

# Step 4: Clear cache
Write-Host "[4/4] Clearing cache..." -ForegroundColor Green
$cacheCommand = @"
cd www/inlet-web && \
find . -name '*.cache' -type f -delete 2>/dev/null || true && \
echo 'Cache cleared!'
"@

ssh "${SSH_USER}@${SSH_HOST}" $cacheCommand

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  ✓ Fix Applied Successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Test the website: https://pbl-inlet.alwaysdata.net/inlet-web/"
Write-Host "2. Check for any errors in browser console"
Write-Host "3. Verify visitor tracking is working"
Write-Host ""
Write-Host "If you still see errors, run:" -ForegroundColor Yellow
Write-Host "  ssh ${SSH_USER}@${SSH_HOST}"
Write-Host "  cd ${REMOTE_PATH}"
Write-Host "  tail -f logs/error.log"
Write-Host ""
