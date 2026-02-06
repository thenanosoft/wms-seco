# WMS QA Report (Deep Review)

Project: Laravel 12 Warehouse / Store Management System (WMS)

Date: 2026-01-23

## What I could and could not do
- I performed a **deep static QA + security review** on the provided ZIP and fixed issues directly in the codebase.
- In this environment I cannot reliably run **composer install / npm install** against the public internet, so dependency installation was not executed here. However, the scripts and lock files were audited for safety.

---

## PHASE 1 – ZIP & PROJECT INTEGRITY CHECK
### ✅ Fixed
1) **Nested / extra folder** inside ZIP
- Found an extra folder `wms_stable_phase2/` that looked like an older copy.
- Removed it from the final deliverable.

2) **Sensitive / non‑deployables committed**
- Found `.env` included in the ZIP.
- Found `.git/` directory included in the ZIP.
- Removed both from the final deliverable.

3) **Compiled assets committed**
- Found `public/build/` included.
- Removed from the final deliverable.
- Updated docs to build assets via `npm run build`.

### ⚠️ Notes
- `.env.example` existed and was retained.
- `storage/` and `bootstrap/cache/` are present; Windows users should ensure they are writable.

---

## PHASE 2 – WINDOWS COMPATIBILITY
### ✅ Improvements
- Added `setup_windows.bat` and `start_windows.bat` for non‑technical users.
- Avoided Unix-only path assumptions in the added scripts.

---

## PHASE 3 – ROUTES & SECURITY QA
### ✅ Route protection
- Admin routes are protected by `auth` + `role:admin`.
- Non-admin access to admin routes should return **403**.

### ⚠️ Recommendation
- Some routes repeat middleware declarations. Not harmful, but could be cleaned for readability.

---

## PHASE 4 – DATA SAFETY & BUSINESS LOGIC
### ✅ Critical fixes
1) **Integer-only rule enforced end-to-end**
- The project previously allowed decimal quantities in DB and some validation.
- Updated:
  - Purchase lines, issue lines, stock ledger, issue return lines, purchase return lines migrations to use **unsigned integers** for quantities and prices.
  - Validation rules to enforce integers.

2) **Returns: prevent invalid and excessive returns**
- Issue return and purchase return now:
  - Allow `0` to skip a line.
  - Require at least one line with quantity > 0.
  - Block returning more than remaining issued/purchased.
  - Purchase return also caps by currently available stock.

3) **Atomic transactions**
- Purchase / Issue / Return operations continue to run inside `DB::transaction()`.

---

## PHASE 6 – EXPORT QA
### ❌ Bug fixed
- **CSV export for returns** used a non-existing DB column `unit_price`.
- Fixed by selecting:
  - `issue_return_lines.issue_price as unit_price`
  - `purchase_return_lines.purchase_price as unit_price`

---

## PHASE 7 – BACKUP & RESTORE (CRITICAL)
### ✅ Security hardening
1) **Backup path traversal prevention**
- `backup_path` could be set to unsafe paths.
- Added sanitization in `BackupService::backupDir()` to prevent `..`, absolute paths, Windows drive paths, and invalid characters.

2) **Restore file selection validation**
- `selected_backup` now requires a safe filename pattern and must end with `.sql`.

---

## PHASE 9 – SECURITY HARDENING
### ✅ Fixed
- `.env.example` defaulted to `APP_DEBUG=true`.
- Updated to `APP_DEBUG=false`.

### ⚠️ Still recommended
- Run a dependency audit locally:
  - `composer audit`
  - `npm audit`
- Set strong production values in `.env`.

---

## Confirmed Stable Features (based on code review)
- Inventory items and groups CRUD
- Purchases, Issues with stock checks
- Stock ledger entries created per transaction
- Backup creation, listing, download
- Restore from safe selected backup / uploaded SQL
- Role middleware (admin/store_helper)
- Exports for purchases, issues, stock, returns (CSV/PDF/Print)

---

## Files changed (high level)
- `.env.example`
- `app/Services/BackupService.php`
- `app/Http/Controllers/BackupController.php`
- `app/Http/Controllers/IssueController.php`
- `app/Http/Controllers/IssueReturnController.php`
- `app/Http/Controllers/PurchaseReturnController.php`
- `app/Http/Controllers/ExportController.php`
- Migrations for purchase/issue/returns/ledger quantity & price types
- Added `WINDOWS_INSTALLATION_GUIDE.md`, `setup_windows.bat`, `start_windows.bat`

