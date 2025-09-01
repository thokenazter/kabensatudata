
# 🔧 Master Prompt + HRD — Upgrade Map Interaktif PIS‑PK (App Sudah Ada)

> **Peran AI yang diminta:** Senior Full‑Stack + GIS Engineer (Laravel + Blade + JS (Leaflet/MapLibre) + Turf + Offline Web)  
> **Konteks:** Aplikasi sudah berjalan. Tugas AI adalah **menganalisis kode yang ada terlebih dulu**, lalu **mengimplementasikan fitur peta terbarukan** secara **inkremental, kompatibel, aman**, dan mudah rollback.  
> **Catatan kunci:** Titik bangunan/rumah **SELALU** diambil dari **koordinat yang diinput petugas saat pendataan** (GPS/klik peta/drag refine) — **bukan geocoding**.

---

## 0) Mode Kerja & Output Wajib
1) **Discovery/Audit Singkat (wajib):**
   - Baca struktur project (Laravel/Blade/JS), file peta utama (contoh: `resources/views/map/index.blade.php`), modul JS terkait, routes/API yang sudah ada, model/migrasi bangunan/keluarga.
   - Hasilkan **Ringkasan Audit** mencakup:
     - Library peta saat ini (Leaflet/MapLibre) & plugin (cluster, heatmap, routing/OSRM, dll).
     - Skema data bangunan/rumah: kolom (lat, lon, nomor bangunan, relasi keluarga), validasi, dan indeks.
     - Endpoint data peta yang sudah ada.
     - Pain points performa/UX/security (PII) yang terlihat.
2) **Rencana Integrasi (minimal‑invasif):**
   - Jelaskan apa yang dipertahankan, apa yang ditambah, dan titik hook/extension yang paling aman.
   - Definisikan **feature flags** (ENV) agar setiap fitur baru bisa ON/OFF.
3) **Implementasi Inkremental (berurutan, aman rollback):**
   1. Tuning kualitas peta & interaksi (zoom halus/retina) + opsi base **Vector Tiles (MapLibre)** via flag.
   2. **BBOX + Delta‑Sync** (endpoint Laravel & loader di klien) — tanpa merusak loader lama.
   3. **Index & Pencarian Nomor Bangunan** (input → `flyTo`).
   4. **Label Nomor Bangunan (toggle)**.
   5. **Ukur Jarak** (Turf.js) + UI 📏.
   6. **Buffer Epi** 50/100/200 m + hitung jumlah rumah dalam tiap cincin.
   7. **Tetangga Terdekat (k‑NN, k=5)** + highlight & jarak.
   8. **Heatmap Kasus** (optional by flag).
   9. **Offline‑lite** (cache tiles & data; queue submit).
   10. **Proteksi PII & Role** (masking nama default; detail penuh butuh role).
4) **Dokumentasi & Uji:**
   - Tulis `README_UPGRADE.md`: cara enable/disable fitur, ENV, rute API, contoh req/resp, dan batasan.
   - Sertakan **Checklist QA** & **Acceptance Criteria**.
5) **Output Akhir yang harus diberikan AI (sebagai jawaban terstruktur):**
   - Ringkasan Audit → Rencana Integrasi → Patch/Diff berurutan (atau PR) dengan komentar dan alasan → Instruksi konfigurasi/ENV → Panduan uji/rollback.
   - Potongan kode Laravel (controller, route, policy), migrasi **non‑destruktif** bila perlu, Blade/JS siap tempel.
   - (Opsional) Template PR pertama (Langkah 1–3).

---

## 1) Spek Fitur Terbarukan (Lengkap)
### 1.1 Base Map “Rasa Google Maps”
- **Opsi A – Tetap Leaflet (cepat):**
  - Tile raster OSM dengan `detectRetina: true`, `maxNativeZoom: 19`, `updateWhenInteracting: true`, `keepBuffer: 4`.
  - Tuning interaksi: `zoomSnap: 0.25`, `zoomDelta: 0.5`, `wheelPxPerZoomLevel: 80`, `inertia: true`, `inertiaDeceleration: 3000`.
- **Opsi B – Vector Tiles (MapLibre GL) via flag `MAP_USE_VECTOR_TILES=true`:**
  - Style publik sementara (bisa ganti ke PMTiles self‑host nanti).
  - Kontrol: Navigation/Scale; `antialias: true`; smooth `flyTo`.
- **Syarat:** Jangan mematahkan layer lama (cluster/heatmap/routing).

### 1.2 Data Bangunan (Koordinat Input Petugas)
- Sumber koordinat sepenuhnya dari input (GPS/klik/drag).  
- **Validasi server**: lat ∈ [−90..90], lon ∈ [−180..180], tolak (0,0) & NaN.
- Simpan atribut (jika tersedia): `gps_accuracy_m`, `capture_method` (`gps`|`map_click`|`manual_edit`), `captured_at` UTC, `captured_by` user id.
- (Opsional) Audit trail perubahan koordinat.

### 1.3 Loading Data Cepat (BBOX + Delta‑Sync)
- **Endpoint baru** (tanpa mematikan endpoint lama):  
  `GET /map/buildings?bbox=minLon,minLat,maxLon,maxLat&since=ISO_TIME`  
  Respon: `{ features: GeoJSON[], last_modified: ISO8601 }`
- **Client:**
  - Debounce `moveend` 250 ms.
  - Simpan `last_modified` di `localStorage`.
  - Cache **mini‑index nomor bangunan** (num → lon/lat/id) untuk pencarian cepat.
- **DB/Indeks Spasial:** MySQL 8/PostGIS dengan kolom `POINT SRID 4326` + SPATIAL INDEX (jika ada).

### 1.4 Interaksi Lapangan & Analitik
- **Pencarian Nomor Bangunan:** input toolbar → `flyTo` ke zoom 18–19; bila nomor belum termuat, beritahu pengguna.
- **Label Nomor Bangunan:** tooltip/symbol kecil **toggle ON/OFF** agar peta tidak ramai.
- **Ukur Jarak:** mode klik titik menghasilkan polyline & total meter (ESC untuk keluar).
- **Buffer Epi 50/100/200 m:** dari rumah yang diklik; tampil cincin; hitung jumlah rumah di dalam masing‑masing cincin.
- **Tetangga Terdekat:** k‑NN (k=5) dari rumah kasus; tampil jarak (meter) & highlight.
- **Heatmap Kasus (opsional):** if data `case_type`/`weight` tersedia; by flag.
- Semua geometri perhitungan di klien memakai **Turf.js** (distance, length, buffer, booleanPointInPolygon, nearest).

### 1.5 Routing & Fallback
- Gunakan OSRM (online) jika tersedia; bila gagal atau offline, tampilkan **garis lurus + jarak geodesik** sebagai fallback.

### 1.6 Offline‑Lite (by flag)
- Cache tiles (IndexedDB) pada area kerja (desa/desa‑tetangga).
- Cache data bangunan hasil delta‑sync.
- Queue submit `POST/PATCH` saat offline → auto‑retry saat online.

### 1.7 Keamanan & Privasi
- **Masking PII Default** di popup (nama disingkat, mis. “A. Rahman”). Detail lengkap hanya jika `user->hasRole('epi_analyst'|'admin')`.
- Hindari mengekspos API key tiles; gunakan proxy backend atau **PMTiles self‑host**.
- Logging akses area sensitif (opsional).

---

## 2) Feature Flags (ENV)
Tambahkan variabel berikut (nama bisa disesuaikan dengan config proyek):
```
MAP_USE_VECTOR_TILES=false
MAP_ENABLE_BBOX_SYNC=true
MAP_ENABLE_SEARCH_BUILDING=true
MAP_ENABLE_HOUSE_LABELS=true
MAP_ENABLE_MEASURE=true
MAP_ENABLE_BUFFERS=true
MAP_ENABLE_NEAREST=true
MAP_ENABLE_HEATMAP=false
MAP_ENABLE_OFFLINE=false
MAP_STYLE_URL=https://demotiles.maplibre.org/style.json
MAP_OSRM_URL=https://router.project-osrm.org
```
> Semua fitur baru harus respek pada flag ini (graceful disable).

---

## 3) Struktur Patch/PR yang Diminta dari AI
- **Routes/API**
  - `routes/api.php` → tambah rute `GET /map/buildings` (BBOX+Delta) & `GET /map/buildings/find?num=`.
- **Controller**
  - `app/Http/Controllers/MapBuildingController.php` → method `bbox()` dan `find()` (non‑destruktif).
- **Migrations (opsional, non‑destruktif)**
  - Tambah kolom POINT SRID 4326 (jika belum ada), indeks spasial.
  - Tabel audit lokasi (jika perlu).
- **Views/Assets**
  - `resources/views/.../index.blade.php` → toolbar search, toggle labels, tombol ukur/buffer/nearest; injeksi Turf.js.
  - `public/js/map.features.js` → modul (measure/buffer/nearest/heatmap).
  - Config JS membaca ENV (via blade/inline script) untuk feature flags.
- **Dokumentasi**
  - `README_UPGRADE.md` → cara enable, env, endpoint, contoh request/response, batasan.
- **Template PR 1 (disarankan)**
  - Tuning peta + search nomor + toggle label (tanpa menyentuh backend).

---

## 4) Contoh Kode yang Harus Dihasilkan AI (Sketsa Aman)
### 4.1 Laravel – API BBOX/Delta
```php
// GET /map/buildings?bbox=minLon,minLat,maxLon,maxLat&since=ISO_TIME
public function bbox(Request $r) {
    $bbox = explode(',', (string) $r->query('bbox', ''));
    abort_unless(count($bbox) === 4, 422, 'Invalid bbox');
    [$minLon,$minLat,$maxLon,$maxLat] = array_map('floatval', $bbox);
    $since = $r->query('since');

    $q = Building::query()
        ->select('id','building_number','latitude','longitude','updated_at')
        ->when($since, fn($qq) => $qq->where('updated_at','>', $since))
        ->whereBetween('latitude', [$minLat, $maxLat])
        ->whereBetween('longitude', [$minLon, $maxLon])
        ->limit(5000);

    $features = $q->get()->map(fn($b)=>[
        'type'=>'Feature',
        'geometry'=>['type'=>'Point','coordinates'=>[(float)$b->longitude,(float)$b->latitude]],
        'properties'=>[
            'id'=>$b->id,
            'num'=>$b->building_number,
            'updated_at'=>optional($b->updated_at)->toIso8601String(),
        ]
    ]);

    return response()->json([
        'features'=>$features,
        'last_modified'=>now()->toIso8601String(),
    ]);
}
```

### 4.2 Laravel – Cari Nomor Bangunan
```php
// GET /map/buildings/find?num=123
public function find(Request $r) {
    $num = trim((string)$r->query('num',''));
    abort_if($num==='',
        422,'num required');
    $b = Building::where('building_number', $num)->first();
    abort_if(!$b, 404,'not found');
    return [
        'id'=>$b->id,
        'num'=>$b->building_number,
        'lat'=>(float)$b->latitude,
        'lon'=>(float)$b->longitude,
    ];
}
```

### 4.3 Frontend – Integrasi Turf & Toolbar Minimal
```html
<!-- Turf.js untuk pengukuran, buffer, nearest -->
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<div class="map-toolbar">
  <input id="searchNum" placeholder="Cari No. Bangunan…" />
  <button id="btnSearch">Cari</button>
  <button id="btnLabels">Label #</button>
  <button id="btnMeasure">Ukur 📏</button>
  <button id="btnBuffer">Buffer 50/100/200m</button>
  <button id="btnNearest">Tetangga 5</button>
</div>
<script>
  // contoh handlers – AI sesuaikan dengan modul & state yang ada
  document.querySelector('#btnSearch').onclick = () => jumpTo(document.querySelector('#searchNum').value);
  document.querySelector('#btnLabels').onclick = () => toggleHouseLabels();
  document.querySelector('#btnMeasure').onclick = () => ToolsMeasure.toggle();
  document.querySelector('#btnBuffer').onclick = () => ToolsBuffer.fromSelected();
  document.querySelector('#btnNearest').onclick = () => ToolsNearest.fromSelected();
</script>
```

### 4.4 Frontend – Pencarian Nomor → flyTo
```js
const buildingIndex = new Map(); // num -> feature
function jumpTo(num){
  const key = String(num||'').trim();
  if(!key){ alert('Masukkan nomor bangunan'); return; }
  const f = buildingIndex.get(key);
  if(!f){ alert('Nomor belum termuat di viewport.\nGeser/zoom atau gunakan menu Desa, lalu coba lagi.'); return; }
  const [lng,lat] = f.geometry.coordinates;
  map.flyTo([lat,lng], 19, {duration: 1.0});
}
```

### 4.5 Frontend – Measure/Buffer/Nearest (sketsa modul)
```js
const ToolsMeasure = (function(){
  let active=false, pts=[], layer=null;
  function toggle(){ active ? stop() : start(); }
  function start(){
    active=true; pts=[];
    map.on('click', onClick);
    window.addEventListener('keydown', onEsc);
  }
  function onClick(e){
    pts.push([e.latlng.lng, e.latlng.lat]); // [lon,lat]
    if(pts.length>=2){
      const line = turf.lineString(pts);
      const dist = turf.length(line,{units:'meters'});
      if(layer) map.removeLayer(layer);
      layer = L.polyline(pts.map(([x,y])=>[y,x]),{weight:3}).addTo(map);
      showToast(`Jarak: ${Math.round(dist)} m`);
    }
  }
  function onEsc(ev){ if(ev.key==='Escape') stop(); }
  function stop(){
    active=false;
    map.off('click', onClick);
    window.removeEventListener('keydown', onEsc);
    if(layer) map.removeLayer(layer), layer=null;
  }
  return {toggle};
})();
```

> **Catatan:** AI harus menyesuaikan potongan di atas dengan arsitektur & variabel peta yang ditemukan saat audit (mis. `PkmKabenApp.state.map`, dll).

---

## 5) Acceptance Criteria (AC)
1. **Zoom & geser halus** (tanpa tearing; label tajam di retina).
2. **BBOX + Delta‑Sync** mengurangi beban muat saat pindah desa (hanya data viewport).
3. **Pencarian nomor** → `flyTo` akurat ke titik; jika belum termuat, notifikasi jelas.
4. **Label toggle** (#) menampilkan/menyembunyikan label nomor bangunan.
5. **Ukur jarak** bekerja; ESC keluar mode.
6. **Buffer 50/100/200 m** tampil & menghitung jumlah rumah per cincin.
7. **Tetangga k=5** tampil & jaraknya benar (meter).
8. **Heatmap** (jika diaktifkan) tidak menutupi marker penting.
9. **Offline‑lite** (jika diaktifkan) menampilkan data/tiles yang sudah di‑cache; submit antri saat offline.
10. **Proteksi PII**: default masking; detail full hanya untuk role berwenang.
11. **Fitur lama tetap berjalan** (kompatibilitas terjaga).
12. **README_UPGRADE.md** tersedia dan jelas.

---

## 6) QA Checklist
- [ ] Peta muncul normal pada halaman existing.
- [ ] Switch Vector Tiles ↔ Raster aman (bila flag aktif).
- [ ] BBOX/delta: network trace menurun saat navigasi luas.
- [ ] Search nomor: kasus berhasil & gagal (nomor belum termuat) ditangani.
- [ ] Measure/Buffer/Nearest tidak mengganggu drag/zoom normal.
- [ ] Heatmap layer ordering benar.
- [ ] Offline‑lite: simulasi offline → data/tiles sebelumnya masih tampak; submit tersimpan di queue.
- [ ] Role & masking diuji (user biasa vs epi_analyst/admin).
- [ ] Rollback: mematikan flags mengembalikan perilaku lama tanpa error.

---

## 7) Catatan Implementasi Penting
- **Koordinat akurat**: simpan presisi 6–7 desimal; tampilkan dibulatkan bila perlu.
- **Audit lokasi** (opsional tapi dianjurkan): simpan reason & changed_by.
- **Kinerja**: gunakan renderer canvas (Leaflet) atau cluster native (MapLibre) untuk ribuan titik.
- **OSRM fallback**: jika gagal, tampilkan garis lurus + jarak geodesik.
- **Privasi**: jangan menampilkan identitas lengkap di label; gunakan popup dengan gating role.

---

## 8) Instruksi untuk AI (Format Jawaban)
> **Urutkan jawaban sebagai berikut:**
1) **Ringkasan Audit** (apa adanya dari project).  
2) **Rencana Integrasi** (fitur dan flag).  
3) **Patch/Diff atau PR** **bertahap** (Langkah 1→3→… dengan komentar dan alasan).  
4) **Instruksi Konfigurasi/ENV** + **README_UPGRADE.md** draft.  
5) **Panduan QA** + **Rollback plan**.

> **Jangan** meminta info tambahan kecuali benar‑benar tidak bisa disimpulkan dari kode — prioritaskan perubahan kecil, jelas, **bisa di‑rollback cepat**.

---

### (Opsional) Tambahan Endpoint
- `GET /map/stats?bbox=...` → ringkas jumlah rumah/KK untuk dashboard; tidak wajib untuk fitur inti.

---
