@echo off
setlocal

where php >nul 2>nul || (echo ERROR: PHP not found in PATH. & pause & exit /b 1)

echo Starting WMS...
echo Open: http://127.0.0.1:8000

echo.
php artisan serve --host=127.0.0.1 --port=8000
