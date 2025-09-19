import { defineStore } from 'pinia'

export const useMapStore = defineStore('map', {
  state: () => ({
    // viewport
    center: { lat: -5.739483493261797, lng: 134.79414177089714 },
    zoom: 15,
    minZoom: 10,
    maxZoom: 21,

    // data
    buildings: [], // normalized buildings
    markers: new Map(), // key: buildingId, value: Leaflet marker
    cluster: null, // Leaflet.markerClusterGroup

    // UI / filter state
    filters: {
      disease: {
        tuberculosis: false,
        tbMedication: false,
        hypertension: false,
        mentalIllness: false,
      },
      sanitation: {
        cleanWater: false,
        protectedWater: false,
        hasToilet: false,
        sanitaryToilet: false,
      },
      maternalChild: {
        pregnant: false,
        facilityBirth: false,
        immunization: false,
      },
      presets: null,
    },
    loading: false,
    error: null,
    lastFetchedBbox: null,
  }),
  actions: {
    setCluster(c) { this.cluster = c },
    setCenter(c) { this.center = c },
    setZoom(z) { this.zoom = z },
    setLoading(v) { this.loading = v },
    setError(e) { this.error = e },
    setBuildings(list) { this.buildings = list },
    setLastFetchedBbox(b) { this.lastFetchedBbox = b },
    resetMarkers() {
      if (this.cluster) this.cluster.clearLayers()
      this.markers.clear()
    },
  },
})
