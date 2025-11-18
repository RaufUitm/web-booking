<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Admin Dashboard</h1>
        <p class="role-badge" :class="authStore.userRole">
          {{ getRoleDisplay(authStore.userRole) }}
          <span v-if="authStore.userDistrict"> - {{ authStore.userDistrict }}</span>
        </p>
      </div>

      <!-- District Filter for Master/State Admins -->
      <div v-if="authStore.canManageAllDistricts" class="district-filter">
        <label>Filter Daerah:</label>
        <select v-model="selectedDistrict" @change="loadDashboardData">
          <option value="">Semua Daerah</option>
          <option value="Besut">Besut</option>
          <option value="Marang">Marang</option>
          <option value="Setiu">Setiu</option>
          <option value="Hulu Terengganu">Hulu Terengganu</option>
        </select>
      </div>
    </div>

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
                <router-link :to="adminRoute('facilities')" class="action-btn">
                  <span class="icon">🏢</span>
                  <span>Urus Kemudahan</span>
                </router-link>
                <router-link :to="adminRoute('bookings')" class="action-btn">
                  <span class="icon">📅</span>
                  <span>Urus Tempahan</span>
                </router-link>

          <!-- User Management - Only for Master/State Admins -->
          <router-link v-if="authStore.canManageAllDistricts" :to="adminRoute('users')" class="action-btn">
            <span class="icon">👥</span>
            <span>Urus Pengguna</span>
          </router-link>

          <!-- Admin Management - Only for Master/State Admins -->
          <router-link v-if="authStore.canAssignRoles" :to="adminRoute('admins')" class="action-btn">
            <span class="icon">👑</span>
            <span>Urus Admin</span>
          </router-link>

          <router-link :to="adminRoute('categories')" class="action-btn">
            <span class="icon">📁</span>
            <span>Urus Kategori</span>
          </router-link>
          <router-link :to="adminRoute('reports')" class="action-btn">
            <span class="icon">📈</span>
            <span>Lihat Laporan</span>
          </router-link>

          <!-- System Settings - Only for Master Admin -->
          <router-link v-if="authStore.isMasterAdmin" :to="adminRoute('')" class="action-btn master-only">
            <span class="icon">⚙️</span>
            <span>Tetapan Sistem</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

defineOptions({
  name: 'AdminDashboard'
})

const authStore = useAuthStore()

// no prefixPath needed here; building named routes directly

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
const selectedDistrict = ref('')

onMounted(() => {
  // Auto-select district for district admins
  if (authStore.isDistrictAdmin) {
    selectedDistrict.value = authStore.userDistrict
  }
  loadDashboardData()
})

const getRoleDisplay = (role) => {
  const roleMap = {
    'master_admin': 'Master Admin - Full System Access',
    'state_admin': 'State Admin - All Districts',
    'district_admin': 'District Admin',
    'user': 'User'
  }
  return roleMap[role] || role
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    const params = {}

    // Apply district filter based on role
    if (authStore.isDistrictAdmin) {
      params.district = authStore.userDistrict
    } else if (selectedDistrict.value) {
      params.district = selectedDistrict.value
    }

    const response = await api.get('/admin/dashboard', { params })
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

// Helper to return the appropriate admin route for the current user
const adminRoute = (section) => {
  // section: '', 'facilities', 'bookings', 'users', 'reports', 'categories', 'admins'
  if (authStore.isDistrictAdmin) {
    // route under district prefix
    const slug = (authStore.userDistrict || selectedDistrict.value || '').toLowerCase().replace(/\s+/g, '-')
    if (!slug) {
      return { name: 'district-home' }
    }
    if (!section) return { name: 'district-admin', params: { district: slug } }
    return { name: `district-admin-${section}`, params: { district: slug } }
  }

  // Default to MDHT/system admin routes
  if (!section) return { name: 'mdht-admin' }
  return { name: `mdht-admin-${section}` }
}
</script>

<style scoped>
.admin-dashboard {
  padding: 30px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

h1 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 32px;
}

.role-badge {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  margin: 0;
}

.role-badge.master_admin {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.role-badge.state_admin {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.role-badge.district_admin {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.district-filter {
  display: flex;
  align-items: center;
  gap: 10px;
}

.district-filter label {
  font-weight: 600;
  color: #555;
}

.district-filter select {
  padding: 10px 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  background: white;
  min-width: 200px;
}

.district-filter select:focus {
  outline: none;
  border-color: #FF8C00;
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
  position: relative;
}

.action-btn:hover {
  background-color: #FF8C00;
  color: white;
  transform: translateX(5px);
}

.action-btn.master-only {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: 2px solid #5568d3;
}

.action-btn.master-only:hover {
  background: linear-gradient(135deg, #5568d3 0%, #6a4199 100%);
  transform: translateX(5px) scale(1.02);
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
