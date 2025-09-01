# Instruksi Load Testing dan Audit Keamanan

## Persiapan Load Testing dengan Artillery

### Instalasi Artillery
```bash
# Menggunakan npm (pastikan Node.js sudah terinstal)
npm install -g artillery

# ATAU menggunakan yarn
yarn global add artillery
```

### Menjalankan Load Testing
1. Pastikan aplikasi Laravel Anda berjalan (lokal atau server)
2. Sesuaikan konfigurasi di file `artillery-load-test.yml`:
   - Ubah URL target ke alamat aplikasi Anda
   - Sesuaikan kredensial login dengan pengguna valid di sistem
3. Jalankan tes dengan perintah:
```bash
artillery run artillery-load-test.yml
```

4. Untuk menyimpan hasil dalam bentuk HTML:
```bash
artillery run --output report.json artillery-load-test.yml
artillery report report.json
```

## Alternative Load Testing dengan k6

### Instalasi k6
```bash
# macOS
brew install k6

# Linux
curl -s https://packagecloud.io/install/repositories/k6/k6/script.deb.sh | sudo bash
sudo apt install k6
```

### Contoh Skrip k6 Sederhana
Buat file `load-test.js` dengan konten berikut:

```javascript
import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  vus: 50,
  duration: '60s',
};

export default function () {
  // Kunjungi halaman utama
  let res = http.get('http://localhost:8000/');
  check(res, { 'halaman utama status 200': (r) => r.status === 200 });
  
  // Kunjungi dashboard
  res = http.get('http://localhost:8000/dashboard');
  check(res, { 'dashboard status 200': (r) => r.status === 200 });
  
  sleep(1);
}
```

Jalankan dengan perintah:
```bash
k6 run load-test.js
```

## Rekomendasi Keamanan

### 1. Implementasi Rate Limiting
Tambahkan middleware rate limiting di `routes/web.php`:

```php
// Terapkan rate limiting untuk endpoint login
Route::middleware(['throttle:login'])->group(function () {
    Route::post('/login', [AuthController::class, 'authenticate']);
});

// Definisikan throttle login di app/Providers/RouteServiceProvider.php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

### 2. Aktifkan HTTPS
Ubah konfigurasi di `.env-octane-settings`:
```
OCTANE_HTTPS=true
```

### 3. Optimasi Cache Konfigurasi
Jalankan perintah berikut pada deployment:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Header Keamanan
Buat middleware baru:
```bash
php artisan make:middleware SecurityHeaders
```

Isi dengan:
```php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

Daftarkan di middleware global di Kernel. 