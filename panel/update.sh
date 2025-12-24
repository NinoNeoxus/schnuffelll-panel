#!/bin/bash

# Schnuffelll Panel Auto-Updater
# Usage: ./update.sh

echo "Starting Update Process..."

# 1. Enable Maintenance Mode
php artisan down || true

# 2. Pull latest changes
# Assumes git is initialized
git pull origin main

# 3. Update Dependencies
composer install --no-dev --optimize-autoloader

# 4. Migrate Database
php artisan migrate --force

# 5. Clear Caches
php artisan view:clear
php artisan config:clear
php artisan route:clear

# 6. Disable Maintenance Mode
php artisan up

echo "Update Completed Successfully!"
