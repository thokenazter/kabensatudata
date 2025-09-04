<template>
  <div ref="container" class="w-full h-full">
    <div ref="mapEl" class="w-full h-full" aria-label="Peta Kesehatan" />

    <!-- Loading & Error -->
    <div v-if="map.loading" class="absolute top-2 left-1/2 -translate-x-1/2 bg-white/90 px-3 py-1 rounded shadow text-sm">
      Memuat data peta...
    </div>
    <div v-if="map.error" class="absolute top-12 left-1/2 -translate-x-1/2 bg-red-50 text-red-700 px-3 py-2 rounded shadow text-sm">
      {{ map.error }}
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, provide } from 'vue'
import { useMapStore } from '../../stores/mapStore'
import { useMap } from '../../composables/useMap'

const map = useMapStore()
const { initMap, mapRef } = useMap()
const mapEl = ref(null)
const container = ref(null)

onMounted(() => {
  const m = initMap(mapEl.value, map.center, map.zoom)
  provide('leafletMap', mapRef)
})
</script>

<style>
.house-marker {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  border-radius: 6px;
  color: white;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(0,0,0,.2);
  box-sizing: border-box; /* include border in size to keep anchor exact */
}
.status-healthy { background-color: #10b981; border: 2px solid #15803d; }
.status-pra-healthy { background-color: #f59e0b; border: 2px solid #d97706; }
.status-unhealthy { background-color: #ef4444; border: 2px solid #b91c1c; }
.status-unknown { background-color: #3498db; border: 2px solid #2563eb; }

.custom-house-marker.leaflet-div-icon {
  background: transparent;
  border: none;
  width: 32px;
  height: 32px;
}

.marker-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #111827;
  color: #fff;
  border-radius: 9999px;
  font-size: 10px;
  padding: 2px 6px;
  border: 2px solid #fff;
  line-height: 1;
  pointer-events: none; /* ensure marker remains clickable */
}

.custom-popup { max-width: 320px; font-size: 14px; }
.custom-popup .popup-header { border-bottom: 1px solid #eee; margin-bottom: 8px; padding-bottom: 6px; }

/* Ensure Leaflet controls sit above app overlays */
.leaflet-control-container .leaflet-control { z-index: 1500; }

.marker-wrapper { width: 100%; height: 100%; position: relative; }
.marker-wrapper, .house-marker { line-height: 0; }
</style>
