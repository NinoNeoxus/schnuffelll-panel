# Schnuffelll Panel Fixes & Changelog

## Summary of Fixes (December 2025)

This document outlines the changes made to fix the installation, compatibility, and database issues for the Schnuffelll Panel.

### 1. PHP Version Compatibility (Downgrade to Laravel 10)
**Issue:** `composer.lock` required PHP 8.4 packages, but server ran PHP 8.2.
**Fix:**
- Downgraded `laravel/framework` to `v10.50.0` in `composer.json`.
- Updated `installer/install.sh` to use `composer update` instead of `install` to resolve dependencies dynamically for the server's PHP version.

### 2. Laravel 10 Core Compatibility
**Issue:** Project files were checking for Laravel 11 syntax/structure which failed on Laravel 10.
**Fix:**
- **bootstrap/app.php:** Reverted from `Application::configure` (L11) to standard Laravel 10 bootstrap.
- **artisan:** Updated to use `Kernel->handle()`.
- **Missing Files:** Created essential files missing from the downgrade/repo:
    - `app/Exceptions/Handler.php`
    - `app/Http/Kernel.php`
    - `app/Http/Middleware/*` (Authenticate, RedirectIfAuthenticated, VerifyCsrfToken, etc.)
    - `app/Providers/*` (AppServiceProvider, AuthServiceProvider, EventServiceProvider, RouteServiceProvider)
    - `config/*` (All standard config files: app, auth, database, filesystems, etc.)

### 3. Installer Script Logic
**Issue:** Database migrations were running *before* the `.env` file was configured with database credentials, causing "Access denied for user 'root'@'localhost'".
**Fix:**
- **installer/installers/panel.sh:** 
    - Split `setup_database` into `create_database` (user/db creation) and `run_migrations`.
    - Reordered execution in `main()`: 
        1. `create_database`
        2. `configure_environment` (sets .env)
        3. `run_migrations` (runs with correct credentials)

### 4. Database Schema & Migrations
**Issue:** 
- Pterodactyl compatibility issues (missing `username`, `name_first`, `name_last`, `uuid` in default Laravel user table).
- Duplicate column errors when running migrations (colliding fields between `create_users_table` and `add_pterodactyl_fields`).
- Seeder failure due to missing `username`.
**Fix:**
- **2014_10_12_000000_create_users_table.php:** Added `uuid`, `username`, `name_first`, `name_last`, `language` directly here.
- **2024_12_31_000002_create_nodes_table.php:** Added `uuid`, `daemon_base`, `upload_size` directly here.
- **Removed:** Redundant `2025_12_25_000001_add_pterodactyl_fields.php` to prevent duplicate column errors.
- **User Model:** Added `uuid` auto-generation on boot.
- **DatabaseSeeder:** Updated to include `username`, `name_first`, `name_last` when creating the admin user.

---

**Status:** ✅ All components tested locally. Installation should proceed smoothly on a fresh VPS.
