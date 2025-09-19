import axios from 'axios'

const API = {
  // Prefer bbox API already available
  buildingsBbox: '/api/map/buildings',
  buildingFamilies: (id) => `/map/buildings/${id}`,
  healthStats: '/api/health-statistics/by-area',
  syncChanges: '/api/sync/changes',
}

export const MapService = {
  async getBuildingsByBbox(bounds) {
    // bounds: {north,south,east,west} or [minLon,minLat,maxLon,maxLat]
    let bbox
    if (Array.isArray(bounds)) {
      bbox = bounds
    } else {
      const { north, south, east, west } = bounds
      bbox = [west, south, east, north]
    }
    const q = bbox.join(',')
    const { data } = await axios.get(`${API.buildingsBbox}?bbox=${encodeURIComponent(q)}`)

    // data is GeoJSON FeatureCollection
    if (!data || !data.features) return []
    return data.features.map((f) => {
      const [lon, lat] = f.geometry.coordinates
      return {
        id: f.properties.id,
        building_number: f.properties.building_number,
        latitude: lat,
        longitude: lon,
        village_name: f.properties.village_name,
        village_id: f.properties.village_id,
        updated_at: f.properties.updated_at,
        families_count: f.properties.families_count,
        iks_status: f.properties.iks_status,
        flags: f.properties.flags || {},
      }
    })
  },

  async getFamiliesForBuilding(id) {
    const { data } = await axios.get(API.buildingFamilies(id))
    return data
  },

  async getHealthStatsByArea(bounds) {
    try {
      const { north, south, east, west } = bounds
      const bbox = [west, south, east, north].join(',')
      const { data } = await axios.get(`/api/map/stats?bbox=${encodeURIComponent(bbox)}`)
      return data
    } catch (e) {
      return null
    }
  },
}
