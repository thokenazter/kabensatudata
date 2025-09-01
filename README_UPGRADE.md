# 🗺️ PKM Kaben Map Upgrade - Dokumentasi

## 📋 Ringkasan Upgrade

Upgrade ini menambahkan fitur-fitur peta interaktif terbarukan ke aplikasi PIS-PK yang sudah ada, dengan pendekatan **minimal-invasif** dan **mudah rollback**.

### ✅ Fitur yang Ditambahkan

1. **🎯 BBOX + Delta Sync** - Loading data efisien berdasarkan viewport
2. **🔍 Pencarian Nomor Bangunan** - Cari dan jump ke bangunan spesifik  
3. **🏷️ Label Nomor Bangunan** - Toggle tampilan label nomor (ON/OFF)
4. **📏 Ukur Jarak** - Tool pengukuran jarak dengan Turf.js
5. **🎯 Buffer Epidemiologi** - Analisis buffer 50/100/200m + hitung rumah
6. **👥 Tetangga Terdekat** - Analisis k-NN (k=5) dengan jarak
7. **⚙️ Feature Flags** - Kontrol ON/OFF setiap fitur
8. **🚀 Performance Tuning** - Indeks spasial + optimasi Leaflet

### 🛡️ Kompatibilitas

- ✅ **Tidak merusak** fitur existing
- ✅ **Rollback mudah** via feature flags  
- ✅ **Endpoint lama tetap berfungsi**
- ✅ **JavaScript existing tidak terpengaruh**

---

## 🔧 Instalasi & Konfigurasi

### 1. Jalankan Migrasi

```bash
php artisan migrate
```

Migrasi ini menambahkan indeks spasial untuk performa BBOX query yang lebih baik.

### 2. Konfigurasi Feature Flags

Tambahkan ke file `.env`:

```env
# Base Map Configuration
MAP_USE_VECTOR_TILES=false
MAP_STYLE_URL=https://demotiles.maplibre.org/style.json

# Performance Features  
MAP_ENABLE_BBOX_SYNC=true
MAP_BBOX_DEBOUNCE_MS=250
MAP_MAX_FEATURES=5000

# Interactive Features
MAP_ENABLE_SEARCH_BUILDING=true
MAP_ENABLE_HOUSE_LABELS=true
MAP_ENABLE_MEASURE=true
MAP_ENABLE_BUFFERS=true
MAP_ENABLE_NEAREST=true

# Advanced Features (Optional)
MAP_ENABLE_HEATMAP=false
MAP_ENABLE_OFFLINE=false

# External Services
MAP_OSRM_URL=https://router.project-osrm.org

# Map Settings
MAP_DEFAULT_LAT=-5.7465
MAP_DEFAULT_LNG=134.797032
MAP_DEFAULT_ZOOM=15
MAP_MAX_ZOOM=25
```

### 3. Clear Cache (Opsional)

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📚 API Endpoints Baru

### BBOX Endpoint

**GET** `/api/map/buildings?bbox=minLon,minLat,maxLon,maxLat&since=ISO_TIME`

**Contoh Request:**
```
GET /api/map/buildings?bbox=134.79,5.74,134.80,-5.75&since=2025-08-27T07:00:00Z
```

**Contoh Response:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature", 
      "geometry": {
        "type": "Point",
        "coordinates": [134.797032, -5.7465]
      },
      "properties": {
        "id": 123,
        "building_number": "001",
        "village_name": "Kabalsiang",
        "village_id": 1,
        "updated_at": "2025-08-27T07:30:00Z"
      }
    }
  ],
  "bbox": [134.79, -5.75, 134.80, -5.74],
  "last_modified": "2025-08-27T08:00:00Z",
  "count": 1,
  "has_more": false
}
```

### Search Endpoint

**GET** `/api/map/buildings/find?num=123`

**Contoh Response:**
```json
{
  "id": 123,
  "building_number": "123", 
  "lat": -5.7465,
  "lon": 134.797032,
  "village_name": "Kabalsiang",
  "village_id": 1
}
```

### Stats Endpoint (Opsional)

**GET** `/api/map/stats?bbox=minLon,minLat,maxLon,maxLat`

**Contoh Response:**
```json
{
  "bbox": [134.79, -5.75, 134.80, -5.74],
  "buildings_count": 150,
  "families_count": 200,
  "generated_at": "2025-08-27T08:00:00Z"
}
```

---

## 🎮 Cara Menggunakan Fitur

### 1. Pencarian Bangunan

1. Buka halaman peta `/map`
2. Cari kotak pencarian di control panel
3. Ketik nomor bangunan (contoh: "123")
4. Klik hasil atau tekan Enter
5. Peta akan zoom ke lokasi bangunan

### 2. Label Nomor Bangunan

1. Klik tombol **"🏷️ Label #"** di control panel
2. Label nomor akan muncul/hilang pada marker
3. Berguna untuk orientasi cepat di area padat

### 3. Ukur Jarak

1. Klik tombol **"📏 Ukur Jarak"**
2. Toolbar pengukuran akan muncul
3. Klik **"Mulai Pengukuran"**
4. Klik titik-titik di peta untuk mengukur
5. Hasil jarak total akan ditampilkan
6. Tekan **ESC** atau **"Berhenti Ukur"** untuk selesai

### 4. Buffer Epidemiologi

1. Klik tombol **"🎯 Buffer Epi"**
2. Klik pada bangunan yang ingin dianalisis
3. Sistem akan menampilkan:
   - Lingkaran buffer 50m (hijau)
   - Lingkaran buffer 100m (kuning)  
   - Lingkaran buffer 200m (merah)
   - Jumlah rumah dalam setiap buffer
4. Panel info akan muncul dengan statistik

### 5. Tetangga Terdekat

1. Klik tombol **"👥 Tetangga 5"**
2. Klik pada bangunan pusat analisis
3. Sistem akan menampilkan:
   - 5 bangunan terdekat dengan garis penghubung
   - Nomor urutan (1-5) berdasarkan jarak
   - Popup dengan jarak dalam meter

---

## ⚙️ Kontrol Feature Flags

Setiap fitur dapat diaktifkan/nonaktifkan tanpa mengubah kode:

### Menonaktifkan Fitur

```env
MAP_ENABLE_SEARCH_BUILDING=false  # Nonaktifkan pencarian
MAP_ENABLE_HOUSE_LABELS=false     # Nonaktifkan label
MAP_ENABLE_MEASURE=false          # Nonaktifkan pengukuran
MAP_ENABLE_BUFFERS=false          # Nonaktifkan buffer
MAP_ENABLE_NEAREST=false          # Nonaktifkan tetangga
```

### Menggunakan Vector Tiles

```env
MAP_USE_VECTOR_TILES=true
MAP_STYLE_URL=https://your-vector-tiles-server.com/style.json
```

### Tuning Performance

```env
MAP_BBOX_DEBOUNCE_MS=500          # Delay loading (ms)
MAP_MAX_FEATURES=1000             # Max features per request
```

---

## 🔧 Troubleshooting

### Fitur Tidak Muncul

1. **Periksa feature flags** di `.env`
2. **Clear config cache**: `php artisan config:clear`
3. **Periksa console browser** untuk error JavaScript
4. **Pastikan Turf.js loaded** (untuk fitur pengukuran)

### BBOX Loading Lambat

1. **Tuning debounce**: Increase `MAP_BBOX_DEBOUNCE_MS`
2. **Reduce max features**: Decrease `MAP_MAX_FEATURES`  
3. **Check database indexes**: Pastikan migrasi sudah jalan

### Pencarian Tidak Akurat

1. **Periksa koordinat building**: Pastikan lat/lon tidak null
2. **Check building index**: Data mungkin belum termuat di viewport
3. **Geser peta** ke area yang dicari terlebih dulu

### Tools Tidak Responsif

1. **Periksa Turf.js**: Pastikan library loaded
2. **Check map state**: Pastikan `PkmKabenApp.state.map` tersedia
3. **Browser console**: Lihat error JavaScript

---

## 🚀 Performance Tips

### Database

- ✅ Migrasi indeks spasial sudah ditambahkan
- ✅ Query BBOX menggunakan `BETWEEN` yang cepat
- ✅ Limit 5000 features per request

### Frontend

- ✅ BBOX loading dengan debounce 250ms
- ✅ Delta sync mengurangi data transfer
- ✅ Local storage caching untuk building index
- ✅ Lazy loading fitur berdasarkan flags

### Monitoring

```javascript
// Debug di browser console
console.log(window.MAP_FEATURES);           // Lihat feature flags
console.log(window.MapFeatures);            // Debug BBOX loader
console.log(window.MapTools);               // Debug measurement tools
```

---

## 🔄 Rollback Plan

### Rollback Cepat (Tanpa Downtime)

1. **Nonaktifkan semua fitur baru**:
   ```env
   MAP_ENABLE_BBOX_SYNC=false
   MAP_ENABLE_SEARCH_BUILDING=false
   MAP_ENABLE_HOUSE_LABELS=false
   MAP_ENABLE_MEASURE=false
   MAP_ENABLE_BUFFERS=false
   MAP_ENABLE_NEAREST=false
   ```

2. **Clear cache**:
   ```bash
   php artisan config:clear
   ```

### Rollback Penuh (Jika Diperlukan)

1. **Revert migrasi** (opsional, aman dibiarkan):
   ```bash
   php artisan migrate:rollback --step=1
   ```

2. **Hapus file baru**:
   ```bash
   rm config/map.php
   rm app/Http/Controllers/MapBuildingController.php
   rm public/js/map-features.js
   rm public/js/map-tools.js
   rm public/css/map-features.css
   ```

3. **Revert routes** di `routes/api.php`
4. **Revert Blade** di `resources/views/map/index.blade.php`

---

## 📊 QA Checklist

### ✅ Functional Testing

- [ ] Peta muncul normal tanpa error
- [ ] BBOX loading bekerja saat geser/zoom
- [ ] Pencarian bangunan: kasus berhasil & tidak ditemukan
- [ ] Label toggle ON/OFF berfungsi
- [ ] Measurement tool: mulai, ukur, stop, clear
- [ ] Buffer analysis: klik building, tampil buffer + stats
- [ ] Nearest neighbors: klik building, tampil 5 tetangga
- [ ] Feature flags: ON/OFF mengubah perilaku

### ✅ Performance Testing

- [ ] Network trace: BBOX request mengurangi payload
- [ ] No memory leaks saat navigasi lama
- [ ] Responsive di mobile & desktop
- [ ] Loading indicator saat request API

### ✅ Compatibility Testing

- [ ] Fitur lama tetap berfungsi (cluster, heatmap, routing)
- [ ] Modal building detail masih bisa dibuka
- [ ] Village navigation tidak terpengaruh
- [ ] Search existing tidak konflik

### ✅ Error Handling

- [ ] BBOX request gagal: fallback graceful
- [ ] Building tidak ditemukan: pesan jelas
- [ ] Turf.js tidak load: fitur dinonaktifkan
- [ ] Koordinat invalid: validasi server

---

## 🆘 Support & Maintenance

### Log Monitoring

```bash
# Monitor API errors
tail -f storage/logs/laravel.log | grep "MapBuildingController"

# Monitor performance
tail -f storage/logs/laravel.log | grep "BBOX\|slow"
```

### Database Maintenance

```sql
-- Check spatial indexes
SHOW INDEX FROM buildings WHERE Key_name LIKE '%coordinates%';

-- Monitor query performance  
EXPLAIN SELECT * FROM buildings 
WHERE latitude BETWEEN -5.75 AND -5.74 
AND longitude BETWEEN 134.79 AND 134.80;
```

### JavaScript Debugging

```javascript
// Enable debug mode
localStorage.setItem('pkmKaben_debug', 'true');

// Monitor BBOX requests
window.MapFeatures.BboxLoader.buildingIndex.size; // Jumlah buildings cached

// Check feature flags
console.table(window.MAP_FEATURES);
```

---

## 📈 Future Enhancements

### Fitur yang Bisa Ditambahkan

1. **Heatmap Kasus** - Visualisasi density penyakit
2. **Offline Mode** - Cache tiles + data untuk area kerja
3. **Vector Tiles** - Peta lebih smooth dengan PMTiles
4. **Export Analysis** - Export hasil buffer/nearest ke Excel
5. **Routing Optimization** - OSRM self-hosted
6. **Real-time Sync** - WebSocket untuk update data live

### Optimasi Lanjutan

1. **Spatial Database** - Migrasi ke PostGIS untuk query kompleks
2. **CDN Assets** - Host Turf.js dan libraries lokal
3. **Service Worker** - Progressive Web App untuk offline
4. **Clustering** - Server-side clustering untuk ribuan marker

---

## 📞 Kontak

Untuk pertanyaan teknis atau bug report terkait upgrade ini:

- **Developer**: AI Assistant (Claude)
- **Dokumentasi**: README_UPGRADE.md (file ini)
- **Source Code**: `git log --grep="MAP_UPGRADE"`

---

*Dokumentasi ini dibuat otomatis sebagai bagian dari upgrade map interaktif PIS-PK.*
