@echo off
setlocal enabledelayedexpansion

echo =========================================
echo WMS Setup (Windows)
echo =========================================
echo.

REM 1) Basic checks
where php >nul 2>nul || (echo ERROR: PHP not found in PATH. Install Laragon/XAMPP PHP 8.2+ and try again. & pause & exit /b 1)
where composer >nul 2>nul || (echo ERROR: Composer not found in PATH. Install Composer 2 and try again. & pause & exit /b 1)
where node >nul 2>nul || (echo ERROR: Node.js not found in PATH. Install Node.js 18+ and try again. & pause & exit /b 1)
where npm >nul 2>nul || (echo ERROR: NPM not found in PATH. Install Node.js 18+ and try again. & pause & exit /b 1)

REM 2) Create .env
if not exist .env (
  if exist .env.example (
    copy .env.example .env >nul
    echo Created .env from .env.example
  ) else (
    echo ERROR: .env.example not found.
    pause
    exit /b 1
  )
) else (
  echo .env already exists (skipping)
)

REM 3) Install backend deps
echo.
echo Installing PHP dependencies...
call composer install --no-interaction
if errorlevel 1 (echo ERROR: composer install failed. & pause & exit /b 1)

REM 4) Install frontend deps
echo.
echo Installing Node dependencies...
call npm install
if errorlevel 1 (echo ERROR: npm install failed. & pause & exit /b 1)

REM 5) App key
echo.
echo Generating APP_KEY...
php artisan key:generate
if errorlevel 1 (echo ERROR: key:generate failed. & pause & exit /b 1)

REM 6) Migrations + seed
echo.
echo Running migrations + seed...
php artisan migrate --force
if errorlevel 1 (echo ERROR: migrate failed. Check DB settings in .env. & pause & exit /b 1)
php artisan db:seed --force
if errorlevel 1 (echo ERROR: db:seed failed. & pause & exit /b 1)

REM 7) Build assets
echo.
echo Building frontend assets...
npm run build
if errorlevel 1 (echo ERROR: npm run build failed. & pause & exit /b 1)

echo.
echo =========================================
echo Setup completed successfully.
echo Next: run start_windows.bat
echo =========================================
echo.
pause
