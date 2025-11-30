<template>
  <div class="my-bookings-view">
    <div class="container">
      <h1>Tempahan Saya</h1>

      <div class="filters">
        <select v-model="statusFilter" @change="filterBookings">
          <option value="">Semua Tempahan</option>
          <option value="pending">Menunggu</option>
          <option value="confirmed">Disahkan</option>
          <option value="completed">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </div>

      <div v-if="loading" class="loading">Memuatkan tempahan...</div>

      <div v-else-if="filteredBookings.length === 0" class="no-bookings">
        <p>Tiada tempahan dijumpai.</p>
        <router-link :to="prefixPath('/facilities')" class="btn-browse" :style="{ background: currentDistrictColor.main, color: '#fff' }">
          Lihat Kemudahan
        </router-link>
      </div>

      <div v-else class="bookings-list">
        <div
          v-for="booking in filteredBookings"
          :key="booking.id"
          class="booking-card"
        >
          <div class="booking-header">
            <h3>{{ booking.facility?.name }}</h3>
            <span :class="['status-badge', booking.status]">
              {{ formatStatus(booking.status) }}
            </span>
          </div>

          <div class="booking-details">
            <div class="detail">
              <span class="label">📅 Tarikh:</span>
              <span>{{ formatDate(booking.booking_date) }}</span>
            </div>
            <div class="detail">
              <span class="label">⏰ Masa:</span>
              <span>
                <template v-if="booking.booking_type === 'per_day'">Sepanjang Hari</template>
                <template v-else>{{ booking.start_time || '' }} - {{ booking.end_time || '' }}</template>
              </span>
            </div>
            <div class="detail">
              <span class="label">👥 Bilangan:</span>
              <span>{{ booking.number_of_people }} orang</span>
            </div>
            <div class="detail">
              <span class="label">📍 Lokasi:</span>
              <span>{{ booking.facility?.location }}</span>
            </div>
          </div>

          <div class="booking-actions">
            <button @click="viewBooking(booking.id)" class="btn-view" :style="{ background: currentDistrictColor.main, color: '#fff' }">
              Lihat Butiran
            </button>
            <button
              v-if="canCancel(booking)"
              @click="cancelBooking(booking.id)"
              class="btn-cancel"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useDistrictRoutes from '@/utils/districtRoutes'
import { useBookingStore } from '@/stores/booking'
import { useDistrictStore } from '@/stores/district'

const router = useRouter()
const { prefixPath } = useDistrictRoutes()
const bookingStore = useBookingStore()

const districtStore = useDistrictStore()
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])

const bookings = ref([])
const loading = ref(false)
const statusFilter = ref('')

onMounted(async () => {
  await loadBookings()
})

const loadBookings = async () => {
  loading.value = true
  try {
    await bookingStore.fetchBookings()
    // The backend already filters by user_id for non-admin users
    // So we can directly use the bookings from the store
    bookings.value = bookingStore.bookings
    console.log('Loaded bookings:', bookings.value.length)
  } catch (error) {
    console.error('Failed to load bookings:', error)
  } finally {
    loading.value = false
  }
}

const filteredBookings = computed(() => {
  if (!statusFilter.value) return bookings.value
  return bookings.value.filter(b => b.status === statusFilter.value)
})

const filterBookings = () => {
  // Filtering is handled by computed property
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ms-MY', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  const map = {
    pending: 'Menunggu',
    confirmed: 'Disahkan',
    completed: 'Selesai',
    cancelled: 'Dibatalkan'
  }
  return map[status] || status
}

const canCancel = (booking) => {
  return ['pending', 'confirmed'].includes(booking.status)
}

const viewBooking = (id) => {
  router.push(prefixPath(`/booking-confirmation/${id}`))
}

const cancelBooking = async (id) => {
  if (!confirm('Adakah anda pasti mahu membatalkan tempahan ini?')) return

  try {
    await bookingStore.cancelBooking(id)
    await loadBookings()
  } catch (error) {
    console.error('Failed to cancel booking:', error)
    alert('Gagal membatalkan tempahan. Sila cuba lagi.')
  }
}
</script>

<style scoped>
.my-bookings-view {
  padding: 40px 0;
  min-height: calc(100vh - 200px);
  background-color: #f5f5f5;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

h1 {
  margin: 0 0 30px 0;
  color: #333;
  font-size: 32px;
}

.filters {
  margin-bottom: 30px;
}

.filters select {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background-color: white;
  cursor: pointer;
}

.loading,
.no-bookings {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.btn-browse {
  display: inline-block;
  margin-top: 20px;
  padding: 12px 30px;
  background-color: #FF8C00;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.3s;
}

.btn-browse:hover {
  background-color: #E67E00;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(255, 140, 0, 0.3);
}

.bookings-list {
  display: grid;
  gap: 20px;
}

.booking-card {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}

.booking-header h3 {
  margin: 0;
  color: #333;
  font-size: 20px;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.pending {
  background-color: #fff3e0;
  color: #f57c00;
}

.status-badge.confirmed {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status-badge.completed {
  background-color: #e3f2fd;
  color: #1976d2;
}

.status-badge.cancelled {
  background-color: #ffebee;
  color: #c62828;
}

.booking-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.detail {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.label {
  color: #666;
  font-size: 14px;
}

.booking-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-view,
.btn-cancel {
  padding: 8px 20px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-view {
  background-color: #2196F3;
  color: white;
}

.btn-view:hover {
  background-color: #1976D2;
}

.btn-cancel {
  background-color: #f44336;
  color: white;
}

.btn-cancel:hover {
  background-color: #d32f2f;
}

@media (max-width: 768px) {
  .booking-details {
    grid-template-columns: 1fr;
  }

  .booking-actions {
    flex-direction: column;
  }

  .btn-view,
  .btn-cancel {
    width: 100%;
  }
}
</style>
