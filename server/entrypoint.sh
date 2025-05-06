#!/bin/bash
set -e

# Composer dependencies
if [ ! -d "vendor" ]; then
    echo "Running composer install..."
    composer install --no-interaction --prefer-dist
fi

# NPM dependencies
if [ ! -d "node_modules" ]; then
    echo "Running npm install..."
    npm ci
fi

# Build assets
echo "Running npm run build..."
npm run build

# Symfony cache clear
echo "Clearing Symfony cache..."
php bin/console cache:clear

exec "$@"