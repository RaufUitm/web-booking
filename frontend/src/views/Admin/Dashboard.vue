<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Papan Pemuka Admin</h1>
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
          <option value="Setiu">Setiu</option>
          <option value="Kuala Terengganu">Kuala Terengganu</option>
          <option value="Marang">Marang</option>
          <option value="Dungun">Dungun</option>
          <option value="Kemaman">Kemaman</option>
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
          <div class="stat-value">{{ formattedRevenue(stats.total_revenue) }}</div>
          <div class="stat-label">Jumlah Pendapatan</div>
        </div>
      </div>
    </div>

    <div class="dashboard-sections">
      <div class="section">
        <h2>Tempahan Terkini</h2>
        <div class="recent-bookings">
          <div v-for="booking in recentBookings" :key="booking.id" class="booking-item">
            <div class="booking-info">
              <strong>{{ booking.facility?.name }}</strong>
              <span>{{ booking.user?.name }}</span>
              <span>{{ formatDate(booking.booking_date) }}</span>
            </div>
            <span :class="['status', booking.status]">{{ formatStatus(booking.status) }}</span>
          </div>
        </div>
      </div>

      <div class="section">
        <h2>Tindakan Pantas</h2>
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
    'master_admin': 'Master Admin - Akses Penuh Sistem',
    'state_admin': 'State Admin - Semua Daerah',
    'district_admin': 'Admin Daerah',
    'user': 'Pengguna'
  }
  return roleMap[role] || role
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    const params = {}

    // Apply district filter based on role
    // NOTE: backend expects the district value as stored in DB (name),
    // so pass the raw district name instead of a slug to ensure matching.
    if (authStore.isDistrictAdmin) {
      const districtName = (authStore.userDistrict || '').toString().trim()
      if (districtName) params.district = districtName
    } else if (selectedDistrict.value) {
      // selectedDistrict contains the display name (e.g. 'Besut'), pass it as-is
      params.district = selectedDistrict.value.toString().trim()
    }

    const response = await api.get('/admin/dashboard', { params })
    // Be defensive: backend may return different wrappers. Try common shapes.
    const raw = response.data
    console.debug('admin/dashboard response', raw)
    const data = raw.data || raw

    // Defensive defaults in case API doesn't return all fields
    const defaults = {
      total_users: 0,
      total_bookings: 0,
      total_facilities: 0,
      total_revenue: 0,
      pending_bookings: 0,
      confirmed_bookings: 0,
      monthly_revenue: 0
    }

    stats.value = Object.assign({}, defaults, (data && data.stats) || raw.stats || {})
    // Ensure numeric types
    stats.value.total_revenue = Number(stats.value.total_revenue || 0)
    stats.value.total_users = Number(stats.value.total_users || 0)
    stats.value.total_bookings = Number(stats.value.total_bookings || 0)
    stats.value.total_facilities = Number(stats.value.total_facilities || 0)

    recentBookings.value = (data && data.recent_bookings) || raw.recent_bookings || raw.recentBookings || []
  } catch (error) {
    // Log detailed server error if available
    console.error('Failed to load dashboard data:', error)
    try {
      if (error.response && error.response.data) {
        console.error('Server response:', error.response.data)
      }
    } catch (e) {
      // ignore
    }
  } finally {
    loading.value = false
  }
}

// Format revenue as Malaysian Ringgit
const currencyFormatter = new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' })
const formattedRevenue = (amount) => currencyFormatter.format(Number(amount || 0))

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ms-MY', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    pending: 'Menunggu',
    confirmed: 'Disahkan',
    cancelled: 'Dibatalkan',
    completed: 'Selesai'
  }
  return statusMap[status] || status
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
  padding: clamp(20px, 3vw, 30px);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: clamp(20px, 3vw, 30px);
  flex-wrap: wrap;
  gap: clamp(15px, 2.5vw, 20px);
}

h1 {
  margin: 0 0 clamp(8px, 1.2vw, 10px) 0;
  color: #333;
  font-size: clamp(24px, 3.5vw, 32px);
}

.role-badge {
  display: inline-block;
  padding: clamp(6px, 1vw, 8px) clamp(12px, 2vw, 16px);
  border-radius: clamp(16px, 2vw, 20px);
  font-size: clamp(12px, 1.5vw, 14px);
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
  gap: clamp(8px, 1.2vw, 10px);
}

.district-filter label {
  font-weight: 600;
  color: #555;
}

.district-filter select {
  padding: clamp(8px, 1.2vw, 10px) clamp(12px, 2vw, 15px);
  border: 2px solid #ddd;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-size: clamp(13px, 1.5vw, 14px);
  cursor: pointer;
  background: white;
  min-width: clamp(180px, 20vw, 200px);
}

.district-filter select:focus {
  outline: none;
  border-color: var(--theme-primary);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(15px, 2.5vw, 20px);
  margin-bottom: clamp(30px, 5vw, 40px);
}

.stat-card {
  background: white;
  border-radius: clamp(6px, 0.8vw, 8px);
  padding: clamp(20px, 3vw, 25px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: clamp(15px, 2.5vw, 20px);
}

.stat-icon {
  font-size: clamp(36px, 5vw, 48px);
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: clamp(24px, 3.5vw, 32px);
  font-weight: 700;
  color: #333;
}

.stat-label {
  color: #666;
  font-size: clamp(13px, 1.5vw, 14px);
  margin-top: clamp(4px, 0.6vw, 5px);
}

.dashboard-sections {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: clamp(20px, 3.5vw, 30px);
}

.section {
  background: white;
  border-radius: clamp(6px, 0.8vw, 8px);
  padding: clamp(20px, 3vw, 25px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section h2 {
  margin: 0 0 clamp(15px, 2.5vw, 20px) 0;
  color: #333;
  font-size: clamp(18px, 2.5vw, 20px);
}

.recent-bookings {
  display: flex;
  flex-direction: column;
  gap: clamp(12px, 2vw, 15px);
}

.booking-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: clamp(12px, 2vw, 15px);
  background-color: #f9f9f9;
  border-radius: clamp(3px, 0.5vw, 4px);
}

.booking-info {
  display: flex;
  flex-direction: column;
  gap: clamp(4px, 0.6vw, 5px);
}

.booking-info span {
  font-size: clamp(13px, 1.5vw, 14px);
  color: #666;
}

.status {
  padding: clamp(3px, 0.5vw, 4px) clamp(10px, 1.5vw, 12px);
  border-radius: clamp(10px, 1.2vw, 12px);
  font-size: clamp(11px, 1.3vw, 12px);
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
  gap: clamp(12px, 2vw, 15px);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: clamp(12px, 2vw, 15px);
  padding: clamp(12px, 2vw, 15px);
  background-color: #f9f9f9;
  border-radius: clamp(3px, 0.5vw, 4px);
  text-decoration: none;
  color: #333;
  transition: all 0.3s;
  position: relative;
}

.action-btn:hover {
  background-color: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
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
  font-size: clamp(20px, 3vw, 24px);
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
    padding: clamp(15px, 2.5vw, 20px);
  }
  
  .dashboard-sections {
    grid-template-columns: 1fr;
  }
}
</style>
