<template>
  <div class="manage-users">
    <div class="page-header">
      <h1>Pengurusan Pengguna</h1>
      <button @click="showAddModal = true" class="btn-add">
        <span>➕</span> Tambah Pengguna
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari pengguna..."
          @input="handleSearch"
        />
        <span class="search-icon">🔍</span>
      </div>

      <select v-model="roleFilter" @change="loadUsers">
        <option value="">Semua Peranan</option>
        <option value="user">Pengguna</option>
        <option value="master_admin">Master Admin</option>
        <option value="state_admin">State Admin</option>
        <option value="district_admin">District Admin</option>
      </select>
    </div>

    <!-- Users Table -->
    <div v-if="loading" class="loading">Memuatkan...</div>

    <div v-else class="users-table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No. Telefon</th>
            <th>No. Kad Pengenalan</th>
            <th>Peranan</th>
            <th>Daerah</th>
            <th>Tarikh Daftar</th>
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.phone || '-' }}</td>
            <td>{{ user.ic_number || '-' }}</td>
            <td>
              <span :class="['role-badge', user.role]">
                {{ getRoleDisplay(user.role) }}
              </span>
            </td>
            <td>{{ user.district || '-' }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td class="actions">
              <button @click="editUser(user)" class="btn-icon" title="Edit">
                ✏️
              </button>
              <button @click="confirmDelete(user)" class="btn-icon btn-danger" title="Delete">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredUsers.length === 0" class="no-data">
        Tiada pengguna dijumpai
      </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h2>
          <button @click="closeModal" class="btn-close">✕</button>
        </div>

        <form @submit.prevent="saveUser" class="modal-body">
          <div class="form-group">
            <label>Nama Penuh <span class="required">*</span></label>
            <input v-model="formData.name" type="text" required />
          </div>

          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input v-model="formData.email" type="email" required />
          </div>

          <div class="form-group">
            <label>No. Telefon</label>
            <input v-model="formData.phone" type="tel" placeholder="0123456789" />
          </div>

          <div class="form-group">
            <label>No. Kad Pengenalan</label>
            <input v-model="formData.ic_number" type="text" placeholder="990101011234" />
          </div>

          <div class="form-group">
            <label>Peranan <span class="required">*</span></label>
            <select v-model="formData.role" required @change="handleRoleChange">
              <option value="user">Pengguna</option>
              <option value="district_admin">District Admin</option>
              <option value="state_admin">State Admin</option>
              <option value="master_admin">Master Admin</option>
            </select>
          </div>

          <div v-if="formData.role === 'district_admin'" class="form-group">
            <label>Daerah <span class="required">*</span></label>
            <select v-model="formData.district" required>
              <option value="">Pilih Daerah</option>
              <option value="Besut">Besut</option>
              <option value="Marang">Marang</option>
              <option value="Setiu">Setiu</option>
              <option value="Hulu Terengganu">Hulu Terengganu</option>
            </select>
          </div>

          <div v-if="!showEditModal" class="form-group">
            <label>Kata Laluan <span class="required">*</span></label>
            <input v-model="formData.password" type="password" required minlength="8" />
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
          <p>Adakah anda pasti mahu memadamkan pengguna <strong>{{ userToDelete?.name }}</strong>?</p>
          <p class="warning-text">Tindakan ini tidak boleh dibatalkan.</p>

          <div class="modal-actions">
            <button @click="showDeleteModal = false" class="btn-secondary">
              Batal
            </button>
            <button @click="deleteUser" :disabled="deleting" class="btn-danger">
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

const users = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')

const searchQuery = ref('')
const roleFilter = ref('')

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  ic_number: '',
  role: 'user',
  district: '',
  password: ''
})

const userToDelete = ref(null)
const editingUserId = ref(null)

const filteredUsers = computed(() => {
  let filtered = users.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query) ||
      (user.phone && user.phone.includes(query)) ||
      (user.ic_number && user.ic_number.includes(query))
    )
  }

  return filtered
})

onMounted(() => {
  loadUsers()
})

const loadUsers = async () => {
  loading.value = true
  try {
    const params = {}
    if (roleFilter.value) {
      params.role = roleFilter.value
    }

    const response = await api.get('/admin/users', { params })
    users.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to load users:', err)
    error.value = 'Gagal memuatkan pengguna'
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  // Search is handled by computed property
}

const getRoleDisplay = (role) => {
  const roleMap = {
    'user': 'Pengguna',
    'district_admin': 'District Admin',
    'state_admin': 'State Admin',
    'master_admin': 'Master Admin',
    'admin': 'Admin'
  }
  return roleMap[role] || role
}

const handleRoleChange = () => {
  // Clear district if not district admin
  if (formData.value.role !== 'district_admin') {
    formData.value.district = ''
  }
}

const editUser = (user) => {
  editingUserId.value = user.id
  formData.value = {
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    ic_number: user.ic_number || '',
    role: user.role,
    district: user.district || ''
  }
  showEditModal.value = true
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const saveUser = async () => {
  saving.value = true
  error.value = ''

  try {
    if (showEditModal.value) {
      await api.put(`/admin/users/${editingUserId.value}`, formData.value)
    } else {
      await api.post('/admin/users', formData.value)
    }

    await loadUsers()
    closeModal()
  } catch (err) {
    console.error('Failed to save user:', err)
    error.value = err.response?.data?.message || 'Gagal menyimpan pengguna'
  } finally {
    saving.value = false
  }
}

const deleteUser = async () => {
  deleting.value = true
  try {
    await api.delete(`/admin/users/${userToDelete.value.id}`)
    await loadUsers()
    showDeleteModal.value = false
  } catch (err) {
    console.error('Failed to delete user:', err)
    alert('Gagal memadamkan pengguna')
  } finally {
    deleting.value = false
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  formData.value = {
    name: '',
    email: '',
    phone: '',
    ic_number: '',
    role: 'user',
    password: ''
  }
  editingUserId.value = null
  error.value = ''
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('ms-MY', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.manage-users {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #2d5f2e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-add:hover {
  background: #244d25;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(45, 95, 46, 0.3);
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.search-box {
  position: relative;
  flex: 1;
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

select {
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
}

select:focus {
  outline: none;
  border-color: #2d5f2e;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.users-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table thead {
  background: #2d5f2e;
  color: white;
}

.users-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
}

.users-table td {
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.users-table tbody tr:hover {
  background: rgba(45, 95, 46, 0.03);
}

.role-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.role-badge.admin {
  background: #fef3c7;
  color: #92400e;
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

.role-badge.user {
  background: rgba(45, 95, 46, 0.1);
  color: #2d5f2e;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-icon:hover {
  background: rgba(0, 0, 0, 0.05);
}

.btn-icon.btn-danger:hover {
  background: rgba(239, 68, 68, 0.1);
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
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.modal-small {
  max-width: 500px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
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
  border-radius: 4px;
}

.btn-close:hover {
  background: rgba(0, 0, 0, 0.05);
}

.modal-body {
  padding: 1.5rem;
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

.required {
  color: #e74c3c;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2d5f2e;
}

.warning-text {
  color: #e74c3c;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-primary,
.btn-secondary,
.btn-danger {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: #2d5f2e;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #244d25;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  color: #2d5f2e;
  border: 2px solid #2d5f2e;
}

.btn-secondary:hover {
  background: rgba(45, 95, 46, 0.05);
}

.btn-danger {
  background: #e74c3c;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c0392b;
  transform: translateY(-1px);
}

.btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .manage-users {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .btn-add {
    width: 100%;
    justify-content: center;
  }

  .filters {
    flex-direction: column;
  }

  .users-table-container {
    overflow-x: auto;
  }

  .users-table {
    min-width: 800px;
  }

  .modal {
    max-width: 100%;
    margin: 0;
    border-radius: 0;
  }
}
</style>
