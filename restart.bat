@echo off
setlocal
title Repair System Class - Restart Servers

echo ================================================================
echo           Restarting Apache and MariaDB Services...
echo ================================================================
echo.

:: 1. Stop Services
echo [1/4] Stopping Apache...
taskkill /F /IM httpd.exe >nul 2>&1

echo [2/4] Stopping MariaDB...
if exist "C:\tools\mariadb\bin\mariadb-admin.exe" (
    "C:\tools\mariadb\bin\mariadb-admin.exe" -u root shutdown >nul 2>&1
)
taskkill /F /IM mariadbd.exe >nul 2>&1

ping 127.0.0.1 -n 2 >nul

:: 2. Start MariaDB
echo [3/4] Launching MariaDB on port 3306...
start "MariaDB-Server" /min "C:\tools\mariadb\bin\mariadbd.exe" --console

:: 3. Start Apache
echo [4/4] Launching Apache on port 8090...
start "Apache-Server" /min "C:\tools\apache\Apache24\bin\httpd.exe"

ping 127.0.0.1 -n 3 >nul

echo.
echo ================================================================
echo   [DONE] Services restarted successfully!
echo   - Project:    http://localhost:8090
echo   - phpMyAdmin: http://localhost:8090/phpmyadmin
echo ================================================================
echo.
ping 127.0.0.1 -n 3 >nul
