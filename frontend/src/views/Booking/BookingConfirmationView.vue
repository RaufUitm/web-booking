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
        <router-link to="/my-bookings" class="btn-back">
          Back to My Bookings
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBookingStore } from '@/stores/booking'
import BookingSummary from '@/components/Booking/BookingSummary.vue'

const route = useRoute()
const router = useRouter()
const bookingStore = useBookingStore()

const booking = ref(null)
const loading = ref(false)

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
  router.push(`/booking/${booking.value.facility_id}`)
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
