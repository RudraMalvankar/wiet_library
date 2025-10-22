@echo off
REM ============================================
REM WIET Library Database Setup Script
REM Automatically imports schema to MySQL
REM ============================================

echo.
echo ========================================
echo   WIET Library Database Setup
echo ========================================
echo.

REM Check if MySQL is running
echo [1/5] Checking MySQL status...
netstat -an | find "3306" > nul
if errorlevel 1 (
    echo [ERROR] MySQL is not running!
    echo Please start MySQL from XAMPP Control Panel.
    pause
    exit /b 1
)
echo [OK] MySQL is running on port 3306
echo.

REM Set MySQL path
set MYSQL_PATH=C:\xampp\mysql\bin
set SQL_FILE=%~dp0schema.sql

REM Check if MySQL executable exists
if not exist "%MYSQL_PATH%\mysql.exe" (
    echo [ERROR] MySQL not found at: %MYSQL_PATH%
    echo Please update MYSQL_PATH in this script.
    pause
    exit /b 1
)

echo [2/5] Found MySQL at: %MYSQL_PATH%
echo [3/5] SQL File: %SQL_FILE%
echo.

REM Create database and import schema
echo [4/5] Creating database and importing schema...
echo This may take a few seconds...
echo.

"%MYSQL_PATH%\mysql.exe" -u root -p --execute="DROP DATABASE IF EXISTS wiet_library; CREATE DATABASE wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE wiet_library; SOURCE %SQL_FILE%;"

if errorlevel 1 (
    echo.
    echo [ERROR] Import failed!
    echo Please check the error messages above.
    echo.
    echo Common issues:
    echo - MySQL not started in XAMPP
    echo - Incorrect MySQL password
    echo - SQL file not found
    pause
    exit /b 1
)

echo.
echo [5/5] Verifying tables...
"%MYSQL_PATH%\mysql.exe" -u root -p --execute="USE wiet_library; SHOW TABLES;"

echo.
echo ========================================
echo   SUCCESS! Database Created
echo ========================================
echo.
echo Database: wiet_library
echo Tables: 15 tables created
echo Sample Data: Included
echo.
echo Next Steps:
echo 1. Import additional data: http://localhost/wiet_lib/database/import_data.php
echo 2. Login to admin: http://localhost/wiet_lib/admin/admin_login.php
echo    Email: admin@wiet.edu.in
echo    Password: admin123
echo.
pause
