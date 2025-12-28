#!/usr/bin/env bash
# exit on error
set -o errexit

echo "🚀 Starting build process..."

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Install Node dependencies
echo "📦 Installing npm dependencies..."
npm ci

# Build frontend assets
echo "🔨 Building Vue.js frontend..."
npm run build

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Clear and cache config
echo "⚙️  Caching Laravel configuration..."
php artisan config:clear
php artisan config:cache

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache

# Cache views
echo "👁️  Caching views..."
php artisan view:cache

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction

# Seed database (optional - comment out if you don't want to seed on every deploy)
# echo "🌱 Seeding database..."
# php artisan db:seed --force

echo "✅ Build completed successfully!"
