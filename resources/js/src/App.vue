<template>
  <div class="min-h-screen w-full bg-slate-50">
    <div class="relative h-screen">
      <HealthMap />

      <!-- Toggle Panels Button -->
      <button
        class="absolute top-3 left-3 z-[1100] w-10 h-10 rounded-full bg-white shadow flex items-center justify-center border border-slate-200 hover:bg-slate-50"
        :aria-pressed="panelsVisible ? 'true' : 'false'"
        aria-label="Toggle Panel Filter & Legenda"
        @click="togglePanels"
      >
        <svg v-if="panelsVisible" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 3l18 18"/>
          <path d="M10.584 10.587A3 3 0 0012 15a3 3 0 002.419-4.746"/>
          <path d="M9.88 5.09A10.48 10.48 0 0112 5c7 0 11 7 11 7a18.2 18.2 0 01-4.266 4.592"/>
          <path d="M6.61 6.62A18.504 18.504 0 001 12s4 7 11 7c1.21 0 2.35-.2 3.41-.56"/>
        </svg>
      </button>

      <div v-if="panelsVisible" class="absolute top-3 left-3 z-[1000]">
        <FilterPanel />
      </div>

      <div v-if="panelsVisible" class="absolute bottom-3 left-3 z-[1000]">
        <MapLegend />
      </div>

      <!-- Village quick navigation -->
      <div class="absolute top-3 left-1/2 -translate-x-1/2 z-[1000]">
        <VillageNav />
      </div>

      <div class="absolute right-3 z-[1000] flex flex-col gap-2 items-end top-20 md:top-3">
        <OfflineControls />
        <SyncStatus />
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { ref, onMounted } from 'vue'
import HealthMap from './components/Map/HealthMap.vue'
import FilterPanel from './components/Filters/FilterPanel.vue'
import MapLegend from './components/Map/MapLegend.vue'
import VillageNav from './components/Map/VillageNav.vue'
import OfflineControls from './components/Offline/OfflineControls.vue'
import SyncStatus from './components/Offline/SyncStatus.vue'

const panelsVisible = ref(true)

function togglePanels() {
  panelsVisible.value = !panelsVisible.value
  try { localStorage.setItem('map.panelsVisible', panelsVisible.value ? '1' : '0') } catch (_) {}
}

onMounted(() => {
  try {
    const saved = localStorage.getItem('map.panelsVisible')
    if (saved !== null) {
      panelsVisible.value = saved === '1'
    } else if (window.innerWidth < 768) {
      panelsVisible.value = false
    }
  } catch (_) {
    // default true on desktop, false on small screens
    if (window.innerWidth < 768) panelsVisible.value = false
  }
})
</script>

<style scoped>
/* Page-level styles if needed */
</style>
