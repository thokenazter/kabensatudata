import { defineStore } from 'pinia'

function initialState() {
  return {
    isActive: false,
    status: 'idle',
    mode: null, // 'online' | 'cached' | 'direct'
    source: null,
    destination: null, // { id, label, lat, lng }
    instructions: [],
    totalDistance: 0,
    totalDuration: 0,
    userLocation: null, // { lat, lng, accuracy }
    error: null,
    lastUpdated: null,
    stopHandler: null,
    heading: null,
  }
}

export const useNavigationStore = defineStore('navigation', {
  state: () => initialState(),
  getters: {
    hasInstructions(state) {
      return Array.isArray(state.instructions) && state.instructions.length > 0
    },
  },
  actions: {
    start({ destination }) {
      this.$patch({
        ...initialState(),
        isActive: true,
        status: 'Mengambil lokasi pengguna...',
        destination,
      })
    },
    setStatus(status) {
      this.status = status
      this.lastUpdated = Date.now()
    },
    setMode(mode, source = null) {
      this.mode = mode
      this.source = source || mode
      this.lastUpdated = Date.now()
    },
    setRoute({ mode, instructions = [], totalDistance = 0, totalDuration = 0, source = null }) {
      this.instructions = instructions
      this.totalDistance = totalDistance
      this.totalDuration = totalDuration
      this.mode = mode || this.mode
      this.source = source || this.source || mode
      this.status = null
      this.error = null
      this.lastUpdated = Date.now()
    },
    updateDirectGuidance({ distance, headingText }) {
      this.totalDistance = distance
      this.totalDuration = 0
      const text = headingText ? `Arah ${headingText}` : 'Menuju tujuan'
      this.instructions = [{ text, distance }]
      this.mode = 'direct'
      this.source = 'Offline (garis lurus)'
      this.status = null
      this.error = null
      this.lastUpdated = Date.now()
    },
    setUserLocation(location) {
      this.userLocation = location
      this.lastUpdated = Date.now()
    },
    setHeading(heading) {
      this.heading = Number.isFinite(heading) ? heading : null
      this.lastUpdated = Date.now()
    },
    setError(message) {
      this.error = message
      this.status = null
      this.lastUpdated = Date.now()
    },
    setStopHandler(handler) {
      this.stopHandler = handler
    },
    requestStop() {
      if (typeof this.stopHandler === 'function') {
        this.stopHandler()
      } else {
        this.reset()
      }
    },
    reset() {
      this.$patch(initialState())
    },
  },
})
