# Final Stable WMS - Import Guide (Mac Herd / Windows Laragon)

This project runs **offline** on LAN. You only need **PHP + MySQL**.

## 1) Unzip Project

Unzip the provided ZIP into your Laravel projects folder.

Example (Mac):

```bash
cd ~/Development/web/laravel
unzip wms-final-stable.zip -d wms
cd wms
```

## 2) Install Dependencies

```bash
composer install
```

## 3) Environment File

If `.env` exists in zip, keep it. Otherwise:

```bash
cp .env.example .env
php artisan key:generate
```

Update DB settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wms
DB_USERNAME=root
DB_PASSWORD=
```

## 4) Create Database

### Mac (MySQL installed)

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS wms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Laragon (Windows)

Use Laragon > Database, or run the same command in Laragon terminal.

## 5) Migrate (Create Tables)

```bash
php artisan migrate
```

## 6) Import Sample Data (No SQL keyword errors)

We ship a clean, demo SQL file:

- `database/sample_dump.sql`

Import:

```bash
mysql -u root -p wms < database/sample_dump.sql
```

Demo login:
- Admin: `admin@wms.local` / `password`
- Helper: `helper@wms.local` / `password`

## 7) Build Frontend Assets

```bash
npm install
npm run build
```

(If you don't want Node on client PC, you can build once and ship the built assets.)

## 8) Run the Project

### Mac Herd
Herd auto-runs: open `http://wms.test` (or your configured domain).

### Laravel built-in server

```bash
php artisan serve
```

## 9) Backup & Restore (Offline)

- **Backup** uses `mysqldump` if available.
- If `mysqldump` is not available, backup uses a **Laravel fallback** data-only dump.
- **Restore** uses `mysql` CLI if available. If not, it runs a **Laravel fallback restore** for simple dumps.

Where backups are saved:
- `storage/app/backups/`

## 10) What's Changed (Key Features)

1) **Manual Return removed**
   - Only **Issue Return (Inward)** and **Purchase Return (Outward)** exist.

2) **Payment totals**
   - Dashboard shows total in/out values and balance value from `stock_ledger`.

3) **CSV export**
   - Full history CSV export: purchases + issues + returns in one file.

4) **Clickable history**
   - Item code links to single item stock + history screen.

5) **Backup restore error fixed**
   - Works even if mysql CLI not present (fallback).

