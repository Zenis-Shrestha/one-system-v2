#!/bin/bash

set -e

echo "Starting Laravel CAS Server entrypoint..."

echo "Waiting for database connection..."
until php artisan db:show --database=pgsql; do
    echo "Database not ready, waiting 5 seconds..."
    sleep 5
done

echo "Database connected successfully!"

echo "Running Laravel setup commands..."

if grep -q "APP_KEY=base64::CHANGEME" /var/www/html/.env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "Caching configuration..."
php artisan config:cache

echo "Running database migrations..."
php artisan migrate --force

#if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
#fi

echo "Creating storage symlink..."
php artisan storage:link || true

echo "Optimizing application..."
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "Setting proper permissions..."
chown -R www:www /var/www/html/storage
chown -R www:www /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

#mkdir -p /var/log/supervisor
#mkdir -p /var/www/html/storage/logs

echo "One System setup completed!"

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf