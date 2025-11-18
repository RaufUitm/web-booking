<template>
  <div class="manage-categories">
    <h1>Urus Kategori Kemudahan</h1>

    <div class="actions-bar">
      <div class="search-box">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kategori..."
        />
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        ➕ Tambah Kategori
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Memuatkan...</div>

    <!-- Categories Table -->
    <div v-else class="table-container">
      <table class="categories-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Penerangan</th>
            <th>Bilangan Kemudahan</th>
            <th>Status</th>
            <th>Tarikh Dicipta</th>
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in filteredCategories" :key="category.id">
            <td>{{ category.id }}</td>
            <td>
              <strong>{{ category.name }}</strong>
            </td>
            <td>{{ category.description || 'Tiada penerangan' }}</td>
            <td>{{ category.facilities_count || 0 }}</td>
            <td>
              <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                {{ category.is_active ? 'Aktif' : 'Tidak Aktif' }}
              </span>
            </td>
            <td>{{ formatDate(category.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button @click="openEditModal(category)" class="btn-edit">
                  ✏️
                </button>
                <button @click="openDeleteModal(category)" class="btn-delete">
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredCategories.length === 0" class="no-data">
        Tiada kategori dijumpai
      </div>
    </div>

    <!-- Add Category Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeAddModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Tambah Kategori Baru</h2>
          <button @click="closeAddModal" class="btn-close">✕</button>
        </div>
        <form @submit.prevent="addCategory" class="modal-body">
          <div class="form-group">
            <label>Nama Kategori *</label>
            <input
              v-model="newCategory.name"
              type="text"
              required
              placeholder="Contoh: Sukan & Rekreasi"
            />
          </div>

          <div class="form-group">
            <label>Penerangan</label>
            <textarea
              v-model="newCategory.description"
              rows="4"
              placeholder="Penerangan mengenai kategori ini"
            ></textarea>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="newCategory.is_active"
                type="checkbox"
              />
              <span>Aktif</span>
            </label>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeAddModal" class="btn-secondary">
              Batal
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Tambah Kategori' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Category Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Kemaskini Kategori</h2>
          <button @click="closeEditModal" class="btn-close">✕</button>
        </div>
        <form @submit.prevent="updateCategory" class="modal-body">
          <div class="form-group">
            <label>Nama Kategori *</label>
            <input
              v-model="editCategory.name"
              type="text"
              required
              placeholder="Nama kategori"
            />
          </div>

          <div class="form-group">
            <label>Penerangan</label>
            <textarea
              v-model="editCategory.description"
              rows="4"
              placeholder="Penerangan mengenai kategori ini"
            ></textarea>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="editCategory.is_active"
                type="checkbox"
              />
              <span>Aktif</span>
            </label>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeEditModal" class="btn-secondary">
              Batal
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Kemaskini' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal modal-small">
        <div class="modal-header">
          <h2>Padam Kategori</h2>
          <button @click="closeDeleteModal" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <p>Adakah anda pasti ingin memadam kategori ini?</p>
          <p class="warning-text">
            <strong>{{ categoryToDelete?.name }}</strong>
          </p>
          <p v-if="categoryToDelete?.facilities_count > 0" class="alert-text">
            ⚠️ Kategori ini mempunyai {{ categoryToDelete.facilities_count }} kemudahan.
            Kemudahan akan tiada kategori selepas ini.
          </p>
        </div>
        <div class="modal-footer">
          <button @click="closeDeleteModal" class="btn-secondary">
            Batal
          </button>
          <button
            @click="deleteCategory"
            class="btn-danger"
            :disabled="deleting"
          >
            {{ deleting ? 'Memadam...' : 'Padam' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const searchQuery = ref('')

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

const newCategory = ref({
  name: '',
  description: '',
  is_active: true
})

const editCategory = ref({
  id: null,
  name: '',
  description: '',
  is_active: true
})

const categoryToDelete = ref(null)

const filteredCategories = computed(() => {
  if (!searchQuery.value) return categories.value

  return categories.value.filter(category =>
    category.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (category.description && category.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

onMounted(() => {
  loadCategories()
})

const loadCategories = async () => {
  loading.value = true
  try {
    const params = {}
    const authStore = useAuthStore()
    if (authStore.isDistrictAdmin) {
      params.district = authStore.userDistrict
    }
    const response = await api.get('/admin/categories', { params })
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load categories:', error)
    alert('Gagal memuat kategori')
  } finally {
    loading.value = false
  }
}

const addCategory = async () => {
  saving.value = true
  try {
    await api.post('/admin/categories', newCategory.value)
    alert('Kategori berjaya ditambah')
    closeAddModal()
    await loadCategories()
  } catch (error) {
    console.error('Failed to add category:', error)
    alert('Gagal menambah kategori')
  } finally {
    saving.value = false
  }
}

const updateCategory = async () => {
  saving.value = true
  try {
    await api.put(`/admin/categories/${editCategory.value.id}`, {
      name: editCategory.value.name,
      description: editCategory.value.description,
      is_active: editCategory.value.is_active
    })
    alert('Kategori berjaya dikemaskini')
    closeEditModal()
    await loadCategories()
  } catch (error) {
    console.error('Failed to update category:', error)
    alert('Gagal mengemaskini kategori')
  } finally {
    saving.value = false
  }
}

const deleteCategory = async () => {
  deleting.value = true
  try {
    await api.delete(`/admin/categories/${categoryToDelete.value.id}`)
    alert('Kategori berjaya dipadam')
    closeDeleteModal()
    await loadCategories()
  } catch (error) {
    console.error('Failed to delete category:', error)
    alert('Gagal memadam kategori')
  } finally {
    deleting.value = false
  }
}

const openEditModal = (category) => {
  editCategory.value = { ...category }
  showEditModal.value = true
}

const openDeleteModal = (category) => {
  categoryToDelete.value = category
  showDeleteModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  newCategory.value = {
    name: '',
    description: '',
    is_active: true
  }
}

const closeEditModal = () => {
  showEditModal.value = false
  editCategory.value = {
    id: null,
    name: '',
    description: '',
    is_active: true
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  categoryToDelete.value = null
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('ms-MY', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<style scoped>
.manage-categories {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

h1 {
  margin: 0 0 2rem 0;
  color: #2c3e50;
  font-size: 2rem;
}

.actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-box input {
  padding: 0.75rem 1.25rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  width: 300px;
  font-size: 1rem;
}

.search-box input:focus {
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

.categories-table {
  width: 100%;
  border-collapse: collapse;
}

.categories-table thead {
  background: #2d5f2e;
  color: white;
}

.categories-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
}

.categories-table td {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.categories-table tbody tr:hover {
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

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-edit, .btn-delete {
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-edit {
  background: #4a8b4d;
  color: white;
}

.btn-edit:hover {
  background: #2d5f2e;
}

.btn-delete {
  background: #dc3545;
  color: white;
}

.btn-delete:hover {
  background: #c82333;
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
  transform: translateY(-1px);
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
  font-size: 1.1rem;
  color: #856404;
}

.alert-text {
  background: #fff3cd;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid #ffc107;
  color: #856404;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="password"],
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

@media (max-width: 768px) {
  .manage-categories {
    padding: 1rem;
  }

  .actions-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box input {
    width: 100%;
  }

  .table-container {
    overflow-x: auto;
  }

  .categories-table {
    min-width: 800px;
  }

  .modal {
    width: 95%;
    max-height: 95vh;
  }
}
</style>
