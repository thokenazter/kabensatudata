import { openDB } from 'idb'

const DB_NAME = 'health-map-db'
const DB_VERSION = 1

async function getDb() {
  return openDB(DB_NAME, DB_VERSION, {
    upgrade(db) {
      if (!db.objectStoreNames.contains('buildings')) {
        const store = db.createObjectStore('buildings', { keyPath: 'id' })
        store.createIndex('byVillage', 'village_id')
        store.createIndex('byUpdatedAt', 'updated_at')
      }
      if (!db.objectStoreNames.contains('families')) {
        const store = db.createObjectStore('families', { keyPath: 'id' })
        store.createIndex('byBuilding', 'building_id')
      }
      if (!db.objectStoreNames.contains('sync_queue')) {
        db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true })
      }
      if (!db.objectStoreNames.contains('meta')) {
        db.createObjectStore('meta')
      }
    },
  })
}

export const OfflineManager = {
  async saveBuildings(buildings) {
    const db = await getDb()
    const tx = db.transaction('buildings', 'readwrite')
    for (const b of buildings) await tx.store.put(b)
    await tx.done
  },
  async getBuildingsByBbox(bbox) {
    // bbox: [minLon,minLat,maxLon,maxLat]
    const db = await getDb()
    const all = await db.getAll('buildings')
    const [minLon, minLat, maxLon, maxLat] = bbox.map(Number)
    return all.filter((b) => {
      const lat = Number(b.latitude)
      const lon = Number(b.longitude)
      return lat >= minLat && lat <= maxLat && lon >= minLon && lon <= maxLon
    })
  },
  async saveFamiliesForBuilding(buildingId, families) {
    const db = await getDb()
    const tx = db.transaction('families', 'readwrite')
    for (const f of families) {
      await tx.store.put({ ...f, building_id: buildingId })
    }
    await tx.done
  },
  async getFamiliesForBuilding(buildingId) {
    const db = await getDb()
    const idx = db.transaction('families').store.index('byBuilding')
    return idx.getAll(buildingId)
  },
  async enqueueChange(change) {
    const db = await getDb()
    await db.add('sync_queue', { ...change, created_at: new Date().toISOString() })
  },
  async getQueue() {
    const db = await getDb()
    return db.getAll('sync_queue')
  },
  async clearQueue() {
    const db = await getDb()
    await db.clear('sync_queue')
  },
  async setMeta(key, value) {
    const db = await getDb()
    await db.put('meta', value, key)
  },
  async getMeta(key) {
    const db = await getDb()
    return db.get('meta', key)
  },
}

