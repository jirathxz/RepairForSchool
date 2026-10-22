@echo off
setlocal
title Repair System Class - Stop Server Stack

echo ================================================================
echo           Stopping Apache and MariaDB Services...
echo ================================================================
echo.

:: 1. Stop Apache
echo [STOP] Shutting down Apache HTTP Server...
taskkill /F /IM httpd.exe >nul 2>&1

:: 2. Stop MariaDB cleanly, fallback to taskkill
echo [STOP] Shutting down MariaDB Database Server...
if exist "C:\tools\mariadb\bin\mariadb-admin.exe" (
    "C:\tools\mariadb\bin\mariadb-admin.exe" -u root shutdown >nul 2>&1
)
taskkill /F /IM mariadbd.exe >nul 2>&1

echo.
echo ================================================================
echo   [DONE] All servers (Apache and MariaDB) have been stopped.
echo ================================================================
echo.
ping 127.0.0.1 -n 3 >nul
