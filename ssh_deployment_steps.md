# SSH Deployment Steps untuk Kaben Satu Data

## 🚀 Panduan Deployment via SSH Terminal

### Persiapan Informasi
Sebelum mulai, pastikan Anda punya:
- SSH credentials (host, username, password/key)
- Database sudah dibuat di hosting
- Domain sudah pointing ke server

### Step 1: Koneksi SSH
```bash
ssh username@kabensatudata.web.id
# atau
ssh username@server-ip-address
```

### Step 2: Navigate ke Directory Web
```bash
# Biasanya salah satu dari ini:
cd ~/public_html
# atau
cd ~/domains/kabensatudata.web.id/public_html
# atau
cd /var/www/html
# atau sesuai struktur hosting Anda
```

### Step 3: Clone Repository
```bash
# Clone project dari GitHub
git clone https://github.com/thokenazter/kabensatudata.git project

# Masuk ke directory project
cd project
```

### Step 4: Install Dependencies
```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies (jika ada Node.js)
npm ci --production
npm run build
```

### Step 5: Setup Environment
```bash
# Copy environment file
cp .env.production .env

# Generate application key
php artisan key:generate

# Edit .env jika perlu
nano .env
```

### Step 6: Setup Database
```bash
# Run migrations
php artisan migrate --force

# Seed data (jika ada)
php artisan db:seed --force
```

### Step 7: Set Permissions
```bash
# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 8: Optimize for Production
```bash
# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear unnecessary caches
php artisan config:clear
php artisan cache:clear
```

### Step 9: Setup Document Root
```bash
# Buat symlink dari public_html ke project/public
ln -sf ~/project/public/* ~/public_html/
# atau
ln -sf ~/project/public ~/public_html
```

### Step 10: Test Application
```bash
# Test artisan commands
php artisan --version

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/
```

## 🔧 Commands untuk Copy-Paste

Berikut command lengkap yang bisa Anda copy-paste:

```bash
# 1. Clone repository
git clone https://github.com/thokenazter/kabensatudata.git project && cd project

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Setup environment
cp .env.production .env && php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Set permissions
chmod -R 775 storage bootstrap/cache

# 6. Optimize
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 7. Test
php artisan --version
```