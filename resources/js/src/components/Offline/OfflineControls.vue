<template>
  <div class="bg-white/95 rounded shadow p-3 text-sm w-64">
    <div class="flex items-center justify-between mb-2">
      <div class="font-semibold">Offline</div>
      <div :class="['px-2 py-0.5 rounded text-white', offline.isOnline ? 'bg-emerald-500' : 'bg-red-500']">
        {{ offline.isOnline ? 'Online' : 'Offline' }}
      </div>
    </div>
    <div class="space-y-2">
      <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-1 disabled:opacity-60 disabled:cursor-not-allowed"
              :disabled="isDownloading || !offline.isOnline"
              @click="downloadArea">
        <span v-if="!isDownloading">Download area saat ini</span>
        <span v-else class="inline-flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          Mengunduh...
        </span>
      </button>
      <p v-if="!offline.isOnline" class="text-xs text-slate-500">Sambungkan ke internet untuk memperbarui cache peta.</p>
      <div class="text-xs text-slate-600">
        Area tersimpan: {{ offline.cachedAreas.length }}
      </div>
      <!-- List cached areas -->
      <div v-if="offline.cachedAreas.length" class="max-h-48 overflow-auto border rounded p-2 text-xs space-y-2">
        <div v-for="(area, idx) in offline.cachedAreas" :key="idx" class="flex items-start justify-between gap-2">
          <div>
            <div class="font-medium">Area {{ idx + 1 }}</div>
            <div class="text-slate-500">{{ formatDate(area.date) }}</div>
            <div class="text-slate-400">BBOX: {{ fmtBbox(area.bbox) }}</div>
            <div class="text-slate-400">Bangunan: {{ area.buildingCount ?? '-' }} | Detail keluarga: {{ area.familiesPrefetched ?? 0 }}/{{ area.familiesRequested ?? area.buildingCount ?? 0 }}</div>
          </div>
          <div class="flex flex-col gap-1">
            <button class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700" @click="gotoArea(area)">Lihat</button>
            <button class="px-2 py-0.5 rounded bg-red-50 hover:bg-red-100 text-red-600" @click="removeArea(idx)">Hapus</button>
          </div>
        </div>
      </div>
      <div v-if="status" class="text-xs" :class="statusClass">{{ status }}</div>
    </div>
  </div>
 </template>

<script setup>
import { inject, ref, computed } from 'vue'
import L from 'leaflet'
import { useMapStore } from '../../stores/mapStore'
import { useOfflineStore } from '../../stores/offlineStore'
import { useOfflineMap } from '../../composables/useOfflineMap'

const offline = useOfflineStore()
offline.initOnlineListeners()
offline.initPersistence()

const { cacheViewport } = useOfflineMap()

// Access Leaflet map instance via store; fallback to inject (older approach)
const mapStore = useMapStore()
const injected = inject('leafletMap', null)
function getMap() {
  return mapStore.map || injected?.value || null
}

async function downloadArea() {
  if (!offline.isOnline) {
    status.value = 'Gagal: tidak dapat mengunduh saat offline'
    setTimeout(() => { status.value = '' }, 3000)
    return
  }
  const map = getMap()
  if (!map) {
    status.value = 'Gagal: peta belum siap'
    setTimeout(() => { status.value = '' }, 2000)
    return
  }
  if (isDownloading.value) return
  isDownloading.value = true
  status.value = 'Info: mengunduh area...'
  try {
    const summary = await cacheViewport(map)
    const saved = summary?.savedBuildings ?? 0
    const familyPrefetch = summary?.familiesPrefetched?.succeeded ?? 0
    const familyRequested = summary?.familiesPrefetched?.requested ?? 0
    status.value = `Sukses: area tersimpan (${saved} bangunan, ${familyPrefetch}/${familyRequested} detail keluarga) — total area ${offline.cachedAreas.length}`
  } catch (e) {
    status.value = 'Gagal: pengunduhan area tidak berhasil'
  } finally {
    isDownloading.value = false
    setTimeout(() => { status.value = '' }, 3000)
  }
}

const isDownloading = ref(false)
const status = ref('')
const statusClass = computed(() => {
  if (!status.value) return ''
  if (status.value.startsWith('Gagal')) return 'text-red-600'
  if (status.value.startsWith('Info')) return 'text-slate-600'
  return 'text-emerald-600'
})

function formatDate(dateStr) {
  try { return new Date(dateStr).toLocaleString() } catch { return dateStr || '' }
}

function fmtBbox(bbox) {
  if (!Array.isArray(bbox) || bbox.length !== 4) return '-'
  const [w, s, e, n] = bbox.map(v => Number(v).toFixed(5))
  return `${w}, ${s}, ${e}, ${n}`
}

function gotoArea(area) {
  const map = getMap()
  if (!map) return
  const bbox = area.bbox
  if (!Array.isArray(bbox) || bbox.length !== 4) return
  const [w, s, e, n] = bbox.map(Number)
  const bounds = L.latLngBounds(L.latLng(s, w), L.latLng(n, e))
  map.fitBounds(bounds, { padding: [20, 20] })
}

async function removeArea(index) {
  await offline.removeCachedArea(index)
}
</script>
