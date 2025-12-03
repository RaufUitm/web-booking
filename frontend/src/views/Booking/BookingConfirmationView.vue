<template>
  <div class="booking-confirmation-view">
    <div class="container">
      <div v-if="loading" class="loading">Memuatkan butiran tempahan...</div>

      <BookingSummary
        v-else-if="booking"
        :booking="booking"
        :show-actions="true"
        @cancel="handleCancel"
        @edit="handleEdit"
        @payment="handlePayment"
      />

      <EditBookingForm
        v-if="showEdit && booking"
        :booking="booking"
        @saved="handleEditSaved"
        @cancel="handleEditCancel"
      />

      <div v-else class="error">
        <p>Tempahan tidak ditemui.</p>
        <router-link :to="prefixPath('/my-bookings')" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">
          Kembali ke Tempahan Saya
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
import EditBookingForm from '@/components/Booking/EditBookingForm.vue'
import api from '@/api/axios'
import { useDistrictStore } from '@/stores/district'

const route = useRoute()
const router = useRouter()
const { prefixPath } = useDistrictRoutes()
const bookingStore = useBookingStore()

const booking = ref(null)
const loading = ref(false)
const showEdit = ref(false)
const showPayment = ref(false) // kept for compatibility but no PaymentForm UI

const totalAmount = computed(() => {
  if (!booking.value?.facility) return 0
  
  const parseNum = (val) => {
    const n = Number(val ?? 0)
    return Number.isNaN(n) ? 0 : n
  }
  
  const pricePerHour = parseNum(booking.value.facility.price_per_hour)
  const pricePerDay = parseNum(booking.value.facility.price_per_day)
  
  if (booking.value.booking_type === 'per_day') {
    return pricePerDay || (pricePerHour * 24)
  }
  
  // Calculate duration for per_hour
  if (booking.value.start_time && booking.value.end_time) {
    const start = new Date(`2000-01-01 ${booking.value.start_time}`)
    const end = new Date(`2000-01-01 ${booking.value.end_time}`)
    const hours = (end - start) / (1000 * 60 * 60)
    return pricePerHour * hours
  }
  
  return 0
})

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
  if (!confirm('Adakah anda pasti mahu membatalkan tempahan ini?')) return

  try {
    await bookingStore.cancelBooking(booking.value.id)
    await loadBooking()
  } catch (error) {
    console.error('Failed to cancel booking:', error)
    alert('Gagal membatalkan tempahan. Sila cuba lagi.')
  }
}

const handleEdit = () => {
  // Open edit modal
  showEdit.value = true
}

const handleEditSaved = async (updated) => {
  // Update local booking and close modal
  booking.value = updated
  showEdit.value = false
}

const handleEditCancel = () => {
  showEdit.value = false
}

const handlePayment = async () => {
  if (!booking.value) return
  try {
    // Create payment directly and redirect to gateway
    const amount = Math.round(Number(totalAmount.value) * 100) // cents
    const { data } = await api.post('/payment/create', {
      booking_id: booking.value.id,
      amount
    })
    const paymentUrl = data?.payment_url
    if (paymentUrl) {
      window.location.href = paymentUrl
    } else {
      alert('Gagal menjana pautan pembayaran. Sila cuba lagi.')
    }
  } catch (error) {
    console.error('Failed to create payment:', error)
    alert('Gagal menjana pembayaran. Sila cuba lagi atau hubungi sokongan.')
  }
}

// Direct redirect flow; success handled on gateway return page
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
  background-color: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.3s;
}

.btn-back:hover {
  background-color: var(--theme-primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(var(--theme-primary-rgb), 0.3);
}
</style>
