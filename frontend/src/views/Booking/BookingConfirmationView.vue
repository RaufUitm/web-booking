<template>
  <div class="booking-confirmation-view">
    <div class="container">
      <div class="top-actions">
        <router-link :to="prefixPath('/my-bookings')" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">
          Kembali
        </router-link>
      </div>
      <div v-if="loading" class="loading">Memuatkan butiran tempahan...</div>

      <BookingSummary
        v-else-if="hasBooking"
        :booking="booking"
        :show-actions="true"
        @cancel="handleCancel"
        @edit="handleEdit"
        @payment="handlePayment"
      />

      <EditBookingForm
        v-if="!loading && showEdit && hasBooking"
        :booking="booking"
        @saved="handleEditSaved"
        @cancel="handleEditCancel"
      />

      <div v-else-if="!hasBooking" class="error">
        <p>{{ errorMessage || 'Tempahan tidak ditemui.' }}</p>
        <div style="margin-top:16px; display:flex; gap:12px; justify-content:center;">
          <button @click="reload" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">Cuba Lagi</button>
          <router-link :to="prefixPath('/my-bookings')" class="btn-back" :style="{ background: currentDistrictColor.main, color: '#fff' }">
            Kembali ke Tempahan Saya
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
const errorMessage = ref('')
const hasBooking = computed(() => !!(booking.value && booking.value.id))

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
    errorMessage.value = ''
    const id = route.params.id
    if (!id) {
      errorMessage.value = 'ID tempahan tiada dalam pautan.'
      booking.value = null
      return
    }
    booking.value = await bookingStore.fetchBookingById(id)
    if (!booking.value || !booking.value.id) {
      errorMessage.value = 'Tempahan tidak ditemui atau telah dibatalkan.'
    }
  } catch (error) {
    console.error('Failed to load booking:', error)
    errorMessage.value = error?.response?.data?.message || 'Gagal memuatkan tempahan. Cuba lagi.'
    booking.value = null
  } finally {
    loading.value = false
  }
}

const reload = async () => {
  await loadBooking()
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
    if (Number(totalAmount.value) < 1) {
      alert('Jumlah pembayaran kurang daripada RM1.00. Sila semak tempahan.');
      return
    }
    const { data } = await api.post('/payment/create', { booking_id: booking.value.id })
    const paymentUrl = data?.payment_url
    if (paymentUrl) {
      window.location.href = paymentUrl
    } else {
      alert('Gagal menjana pautan pembayaran. Sila cuba lagi.')
    }
  } catch (error) {
    console.error('Failed to create payment:', error)
    const serverMsg = error?.response?.data?.details || error?.response?.data?.error || error?.message
    alert(`Gagal menjana pembayaran. ${serverMsg ? 'Butiran: ' + serverMsg : 'Sila cuba lagi atau hubungi sokongan.'}`)
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

.top-actions { display:flex; justify-content:flex-start; margin-bottom: 16px; }

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
