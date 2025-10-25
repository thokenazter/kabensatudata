import { defineStore } from 'pinia'
import { OfflineManager } from '../services/OfflineManager'

function normalizeBbox(bbox) {
  if (!Array.isArray(bbox) || bbox.length !== 4) return null
  return bbox.map((v) => Number(v))
}

function sameBbox(a, b, tolerance = 1e-6) {
  if (!Array.isArray(a) || !Array.isArray(b) || a.length !== 4 || b.length !== 4) return false
  for (let i = 0; i < 4; i += 1) {
    if (Math.abs(Number(a[i]) - Number(b[i])) > tolerance) return false
  }
  return true
}

export const useOfflineStore = defineStore('offline', {
  state: () => ({
    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
    cachedAreas: [], // [{bbox:[minLon,minLat,maxLon,maxLat], date}]
    syncQueueCount: 0,
    lastSync: null,
    listenersReady: false,
  }),
  actions: {
    setOnline(v) { this.isOnline = v },
    async addCachedArea(area) {
      const bbox = normalizeBbox(area?.bbox)
      if (!bbox) return
      const payload = { ...area, bbox, date: new Date().toISOString() }
      const existingIndex = this.cachedAreas.findIndex((saved) => sameBbox(saved?.bbox, bbox))
      if (existingIndex >= 0) this.cachedAreas.splice(existingIndex, 1)
      this.cachedAreas.unshift(payload)
      try { await OfflineManager.setMeta('cachedAreas', this.cachedAreas) } catch (_) {}
    },
    async loadCachedAreas() {
      try {
        const saved = await OfflineManager.getMeta('cachedAreas')
        if (Array.isArray(saved)) {
          this.cachedAreas = saved
            .map((area) => {
              const bbox = normalizeBbox(area?.bbox)
              return bbox ? { ...area, bbox } : null
            })
            .filter(Boolean)
        }
      } catch (_) {}
    },
    async removeCachedArea(index) {
      try {
        if (index < 0 || index >= this.cachedAreas.length) return
        this.cachedAreas.splice(index, 1)
        await OfflineManager.setMeta('cachedAreas', this.cachedAreas)
      } catch (_) {}
    },
    setQueueCount(n) { this.syncQueueCount = n },
    setLastSync(t) { this.lastSync = t },
    initOnlineListeners() {
      if (this.listenersReady) return
      window.addEventListener('online', () => this.setOnline(true))
      window.addEventListener('offline', () => this.setOnline(false))
      this.listenersReady = true
    },
    async initPersistence() {
      await this.loadCachedAreas()
    }
  },
})
