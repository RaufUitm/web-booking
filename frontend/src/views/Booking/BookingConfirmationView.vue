<template>
  <div class="booking-confirmation-view">
    <div class="container">
      <div v-if="loading" class="loading">Loading booking details...</div>

      <BookingSummary
        v-else-if="booking"
        :booking="booking"
        :show-actions="true"
        @cancel="handleCancel"
        @edit="handleEdit"
      />

      <div v-else class="error">
        <p>Booking not found.</p>
        <router-link :to="prefixPath('/my-bookings')" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">
          Back to My Bookings
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useDistrictRoutes from '@/utils/districtRoutes'
import { useBookingStore } from '@/stores/booking'
import BookingSummary from '@/components/Booking/BookingSummary.vue'
import { useDistrictStore } from '@/stores/district'

const route = useRoute()
const router = useRouter()
const { prefixPath } = useDistrictRoutes()
const bookingStore = useBookingStore()

const booking = ref(null)
const loading = ref(false)

const districtStore = useDistrictStore()
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])

onMounted(async () => {
  await loadBooking()
})

const loadBooking = async () => {
  loading.value = true
  try {
    booking.value = await bookingStore.fetchBookingById(route.params.id)
  } catch (error) {
    console.error('Failed to load booking:', error)
  } finally {
    loading.value = false
  }
}

const handleCancel = async () => {
  if (!confirm('Are you sure you want to cancel this booking?')) return

  try {
    await bookingStore.cancelBooking(booking.value.id)
    await loadBooking()
  } catch (error) {
    console.error('Failed to cancel booking:', error)
    alert('Failed to cancel booking. Please try again.')
  }
}

const handleEdit = () => {
  router.push(prefixPath(`/booking/${booking.value.facility_id}`))
}
</script>

<style scoped>
.booking-confirmation-view {
  padding: 40px 0;
  min-height: calc(100vh - 200px);
  background-color: #f5f5f5;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.loading,
.error {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.btn-back {
  display: inline-block;
  margin-top: 20px;
  padding: 12px 30px;
  background-color: #FF8C00;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.3s;
}

.btn-back:hover {
  background-color: #E67E00;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(255, 140, 0, 0.3);
}
</style>
