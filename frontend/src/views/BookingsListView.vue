<template>
  <div class="bookings-list-container">
    <div class="header">
      <h1>All Bookings</h1>
      <button @click="refreshBookings" class="refresh-btn">
        🔄 Refresh
      </button>
    </div>

    <div v-if="loading" class="loading">
      <p>Loading bookings...</p>
    </div>

    <div v-else-if="error" class="error-message">
      <p>Error: {{ error }}</p>
    </div>

    <div v-else-if="!bookings.length" class="no-bookings">
      <p>No bookings found.</p>
      <router-link to="/" class="book-now-btn">Make a Booking</router-link>
    </div>

    <div v-else class="bookings-grid">
      <div
        v-for="booking in bookings"
        :key="booking.id"
        class="booking-card"
        :class="`status-${booking.status}`"
      >
        <div class="booking-header">
          <span class="booking-id">#{{ booking.id }}</span>
          <span class="status-badge" :class="`badge-${booking.status}`">
            {{ booking.status.toUpperCase() }}
          </span>
        </div>

        <div class="booking-details">
          <div class="detail-row">
            <span class="label">Service:</span>
            <span class="value">{{ booking.service?.name }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Customer:</span>
            <span class="value">{{ booking.customer_name }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Email:</span>
            <span class="value">{{ booking.customer_email }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Phone:</span>
            <span class="value">{{ booking.customer_phone }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Date:</span>
            <span class="value">{{ formatDate(booking.time_slot?.date) }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Time:</span>
            <span class="value">
              {{ formatTime(booking.time_slot?.start_time) }} -
              {{ formatTime(booking.time_slot?.end_time) }}
            </span>
          </div>

          <div class="detail-row">
            <span class="label">Price:</span>
            <span class="value price">${{ booking.service?.price }}</span>
          </div>

          <div v-if="booking.notes" class="detail-row notes">
            <span class="label">Notes:</span>
            <span class="value">{{ booking.notes }}</span>
          </div>

          <div class="detail-row">
            <span class="label">Booked On:</span>
            <span class="value">{{ formatDateTime(booking.created_at) }}</span>
          </div>
        </div>

        <div class="booking-actions">
          <button
            v-if="booking.status === 'pending'"
            @click="updateStatus(booking.id, 'confirmed')"
            class="btn btn-confirm"
          >
            ✓ Confirm
          </button>

          <button
            v-if="booking.status === 'confirmed'"
            @click="updateStatus(booking.id, 'completed')"
            class="btn btn-complete"
          >
            ✓ Complete
          </button>

          <button
            v-if="['pending', 'confirmed'].includes(booking.status)"
            @click="updateStatus(booking.id, 'cancelled')"
            class="btn btn-cancel"
          >
            ✕ Cancel
          </button>

          <button
            @click="deleteBooking(booking.id)"
            class="btn btn-delete"
            :disabled="booking.status !== 'cancelled'"
          >
            🗑️ Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Summary -->
    <div v-if="bookings.length" class="statistics">
      <h2>Statistics</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total Bookings</span>
        </div>
        <div class="stat-card pending">
          <span class="stat-value">{{ stats.pending }}</span>
          <span class="stat-label">Pending</span>
        </div>
        <div class="stat-card confirmed">
          <span class="stat-value">{{ stats.confirmed }}</span>
          <span class="stat-label">Confirmed</span>
        </div>
        <div class="stat-card completed">
          <span class="stat-value">{{ stats.completed }}</span>
          <span class="stat-label">Completed</span>
        </div>
        <div class="stat-card cancelled">
          <span class="stat-value">{{ stats.cancelled }}</span>
          <span class="stat-label">Cancelled</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'
import { storeToRefs } from 'pinia'
import api from '@/api/axios'

const bookingStore = useBookingStore()
const { bookings, loading, error } = storeToRefs(bookingStore)

onMounted(() => {
  bookingStore.fetchBookings()
})

const stats = computed(() => {
  return {
    total: bookings.value.length,
    pending: bookings.value.filter(b => b.status === 'pending').length,
    confirmed: bookings.value.filter(b => b.status === 'confirmed').length,
    completed: bookings.value.filter(b => b.status === 'completed').length,
    cancelled: bookings.value.filter(b => b.status === 'cancelled').length
  }
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (time) => {
  if (!time) return 'N/A'
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

const formatDateTime = (datetime) => {
  if (!datetime) return 'N/A'
  return new Date(datetime).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

const updateStatus = async (bookingId, newStatus) => {
  try {
    await api.patch(`/bookings/${bookingId}`, { status: newStatus })
    await bookingStore.fetchBookings()
  } catch (err) {
    console.error('Failed to update booking status:', err)
    alert('Failed to update booking status')
  }
}

const deleteBooking = async (bookingId) => {
  if (!confirm('Are you sure you want to delete this booking?')) {
    return
  }

  try {
    await api.delete(`/bookings/${bookingId}`)
    await bookingStore.fetchBookings()
  } catch (err) {
    console.error('Failed to delete booking:', err)
    alert('Failed to delete booking')
  }
}

const refreshBookings = () => {
  bookingStore.fetchBookings()
}
</script>

<style scoped>
.bookings-list-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h1 {
  color: #2c3e50;
  margin: 0;
}

.refresh-btn {
  background-color: #3498db;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.refresh-btn:hover {
  background-color: #2980b9;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #7f8c8d;
  font-size: 1.2rem;
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #f5c6cb;
}

.no-bookings {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.no-bookings p {
  color: #7f8c8d;
  font-size: 1.2rem;
  margin-bottom: 1.5rem;
}

.book-now-btn {
  display: inline-block;
  background-color: #27ae60;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.book-now-btn:hover {
  background-color: #229954;
}

.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.booking-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-left: 4px solid #e0e0e0;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.booking-card.status-pending {
  border-left-color: #f39c12;
}

.booking-card.status-confirmed {
  border-left-color: #3498db;
}

.booking-card.status-completed {
  border-left-color: #27ae60;
}

.booking-card.status-cancelled {
  border-left-color: #e74c3c;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #ecf0f1;
}

.booking-id {
  font-size: 1.2rem;
  font-weight: bold;
  color: #2c3e50;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: bold;
  text-transform: uppercase;
}

.badge-pending {
  background-color: #fff3cd;
  color: #856404;
}

.badge-confirmed {
  background-color: #d1ecf1;
  color: #0c5460;
}

.badge-completed {
  background-color: #d4edda;
  color: #155724;
}

.badge-cancelled {
  background-color: #f8d7da;
  color: #721c24;
}

.booking-details {
  margin-bottom: 1.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f8f9fa;
}

.detail-row.notes {
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  color: #7f8c8d;
  font-weight: 500;
}

.value {
  color: #2c3e50;
  font-weight: 600;
  text-align: right;
}

.value.price {
  color: #27ae60;
  font-size: 1.1rem;
}

.booking-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.3s ease;
  min-width: 100px;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-confirm {
  background-color: #3498db;
  color: white;
}

.btn-confirm:hover {
  background-color: #2980b9;
}

.btn-complete {
  background-color: #27ae60;
  color: white;
}

.btn-complete:hover {
  background-color: #229954;
}

.btn-cancel {
  background-color: #e74c3c;
  color: white;
}

.btn-cancel:hover {
  background-color: #c0392b;
}

.btn-delete {
  background-color: #95a5a6;
  color: white;
}

.btn-delete:hover:not(:disabled) {
  background-color: #7f8c8d;
}

.statistics {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-top: 2rem;
}

.statistics h2 {
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.stat-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
}

.stat-card.pending {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card.confirmed {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-card.completed {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.stat-card.cancelled {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-value {
  display: block;
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.stat-label {
  display: block;
  font-size: 0.9rem;
  opacity: 0.9;
}

@media (max-width: 768px) {
  .bookings-grid {
    grid-template-columns: 1fr;
  }

  .booking-actions {
    flex-direction: column;
  }

  .btn {
    min-width: auto;
  }
}
</style>
