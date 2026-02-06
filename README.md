# WMS (Warehouse / Store Management System) – Laravel 12

Modules included:
- Inventory (Items, Groups)
- Purchases
- Issues
- Returns (Issue Return, Purchase Return)
- Stock Ledger
- Backup & Restore + Auto Backup + Retention
- User Roles (Admin / Store Helper)
- Export (CSV / PDF / Print)

## Quick start (Windows)
1. Extract the ZIP to a clean folder (example: `C:\Projects\wms`)
2. Follow: `WINDOWS_INSTALLATION_GUIDE.md`

Fast way:
- Run `setup_windows.bat`
- Then run `start_windows.bat`

## Default users
After `php artisan db:seed`:
- Admin: `admin@wms.local` / `Admin@12345`
- Store Helper: `helper@wms.local` / `Helper@12345`

## Security notes
- Keep `APP_DEBUG=false` in production
- Configure a secure DB user/password
- Store backups safely and restrict access to backup files

## QA
See `QA_REPORT.md` for:
- Bugs fixed
- Security hardening
- Integrity checks
