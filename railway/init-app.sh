#!/usr/bin/env sh
set -e

php artisan migrate --force
php artisan storage:link --force
php artisan config:cache
php artisan view:cache
