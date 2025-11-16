<template>
  <div class="admin-dashboard">
    <h1>Admin Dashboard</h1>

    <div v-if="loading" class="loading">Memuatkan...</div>

    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total_facilities }}</div>
          <div class="stat-label">Jumlah Kemudahan</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total_bookings }}</div>
          <div class="stat-label">Jumlah Tempahan</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total_users }}</div>
          <div class="stat-label">Jumlah Pengguna</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value">RM {{ stats.total_revenue.toFixed(2) }}</div>
          <div class="stat-label">Jumlah Pendapatan</div>
        </div>
      </div>
    </div>

    <div class="dashboard-sections">
      <div class="section">
        <h2>Recent Bookings</h2>
        <div class="recent-bookings">
          <div v-for="booking in recentBookings" :key="booking.id" class="booking-item">
            <div class="booking-info">
              <strong>{{ booking.facility?.name }}</strong>
              <span>{{ booking.user?.name }}</span>
              <span>{{ formatDate(booking.booking_date) }}</span>
            </div>
            <span :class="['status', booking.status]">{{ booking.status }}</span>
          </div>
        </div>
      </div>

      <div class="section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
          <router-link to="/admin/facilities" class="action-btn">
            <span class="icon">🏢</span>
            <span>Manage Facilities</span>
          </router-link>
          <router-link to="/admin/bookings" class="action-btn">
            <span class="icon">📅</span>
            <span>Manage Bookings</span>
          </router-link>
          <router-link to="/admin/users" class="action-btn">
            <span class="icon">👥</span>
            <span>Urus Pengguna</span>
          </router-link>
          <router-link to="/admin/categories" class="action-btn">
            <span class="icon">📁</span>
            <span>Urus Kategori</span>
          </router-link>
          <router-link to="/admin/reports" class="action-btn">
            <span class="icon">📈</span>
            <span>Lihat Laporan</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

defineOptions({
  name: 'AdminDashboard'
})

const stats = ref({
  total_users: 0,
  total_bookings: 0,
  total_facilities: 0,
  total_revenue: 0,
  pending_bookings: 0,
  confirmed_bookings: 0,
  monthly_revenue: 0
})

const recentBookings = ref([])
const loading = ref(false)

onMounted(() => {
  loadDashboardData()
})

const loadDashboardData = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/dashboard')
    const data = response.data.data

    stats.value = data.stats
    recentBookings.value = data.recent_bookings || []
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ms-MY', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}
</script>

<style scoped>
.admin-dashboard {
  padding: 30px;
}

h1 {
  margin: 0 0 30px 0;
  color: #333;
  font-size: 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-icon {
  font-size: 48px;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #333;
}

.stat-label {
  color: #666;
  font-size: 14px;
  margin-top: 5px;
}

.dashboard-sections {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.section {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section h2 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 20px;
}

.recent-bookings {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.booking-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 4px;
}

.booking-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.booking-info span {
  font-size: 14px;
  color: #666;
}

.status {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.status.pending {
  background-color: #fff3e0;
  color: #f57c00;
}

.status.confirmed {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.quick-actions {
  display: grid;
  gap: 15px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 4px;
  text-decoration: none;
  color: #333;
  transition: all 0.3s;
}

.action-btn:hover {
  background-color: #2d5f2e;
  color: white;
  transform: translateX(5px);
}

.action-btn .icon {
  font-size: 24px;
}

@media (max-width: 1024px) {
  .dashboard-sections {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .admin-dashboard {
    padding: 20px;
  }
}
</style>
