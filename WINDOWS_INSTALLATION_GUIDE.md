# WMS (Laravel 12) – Windows Installation Guide (Non‑Technical Friendly)

This guide is written for **Windows 10/11** users.

## 1) What you need (one time)
Install these tools (recommended versions):

1. **Laragon** (recommended) OR XAMPP
   - Laragon is easiest because it bundles Apache/Nginx, PHP, MySQL, and a terminal.
2. **PHP 8.2+** (Laragon can manage this)
3. **MySQL 8+** (Laragon provides it)
4. **Node.js 18+** (LTS)
5. **Composer 2**

## 2) Project placement
1. Extract the ZIP into a clean folder, for example:
   - `C:\Projects\wms`
2. Do not keep it inside nested folders like `wms\wms\...`

## 3) One‑click setup (recommended)
Inside the project folder you will find:
- `setup_windows.bat`

### Steps
1. Start Laragon
2. Open Laragon Terminal
3. Go to your project folder:
   - `cd C:\Projects\wms`
4. Double‑click **setup_windows.bat** (or run it from terminal).

What it does:
- Installs PHP dependencies (composer)
- Installs Node dependencies (npm)
- Creates `.env` from `.env.example`
- Generates app key
- Creates database if possible (optional)
- Runs migrations + seeds
- Builds frontend assets

## 4) Database configuration
Open `.env` and update these lines if needed:

- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=wms`
- `DB_USERNAME=root`
- `DB_PASSWORD=`

If you use Laragon default MySQL, usually `root` with blank password works.

## 5) Run the system
Use:
- `start_windows.bat`

Or manually:

```bash
php artisan serve
```

Then open:
- http://127.0.0.1:8000

## 6) Default logins
After seeding, the system creates:

- **Admin**
  - Email: `admin@wms.local`
  - Password: `Admin@12345`

- **Store Helper**
  - Email: `helper@wms.local`
  - Password: `Helper@12345`

You can change these from Admin → Users.

## 7) Common fixes
### A) “storage/ or bootstrap/cache not writable”
Run in terminal:
```bash
php artisan optimize:clear
```
If using XAMPP, ensure folder permissions allow write access.

### B) Assets not loading
Run:
```bash
npm install
npm run build
```

### C) “APP_KEY is missing”
Run:
```bash
php artisan key:generate
```

### D) Database errors
Run:
```bash
php artisan migrate:fresh --seed
```

## 8) Production notes
- Set `APP_DEBUG=false` in `.env`
- Set a strong `APP_KEY`
- Use real mail settings if email features are enabled
- Protect backups folder and keep auto‑retention enabled

