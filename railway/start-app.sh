#!/usr/bin/env sh
set -e

mkdir -p storage/app/public/capas storage/app/public/autores
php artisan storage:link --force
php artisan serve --host=0.0.0.0 --port="${PORT}"
