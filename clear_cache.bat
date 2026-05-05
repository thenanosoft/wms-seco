@echo off
setlocal
cd /d "%~dp0"

where php >nul 2>nul || (echo ERROR: PHP not found in PATH. & pause & exit /b 1)

echo Clearing Laravel cache...
echo.

php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo.
echo Done. Agar phir bhi error aaye to: php artisan config:cache
echo.
pause
