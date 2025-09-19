#!/bin/bash

# Konfigurasi Deployment Kaben Satu Data
PROJECT_DIR="/home/username/domains/kabensatudata.web.id/project"
BACKUP_DIR="/home/username/domains/kabensatudata.web.id/backups"
REPO_URL="https://github.com/thokenazter/kabensatudata.git"

echo "=== Kaben Satu Data Deployment Started ==="
echo "Timestamp: $(date)"

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Backup database
echo "📦 Creating database backup..."
mysqldump -u kabensatudataweb_coretrack -pThokenazter12 kabensatudataweb_coretrack > "$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

# Navigate to project directory
cd $PROJECT_DIR

# Put application in maintenance mode
echo "🔧 Enabling maintenance mode..."
php artisan down --message="System sedang dalam pemeliharaan. Mohon tunggu beberapa menit."

# Pull latest changes from GitHub
echo "📥 Pulling latest changes from GitHub..."
git pull origin main

# Install/Update Composer dependencies
echo "📦 Installing/Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install/Update NPM dependencies and build assets
echo "🎨 Building frontend assets..."
npm ci --production
npm run build

# Clear all caches
echo "🧹 Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Cache configurations for production
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔐 Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Sync chatbot knowledge (if command exists)
echo "🤖 Syncing chatbot knowledge..."
php artisan chatbot:sync-knowledge || echo "Chatbot sync command not found, skipping..."

# Disable maintenance mode
echo "✅ Disabling maintenance mode..."
php artisan up

echo "=== Deployment Completed Successfully ==="
echo "🎉 Kaben Satu Data is now live at https://kabensatudata.web.id"
echo "Timestamp: $(date)"

# Optional: Send notification (uncomment if needed)
# curl -X POST "https://api.telegram.org/bot<BOT_TOKEN>/sendMessage" \
#      -d "chat_id=<CHAT_ID>" \
#      -d "text=✅ Kaben Satu Data deployment completed successfully at $(date)"