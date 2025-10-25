import { useOfflineStore } from '../stores/offlineStore'
import { OfflineManager } from '../services/OfflineManager'
import { MapService } from '../services/MapService'

export function useOfflineMap() {
  const offline = useOfflineStore()

  async function cacheViewport(map) {
    // Cache tiles via service worker (runtime caching handles tiles)
    // Cache data via IndexedDB
    const bounds = map.getBounds()
    const north = bounds.getNorth()
    const south = bounds.getSouth()
    const east = bounds.getEast()
    const west = bounds.getWest()
    const bbox = [west, south, east, north]
    // Precache tiles for a couple of zoom levels around current
    const z = map.getZoom()
    await precacheTiles({ west, south, east, north }, [Math.max(10, z - 1), Math.min(18, z + 1)])

    // Prefetch and store buildings data for this bbox
    let buildings = []
    try {
      buildings = await MapService.getBuildingsByBbox(bbox)
    } catch (err) {
      throw err
    }

    const savedCount = Array.isArray(buildings) ? buildings.length : 0
    let familiesSummary = { requested: 0, succeeded: 0 }
    if (savedCount) {
      await saveBuildings(buildings)
      familiesSummary = await prefetchFamilies(buildings)
    }

    await offline.addCachedArea({
      bbox,
      buildingCount: savedCount,
      familiesPrefetched: familiesSummary.succeeded,
      familiesRequested: familiesSummary.requested,
    })

    return {
      savedBuildings: savedCount,
      familiesPrefetched: familiesSummary,
    }
  }

  async function saveBuildings(buildings) {
    await OfflineManager.saveBuildings(buildings)
  }

  async function getOfflineBuildingsByBbox(bbox) {
    return OfflineManager.getBuildingsByBbox(bbox)
  }

  // Precache OSM tiles by iterating x/y in range
  async function precacheTiles(bounds, zoomRange) {
    const [zMin, zMax] = zoomRange
    const tasks = []
    for (let z = zMin; z <= zMax; z++) {
      const { xMin, xMax, yMin, yMax } = tileRangeForBounds(bounds, z)
      for (let x = xMin; x <= xMax; x++) {
        for (let y = yMin; y <= yMax; y++) {
          // Use only subdomain 'a' to match tile layer subdomains (set to ['a'])
          const urlA = `https://a.tile.openstreetmap.org/${z}/${x}/${y}.png`
          tasks.push(fetch(urlA).catch(() => null))
        }
      }
    }
    // Run in chunks to avoid flooding
    const chunk = 20
    for (let i = 0; i < tasks.length; i += chunk) {
      await Promise.all(tasks.slice(i, i + chunk))
    }
  }

  async function prefetchFamilies(buildings) {
    const MAX_BUILDINGS = 150
    const CONCURRENCY = 5
    const targets = buildings.slice(0, MAX_BUILDINGS)
    let succeeded = 0
    for (let i = 0; i < targets.length; i += CONCURRENCY) {
      const batch = targets.slice(i, i + CONCURRENCY)
      const results = await Promise.all(
        batch.map((b) =>
          MapService.getFamiliesForBuilding(b.id)
            .then(() => true)
            .catch(() => false)
        )
      )
      succeeded += results.filter(Boolean).length
    }
    return { requested: targets.length, succeeded }
  }

  function tileRangeForBounds({ west, south, east, north }, z) {
    function lon2tile(lon, zoom) { return Math.floor(((lon + 180) / 360) * Math.pow(2, zoom)) }
    function lat2tile(lat, zoom) {
      return Math.floor(
        (
          (1 - Math.log(Math.tan((lat * Math.PI) / 180) + 1 / Math.cos((lat * Math.PI) / 180)) / Math.PI) /
          2
        ) * Math.pow(2, zoom)
      )
    }
    const xMin = lon2tile(west, z)
    const xMax = lon2tile(east, z)
    const yMin = lat2tile(north, z)
    const yMax = lat2tile(south, z)
    return { xMin, xMax, yMin, yMax }
  }

  return { cacheViewport, saveBuildings, getOfflineBuildingsByBbox }
}
