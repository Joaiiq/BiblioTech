#!/usr/bin/env sh
set -e

php artisan migrate --force
php artisan db:seed --force
mkdir -p storage/app/public/capas storage/app/public/autores storage/app/public/perfis
php artisan storage:link --force
php artisan config:cache
php artisan view:cache
