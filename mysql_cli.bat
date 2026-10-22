@echo off
setlocal
title MariaDB / MySQL CLI - repair_system_db

echo ================================================================
echo   Opening MariaDB / MySQL CLI for database: repair_system_db
echo ================================================================
echo.

if not exist "C:\tools\mariadb\bin\mariadb.exe" (
    echo [ERROR] MariaDB client not found at C:\tools\mariadb\bin\mariadb.exe
    pause
    exit /b 1
)

"C:\tools\mariadb\bin\mariadb.exe" -u root --database=repair_system_db
