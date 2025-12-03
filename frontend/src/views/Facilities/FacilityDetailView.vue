<template>
  <div class="facility-detail-view">
    <div class="container">
      <button @click="goBack" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">← Kembali ke Senarai Kemudahan</button>

      <div v-if="loading" class="loading">Memuatkan butiran kemudahan...</div>

      <FacilityDetails
        v-else-if="facility"
        :facility="facility"
        @book="handleBook"
      />

      <div v-else class="error">
        <p>Kemudahan tidak ditemui.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFacilityStore } from '@/stores/facility'
import useDistrictRoutes from '@/utils/districtRoutes'
import FacilityDetails from '@/components/Facility/FacilityDetails.vue'
import { useDistrictStore } from '@/stores/district'

const route = useRoute()
const router = useRouter()
const facilityStore = useFacilityStore()

const facility = ref(null)
const loading = ref(false)

const districtStore = useDistrictStore()
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
  'Kuala Terengganu': { main: '#EEBF04', dark: '#a88903', gradient: 'linear-gradient(135deg, #EEBF04 0%, #a88903 100%)' },
  'Kemaman': { main: '#1E3A8A', dark: '#152a61', gradient: 'linear-gradient(135deg, #1E3A8A 0%, #152a61 100%)' },
  'Dungun': { main: '#06B6D4', dark: '#058099', gradient: 'linear-gradient(135deg, #06B6D4 0%, #058099 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])

onMounted(async () => {
  await loadFacility()
})

const loadFacility = async () => {
  loading.value = true
  try {
    facility.value = await facilityStore.fetchFacility(route.params.id)
  } catch (error) {
    console.error('Failed to load facility:', error)
  } finally {
    loading.value = false
  }
}

const { bookingPath } = useDistrictRoutes()

const handleBook = () => {
  const bp = bookingPath(facility.value.id)
  router.push(bp)
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
