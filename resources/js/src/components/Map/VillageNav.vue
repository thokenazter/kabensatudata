<template>
  <div class="bg-white/95 rounded-full shadow border border-slate-200 px-2 py-1">
    <div class="flex items-center gap-1 overflow-x-auto whitespace-nowrap max-w-[92vw] md:max-w-[70vw]">
      <span class="hidden md:inline text-xs text-slate-600 px-2">Pilih Desa:</span>
      <button
        v-for="v in villages"
        :key="v.key"
        class="px-3 py-1 text-xs md:text-sm rounded-full border border-slate-200 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-700"
        @click="goToVillage(v)"
      >
        {{ v.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useMapStore } from '../../stores/mapStore'

const store = useMapStore()

const DEFAULT_ZOOM = 18

const villages = [
  { key: 'kabalsiang', label: 'Desa Kabalsiang', lat: -5.735956570057368, lng: 134.81293823523026 },
  { key: 'benjuring', label: 'Desa Benjuring',  lat: -5.741883608445164, lng: 134.81199436352347 },
  { key: 'kumul',     label: 'Desa Kumul',      lat: -5.789409950835995, lng: 134.79535878201878 },
  { key: 'batuley',   label: 'Desa Batuley',    lat: -5.807776092520572, lng: 134.8088228179635 },
  { key: 'kompane',   label: 'Desa Kompane',    lat: -5.647312941449185, lng: 134.763056963146 },
]

function goToVillage(v) {
  const z = Math.max(store.zoom, DEFAULT_ZOOM)
  try {
    window.dispatchEvent(new CustomEvent('map:goto', { detail: { lat: v.lat, lng: v.lng, zoom: z } }))
  } catch (_) {}
  store.setCenter({ lat: v.lat, lng: v.lng })
  store.setZoom(z)
}
</script>

<style scoped>
/* no-op */
</style>
