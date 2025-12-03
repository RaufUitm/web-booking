<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="reports-view">
    <h1>Laporan & Statistik</h1>

    <!-- Report Type Selection -->
    <div class="report-controls">
      <div class="control-group">
        <label>Jenis Laporan:</label>
        <select v-model="reportType" @change="loadReport">
          <option value="monthly">Bulanan</option>
          <option value="yearly">Tahunan</option>
          <option value="facility">Mengikut Kemudahan</option>
        </select>
      </div>

      <div v-if="canFilterDistrict" class="control-group">
        <label>Daerah:</label>
        <select v-model="selectedDistrict" @change="loadReport">
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

      <div v-if="reportType !== 'yearly'" class="control-group">
        <label>Bulan:</label>
        <select v-model="selectedMonth" @change="loadReport">
          <option v-for="(month, index) in months" :key="index" :value="index + 1">
            {{ month }}
          </option>
        </select>
      </div>

      <div class="control-group">
        <label>Tahun:</label>
        <select v-model="selectedYear" @change="loadReport">
          <option v-for="year in years" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>

      <button @click="loadReport" class="btn-refresh">🔄 Muat Semula</button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Menjana laporan...</div>

    <!-- Report Content -->
    <div v-else class="report-content">
      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="summary-card">
          <div class="card-icon">📊</div>
          <div class="card-content">
            <div class="card-value">{{ reportData.total_bookings || 0 }}</div>
            <div class="card-label">Jumlah Tempahan</div>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon">💰</div>
          <div class="card-content">
            <div class="card-value">RM {{ Number(reportData.total_revenue || 0).toFixed(2) }}</div>
            <div class="card-label">Jumlah Pendapatan</div>
          </div>
        </div>
      </div>

      <!-- Report Table -->
      <div class="report-table-container">
        <table class="report-table">
          <thead>
            <tr>
              <th v-if="reportType === 'monthly'">Tarikh</th>
              <th v-else-if="reportType === 'yearly'">Bulan</th>
              <th v-else>Kemudahan</th>
              <th>Bilangan Tempahan</th>
              <th>Pendapatan (RM)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in reportData.bookings" :key="index">
              <td>
                <span v-if="reportType === 'monthly'">
                  {{ formatDate(item.date) }}
                </span>
                <span v-else-if="reportType === 'yearly'">
                  {{ months[item.month - 1] }}
                </span>
                <span v-else>
                  {{ item.facility?.name || 'N/A' }}
                </span>
              </td>
              <td>{{ item.total }}</td>
              <td>RM {{ Number(item.revenue || 0).toFixed(2) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td><strong>Jumlah</strong></td>
              <td><strong>{{ reportData.total_bookings || 0 }}</strong></td>
              <td><strong>RM {{ Number(reportData.total_revenue || 0).toFixed(2) }}</strong></td>
            </tr>
          </tfoot>
        </table>

        <div v-if="!reportData.bookings || reportData.bookings.length === 0" class="no-data">
          Tiada data untuk tempoh yang dipilih
        </div>
      </div>

      <!-- Export Button -->
      <div class="export-actions">
        <button @click="exportToCSV" class="btn-export">
          📥 Muat Turun CSV
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const reportType = ref('monthly')
const selectedMonth = ref(new Date().getMonth() + 1)
const selectedYear = ref(new Date().getFullYear())
const selectedDistrict = ref('')
const loading = ref(false)
const reportData = ref({
  bookings: [],
  total_bookings: 0,
  total_revenue: 0
})

const canFilterDistrict = computed(() => {
  return authStore.isMasterAdmin || authStore.isStateAdmin
})

const months = [
  'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
  'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'
]

const years = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i)

onMounted(() => {
  loadReport()
})

const loadReport = async () => {
  loading.value = true
  try {
    const params = {
      type: reportType.value,
      year: selectedYear.value,
      month: selectedMonth.value
    }
    
    // Apply district filter based on role
    if (authStore.isDistrictAdmin) {
      // District admin: always filter by their district
      params.district = authStore.userDistrict
    } else if (selectedDistrict.value) {
      // Master/State admin: filter by selected district
      params.district = selectedDistrict.value
    }

    const response = await api.get('/admin/reports', { params })
    reportData.value = response.data.data
  } catch (error) {
    console.error('Failed to load report:', error)
    alert('Gagal memuat laporan')
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('ms-MY', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const exportToCSV = () => {
  let csvContent = ''
  let headers = []

  if (reportType.value === 'monthly') {
    headers = ['Tarikh', 'Bilangan Tempahan', 'Pendapatan (RM)']
  } else if (reportType.value === 'yearly') {
    headers = ['Bulan', 'Bilangan Tempahan', 'Pendapatan (RM)']
  } else {
    headers = ['Kemudahan', 'Bilangan Tempahan', 'Pendapatan (RM)']
  }

  csvContent += headers.join(',') + '\n'

  reportData.value.bookings.forEach(item => {
    let row = []
    if (reportType.value === 'monthly') {
      row.push(item.date)
    } else if (reportType.value === 'yearly') {
      row.push(months[item.month - 1])
    } else {
      row.push(item.facility?.name || 'N/A')
    }
    row.push(item.total)
    row.push(Number(item.revenue || 0).toFixed(2))
    csvContent += row.join(',') + '\n'
  })

  // Create download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `laporan-${reportType.value}-${selectedYear.value}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
</script>

<style scoped>
.reports-view {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

h1 {
  margin: 0 0 2rem 0;
  color: #2c3e50;
  font-size: 2rem;
}

.report-controls {
  display: flex;
  gap: 1rem;
  align-items: end;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.control-group label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
}

.control-group select {
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  min-width: 150px;
}

.control-group select:focus {
  outline: none;
  border-color: #2d5f2e;
}

.btn-refresh {
  padding: 0.75rem 1.5rem;
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-refresh:hover {
  background: #244d25;
  transform: translateY(-1px);
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.report-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.card-icon {
  font-size: 3rem;
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #2d5f2e;
}

.card-label {
  font-size: 0.9rem;
  color: #666;
  margin-top: 0.25rem;
}

.report-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
}

.report-table thead {
  background: #2d5f2e;
  color: white;
}

.report-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
}

.report-table td {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.report-table tbody tr:hover {
  background: rgba(45, 95, 46, 0.03);
}

.report-table tfoot {
  background: #f8f9fa;
  font-weight: 600;
}

.total-row td {
  padding: 1.25rem 1rem;
  border-top: 2px solid #2d5f2e;
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.export-actions {
  display: flex;
  justify-content: flex-end;
}

.btn-export {
  padding: 0.75rem 1.5rem;
  background: #4a8b4d;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-export:hover {
  background: #2d5f2e;
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .reports-view {
    padding: 1rem;
  }

  .report-controls {
    flex-direction: column;
    align-items: stretch;
  }

  .control-group select {
    width: 100%;
  }

  .btn-refresh {
    width: 100%;
  }

  .report-table-container {
    overflow-x: auto;
  }

  .report-table {
    min-width: 600px;
  }
}
</style>
