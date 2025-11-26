<template>
  <div class="booking-view">
    <div class="container">
      <h1>Tempahan Kemudahan</h1>

      <div v-if="loading" class="loading">Memuatkan...</div>

      <CalendarAvailability
        v-else-if="facility && showCalendar && !bookingComplete"
        :facility-id="facility.id"
        @close="goBack"
        @date-selected="handleDateSelected"
      />

      <BookingForm
        v-else-if="facility && !showCalendar && !bookingComplete"
        :facility="facility"
        :selected-date="selectedDate"
        @cancel="goBackToCalendar"
        @success="handleBookingSuccess"
      />

      <div v-else-if="bookingComplete" class="success-message">
        <div class="success-icon" :style="{ background: currentDistrictColor.main, color: '#fff' }">✓</div>
        <h2>Tempahan Berjaya!</h2>
        <p>Tempahan anda telah disahkan. Sila tunggu pengesahan daripada admin.</p>
        <div class="actions">
          <router-link :to="prefixPath('/my-bookings')" class="btn-primary" :style="{ background: currentDistrictColor.main, color: '#fff' }">
            Lihat Tempahan Saya
          </router-link>
          <router-link :to="prefixPath('/facilities')" class="btn-secondary" :style="{ borderColor: currentDistrictColor.main, color: currentDistrictColor.main }">
            Lihat Kemudahan Lain
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useDistrictRoutes from '@/utils/districtRoutes'
import { useFacilityStore } from '@/stores/facility'
import CalendarAvailability from '@/components/Booking/CalendarAvailability.vue'
import BookingForm from '@/components/Booking/BookingForm.vue'
import { useDistrictStore } from '@/stores/district'

const route = useRoute()
const router = useRouter()
const facilityStore = useFacilityStore()

const facility = ref(null)
const loading = ref(false)
const bookingComplete = ref(false)
const showCalendar = ref(true)
const selectedDate = ref(null)
const { prefixPath } = useDistrictRoutes()

const districtStore = useDistrictStore()
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])

onMounted(async () => {
  await loadFacility()
})

const loadFacility = async () => {
  loading.value = true
  try {
    const facilityId = route.params.facilityId || route.params.id
    facility.value = await facilityStore.fetchFacility(facilityId)
  } catch (error) {
    console.error('Failed to load facility:', error)
    router.push(prefixPath('/facilities'))
  } finally {
    loading.value = false
  }
}

const handleDateSelected = (date) => {
  selectedDate.value = date
  showCalendar.value = false
}

const goBackToCalendar = () => {
  showCalendar.value = true
  selectedDate.value = null
}

const handleBookingSuccess = () => {
  bookingComplete.value = true
}

const goBack = () => {
  router.back()
}
</script>

<style scoped>
.booking-view {
  padding: 40px 0;
  min-height: calc(100vh - 200px);
  background-color: #f5f5f5;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

h1 {
  text-align: center;
  margin: 0 0 30px 0;
  color: #333;
  font-size: 32px;
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.success-message {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.success-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background-color: #FF8C00;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: white;
}

.success-message h2 {
  margin: 0 0 10px 0;
  color: #333;
}

.success-message p {
  margin: 0 0 30px 0;
  color: #666;
  font-size: 16px;
}

.actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  padding: 12px 30px;
  text-decoration: none;
  border-radius: 4px;
  font-size: 16px;
  transition: all 0.3s;
}

.btn-primary {
  background-color: #FF8C00;
  color: white;
}

.btn-primary:hover {
  background-color: #E67E00;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(255, 140, 0, 0.3);
}

.btn-secondary {
  background-color: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
}

.btn-secondary:hover {
  background-color: #e0e0e0;
}
</style>
