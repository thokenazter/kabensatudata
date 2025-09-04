import { computed, ref, watch } from 'vue'
import { useMapStore } from '../stores/mapStore'

export function useFilters() {
  const store = useMapStore()

  const activeFilters = ref(store.filters)

  const hasAnyFilter = computed(() => {
    const f = activeFilters.value
    return (
      f.disease.tuberculosis ||
      f.disease.tbMedication ||
      f.disease.hypertension ||
      f.disease.mentalIllness ||
      f.sanitation.cleanWater ||
      f.sanitation.protectedWater ||
      f.sanitation.hasToilet ||
      f.sanitation.sanitaryToilet ||
      f.maternalChild.pregnant ||
      f.maternalChild.facilityBirth ||
      f.maternalChild.immunization
    )
  })

  function applyPreset(preset) {
    // Clear first
    const f = store.filters
    Object.assign(f.disease, { tuberculosis: false, tbMedication: false, hypertension: false, mentalIllness: false })
    Object.assign(f.sanitation, { cleanWater: false, protectedWater: false, hasToilet: false, sanitaryToilet: false })
    Object.assign(f.maternalChild, { pregnant: false, facilityBirth: false, immunization: false })

    if (preset === 'critical') {
      f.disease.tuberculosis = true
      f.disease.hypertension = true
      f.disease.mentalIllness = true
    } else if (preset === 'sanitation') {
      f.sanitation.cleanWater = true
      f.sanitation.protectedWater = true
      f.sanitation.hasToilet = true
      f.sanitation.sanitaryToilet = true
    } else if (preset === 'maternalChild') {
      f.maternalChild.pregnant = true
      f.maternalChild.facilityBirth = true
      f.maternalChild.immunization = true
    }
  }

  function buildingPassesFilters(building) {
    const f = store.filters
    if (!hasAnyFilter.value) return true
    const flags = building.flags || {}

    // Disease
    if (f.disease.tuberculosis && !flags.has_tb) return false
    if (f.disease.tbMedication && !flags.tb_medication) return false
    if (f.disease.hypertension && !flags.has_hypertension) return false
    if (f.disease.mentalIllness && !flags.mental_illness) return false

    // Sanitation
    if (f.sanitation.cleanWater && !flags.has_clean_water) return false
    if (f.sanitation.protectedWater && !flags.is_water_protected) return false
    if (f.sanitation.hasToilet && !flags.has_toilet) return false
    if (f.sanitation.sanitaryToilet && !flags.is_toilet_sanitary) return false

    // Maternal/Child
    if (f.maternalChild.pregnant && !flags.pregnant) return false
    if (f.maternalChild.facilityBirth && !flags.facility_birth) return false
    if (f.maternalChild.immunization && !flags.immunization) return false

    return true
  }

  watch(
    () => store.filters,
    () => {
      // Notify map to re-render markers according to filters
      try { window.dispatchEvent(new CustomEvent('filters:changed')) } catch (_) {}
    },
    { deep: true }
  )

  return { activeFilters, hasAnyFilter, applyPreset, buildingPassesFilters }
}
