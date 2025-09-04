<template>
  <div class="bg-white/95 rounded shadow p-2 text-xs flex items-center gap-2">
    <span class="inline-block w-2 h-2 rounded-full" :class="offline.isOnline ? 'bg-emerald-500' : 'bg-red-500'"></span>
    <span>{{ offline.isOnline ? 'Sinkron aktif' : 'Mode offline' }}</span>
    <span class="ml-2 text-slate-500">Antrian: {{ offline.syncQueueCount }}</span>
  </div>
</template>

<script setup>
import { useOfflineStore } from '../../stores/offlineStore'
const offline = useOfflineStore()
</script>

