import { defineStore } from 'pinia'

export const useOfflineStore = defineStore('offline', {
  state: () => ({
    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
    cachedAreas: [], // [{bbox:[minLon,minLat,maxLon,maxLat], date}]
    syncQueueCount: 0,
    lastSync: null,
  }),
  actions: {
    setOnline(v) { this.isOnline = v },
    addCachedArea(area) { this.cachedAreas.push({ ...area, date: new Date().toISOString() }) },
    setQueueCount(n) { this.syncQueueCount = n },
    setLastSync(t) { this.lastSync = t },
    initOnlineListeners() {
      window.addEventListener('online', () => this.setOnline(true))
      window.addEventListener('offline', () => this.setOnline(false))
    },
  },
})

