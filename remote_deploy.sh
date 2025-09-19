#!/bin/bash

# Remote Deployment Script untuk Kaben Satu Data
# Jalankan script ini dari komputer lokal Anda

echo "=== Kaben Satu Data Remote Deployment ==="

# Konfigurasi - EDIT SESUAI SERVER ANDA
SERVER_HOST="kabensatudata.web.id"
SERVER_USER="your_username"
SERVER_PATH="/home/your_username/domains/kabensatudata.web.id/project"
REPO_URL="https://github.com/thokenazter/kabensatudata.git"

echo "Connecting to server: $SERVER_HOST"

# Deploy via SSH
ssh $SERVER_USER@$SERVER_HOST << 'ENDSSH'
    echo "=== Starting deployment on server ==="
    
    # Navigate to project directory
    cd /home/your_username/domains/kabensatudata.web.id/project
    
    # Pull latest changes
    echo "Pulling latest changes..."
    git pull origin main
    
    # Install dependencies
    echo "Installing dependencies..."
    composer install --no-dev --optimize-autoloader
    
    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force
    
    # Clear and cache
    echo "Optimizing application..."
    php artisan config:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Set permissions
    echo "Setting permissions..."
    chmod -R 775 storage bootstrap/cache
    
    echo "=== Deployment completed ==="
ENDSSH

echo "Remote deployment finished!"