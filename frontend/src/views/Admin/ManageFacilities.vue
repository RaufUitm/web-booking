<template>
  <div class="manage-facilities">
    <div class="page-header">
      <h1>Pengurusan Kemudahan</h1>
      <button @click="showAddModal = true" class="btn-add">
        <span>➕</span> Tambah Kemudahan
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kemudahan..."
        />
        <span class="search-icon">🔍</span>
      </div>

      <select v-model="categoryFilter" @change="loadFacilities">
        <option value="">Semua Kategori</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>

      <select v-model="statusFilter">
        <option value="">Semua Status</option>
        <option value="available">Tersedia</option>
        <option value="unavailable">Tidak Tersedia</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Memuatkan...</div>

    <!-- Facilities Table -->
    <div v-else class="table-container">
      <table class="facilities-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Kapasiti</th>
            <th>Harga/Jam</th>
            <th>Harga/Hari</th>
            <th>Status</th>
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="facility in filteredFacilities" :key="facility.id">
            <td>{{ facility.id }}</td>
            <td><strong>{{ facility.name }}</strong></td>
            <td>{{ facility.category?.name || '-' }}</td>
            <td>{{ facility.location || '-' }}</td>
            <td>{{ facility.capacity }} orang</td>
            <td>RM {{ parseFloat(facility.price_per_hour).toFixed(2) }}</td>
            <td>
              <span v-if="facility.price_per_day !== null && facility.price_per_day !== undefined">RM {{ parseFloat(facility.price_per_day).toFixed(2) }}</span>
              <span v-else>-</span>
            </td>
            <td>
              <span :class="['status-badge', facility.is_available ? 'active' : 'inactive']">
                {{ facility.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
              </span>
            </td>
            <td class="actions">
              <button @click="editFacility(facility)" class="btn-icon" title="Edit">
                ✏️
              </button>
              <button @click="confirmDelete(facility)" class="btn-icon btn-danger" title="Delete">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredFacilities.length === 0" class="no-data">
        Tiada kemudahan dijumpai
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Edit Kemudahan' : 'Tambah Kemudahan' }}</h2>
          <button @click="closeModal" class="btn-close">✕</button>
        </div>

        <form @submit.prevent="saveFacility" class="modal-body">
          <div class="form-group">
            <label>Nama Kemudahan <span class="required">*</span></label>
            <input v-model="formData.name" type="text" required />
          </div>

          <div class="form-group">
            <label>Kategori <span class="required">*</span></label>
            <select v-model="formData.category_id" required>
              <option value="">Pilih Kategori</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Penerangan</label>
            <textarea v-model="formData.description" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label>Lokasi <span class="required">*</span></label>
            <input v-model="formData.location" type="text" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Kapasiti <span class="required">*</span></label>
              <input v-model.number="formData.capacity" type="number" min="1" required />
            </div>

            <div class="form-group">
              <label>Harga/Jam (RM) <span class="required">*</span></label>
              <input v-model.number="formData.price_per_hour" type="number" step="0.01" min="0" required />
            </div>

            <div class="form-group">
              <label>Harga/Hari (RM)</label>
              <input v-model.number="formData.price_per_day" type="number" step="0.01" min="0" />
                <div class="suggestion-row">
                  <small class="hint">Cadangan: RM {{ suggestedPerDay.toFixed(2) }} (harga/jam × 8)</small>
                  <button type="button" class="btn-ghost" @click="applySuggestedPerDay">Gunakan Cadangan</button>
                </div>
            </div>
          </div>

          <div class="form-group">
            <label>URL Gambar</label>
            <input v-model="formData.image" type="text" placeholder="https://..." />
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input v-model="formData.is_available" type="checkbox" />
              <span>Tersedia untuk tempahan</span>
            </label>
          </div>

          <div v-if="error" class="error-message">{{ error }}</div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary">
              Batal
            </button>
            <button type="submit" :disabled="saving" class="btn-primary">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal modal-small">
        <div class="modal-header">
          <h2>Sahkan Padam</h2>
          <button @click="showDeleteModal = false" class="btn-close">✕</button>
        </div>

        <div class="modal-body">
          <p>Adakah anda pasti mahu memadamkan kemudahan <strong>{{ facilityToDelete?.name }}</strong>?</p>
          <p class="warning-text">Tindakan ini tidak boleh dibatalkan.</p>

          <div class="modal-actions">
            <button @click="showDeleteModal = false" class="btn-secondary">
              Batal
            </button>
            <button @click="deleteFacility" :disabled="deleting" class="btn-danger">
              {{ deleting ? 'Memadamkan...' : 'Padam' }}
            </button>
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

const facilities = ref([])
const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')

const searchQuery = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

const formData = ref({
  name: '',
  category_id: '',
  description: '',
  location: '',
  capacity: 1,
  price_per_hour: 0,
  price_per_day: null,
  image: '',
  is_available: true
})

const facilityToDelete = ref(null)
const editingFacilityId = ref(null)

const filteredFacilities = computed(() => {
  let filtered = facilities.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(facility =>
      facility.name.toLowerCase().includes(query) ||
      (facility.location && facility.location.toLowerCase().includes(query)) ||
      (facility.description && facility.description.toLowerCase().includes(query))
    )
  }

  if (statusFilter.value === 'available') {
    filtered = filtered.filter(f => f.is_available)
  } else if (statusFilter.value === 'unavailable') {
    filtered = filtered.filter(f => !f.is_available)
  }

  return filtered
})

onMounted(() => {
  loadFacilities()
  loadCategories()
})

const loadFacilities = async () => {
  loading.value = true
  try {
    const params = {}
    if (categoryFilter.value) {
      params.category_id = categoryFilter.value
    }

    // If district admin, restrict facilities to their district
    const authStore = useAuthStore()
    if (authStore.isDistrictAdmin) {
      params.district = authStore.userDistrict
    }

    const response = await api.get('/facilities', { params })
    const data = response.data.data
    // Handle paginated response (data.data) or direct array
    facilities.value = data?.data || data || []
  } catch (err) {
    console.error('Failed to load facilities:', err)
    error.value = 'Gagal memuatkan kemudahan'
    facilities.value = []
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    const data = response.data.data || response.data
    categories.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Failed to load categories:', err)
    categories.value = []
  }
}

const saveFacility = async () => {
  saving.value = true
  error.value = ''

  try {
    if (showEditModal.value) {
      await api.put(`/admin/facilities/${editingFacilityId.value}`, formData.value)
    } else {
      await api.post('/admin/facilities', formData.value)
    }

    closeModal()
    await loadFacilities()
  } catch (err) {
    console.error('Failed to save facility:', err)
    error.value = err.response?.data?.message || 'Gagal menyimpan kemudahan'
  } finally {
    saving.value = false
  }
}

const editFacility = (facility) => {
  formData.value = {
    name: facility.name,
    category_id: facility.category_id,
    description: facility.description || '',
    location: facility.location || '',
    capacity: facility.capacity,
    price_per_hour: parseFloat(facility.price_per_hour),
    price_per_day: facility.price_per_day !== undefined && facility.price_per_day !== null ? parseFloat(facility.price_per_day) : null,
    image: facility.image || '',
    is_available: facility.is_available
  }
  editingFacilityId.value = facility.id
  showEditModal.value = true
}

const suggestedPerDay = computed(() => {
  const ph = parseFloat(formData.value.price_per_hour || 0)
  return Number.isFinite(ph) ? +(ph * 8).toFixed(2) : 0
})

const applySuggestedPerDay = () => {
  formData.value.price_per_day = suggestedPerDay.value
}

const confirmDelete = (facility) => {
  facilityToDelete.value = facility
  showDeleteModal.value = true
}

const deleteFacility = async () => {
  deleting.value = true
  try {
    await api.delete(`/admin/facilities/${facilityToDelete.value.id}`)
    showDeleteModal.value = false
    await loadFacilities()
  } catch (err) {
    console.error('Failed to delete facility:', err)
    alert('Gagal memadamkan kemudahan')
  } finally {
    deleting.value = false
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  formData.value = {
    name: '',
    category_id: '',
    description: '',
    location: '',
    capacity: 1,
    price_per_hour: 0,
    price_per_day: null,
    image: '',
    is_available: true
  }
  editingFacilityId.value = null
  error.value = ''
}
</script>

<style scoped>
.manage-facilities {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.btn-add {
  padding: 0.75rem 1.5rem;
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.btn-add:hover {
  background: #244d25;
  transform: translateY(-1px);
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
}

.search-box input:focus {
  outline: none;
  border-color: #2d5f2e;
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.filters select {
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
}

.filters select:focus {
  outline: none;
  border-color: #2d5f2e;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.facilities-table {
  width: 100%;
  border-collapse: collapse;
}

.facilities-table thead {
  background: #2d5f2e;
  color: white;
}

.facilities-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
}

.facilities-table td {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.facilities-table tbody tr:hover {
  background: rgba(45, 95, 46, 0.03);
}

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem 0.75rem;
  background: #4a8b4d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-icon:hover {
  background: #2d5f2e;
}

.btn-icon.btn-danger {
  background: #dc3545;
}

.btn-icon.btn-danger:hover {
  background: #c82333;
}

.no-data {
  text-align: center;
  padding: 3rem;
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
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.modal-small {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #2c3e50;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
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

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  margin: 0 0 1rem 0;
  color: #2c3e50;
}

.warning-text {
  font-size: 0.9rem;
  color: #856404;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.required {
  color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2d5f2e;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.error-message {
  padding: 1rem;
  background: #f8d7da;
  color: #721c24;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background: #244d25;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  color: #2d5f2e;
  border: 2px solid #2d5f2e;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: rgba(45, 95, 46, 0.05);
}

.btn-danger {
  padding: 0.75rem 1.5rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-danger:hover:not(:disabled) {
  background: #c82333;
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .manage-facilities {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .filters {
    flex-direction: column;
  }

  .table-container {
    overflow-x: auto;
  }

  .facilities-table {
    min-width: 900px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
