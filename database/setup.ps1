# WIET Library Database Setup - PowerShell Script
# Automatically imports schema.sql to MySQL

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   WIET Library Database Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = Join-Path $PSScriptRoot "schema.sql"

# Check if MySQL is running
Write-Host "[1/5] Checking MySQL status..." -ForegroundColor Yellow
$mysqlRunning = Get-NetTCPConnection -LocalPort 3306 -ErrorAction SilentlyContinue
if (-not $mysqlRunning) {
    Write-Host "[ERROR] MySQL is not running!" -ForegroundColor Red
    Write-Host "Please start MySQL from XAMPP Control Panel." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}
Write-Host "[OK] MySQL is running on port 3306" -ForegroundColor Green
Write-Host ""

# Check if MySQL executable exists
Write-Host "[2/5] Checking MySQL installation..." -ForegroundColor Yellow
if (-not (Test-Path $mysqlPath)) {
    Write-Host "[ERROR] MySQL not found at: $mysqlPath" -ForegroundColor Red
    Write-Host "Please update `$mysqlPath in this script." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}
Write-Host "[OK] Found MySQL at: $mysqlPath" -ForegroundColor Green
Write-Host ""

# Check if SQL file exists
Write-Host "[3/5] Checking SQL file..." -ForegroundColor Yellow
if (-not (Test-Path $sqlFile)) {
    Write-Host "[ERROR] SQL file not found: $sqlFile" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}
Write-Host "[OK] Found SQL file: $sqlFile" -ForegroundColor Green
Write-Host ""

# Import database
Write-Host "[4/5] Creating database and importing schema..." -ForegroundColor Yellow
Write-Host "Please enter MySQL password (press Enter if no password):" -ForegroundColor Cyan

try {
    # Create database
    $createDb = "DROP DATABASE IF EXISTS wiet_library; CREATE DATABASE wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $createDb | & $mysqlPath -u root -p
    
    # Import schema
    & $mysqlPath -u root -p wiet_library "--execute=SOURCE $sqlFile"
    
    Write-Host "[OK] Import successful!" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "[ERROR] Import failed!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Verify tables
Write-Host "[5/5] Verifying tables..." -ForegroundColor Yellow
& $mysqlPath -u root -p wiet_library "--execute=SHOW TABLES;"

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   SUCCESS! Database Created" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Database: wiet_library" -ForegroundColor Cyan
Write-Host "Tables: 15 tables created" -ForegroundColor Cyan
Write-Host "Sample Data: Included (3 books, 3 members)" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Import additional data:" -ForegroundColor White
Write-Host "   http://localhost/wiet_lib/database/import_data.php" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Login to admin panel:" -ForegroundColor White
Write-Host "   http://localhost/wiet_lib/admin/admin_login.php" -ForegroundColor Gray
Write-Host "   Email: admin@wiet.edu.in" -ForegroundColor Gray
Write-Host "   Password: admin123" -ForegroundColor Gray
Write-Host ""

Read-Host "Press Enter to exit"
