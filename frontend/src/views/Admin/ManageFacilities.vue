<template>
  <div class="manage-facilities">
    <div class="page-header">
      <h1>Pengurusan Kemudahan</h1>
      <button @click="openAddModal" class="btn-add">
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

      <select v-if="authStore.canManageAllDistricts" v-model="selectedDistrictFilter" @change="loadFacilities">
        <option value="">Semua Daerah</option>
        <option v-for="d in districtStore.districts" :key="d.slug" :value="d.name">{{ d.name }}</option>
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
      <div class="table-header">
        <div class="results-info">
          Menunjukkan {{ filteredFacilities.length > 0 ? ((currentPage - 1) * itemsPerPage + 1) : 0 }} - {{ Math.min(currentPage * itemsPerPage, totalItems) }} daripada {{ totalItems }} kemudahan
        </div>
        <div class="items-per-page">
          <label>Papar:</label>
          <select v-model.number="itemsPerPage" @change="changeItemsPerPage">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
      </div>

      <table class="facilities-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Gambar</th>
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
            <td>
              <img v-if="facility.image" :src="getImageUrl(facility.image)" :alt="facility.name" class="facility-thumbnail" @error="handleImageError" />
              <span v-else class="no-image">📷</span>
            </td>
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
              <button v-if="canModify(facility)" @click="editFacility(facility)" class="btn-icon" title="Edit">
                ✏️
              </button>
              <button v-if="canModify(facility)" @click="confirmDelete(facility)" class="btn-icon btn-danger" title="Delete">
                🗑️
              </button>
              <span v-if="!canModify(facility)" class="text-muted">Dilarang</span>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredFacilities.length === 0 && totalItems === 0" class="no-data">
        Tiada kemudahan dijumpai
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          @click="goToPage(1)" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          ⟪
        </button>
        <button 
          @click="goToPage(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          ‹
        </button>
        
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="goToPage(page)"
          :class="['pagination-btn', { active: currentPage === page }]"
        >
          {{ page }}
        </button>

        <button 
          @click="goToPage(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          ›
        </button>
        <button 
          @click="goToPage(totalPages)" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          ⟫
        </button>
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
            <label>Gambar Kemudahan</label>
            <input id="facility-image-input" ref="imageInput" @change="handleImageChange" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" />
            <div v-if="imagePreview" class="image-preview">
              <img :src="imagePreview" alt="Preview" />
              <small class="hint" style="display: block; margin-top: 0.5rem;">Pratonton imej baru</small>
            </div>
            <div v-else-if="formData.image && showEditModal" class="image-preview">
              <img :src="formData.image" alt="Current" />
              <small class="hint" style="display: block; margin-top: 0.5rem;">Imej semasa</small>
            </div>
            <small class="hint">Muat naik imej (.jpg, .png, .webp). Saiz maksimum 2MB.</small>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input v-model="formData.is_available" type="checkbox" />
              <span>Tersedia untuk tempahan</span>
            </label>
          </div>

          <div v-if="error" class="error-message">
            <div v-if="typeof error === 'string'">{{ error }}</div>
            <div v-else-if="typeof error === 'object'">
              <div v-for="(msgs, field) in error" :key="field">
                <strong>{{ field }}:</strong> {{ Array.isArray(msgs) ? msgs.join(', ') : msgs }}
              </div>
            </div>
          </div>

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
import { useDistrictStore } from '@/stores/district'
import { getImageUrl } from '@/utils/helpers'

const facilities = ref([])
const categories = ref([])
const districtStore = useDistrictStore()
const selectedDistrictFilter = ref('')
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

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)

// Auth store (used to restrict district-admins)
const authStore = useAuthStore()

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

const imageFile = ref(null)
const imagePreview = ref('')

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

  // If user is a district admin, ensure the UI only shows facilities belonging to their district.
  if (authStore.isDistrictAdmin) {
    const myDistrict = (authStore.userDistrict || '').toString().trim().toLowerCase()
    filtered = filtered.filter(f => {
      if (!f) return false
      // Normalize possible district representations on the facility
      const vals = []
      ;['district', 'district_name', 'district_slug', 'pbt', 'council'].forEach(k => {
        if (f[k] !== undefined && f[k] !== null) vals.push(String(f[k]))
      })
      if (f.district && typeof f.district === 'object') {
        if (f.district.name) vals.push(String(f.district.name))
        if (f.district.slug) vals.push(String(f.district.slug))
        if (f.district.id) vals.push(String(f.district.id))
      }
      if (f.district_id !== undefined && f.district_id !== null) vals.push(String(f.district_id))
      if (f.districtId !== undefined && f.districtId !== null) vals.push(String(f.districtId))

      const norm = vals.map(v => v.toString().trim().toLowerCase()).filter(Boolean)
      if (norm.length === 0) return false
      return norm.includes(myDistrict)
    })
  }

  // Update total items for pagination
  totalItems.value = filtered.length

  // Apply pagination
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filtered.slice(start, end)
})

const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value))

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const changeItemsPerPage = () => {
  currentPage.value = 1 // Reset to first page when changing items per page
}

onMounted(() => {
  loadFacilities()
  loadCategories()
})

const loadFacilities = async () => {
  loading.value = true
  try {
    const params = {
      per_page: 1000 // Request a large number to get all facilities
    }
    if (categoryFilter.value) {
      params.category_id = categoryFilter.value
    }

    // If district admin, restrict facilities to their district. Otherwise, allow filtering by selectedDistrictFilter (for state/master admins)
    const authStore = useAuthStore()
    if (authStore.isDistrictAdmin) {
      params.district = authStore.userDistrict
    } else if (selectedDistrictFilter.value) {
      params.district = selectedDistrictFilter.value
    }

    const response = await api.get('/facilities', { params })
    const data = response.data.data
    // Handle paginated response (data.data) or direct array
    facilities.value = data?.data || data || []
    console.log('Loaded facilities count:', facilities.value.length)
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
    // Always use FormData to handle both file uploads and regular data
    const fd = new FormData()
    fd.append('name', formData.value.name)
    fd.append('category_id', formData.value.category_id)
    fd.append('description', formData.value.description)
    fd.append('location', formData.value.location)
    fd.append('capacity', formData.value.capacity)
    fd.append('price_per_hour', formData.value.price_per_hour)
    if (formData.value.price_per_day !== null && formData.value.price_per_day !== undefined) {
      fd.append('price_per_day', formData.value.price_per_day)
    }
    fd.append('is_available', formData.value.is_available ? 1 : 0)
    
    // Only append image if a new file was selected
    if (imageFile.value) {
      fd.append('image', imageFile.value)
    }

    if (showEditModal.value) {
      // For edit, use POST with _method=PUT for better multipart support
      fd.append('_method', 'PUT')
      const response = await api.post(`/admin/facilities/${editingFacilityId.value}`, fd, { 
        headers: { 'Content-Type': 'multipart/form-data' } 
      })
      console.log('Update response:', response.data)
    } else {
      const response = await api.post('/admin/facilities', fd, { 
        headers: { 'Content-Type': 'multipart/form-data' } 
      })
      console.log('Create response:', response.data)
    }

    closeModal()
    await loadFacilities()
  } catch (err) {
    console.error('Failed to save facility:', err)
    console.error('Error details:', err.response?.data)
    error.value = err.response?.data?.message || err.response?.data?.errors || 'Gagal menyimpan kemudahan'
  } finally {
    saving.value = false
  }
}

const editFacility = (facility) => {
  if (!canModify(facility)) {
    alert('Anda tidak dibenarkan mengubah kemudahan di luar daerah anda.')
    return
  }

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
  // set preview to existing image (if any) and clear any pending file
  imagePreview.value = facility.image ? getImageUrl(facility.image) : ''
  imageFile.value = null
  showEditModal.value = true
}

const openAddModal = () => {
  // prepare fresh form for adding
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
  resetImageState()
  showAddModal.value = true
}

const handleImageChange = (e) => {
  const file = e.target.files && e.target.files[0]
  if (!file) {
    imageFile.value = null
    imagePreview.value = ''
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    alert('Sila pilih imej saiz kurang daripada 2MB.')
    e.target.value = ''
    imageFile.value = null
    imagePreview.value = ''
    return
  }
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => {
    imagePreview.value = ev.target.result
  }
  reader.readAsDataURL(file)
}

const suggestedPerDay = computed(() => {
  const ph = parseFloat(formData.value.price_per_hour || 0)
  return Number.isFinite(ph) ? +(ph * 8).toFixed(2) : 0
})

const applySuggestedPerDay = () => {
  formData.value.price_per_day = suggestedPerDay.value
}

const confirmDelete = (facility) => {
  if (!canModify(facility)) {
    alert('Anda tidak dibenarkan memadam kemudahan di luar daerah anda.')
    return
  }

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
  // reset image state
  resetImageState()
}

// Clean up imageFile/preview when closing
const resetImageState = () => {
  imageFile.value = null
  imagePreview.value = ''
  try {
    const inp = document.querySelector('#facility-image-input')
    if (inp) inp.value = ''
  } catch (e) {
    // ignore
  }
}

const handleImageError = (e) => {
  // Prevent infinite loop if placeholder also fails
  if (e.target.getAttribute('data-error') === 'true') return
  e.target.setAttribute('data-error', 'true')
  e.target.src = '/images/placeholder.jpg'
}

// Return whether current user can modify/delete a facility
const canModify = (facility) => {
  if (!authStore.isDistrictAdmin) return true
  if (!facility) return false
  const myDistrict = (authStore.userDistrict || '').toString().trim().toLowerCase()
  const vals = []
  ;['district', 'district_name', 'district_slug', 'pbt', 'council'].forEach(k => {
    if (facility[k] !== undefined && facility[k] !== null) vals.push(String(facility[k]))
  })
  if (facility.district && typeof facility.district === 'object') {
    if (facility.district.name) vals.push(String(facility.district.name))
    if (facility.district.slug) vals.push(String(facility.district.slug))
    if (facility.district.id) vals.push(String(facility.district.id))
  }
  if (facility.district_id !== undefined && facility.district_id !== null) vals.push(String(facility.district_id))
  if (facility.districtId !== undefined && facility.districtId !== null) vals.push(String(facility.districtId))

  const norm = vals.map(v => v.toString().trim().toLowerCase()).filter(Boolean)
  return norm.includes(myDistrict)
}

// Visible page numbers for pagination
const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})
</script>

<style scoped>
.manage-facilities {
  padding: clamp(1.5rem, 2.5vw, 2rem);
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
}

h1 {
  margin: 0;
  color: #2c3e50;
  font-size: clamp(1.5rem, 2.5vw, 2rem);
}

.btn-add {
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(1.2rem, 1.8vw, 1.5rem);
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: clamp(0.4rem, 0.6vw, 0.5rem);
  transition: all 0.3s;
}

.btn-add:hover {
  background: #244d25;
  transform: translateY(-1px);
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
  border-color: #2d5f2e;
}

.search-icon {
  position: absolute;
  right: clamp(0.8rem, 1.2vw, 1rem);
  top: 50%;
  transform: translateY(-50%);
}

.filters select {
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(0.8rem, 1.2vw, 1rem);
  border: 2px solid #e0e0e0;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-size: clamp(0.9rem, 1.2vw, 1rem);
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

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 2px solid #f0f0f0;
  background: #f8f9fa;
}

.results-info {
  color: #666;
  font-size: 0.9rem;
}

.items-per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.items-per-page label {
  color: #666;
  font-size: 0.9rem;
  font-weight: 500;
}

.items-per-page select {
  padding: 0.4rem 0.6rem;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
}

.items-per-page select:focus {
  outline: none;
  border-color: #2d5f2e;
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
  padding: clamp(0.75rem, 1.2vw, 1rem);
  text-align: left;
  font-weight: 600;
}

.facilities-table td {
  padding: clamp(0.75rem, 1.2vw, 1rem);
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
  padding: clamp(0.6rem, 0.9vw, 0.75rem);
  border: 2px solid #e0e0e0;
  border-radius: clamp(6px, 0.8vw, 8px);
  font-size: clamp(0.9rem, 1.2vw, 1rem);
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
  padding: clamp(0.6rem, 0.9vw, 0.75rem) clamp(1.2rem, 1.8vw, 1.5rem);
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: clamp(6px, 0.8vw, 8px);
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

.btn-ghost {
  padding: 0.4rem 0.8rem;
  background: transparent;
  color: #2d5f2e;
  border: 1px solid #2d5f2e;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-ghost:hover {
  background: rgba(45, 95, 46, 0.1);
}

.suggestion-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
  gap: 0.5rem;
}

.hint {
  color: #666;
  font-size: 0.85rem;
}

.image-preview {
  margin-top: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 0.5rem;
  text-align: center;
}

.image-preview img {
  max-width: 100%;
  max-height: 200px;
  border-radius: 6px;
  object-fit: cover;
}

.text-muted {
  color: #999;
  font-size: 0.9rem;
  font-style: italic;
}

.facility-thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
}

.no-image {
  display: inline-block;
  width: 60px;
  height: 60px;
  line-height: 60px;
  text-align: center;
  background: #f5f5f5;
  border-radius: 8px;
  font-size: 1.5rem;
}

/* Pagination Styles */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.pagination-btn {
  min-width: 40px;
  height: 40px;
  padding: 0.5rem 0.75rem;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #2d5f2e;
  color: #2d5f2e;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: #2d5f2e;
  color: white;
  border-color: #2d5f2e;
}

@media (max-width: 768px) {
  .manage-facilities {
    padding: clamp(0.75rem, 1.5vw, 1rem);
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: clamp(0.75rem, 1.2vw, 1rem);
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

  .table-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .pagination {
    gap: 0.25rem;
    padding: 1rem;
  }

  .pagination-btn {
    min-width: 36px;
    height: 36px;
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }
}
</style>
