<template>
  <div class="manage-bookings">
    <h1>Pengurusan Tempahan</h1>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari tempahan..."
        />
        <span class="search-icon">🔍</span>
      </div>

      <select v-model="statusFilter" @change="loadBookings">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="confirmed">Disahkan</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatal</option>
      </select>

      <input
        v-model="dateFilter"
        type="date"
        @change="loadBookings"
        class="date-filter"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Memuatkan...</div>

    <!-- Bookings Table -->
    <div v-else class="table-container">
      <table class="bookings-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Pengguna</th>
            <th>Kemudahan</th>
            <th>Tarikh Tempahan</th>
            <th>Masa</th>
            <th>Bilangan Orang</th>
            <th>Status</th>
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="booking in filteredBookings" :key="booking.id">
            <td>{{ booking.id }}</td>
            <td>
              <div class="user-info">
                <strong>{{ booking.user?.name || 'N/A' }}</strong>
                <small>{{ booking.user?.email || '' }}</small>
              </div>
            </td>
            <td>{{ booking.facility?.name || 'N/A' }}</td>
            <td>{{ formatDate(booking.booking_date) }}</td>
            <td>
              <template v-if="booking.booking_type === 'per_day'">Sepanjang Hari</template>
              <template v-else>{{ booking.start_time || '-' }} - {{ booking.end_time || '-' }}</template>
            </td>
            <td>{{ booking.number_of_people }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(booking.status)]">
                {{ getStatusText(booking.status) }}
              </span>
            </td>
            <td class="actions">
              <button
                v-if="booking.status === 'pending'"
                @click="updateStatus(booking.id, 'confirmed')"
                class="btn-icon btn-confirm"
                title="Sahkan"
              >
                ✓
              </button>
              <button
                v-if="booking.status === 'confirmed'"
                @click="updateStatus(booking.id, 'completed')"
                class="btn-icon btn-complete"
                title="Selesai"
              >
                ✓✓
              </button>
              <button
                v-if="['pending', 'confirmed'].includes(booking.status)"
                @click="updateStatus(booking.id, 'cancelled')"
                class="btn-icon btn-cancel"
                title="Batal"
              >
                ✕
              </button>
              <button
                @click="viewDetails(booking)"
                class="btn-icon btn-view"
                title="Lihat"
              >
                👁️
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredBookings.length === 0" class="no-data">
        Tiada tempahan dijumpai
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal" class="modal-overlay" @click.self="showDetailsModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2>Butiran Tempahan #{{ selectedBooking?.id }}</h2>
          <button @click="showDetailsModal = false" class="btn-close">✕</button>
        </div>

        <div class="modal-body details">
          <div class="detail-section">
            <h3>Maklumat Pengguna</h3>
            <div class="detail-row">
              <span class="label">Nama:</span>
              <span class="value">{{ selectedBooking?.user?.name }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Email:</span>
              <span class="value">{{ selectedBooking?.user?.email }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Telefon:</span>
              <span class="value">{{ selectedBooking?.user?.phone || '-' }}</span>
            </div>
          </div>

          <div class="detail-section">
            <h3>Maklumat Tempahan</h3>
            <div class="detail-row">
              <span class="label">Kemudahan:</span>
              <span class="value">{{ selectedBooking?.facility?.name }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Tarikh:</span>
              <span class="value">{{ formatDate(selectedBooking?.booking_date) }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Masa:</span>
              <span class="value">
                <template v-if="selectedBooking?.booking_type === 'per_day'">Sepanjang Hari</template>
                <template v-else>{{ selectedBooking?.start_time || '-' }} - {{ selectedBooking?.end_time || '-' }}</template>
              </span>
            </div>
            <div class="detail-row">
              <span class="label">Bilangan Orang:</span>
              <span class="value">{{ selectedBooking?.number_of_people }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Status:</span>
              <span :class="['status-badge', getStatusClass(selectedBooking?.status)]">
                {{ getStatusText(selectedBooking?.status) }}
              </span>
            </div>
          </div>

          <div v-if="selectedBooking?.notes" class="detail-section">
            <h3>Catatan</h3>
            <p>{{ selectedBooking.notes }}</p>
          </div>

          <div class="modal-actions">
            <button
              v-if="selectedBooking?.payment?.payment_status === 'completed'"
              @click="downloadInvoice(selectedBooking.id)"
              class="btn-secondary"
            >
              Muat Turun Invois
            </button>
            <button @click="showDetailsModal = false" class="btn-secondary">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const bookings = ref([])
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')

const showDetailsModal = ref(false)
const selectedBooking = ref(null)

const filteredBookings = computed(() => {
  let filtered = bookings.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(booking =>
      booking.user?.name.toLowerCase().includes(query) ||
      booking.user?.email.toLowerCase().includes(query) ||
      booking.facility?.name.toLowerCase().includes(query) ||
      booking.id.toString().includes(query)
    )
  }

  return filtered
})

onMounted(() => {
  loadBookings()
})

const loadBookings = async () => {
  loading.value = true
  try {
    const params = {}
    if (statusFilter.value) {
      params.status = statusFilter.value
    }
    if (dateFilter.value) {
      params.date = dateFilter.value
    }
    // Restrict to district for district admins
    const authStore = useAuthStore()
    if (authStore.isDistrictAdmin) {
      params.district = authStore.userDistrict
    }

    const response = await api.get('/admin/bookings', { params })
    // Handle both paginated and non-paginated responses
    const data = response.data.data
    bookings.value = data?.data || data || []
  } catch (err) {
    console.error('Failed to load bookings:', err)
    bookings.value = []
  } finally {
    loading.value = false
  }
}

const updateStatus = async (bookingId, newStatus) => {
  const confirmMessages = {
    confirmed: 'Adakah anda pasti mahu mengesahkan tempahan ini?',
    completed: 'Adakah anda pasti tempahan ini sudah selesai?',
    cancelled: 'Adakah anda pasti mahu membatalkan tempahan ini?'
  }

  if (!confirm(confirmMessages[newStatus])) {
    return
  }

  try {
    await api.put(`/admin/bookings/${bookingId}/status`, { status: newStatus })
    await loadBookings()
  } catch (err) {
    console.error('Failed to update status:', err)
    alert('Gagal mengemaskini status')
  }
}

const viewDetails = (booking) => {
  selectedBooking.value = booking
  showDetailsModal.value = true
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('ms-MY', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'pending',
    confirmed: 'confirmed',
    completed: 'completed',
    cancelled: 'cancelled'
  }
  return classes[status] || ''
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    confirmed: 'Disahkan',
    completed: 'Selesai',
    cancelled: 'Dibatal'
  }
  return texts[status] || status
}

const downloadInvoice = async (bookingId) => {
  try {
    const response = await api.get(`/bookings/${bookingId}/invoice`, { responseType: 'blob' })
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `invoice-${bookingId}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    alert('Gagal memuat turun invois.')
  }
}
</script>

<style scoped>
.manage-bookings {
  padding: clamp(1.5rem, 2.5vw, 2rem);
  max-width: 1600px;
  margin: 0 auto;
}

h1 {
  margin: 0 0 clamp(1.5rem, 2.5vw, 2rem) 0;
  color: #2c3e50;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
}

.filters {
  display: flex;
  gap: clamp(0.75rem, 1.2vw, 1rem);
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(2rem, 3vw, 2.5rem) clamp(0.6rem, 0.9vw, 0.75rem) clamp(0.8rem, 1.2vw, 1rem);
  border: 2px solid #e0e0e0;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-size: clamp(0.9rem, 1.2vw, 1rem);
}

.search-box input:focus {
  outline: none;
  border-color: var(--theme-primary);
}

.search-icon {
  position: absolute;
  right: clamp(0.8rem, 1.2vw, 1rem);
  top: 50%;
  transform: translateY(-50%);
}

.filters select,
.date-filter {
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(0.8rem, 1.2vw, 1rem);
  border: 2px solid #e0e0e0;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-size: clamp(0.9rem, 1.2vw, 1rem);
  cursor: pointer;
}

.filters select:focus,
.date-filter:focus {
  outline: none;
  border-color: var(--theme-primary);
}

.loading {
  text-align: center;
  padding: clamp(2rem, 4vw, 3rem);
  color: #666;
}

.table-container {
  background: white;
  border-radius: clamp(10px, 1.2vw, 12px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.bookings-table {
  width: 100%;
  border-collapse: collapse;
}

.bookings-table thead {
  background: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
}

.bookings-table th {
  padding: clamp(0.75rem, 1.2vw, 1rem);
  text-align: left;
  font-weight: 600;
}

.bookings-table td {
  padding: clamp(0.75rem, 1.2vw, 1rem);
  border-bottom: 1px solid #f0f0f0;
}

.bookings-table tbody tr:hover {
  background: rgba(var(--theme-primary-rgb), 0.06);
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-info small {
  color: #666;
  font-size: clamp(0.8rem, 1vw, 0.85rem);
}

.status-badge {
  padding: clamp(0.35rem, 0.5vw, 0.4rem) clamp(0.7rem, 1vw, 0.8rem);
  border-radius: clamp(16px, 2vw, 20px);
  font-size: clamp(0.8rem, 1vw, 0.85rem);
  font-weight: 600;
}

.status-badge.pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.confirmed {
  background: #d1ecf1;
  color: #0c5460;
}

.status-badge.completed {
  background: #d4edda;
  color: #155724;
}

.status-badge.cancelled {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  display: flex;
  gap: clamp(0.4rem, 0.6vw, 0.5rem);
}

.btn-icon {
  padding: clamp(0.4rem, 0.6vw, 0.5rem) clamp(0.6rem, 0.9vw, 0.75rem);
  border: none;
  border-radius: clamp(5px, 0.6vw, 6px);
  cursor: pointer;
  transition: all 0.3s;
  font-size: clamp(0.95rem, 1.2vw, 1rem);
}

.btn-confirm {
  background: #28a745;
  color: white;
}

.btn-confirm:hover {
  background: #218838;
}

.btn-complete {
  background: #17a2b8;
  color: white;
}

.btn-complete:hover {
  background: #138496;
}

.btn-cancel {
  background: #dc3545;
  color: white;
}

.btn-cancel:hover {
  background: #c82333;
}

.btn-view {
  background: #6c757d;
  color: white;
}

.btn-view:hover {
  background: #5a6268;
}

.no-data {
  text-align: center;
  padding: clamp(2rem, 4vw, 3rem);
  color: #666;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: clamp(10px, 1.2vw, 12px);
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: clamp(1.2rem, 1.8vw, 1.5rem);
  border-bottom: 2px solid #f0f0f0;
}

.modal-header h2 {
  margin: 0;
  font-size: clamp(1.3rem, 1.8vw, 1.5rem);
  color: #2c3e50;
}

.btn-close {
  background: none;
  border: none;
  font-size: clamp(1.3rem, 2vw, 1.5rem);
  cursor: pointer;
  color: #666;
  padding: 0;
  width: clamp(28px, 4vw, 32px);
  height: clamp(28px, 4vw, 32px);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s;
}

.btn-close:hover {
  background: #f0f0f0;
  color: #333;
}

.modal-body.details {
  padding: clamp(1.2rem, 1.8vw, 1.5rem);
}

.detail-section {
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
}

.detail-section h3 {
  margin: 0 0 clamp(0.8rem, 1.2vw, 1rem) 0;
  color: var(--theme-primary);
  font-size: clamp(1rem, 1.4vw, 1.1rem);
  padding-bottom: clamp(0.4rem, 0.6vw, 0.5rem);
  border-bottom: 2px solid #e0e0e0;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: clamp(0.6rem, 0.9vw, 0.75rem) 0;
  border-bottom: 1px solid #f0f0f0;
}

.detail-row .label {
  font-weight: 600;
  color: #666;
}

.detail-row .value {
  color: #2c3e50;
  text-align: right;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: clamp(0.75rem, 1.2vw, 1rem);
  margin-top: clamp(1.2rem, 1.8vw, 1.5rem);
}

.btn-secondary {
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(1.2rem, 1.8vw, 1.5rem);
  background: transparent;
  color: var(--theme-primary);
  border: 2px solid var(--theme-primary);
  border-radius: clamp(6px, 0.8vw, 8px);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: rgba(var(--theme-primary-rgb), 0.08);
}

@media (max-width: 768px) {
  .manage-bookings {
    padding: clamp(0.75rem, 1.5vw, 1rem);
  }

  .filters {
    flex-direction: column;
  }

  .table-container {
    overflow-x: auto;
  }

  .bookings-table {
    min-width: 1000px;
  }

  .detail-row {
    flex-direction: column;
    gap: clamp(0.2rem, 0.4vw, 0.25rem);
  }

  .detail-row .value {
    text-align: left;
  }
}
</style>
