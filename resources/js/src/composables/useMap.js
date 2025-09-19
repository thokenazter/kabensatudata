import L from 'leaflet'
import 'leaflet.markercluster'
import { ref } from 'vue'
import { useMapStore } from '../stores/mapStore'
import { MapService } from '../services/MapService'
import { useFilters } from './useFilters'
import { useOfflineMap } from './useOfflineMap'

export function useMap() {
  const store = useMapStore()
  const { buildingPassesFilters } = useFilters()
  const { saveBuildings } = useOfflineMap()

  const canViewSensitiveHealth = typeof window !== 'undefined' && Boolean(window.__canViewSensitiveHealth)

  function sanitizeFlags(flags = {}) {
    if (canViewSensitiveHealth) {
      return flags
    }

    const {
      has_tb,
      tb_medication,
      has_hypertension,
      mental_illness,
      ...rest
    } = flags || {}

    return rest
  }

  const mapRef = ref(null)
  let tileLayer
  const detailCache = new Map()
  let routeControl = null

  async function ensureRoutingLoaded() {
    if (L && L.Routing) return
    if (typeof window !== 'undefined') window.L = L
    await import('leaflet-routing-machine')
  }

  function colorForIKS(b) {
    switch (b.iks_status) {
      case 'healthy':
        return 'status-healthy'
      case 'pra_healthy':
        return 'status-pra-healthy'
      case 'unhealthy':
        return 'status-unhealthy'
      default:
        return 'status-unknown'
    }
  }

  function iksLabel(status) {
    switch (status) {
      case 'healthy':
        return 'Sehat'
      case 'pra_healthy':
        return 'Pra-Sehat'
      case 'unhealthy':
        return 'Tidak Sehat'
      default:
        return 'Tidak Diketahui'
    }
  }

  function markerHTML(building) {
    const colorClass = colorForIKS(building)
    const badges = badgeHTML(sanitizeFlags(building.flags || {}))
    // House-like icon box with number badge
    return `
      <div class="marker-wrapper">
        <div class="house-marker ${colorClass} leaflet-interactive">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m3 9 9-7 9 7"/><path d="M9 22V12h6v10"/><path d="M2 22h20"/>
          </svg>
        </div>
        <div class="marker-badge">${building.building_number ?? '-'}</div>
        ${badges}
      </div>
    `
  }

  function badgeHTML(flags) {
    const items = []
    if (flags.has_tb) items.push('<span title="TB" class="inline-block w-2 h-2 rounded-full bg-red-500 ml-1"></span>')
    if (flags.has_hypertension) items.push('<span title="Hipertensi" class="inline-block w-2 h-2 rounded-full bg-amber-500 ml-1"></span>')
    if (flags.mental_illness) items.push('<span title="Gg. Jiwa" class="inline-block w-2 h-2 rounded-full bg-purple-500 ml-1"></span>')
    if (flags.pregnant) items.push('<span title="Hamil" class="inline-block w-2 h-2 rounded-full bg-pink-500 ml-1"></span>')
    return items.length ? `<div class="absolute -bottom-1 -right-1 flex">${items.join('')}</div>` : ''
  }

  function gmapsUrl(lat, lon) {
    return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}&travelmode=driving`
  }

  async function startNavigation(toLat, toLon) {
    try {
      await ensureRoutingLoaded()
      if (!('geolocation' in navigator)) throw new Error('Geolocation tidak tersedia')
      const pos = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 8000 })
      })
      const from = L.latLng(pos.coords.latitude, pos.coords.longitude)
      const to = L.latLng(toLat, toLon)
      if (routeControl) {
        try { mapRef.value.removeControl(routeControl) } catch (_) {}
        routeControl = null
      }
      if (!L.Routing) throw new Error('Routing belum termuat')
      routeControl = L.Routing.control({
        waypoints: [from, to],
        router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        lineOptions: { styles: [{ color: '#2563eb', weight: 5, opacity: 0.8 }] },
        createMarker: () => null,
        show: false,
      })
      routeControl.addTo(mapRef.value)
    } catch (e) {
      window.open(gmapsUrl(toLat, toLon), '_blank')
    }
  }

  function createMarker(building) {
    const icon = L.divIcon({
      html: markerHTML(building),
      className: 'custom-house-marker',
      iconSize: [32, 32],
      iconAnchor: [16, 32], // bottom-center for stability
      popupAnchor: [0, -28],
    })
    const marker = L.marker([building.latitude, building.longitude], { icon, riseOnHover: true })

    function renderDetail(detail) {
      const fams = (detail.families || []).map(f => {
        const memberList = f.members || []
        const headMember = memberList.find(m => (m.relationship || '').toLowerCase() === 'kepala keluarga'.toLowerCase())
        const headLabel = f.head_name ? `KK: ${f.head_name}` : (headMember ? `KK: ${headMember.name}` : 'Keluarga')
        const count = memberList.length

        const headLink = headMember && headMember.slug
          ? `<a href="/family-members/${headMember.slug}" target="_blank" class="px-2 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-sm" style="color:#fff">Lihat KK</a>`
          : ''
        const cardHref = headMember && headMember.slug
          ? `/family-members/${headMember.slug}/family-card`
          : `/families/${f.id}/card`
        const familyCardLink = `<a href="${cardHref}" target="_blank" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">Kartu Keluarga</a>`

        return `
          <div class="py-2 border-b border-slate-100">
            <div class="flex items-center justify-between">
              <span class="font-medium">${headLabel}</span>
              <span class="text-xs text-slate-500">${count} org</span>
            </div>
            <div class="mt-1 flex gap-2">${headLink} ${familyCardLink}</div>
          </div>`
      }).join('')
      const originalFlags = building.flags || {}
      const displayFlags = sanitizeFlags(originalFlags)
      const chips = `
        <div class="flex flex-wrap gap-1 mb-2 text-[10px]">
          ${displayFlags.has_tb ? '<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded">TB</span>' : ''}
          ${displayFlags.tb_medication ? '<span class="px-2 py-0.5 bg-red-50 text-red-700 rounded">Obat TB</span>' : ''}
          ${displayFlags.has_hypertension ? '<span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded">Hipertensi</span>' : ''}
          ${displayFlags.mental_illness ? '<span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded">Gg. Jiwa</span>' : ''}
          ${displayFlags.has_clean_water ? '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">Air Bersih</span>' : ''}
          ${displayFlags.is_water_protected ? '<span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded">Air Terlindungi</span>' : ''}
          ${displayFlags.has_toilet ? '<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">Jamban</span>' : ''}
          ${displayFlags.is_toilet_sanitary ? '<span class="px-2 py-0.5 bg-green-50 text-green-700 rounded">Jamban Sehat</span>' : ''}
          ${displayFlags.pregnant ? '<span class="px-2 py-0.5 bg-pink-100 text-pink-700 rounded">Hamil</span>' : ''}
          ${displayFlags.facility_birth ? '<span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded">Persalinan Faskes</span>' : ''}
          ${displayFlags.immunization ? '<span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded">Imunisasi</span>' : ''}
        </div>`
      const sensitiveNote = !canViewSensitiveHealth
        ? '<div class="text-[10px] text-slate-400 italic mb-2">Informasi penyakit disembunyikan untuk pengguna non tenaga kesehatan.</div>'
        : ''
      const navButtons = `
        <div class="mt-2 flex gap-2">
          <button class="px-2 py-1 text-xs rounded bg-green-600 text-white hover:bg-green-700 nav-btn" data-lat="${building.latitude}" data-lon="${building.longitude}">Navigasi</button>
          <a href="${gmapsUrl(building.latitude, building.longitude)}" target="_blank" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">Google Maps</a>
        </div>`
      return `
        <div class="custom-popup">
          <div class="popup-header">
            <h3>Bangunan ${building.building_number ?? ''}</h3>
            <p>Desa: ${building.village_name ?? '-'}</p>
          </div>
          <div class="mb-2 text-xs text-slate-700">Keluarga: ${detail.families?.length ?? 0} | IKS: ${iksLabel(building.iks_status)}</div>
          ${chips}
          ${sensitiveNote}
          <div class="family-list">${fams || '<div class="text-slate-500">Tidak ada data keluarga</div>'}</div>
          ${navButtons}
        </div>
      `
    }

    // Bind popup once; Leaflet will open on click automatically
    marker.bindPopup('<div class="custom-popup">Memuat detail...</div>', { autoClose: true, closeButton: true, maxWidth: 320 })

    marker.on('popupopen', async (e) => {
      const baseHeader = `
        <div class="popup-header">
          <h3>Bangunan ${building.building_number ?? ''}</h3>
          <p>Desa: ${building.village_name ?? '-'}</p>
        </div>`
      e.popup.setContent(`<div class="custom-popup">${baseHeader}<div>Memuat detail...</div></div>`)
      const cached = detailCache.get(building.id)
      if (cached) {
        e.popup.setContent(renderDetail(cached))
        const el = e.popup.getElement()?.querySelector('.nav-btn')
        if (el) el.addEventListener('click', () => startNavigation(building.latitude, building.longitude), { once: true })
        return
      }
      try {
        const detail = await MapService.getFamiliesForBuilding(building.id)
        detailCache.set(building.id, detail)
        e.popup.setContent(renderDetail(detail))
        const el = e.popup.getElement()?.querySelector('.nav-btn')
        if (el) el.addEventListener('click', () => startNavigation(building.latitude, building.longitude), { once: true })
      } catch (err) {
        e.popup.setContent(`<div class="custom-popup">${baseHeader}<div class="text-red-600">Gagal memuat detail</div></div>`)
      }
    })
    return marker
  }

  async function addOrUpdateMarkers(buildings) {
    if (!store.cluster) return
    const toAdd = []
    for (const b of buildings) {
      if (!buildingPassesFilters(b)) continue
      if (store.markers.has(b.id)) continue
      const m = createMarker(b)
      store.markers.set(b.id, m)
      toAdd.push(m)
    }
    if (toAdd.length) store.cluster.addLayers(toAdd)
  }

  function rebuildMarkers() {
    if (!store.cluster) return
    store.cluster.clearLayers()
    store.markers.clear()
    addOrUpdateMarkers(store.buildings)
  }

  function currentBbox(map) {
    const bounds = map.getBounds()
    const north = bounds.getNorth()
    const south = bounds.getSouth()
    const east = bounds.getEast()
    const west = bounds.getWest()
    return [west, south, east, north]
  }

  async function fetchAndRender(map) {
    try {
      store.setLoading(true)
      const bbox = currentBbox(map)
      const buildings = await MapService.getBuildingsByBbox(bbox)
      store.setBuildings(buildings)
      await saveBuildings(buildings)
      await addOrUpdateMarkers(buildings)
      store.setLastFetchedBbox(bbox)
      try { window.dispatchEvent(new CustomEvent('map:buildings-updated')) } catch (_) {}
    } catch (e) {
      store.setError(e?.message || 'Gagal memuat data peta')
    } finally {
      store.setLoading(false)
    }
  }

  function initMap(el, center, zoom) {
    const map = L.map(el, {
      center: [center.lat, center.lng],
      zoom,
      minZoom: store.minZoom,
      maxZoom: store.maxZoom,
      preferCanvas: true,
    })
    mapRef.value = map

    // OSM tiles
    tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: store.maxZoom,
      maxNativeZoom: 19,
      minZoom: store.minZoom,
      attribution: '&copy; OpenStreetMap contributors',
      crossOrigin: true,
    })
    tileLayer.addTo(map)

    // Cluster
    const cluster = L.markerClusterGroup({
      chunkedLoading: true,
      maxClusterRadius: 40,
      spiderfyOnMaxZoom: true,
      removeOutsideVisibleBounds: true,
    })
    store.setCluster(cluster)
    map.addLayer(cluster)

    // Handle deep-linking via query params
    const params = new URLSearchParams(window.location.search)
    const qLat = parseFloat(params.get('lat') || params.get('latitude') || 'NaN')
    const qLon = parseFloat(params.get('lon') || params.get('lng') || params.get('longitude') || 'NaN')
    const qZoom = parseInt(params.get('zoom') || '')
    const qId = params.get('id') || params.get('buildingId')

    if (Number.isFinite(qLat) && Number.isFinite(qLon)) {
      const targetZoom = Number.isFinite(qZoom) ? qZoom : Math.max(19, zoom)
      map.setView([qLat, qLon], targetZoom)
      store.setCenter({ lat: qLat, lng: qLon })
      store.setZoom(targetZoom)
    }

    // Initial data
    fetchAndRender(map).then(() => {
      if (qId) {
        // Try open the marker popup for this building id
        const tryOpen = () => {
          const marker = store.markers.get(Number(qId)) || store.markers.get(qId)
          if (marker) {
            const ll = marker.getLatLng()
            map.setView(ll, Number.isFinite(qZoom) ? qZoom : map.getZoom())
            marker.openPopup()
            window.removeEventListener('map:buildings-updated', tryOpen)
          }
        }
        window.addEventListener('map:buildings-updated', tryOpen)
        // Attempt immediately in case markers already added
        tryOpen()
      }
    })

    // Fetch on view change (debounced)
    let timeout
    map.on('moveend', () => {
      clearTimeout(timeout)
      timeout = setTimeout(() => fetchAndRender(map), 250)
    })

    // Force re-positioning of markers on zoom end (precision/stability)
    map.on('zoomend', () => {
      for (const [id, m] of store.markers.entries()) {
        const b = store.buildings.find(x => x.id === id)
        if (b && m) m.setLatLng([b.latitude, b.longitude])
      }
    })

    // React to filter changes
    window.addEventListener('filters:changed', rebuildMarkers)

    // React to external go-to requests (e.g., VillageNav)
    const onGoto = (ev) => {
      const d = ev?.detail || {}
      const lat = Number(d.lat)
      const lng = Number(d.lng)
      const zoomTo = Number(d.zoom)
      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        map.setView([lat, lng], Number.isFinite(zoomTo) ? zoomTo : map.getZoom())
      }
    }
    window.addEventListener('map:goto', onGoto)

    return map
  }

  return { initMap, mapRef, fetchAndRender }
}
