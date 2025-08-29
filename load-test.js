import http from 'k6/http';
import { sleep, check, group } from 'k6';
import { Counter } from 'k6/metrics';

// Metrik custom
const failedRequests = new Counter('failed_requests');

// Konfigurasi load test
export const options = {
  // Jumlah pengguna virtual dan durasi
  scenarios: {
    constant_load: {
      executor: 'constant-arrival-rate',
      rate: 50,         // 50 pengguna per detik
      timeUnit: '1s',   // per detik
      duration: '60s',  // selama 60 detik
      preAllocatedVUs: 100, // alokasi awal
      maxVUs: 200,     // maksimum VU jika diperlukan
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'], // Tes gagal jika >1% request gagal
    http_req_duration: ['p(95)<500'], // 95% request harus selesai di bawah 500ms
  },
};

export default function () {
  // Cookies untuk menyimpan sesi
  const jar = http.cookieJar();

  group('Halaman Utama dan Dashboard', function () {
    // Kunjungi halaman home (akan redirect ke dashboard)
    let res = http.get('http://localhost:8000/');
    
    check(res, {
      'home status 200 atau 302': (r) => r.status === 200 || r.status === 302,
    }) || failedRequests.add(1);

    // Ambil CSRF token dari halaman untuk digunakan pada request selanjutnya
    const csrfToken = res.html().find('input[name=_token]').attr('value');
    
    // Kunjungi dashboard
    res = http.get('http://localhost:8000/dashboard');
    check(res, {
      'dashboard status 200': (r) => r.status === 200,
      'dashboard memuat benar': (r) => r.body.includes('Dashboard'),
    }) || failedRequests.add(1);

    sleep(0.5);
    
    // Simulasi Login
    if (csrfToken) {
      res = http.post('http://localhost:8000/login', {
        _token: csrfToken,
        email: 'user@example.com',  // Ganti dengan email valid
        password: 'password'        // Ganti dengan password valid
      }, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': csrfToken
        }
      });
      
      check(res, {
        'login berhasil': (r) => r.status === 302, // Redirect setelah login
      }) || failedRequests.add(1);
    }

    // Akses dashboard lagi setelah login
    res = http.get('http://localhost:8000/dashboard');
    check(res, {
      'dashboard setelah login status 200': (r) => r.status === 200,
      'tampil sebagai user yang login': (r) => r.body.includes('Logout'),
    }) || failedRequests.add(1);
  });

  // Istirahat sebentar sebelum VU selanjutnya
  sleep(1);
} 