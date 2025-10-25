<template>
  <div v-if="nav.isActive" class="bg-white/95 rounded shadow p-3 text-xs w-72 space-y-3">
    <div class="flex items-start justify-between gap-2">
      <div>
        <div class="text-sm font-semibold">Navigasi</div>
        <div v-if="destinationLabel" class="text-[11px] text-slate-600">
          {{ destinationLabel }}
        </div>
        <div class="text-[11px] text-slate-400">
          {{ modeLabel }}
        </div>
      </div>
      <button class="text-red-600 hover:text-red-700 text-[11px]" @click="stop">
        Hentikan
      </button>
    </div>

    <div class="flex items-center gap-3 text-[11px] text-slate-700">
      <div>
        <div class="uppercase text-[10px] text-slate-400">Jarak</div>
        <div class="font-semibold text-sm">{{ distanceText }}</div>
      </div>
      <div>
        <div class="uppercase text-[10px] text-slate-400">Estimasi</div>
        <div class="font-semibold text-sm">{{ durationText }}</div>
      </div>
    </div>

    <div v-if="nav.status" class="text-[11px] text-slate-500">
      {{ nav.status }}
    </div>

    <div v-if="headingDisplay" class="text-[11px] text-slate-500">
      Arah perangkat: {{ headingDisplay }}
    </div>

    <div v-if="nav.error" class="text-[11px] text-red-600">
      {{ nav.error }}
    </div>

    <ol v-if="nav.hasInstructions" class="space-y-1 text-[11px] text-slate-700">
      <li v-for="(step, idx) in nav.instructions" :key="idx" class="flex gap-2">
        <span class="font-semibold">{{ idx + 1 }}.</span>
        <div>
          <div>{{ step.text }}</div>
          <div v-if="step.distance" class="text-slate-400">{{ formatDistance(step.distance) }}</div>
        </div>
      </li>
    </ol>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useNavigationStore } from '../../stores/navigationStore'

const nav = useNavigationStore()

const destinationLabel = computed(() => {
  const dest = nav.destination
  if (!dest) return ''
  if (dest.label) return dest.label
  if (dest.lat && dest.lng) return `${dest.lat.toFixed(5)}, ${dest.lng.toFixed(5)}`
  return ''
})

const modeLabel = computed(() => {
  if (!nav.mode) return 'Menunggu rute'
  if (nav.mode === 'online') return 'Online (OSRM)'
  if (nav.mode === 'cached') return 'Offline (rute tersimpan)'
  if (nav.mode === 'direct') return 'Offline (garis lurus)'
  return nav.mode
})

const distanceText = computed(() => {
  if (!nav.totalDistance) return '--'
  return formatDistance(nav.totalDistance)
})

const durationText = computed(() => {
  if (!nav.totalDuration) return '--'
  return formatDuration(nav.totalDuration)
})

const headingDisplay = computed(() => {
  const heading = nav.heading
  if (!Number.isFinite(heading)) return ''
  const deg = Math.round(((heading % 360) + 360) % 360)
  return `${deg} deg ${headingToCardinal(deg)}`
})

function stop() {
  nav.requestStop()
}

function formatDistance(meters) {
  const m = Number(meters || 0)
  if (m >= 1000) {
    return `${(m / 1000).toFixed(2)} km`
  }
  return `${Math.round(m)} m`
}

function formatDuration(seconds) {
  const s = Number(seconds || 0)
  if (s <= 0) return '--'
  const minutes = Math.round(s / 60)
  if (minutes < 1) return '<1 mnt'
  if (minutes < 60) return `${minutes} mnt`
  const hrs = Math.floor(minutes / 60)
  const mins = minutes % 60
  if (mins === 0) return `${hrs} jam`
  return `${hrs} jam ${mins} mnt`
}

function headingToCardinal(deg) {
  const dirs = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut']
  const idx = Math.round(deg / 45) % dirs.length
  return dirs[idx]
}
</script>
