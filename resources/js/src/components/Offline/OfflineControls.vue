<template>
  <div class="bg-white/95 rounded shadow p-3 text-sm w-64">
    <div class="flex items-center justify-between mb-2">
      <div class="font-semibold">Offline</div>
      <div :class="['px-2 py-0.5 rounded text-white', offline.isOnline ? 'bg-emerald-500' : 'bg-red-500']">
        {{ offline.isOnline ? 'Online' : 'Offline' }}
      </div>
    </div>
    <div class="space-y-2">
      <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-1"
              @click="downloadArea">
        Download area saat ini
      </button>
      <div class="text-xs text-slate-600">
        Area tersimpan: {{ offline.cachedAreas.length }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue'
import { useOfflineStore } from '../../stores/offlineStore'
import { useOfflineMap } from '../../composables/useOfflineMap'

const offline = useOfflineStore()
offline.initOnlineListeners()

const { cacheViewport } = useOfflineMap()

// Access Leaflet map instance via provide/inject (optional)
const leafletMap = inject('leafletMap', null)

async function downloadArea() {
  if (!leafletMap?.value) return
  await cacheViewport(leafletMap.value)
}
</script>

