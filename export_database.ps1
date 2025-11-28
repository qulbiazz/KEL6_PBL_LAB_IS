# Database Export Script for Deployment (PowerShell)
# Usage: .\export_database.ps1

Write-Host "==================================" -ForegroundColor Blue
Write-Host "  Database Export Script" -ForegroundColor Blue
Write-Host "==================================" -ForegroundColor Blue
Write-Host ""

# Load .env file
if (Test-Path .env) {
    Get-Content .env | ForEach-Object {
        if ($_ -match '^\s*([^#][^=]*?)\s*=\s*(.+?)\s*$') {
            [System.Environment]::SetEnvironmentVariable($matches[1], $matches[2])
        }
    }
} else {
    Write-Host "Error: .env file not found!" -ForegroundColor Red
    exit 1
}

$DB_HOST = [System.Environment]::GetEnvironmentVariable('DB_HOST')
$DB_PORT = [System.Environment]::GetEnvironmentVariable('DB_PORT')
$DB_USER = [System.Environment]::GetEnvironmentVariable('DB_USER')
$DB_NAME = [System.Environment]::GetEnvironmentVariable('DB_NAME')
$DB_PASS = [System.Environment]::GetEnvironmentVariable('DB_PASSWORD')

# Set password environment variable
$env:PGPASSWORD = $DB_PASS

# Create database/exports directory
New-Item -Path "database\exports" -ItemType Directory -Force | Out-Null

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"

# Export full database
Write-Host "1. Exporting full database..." -ForegroundColor Green
& pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME `
    -f "database\exports\full_database_$timestamp.sql"

# Export schema only
Write-Host "2. Exporting schema only..." -ForegroundColor Green
& pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME `
    --schema-only `
    -f "database\exports\schema_only_$timestamp.sql"

# Export data only
Write-Host "3. Exporting data only..." -ForegroundColor Green
& pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME `
    --data-only `
    -f "database\exports\data_only_$timestamp.sql"

# Export essential tables schema
Write-Host "4. Exporting essential tables..." -ForegroundColor Green
& pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME `
    -t admins `
    -t students `
    -t equipment `
    -t borrowing `
    -t attendance `
    -t team_members `
    -t banners `
    -t news `
    -t gallery `
    -t research_activities `
    -t partners `
    -t products `
    -t cards `
    -t guestbook `
    --schema-only `
    -f "database\exports\essential_schema_$timestamp.sql"

Write-Host ""
Write-Host "==================================" -ForegroundColor Green
Write-Host "  Export Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green
Write-Host ""
Write-Host "Exported files in: database\exports\" -ForegroundColor Cyan
Get-ChildItem "database\exports\" | Sort-Object LastWriteTime -Descending | Select-Object -First 5 | Format-Table Name, Length, LastWriteTime
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Blue
Write-Host "1. Upload SQL files to AlwaysData server"
Write-Host "2. Import using: psql -h HOST -U USER DATABASE < file.sql"
