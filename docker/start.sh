#!/usr/bin/env bash
set -e

php artisan storage:link || true
php artisan optimize:clear
php artisan migrate --force
php artisan optimize

apache2-foreground