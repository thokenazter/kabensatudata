# Panduan Deployment via cPanel untuk Kaben Satu Data

## 🎯 Langkah-langkah Deployment via cPanel

### 1. **Upload Files via File Manager**

1. Login ke cPanel hosting Anda
2. Buka **File Manager**
3. Navigate ke folder `public_html` atau `domains/kabensatudata.web.id/`
4. Upload file project atau clone dari GitHub

### 2. **Setup Database**

1. Buka **MySQL Databases** di cPanel
2. Pastikan database sudah ada:
   - Database: `kabensatudataweb_coretrack`
   - User: `kabensatudataweb_coretrack`
   - Password: `Thokenazter12`

### 3. **Upload via Git (jika tersedia)**

```bash
# Di Terminal cPanel (jika ada)
cd domains/kabensatudata.web.id/
git clone https://github.com/thokenazter/kabensatudata.git project
cd project
```

### 4. **Setup Environment**

1. Copy file `.env.production` ke `.env`
2. Edit `.env` sesuai dengan setting hosting:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kabensatudata.web.id

DB_HOST=localhost
DB_DATABASE=kabensatudataweb_coretrack
DB_USERNAME=kabensatudataweb_coretrack
DB_PASSWORD=Thokenazter12
```

### 5. **Install Dependencies**

Via Terminal cPanel (jika ada):
```bash
cd project
composer install --no-dev --optimize-autoloader
```

Via File Manager (jika tidak ada terminal):
- Upload folder `vendor` yang sudah di-generate di local

### 6. **Run Migrations**

```bash
php artisan migrate --force
```

### 7. **Set Permissions**

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 8. **Optimize for Production**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. **Setup Document Root**

Di cPanel, set document root ke:
`/home/username/domains/kabensatudata.web.id/project/public`

## 🔧 Troubleshooting

### Jika tidak ada SSH/Terminal:
1. Upload semua files via File Manager
2. Upload database via phpMyAdmin
3. Set permissions via File Manager
4. Test aplikasi

### Jika ada error 500:
1. Check file permissions
2. Check .env configuration
3. Check error logs di cPanel