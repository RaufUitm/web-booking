<template>
  <div class="facility-detail-view">
    <div class="container">
      <button @click="goBack" class="btn-back">← Back to Facilities</button>

      <div v-if="loading" class="loading">Loading facility details...</div>

      <FacilityDetails
        v-else-if="facility"
        :facility="facility"
        @book="handleBook"
      />

      <div v-else class="error">
        <p>Facility not found.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFacilityStore } from '@/stores/facility'
import FacilityDetails from '@/components/Facility/FacilityDetails.vue'

const route = useRoute()
const router = useRouter()
const facilityStore = useFacilityStore()

const facility = ref(null)
const loading = ref(false)

onMounted(async () => {
  await loadFacility()
})

const loadFacility = async () => {
  loading.value = true
  try {
    facility.value = await facilityStore.fetchFacilityById(route.params.id)
  } catch (error) {
    console.error('Failed to load facility:', error)
  } finally {
    loading.value = false
  }
}

const handleBook = () => {
  router.push(`/booking/${facility.value.id}`)
}

const goBack = () => {
  router.back()
}
</script>

<style scoped>
.facility-detail-view {
  padding: 40px 0;
  min-height: calc(100vh - 200px);
  background-color: #f5f5f5;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.btn-back {
  padding: 10px 20px;
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  margin-bottom: 20px;
  transition: background-color 0.3s;
}

.btn-back:hover {
  background-color: #e0e0e0;
}

.loading,
.error {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}
</style>
