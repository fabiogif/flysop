#!/usr/bin/env bash
echo "Running composer"
#composer global require hirak/prestissimo
cp /etc/secrets/.env .env
echo "Running composer Install"
composer install --working-dir=/var/www/html
#composer require barryvdh/laravel-debugbar --dev
#composer require laravel/telescope --dev
composer update
composer require ramsey/uuid

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

#echo "Running migrations..."
#php artisan migrate --force

echo "Running telescope migrations..."
#php artisan telescope:install
#php artisan db:seed --class=PlansTableSeeder --force --no-interaction
#php artisan migrate
#echo "Npm Install"
#npm install

#echo "Running seed..."
#php artisan db:seed --class=UsersTableSeeder --force --no-interaction
#php artisan db:seed --class=PlansTableSeeder --force --no-interaction
#php artisan db:seed --class=TenantsTableSeeder --force --no-interaction

echo "Echo key app..."
php artisan key:generate --show
