<template>
  <div class="bg-white/95 rounded shadow p-3 w-72">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold">Filter</h3>
      <select v-model="preset" class="text-sm border rounded px-2 py-1">
        <option :value="null">Preset</option>
        <option v-if="canViewSensitiveHealth" value="critical">Kondisi Kritis</option>
        <option value="sanitation">Masalah Sanitasi</option>
        <option value="maternalChild">Kesehatan Ibu & Anak</option>
      </select>
    </div>

    <div class="space-y-3 text-sm">
      <div v-if="canViewSensitiveHealth">
        <div class="font-medium mb-1">Penyakit</div>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.disease.tuberculosis" /> TBC</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.disease.tbMedication" /> Minum obat TB rutin</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.disease.hypertension" /> Hipertensi</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.disease.mentalIllness" /> Gangguan jiwa</label>
      </div>

      <div v-else class="text-xs text-slate-500 border border-dashed border-slate-200 rounded p-2">
        Filter penyakit hanya tersedia untuk tenaga kesehatan.
      </div>

      <div>
        <div class="font-medium mb-1">Sanitasi</div>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.sanitation.cleanWater" /> Air bersih</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.sanitation.protectedWater" /> Air terlindungi</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.sanitation.hasToilet" /> Ada jamban</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.sanitation.sanitaryToilet" /> Jamban sehat</label>
      </div>

      <div>
        <div class="font-medium mb-1">Kesehatan Ibu/Anak</div>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.maternalChild.pregnant" /> Ibu hamil</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.maternalChild.facilityBirth" /> Persalinan di faskes</label>
        <label class="flex items-center gap-2"><input type="checkbox" v-model="f.maternalChild.immunization" /> Imunisasi</label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch, ref } from 'vue'
import { useMapStore } from '../../stores/mapStore'
import { useFilters } from '../../composables/useFilters'

const store = useMapStore()
const canViewSensitiveHealth = typeof window !== 'undefined' && Boolean(window.__canViewSensitiveHealth)
const f = store.filters
const preset = ref(null)
const { applyPreset } = useFilters()

watch(preset, (p) => {
  if (p) applyPreset(p)
})
</script>
