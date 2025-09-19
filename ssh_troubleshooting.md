# SSH Deployment Troubleshooting

## 🔍 Common Issues & Solutions

### Issue 1: Permission Denied
```bash
# Error: Permission denied
# Solution:
sudo chown -R $USER:$USER ~/project
chmod -R 755 ~/project
```

### Issue 2: Composer Not Found
```bash
# Error: composer: command not found
# Solution - Download composer:
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
# atau gunakan php composer.phar instead of composer
```

### Issue 3: PHP Version Issues
```bash
# Check PHP version
php -v

# If wrong version, try:
php8.2 artisan migrate
# atau
/usr/bin/php8.2 artisan migrate
```

### Issue 4: Database Connection Error
```bash
# Check database credentials in .env
cat .env | grep DB_

# Test database connection
php artisan tinker
# Then run: DB::connection()->getPdo();
```

### Issue 5: Storage Permission Issues
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

### Issue 6: Git Authentication
```bash
# If git asks for credentials
git config --global credential.helper store
# Then enter credentials once
```

### Issue 7: Node.js/NPM Not Available
```bash
# Check if Node.js available
node -v
npm -v

# If not available, skip npm commands or install:
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## 🔧 Verification Commands

```bash
# Check if everything is working
php artisan about
php artisan config:show
php artisan route:list
```

## 📞 Real-time Help Commands

Jika ada error, jalankan ini untuk diagnostic:

```bash
# Check PHP configuration
php -m | grep -E "(mysql|pdo|openssl|mbstring|tokenizer|xml|ctype|json)"

# Check Laravel requirements
php artisan about

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/

# Check .env file
cat .env | head -20

# Check error logs
tail -f storage/logs/laravel.log
```