import L from 'leaflet'
import 'leaflet.markercluster'
import { ref } from 'vue'
import { useMapStore } from '../stores/mapStore'
import { MapService } from '../services/MapService'
import { useFilters } from './useFilters'
import { useOfflineMap } from './useOfflineMap'
import { OfflineManager } from '../services/OfflineManager'
import { useNavigationStore } from '../stores/navigationStore'

const DEFAULT_OSRM_SERVICE_URL = 'https://router.project-osrm.org/route/v1'
const OSRM_SERVICE_URL = (typeof window !== 'undefined' && window.__mapOsrmUrl) || DEFAULT_OSRM_SERVICE_URL

export function useMap() {
  const store = useMapStore()
  const { buildingPassesFilters } = useFilters()
  const { saveBuildings, getOfflineBuildingsByBbox } = useOfflineMap()
  const navigation = useNavigationStore()

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
  let fallbackRouteLine = null
  let userMarker = null
  let geoWatchId = null
  let orientationListener = null
  let orientationAbsoluteAttached = false
  let lastHeading = null

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

  // Navigation helpers and logic defined below

  function buildingLabel(building) {
    if (!building) return 'Bangunan'
    const number = building.building_number ? `Bangunan ${building.building_number}` : 'Bangunan'
    const village = building.village_name ? `, ${building.village_name}` : ''
    return `${number}${village}`
  }

  function toRadians(deg) {
    return (Number(deg) * Math.PI) / 180
  }

  function calculateBearing(from, to) {
    const lat1 = toRadians(from.lat)
    const lat2 = toRadians(to.lat)
    const dLon = toRadians(to.lng - from.lng)
    const y = Math.sin(dLon) * Math.cos(lat2)
    const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon)
    const brng = (Math.atan2(y, x) * 180) / Math.PI
    return (brng + 360) % 360
  }

  function bearingToCardinal(bearing) {
    if (!Number.isFinite(bearing)) return ''
    const dirs = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut']
    const idx = Math.round(bearing / 45) % dirs.length
    return dirs[idx]
  }

  function clearRouteControl() {
    if (routeControl && mapRef.value) {
      try { mapRef.value.removeControl(routeControl) } catch (_) {}
    }
    routeControl = null
  }

  function clearFallbackRouteLine() {
    if (fallbackRouteLine && mapRef.value) {
      try { mapRef.value.removeLayer(fallbackRouteLine) } catch (_) {}
    }
    fallbackRouteLine = null
  }

  function createUserMarkerIcon(heading) {
    const deg = Number.isFinite(heading) ? heading : 0
    return L.divIcon({
      className: 'user-location-icon',
      html: `<div class="user-location-marker" style="--heading:${deg}deg"><span class="user-location-arrow"></span><span class="user-location-dot"></span></div>`,
      iconSize: [32, 32],
      iconAnchor: [16, 16],
    })
  }

  function ensureUserMarker(latlng) {
    const map = mapRef.value
    if (!map) return
    const heading = navigation.heading
    if (!userMarker) {
      userMarker = L.marker(latlng, {
        icon: createUserMarkerIcon(heading),
        interactive: false,
        keyboard: false,
        zIndexOffset: 1000,
      })
      userMarker.addTo(map)
    } else {
      userMarker.setLatLng(latlng)
    }
  }

  function applyHeadingToMarker(heading) {
    if (!userMarker) return
    userMarker.setIcon(createUserMarkerIcon(heading))
  }

  function getCurrentPosition(options = {}) {
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        resolve,
        reject,
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 5000, ...options },
      )
    })
  }

  function updateDistanceToDestination(userLatLng) {
    const map = mapRef.value
    const dest = navigation.destination
    if (!map || !dest) return
    const destLatLng = L.latLng(dest.lat, dest.lng)
    const distance = map.distance(userLatLng, destLatLng)
    if (navigation.mode !== 'direct') {
      navigation.totalDistance = distance
    }
  }

  function updateDirectLine(userLatLng) {
    const map = mapRef.value
    const dest = navigation.destination
    if (!map || !dest) return
    const destLatLng = L.latLng(dest.lat, dest.lng)
    if (fallbackRouteLine) {
      fallbackRouteLine.setLatLngs([userLatLng, destLatLng])
    }
    const distance = map.distance(userLatLng, destLatLng)
    const heading = calculateBearing(userLatLng, destLatLng)
    navigation.updateDirectGuidance({ distance, headingText: bearingToCardinal(heading) })
  }

  function handleLocationUpdate(latlng, accuracy) {
    navigation.setUserLocation({ lat: latlng.lat, lng: latlng.lng, accuracy })
    ensureUserMarker(latlng)
    if (navigation.mode === 'direct') {
      updateDirectLine(latlng)
    } else {
      updateDistanceToDestination(latlng)
    }
  }

  function setupLocationWatch() {
    if (!('geolocation' in navigator)) return
    if (geoWatchId !== null) {
      try { navigator.geolocation.clearWatch(geoWatchId) } catch (_) {}
      geoWatchId = null
    }
    geoWatchId = navigator.geolocation.watchPosition(
      (pos) => {
        const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude)
        handleLocationUpdate(latlng, pos.coords.accuracy)
      },
      () => {
        navigation.setError('Gagal memperbarui lokasi pengguna.')
      },
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 },
    )
  }

  function normalizeHeading(value) {
    if (!Number.isFinite(value)) return null
    const normalized = ((value % 360) + 360) % 360
    return normalized
  }

  function handleOrientation(event) {
    let heading = null
    if (typeof event.webkitCompassHeading === 'number' && !Number.isNaN(event.webkitCompassHeading)) {
      heading = event.webkitCompassHeading
    } else if (typeof event.alpha === 'number' && !Number.isNaN(event.alpha)) {
      heading = event.absolute ? 360 - event.alpha : 360 - event.alpha
    }
    heading = normalizeHeading(heading)
    if (heading === null) return
    if (lastHeading !== null) {
      const diff = Math.abs(heading - lastHeading)
      if (Math.min(diff, 360 - diff) < 1) return
    }
    lastHeading = heading
    navigation.setHeading(heading)
    applyHeadingToMarker(heading)
  }

  async function setupOrientationTracking() {
    if (typeof window === 'undefined') return false
    if (!('DeviceOrientationEvent' in window) && !('ondeviceorientationabsolute' in window) && !('DeviceMotionEvent' in window)) {
      return false
    }
    if (orientationListener) return true
    try {
      if (typeof DeviceOrientationEvent.requestPermission === 'function') {
        const permission = await DeviceOrientationEvent.requestPermission()
        if (permission !== 'granted') {
          return false
        }
      }
    } catch (_) {
      // ignore permission errors; we will fall back silently
    }
    orientationListener = (event) => handleOrientation(event)
    try {
      window.addEventListener('deviceorientation', orientationListener, true)
      if ('ondeviceorientationabsolute' in window) {
        window.addEventListener('deviceorientationabsolute', orientationListener, true)
        orientationAbsoluteAttached = true
      }
    } catch (_) {
      orientationAbsoluteAttached = false
      orientationListener = null
      return false
    }
    return true
  }

  function stopOrientationTracking() {
    if (orientationListener) {
      window.removeEventListener('deviceorientation', orientationListener, true)
      if (orientationAbsoluteAttached) {
        window.removeEventListener('deviceorientationabsolute', orientationListener, true)
        orientationAbsoluteAttached = false
      }
      orientationListener = null
    }
    lastHeading = null
    navigation.setHeading(null)
  }

  function mapInstructionsForStore(instructions = []) {
    if (!Array.isArray(instructions)) return []
    return instructions.map((step) => ({
      text: step.text,
      distance: step.distance,
      time: step.time,
    }))
  }

  async function useDirectGuidance(userLatLng, destination) {
    const map = mapRef.value
    if (!map) return
    clearRouteControl()
    clearFallbackRouteLine()
    fallbackRouteLine = L.polyline([userLatLng, destination], {
      color: '#f97316',
      weight: 4,
      opacity: 0.9,
      dashArray: '6,6',
    })
    fallbackRouteLine.addTo(map)
    const distance = map.distance(userLatLng, destination)
    const heading = calculateBearing(userLatLng, destination)
    navigation.setRoute({
      mode: 'direct',
      instructions: [{ text: `Arah ${bearingToCardinal(heading)}`, distance }],
      totalDistance: distance,
      totalDuration: 0,
      source: 'Offline (garis lurus)',
    })
    navigation.setStatus('Mode offline: ikuti garis menuju bangunan')
    map.fitBounds(L.latLngBounds([userLatLng, destination]), { padding: [40, 40] })
  }

  async function useOfflineRoute(userLatLng, destination, building) {
    const map = mapRef.value
    if (!map) return
    navigation.setStatus('Mencari rute offline...')
    clearRouteControl()
    clearFallbackRouteLine()
    try {
      const cached = await OfflineManager.getRoute(building?.id)
      if (cached && Array.isArray(cached.coordinates) && cached.coordinates.length) {
        fallbackRouteLine = L.polyline(cached.coordinates.map(([lat, lng]) => [lat, lng]), {
          color: '#2563eb',
          weight: 5,
          opacity: 0.85,
        })
        fallbackRouteLine.addTo(map)
        const distance = cached.summary?.distance ?? map.distance(userLatLng, destination)
        navigation.setRoute({
          mode: 'cached',
          instructions: Array.isArray(cached.instructions) ? cached.instructions : [],
          totalDistance: distance,
          totalDuration: cached.summary?.duration ?? 0,
          source: 'Offline (rute tersimpan)',
        })
        navigation.setStatus('Menggunakan rute tersimpan (offline)')
        map.fitBounds(fallbackRouteLine.getBounds(), { padding: [40, 40] })
        return
      }
    } catch (_) {}
    await useDirectGuidance(userLatLng, destination)
  }

  async function computeRoute(userLatLng, destination, building) {
    const map = mapRef.value
    if (!map) return
    navigation.setStatus('Menghitung rute...')
    clearRouteControl()
    clearFallbackRouteLine()
    try {
      await ensureRoutingLoaded()
      if (!L.Routing) throw new Error('Routing belum termuat')
      routeControl = L.Routing.control({
        waypoints: [userLatLng, destination],
        router: L.Routing.osrmv1({ serviceUrl: OSRM_SERVICE_URL }),
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        lineOptions: { styles: [{ color: '#2563eb', weight: 5, opacity: 0.85 }] },
        createMarker: () => null,
        show: false,
      })
      routeControl.on('routesfound', async (ev) => {
        const route = ev?.routes?.[0]
        if (!route) {
          await useOfflineRoute(userLatLng, destination, building)
          return
        }
        const instructions = mapInstructionsForStore(route.instructions)
        const totalDistance = route.summary?.totalDistance ?? map.distance(userLatLng, destination)
        const totalDuration = route.summary?.totalTime ?? 0
        navigation.setRoute({
          mode: 'online',
          instructions,
          totalDistance,
          totalDuration,
          source: 'Online (OSRM)',
        })
        navigation.setStatus('Rute online siap')
        try {
          await OfflineManager.saveRoute(building?.id, {
            destination: {
              id: building?.id ?? null,
              label: buildingLabel(building),
              lat: building?.latitude,
              lng: building?.longitude,
            },
            coordinates: route.coordinates?.map((c) => [c.lat, c.lng]) ?? [],
            instructions,
            summary: {
              distance: totalDistance,
              duration: totalDuration,
            },
          })
        } catch (_) {}
      })
      routeControl.on('routingerror', async () => {
        await useOfflineRoute(userLatLng, destination, building)
      })
      routeControl.addTo(map)
    } catch (_) {
      await useOfflineRoute(userLatLng, destination, building)
    }
  }

  function stopNavigation() {
    clearRouteControl()
    clearFallbackRouteLine()
    stopOrientationTracking()
    if (userMarker && mapRef.value) {
      try { mapRef.value.removeLayer(userMarker) } catch (_) {}
    }
    userMarker = null
    if (geoWatchId !== null) {
      try { navigator.geolocation.clearWatch(geoWatchId) } catch (_) {}
      geoWatchId = null
    }
    navigation.reset()
    navigation.setStopHandler(stopNavigation)
  }

  async function startNavigation(building) {
    const map = mapRef.value
    if (!map) return
    if (!building || !Number.isFinite(Number(building.latitude)) || !Number.isFinite(Number(building.longitude))) {
      navigation.setError('Lokasi bangunan tidak valid.')
      return
    }
    stopNavigation()
    const destination = L.latLng(Number(building.latitude), Number(building.longitude))
    navigation.start({
      destination: {
        id: building.id ?? null,
        label: buildingLabel(building),
        lat: destination.lat,
        lng: destination.lng,
      },
    })
    navigation.setStopHandler(stopNavigation)
    if (!('geolocation' in navigator)) {
      navigation.setError('Perangkat tidak mendukung geolocation.')
      return
    }
    const orientationSetup = setupOrientationTracking()
    let currentPosition
    try {
      currentPosition = await getCurrentPosition()
    } catch (_) {
      navigation.setError('Tidak dapat mengambil lokasi Anda.')
      return
    }
    const userLatLng = L.latLng(currentPosition.coords.latitude, currentPosition.coords.longitude)
    navigation.setUserLocation({
      lat: userLatLng.lat,
      lng: userLatLng.lng,
      accuracy: currentPosition.coords.accuracy,
    })
    ensureUserMarker(userLatLng)
    map.flyTo(userLatLng, Math.max(map.getZoom(), 16), { duration: 0.8 })
    setupLocationWatch()
    await orientationSetup
    await computeRoute(userLatLng, destination, building)
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
      const offlineNotice = detail?.offline
        ? '<div class="text-[10px] text-amber-600 italic mb-2">Data ditampilkan dari cache offline.</div>'
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
          ${offlineNotice}
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
        if (el) el.addEventListener('click', (evt) => {
          evt.preventDefault()
          startNavigation(building)
        }, { once: true })
        return
      }
      try {
        const detail = await MapService.getFamiliesForBuilding(building.id)
        detailCache.set(building.id, detail)
        e.popup.setContent(renderDetail(detail))
        const el = e.popup.getElement()?.querySelector('.nav-btn')
        if (el) el.addEventListener('click', (evt) => {
          evt.preventDefault()
          startNavigation(building)
        }, { once: true })
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
      store.setError(null)
      await saveBuildings(buildings)
      await addOrUpdateMarkers(buildings)
      store.setLastFetchedBbox(bbox)
      try { window.dispatchEvent(new CustomEvent('map:buildings-updated')) } catch (_) {}
    } catch (e) {
      // Try offline fallback from IndexedDB
      try {
        const bbox = currentBbox(map)
        const offlineBuildings = await getOfflineBuildingsByBbox(bbox)
        if (Array.isArray(offlineBuildings) && offlineBuildings.length) {
          store.setBuildings(offlineBuildings)
          await addOrUpdateMarkers(offlineBuildings)
          store.setError('Mode offline: menampilkan data tersimpan')
          try { window.dispatchEvent(new CustomEvent('map:buildings-updated')) } catch (_) {}
        } else {
          store.setError(e?.message || 'Gagal memuat data peta')
        }
      } catch (_) {
        store.setError(e?.message || 'Gagal memuat data peta')
      }
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
    // expose in store for sibling components (e.g., OfflineControls)
    try { store.setMap(map) } catch (_) {}

    // OSM tiles
    tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: store.maxZoom,
      maxNativeZoom: 19,
      minZoom: store.minZoom,
      subdomains: ['a'], // match prefetch subdomain for better offline coverage
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

  navigation.setStopHandler(stopNavigation)

  return { initMap, mapRef, fetchAndRender, stopNavigation }
}
