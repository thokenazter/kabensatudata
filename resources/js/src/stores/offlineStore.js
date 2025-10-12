import { defineStore } from 'pinia'
import { OfflineManager } from '../services/OfflineManager'

export const useOfflineStore = defineStore('offline', {
  state: () => ({
    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
    cachedAreas: [], // [{bbox:[minLon,minLat,maxLon,maxLat], date}]
    syncQueueCount: 0,
    lastSync: null,
  }),
  actions: {
    setOnline(v) { this.isOnline = v },
    async addCachedArea(area) {
      this.cachedAreas.push({ ...area, date: new Date().toISOString() })
      try { await OfflineManager.setMeta('cachedAreas', this.cachedAreas) } catch (_) {}
    },
    async loadCachedAreas() {
      try {
        const saved = await OfflineManager.getMeta('cachedAreas')
        if (Array.isArray(saved)) this.cachedAreas = saved
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
      window.addEventListener('online', () => this.setOnline(true))
      window.addEventListener('offline', () => this.setOnline(false))
    },
    async initPersistence() {
      await this.loadCachedAreas()
    }
  },
})
