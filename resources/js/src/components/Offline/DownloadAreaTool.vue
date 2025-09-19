<template>
  <div class="bg-white/95 rounded shadow p-3 text-sm">
    <div class="font-semibold mb-2">Download Area</div>
    <p class="text-xs text-slate-600">Geser peta ke area yang ingin diunduh, lalu klik tombol Download.</p>
    <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-1" @click="$emit('download')">Download</button>
  </div>
</template>

<script setup>
// Minimal helper panel; functionality handled by OfflineControls
</script>

