# Panduan Deployment Kaben Satu Data ke kabensatudata.web.id

## 1. Persiapan Environment Production

### File .env Production
Buat file `.env` di server dengan konfigurasi berikut:

```env
APP_NAME="Kaben|SatuData"
APP_ENV=production
APP_KEY=base64:Oxphjaid4/1QIAyrxHVLMGo8o2kMwx8sg/Gel3p0bPM=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://kabensatudata.web.id

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=kabensatudataweb_coretrack
DB_USERNAME=kabensatudataweb_coretrack
DB_PASSWORD=Thokenazter12

SESSION_DRIVER=database
SESSION_LIFETIME=1440
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.kabensatudata.web.id

CACHE_STORE=database
QUEUE_CONNECTION=database

# Email Configuration (sesuaikan dengan provider email Anda)
MAIL_MAILER=smtp
MAIL_HOST=mail.kabensatudata.web.id
MAIL_PORT=587
MAIL_USERNAME=noreply@kabensatudata.web.id
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kabensatudata.web.id
MAIL_FROM_NAME="Kaben Satu Data"
```

## 2. Struktur Direktori di Server

```
/home/username/domains/kabensatudata.web.id/
├── public_html/          # Document root (symlink ke project/public)
├── project/              # Laravel project files
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   └── vendor/
└── backups/              # Database backups
```

## 3. Konfigurasi Apache/Nginx

### Apache (.htaccess sudah ada di public/)
Pastikan mod_rewrite aktif dan AllowOverride All di virtual host:

```apache
<VirtualHost *:443>
    ServerName kabensatudata.web.id
    DocumentRoot /home/username/domains/kabensatudata.web.id/project/public
    
    <Directory /home/username/domains/kabensatudata.web.id/project/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```

### Nginx (jika menggunakan Nginx)
```nginx
server {
    listen 443 ssl;
    server_name kabensatudata.web.id;
    root /home/username/domains/kabensatudata.web.id/project/public;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
}
```

## 4. Script Deployment Otomatis

Buat file `deploy.sh` untuk otomatisasi deployment:

```bash
#!/bin/bash

# Konfigurasi
PROJECT_DIR="/home/username/domains/kabensatudata.web.id/project"
BACKUP_DIR="/home/username/domains/kabensatudata.web.id/backups"
REPO_URL="https://github.com/thokenazter/kabensatudata.git"

echo "=== Starting Deployment ==="

# Backup database
echo "Creating database backup..."
mysqldump -u kabensatudataweb_coretrack -p kabensatudataweb_coretrack > "$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

# Pull latest changes
echo "Pulling latest changes..."
cd $PROJECT_DIR
git pull origin main

# Install/Update dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear and cache config
echo "Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Build assets
echo "Building assets..."
npm ci --production
npm run build

echo "=== Deployment completed ==="
```

## 5. Optimisasi Production

### Composer Optimizations
```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize
```

### Laravel Optimizations
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### File Permissions
```bash
chmod -R 755 /path/to/project
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 6. Monitoring & Maintenance

### Cron Jobs untuk Queue & Scheduler
Tambahkan ke crontab:
```bash
# Laravel Scheduler
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker (restart setiap jam)
0 * * * * cd /path/to/project && php artisan queue:restart
```

### Log Monitoring
```bash
# Monitor error logs
tail -f storage/logs/laravel.log

# Monitor access logs
tail -f /var/log/apache2/access.log
```

## 7. Security Checklist

- [ ] APP_DEBUG=false di production
- [ ] APP_ENV=production
- [ ] SSL Certificate terpasang
- [ ] Database credentials aman
- [ ] File .env tidak accessible dari web
- [ ] Storage directory tidak accessible dari web
- [ ] Regular backup database
- [ ] Update dependencies secara berkala

## 8. Troubleshooting

### Common Issues:
1. **500 Error**: Check storage permissions
2. **Database Connection**: Verify DB credentials
3. **Assets not loading**: Run `npm run build`
4. **Cache issues**: Clear all caches

### Debug Commands:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```