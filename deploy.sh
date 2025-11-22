#!/usr/bin/env bash
set -e

cd /var/www/booknotes

echo "[DEPLOY] Pulling latest code..."
git pull

echo "[DEPLOY] Installing composer deps..."
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-bcmath

echo "[DEPLOY] Installing npm deps..."
npm ci || npm install

echo "[DEPLOY] Building assets..."
npm run build

echo "[DEPLOY] Running migrations..."
php artisan migrate --force

echo "[DEPLOY] Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[DEPLOY] Clearing other caches..."
php artisan optimize:clear
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "[DEPLOY] Fixing permissions..."
chown -R www-data:www-data /var/www/booknotes

echo "[DEPLOY] Restarting queue workers..."
supervisorctl restart booknotes-queue
supervisorctl status booknotes-queue

echo "[DEPLOY] Reloading PHP-FPM..."
systemctl reload php8.3-fpm

echo "[DEPLOY] Done!"
