#!/usr/bin/env sh
set -e

php artisan migrate --force
mkdir -p storage/app/public/capas storage/app/public/autores storage/app/public/perfis
php artisan storage:link --force
php artisan serve --host=0.0.0.0 --port="${PORT}"
