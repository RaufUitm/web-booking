<template>
  <div class="manage-admins">
    <div class="page-header">
      <h1>Pengurusan Admin</h1>
      <button @click="showAddModal = true" class="btn-add">
        <span>➕</span> Tambah Admin
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <select v-model="roleFilter" @change="loadAdmins">
        <option value="">Semua Peranan Admin</option>
        <option value="master_admin">Master Admin</option>
        <option value="state_admin">State Admin</option>
        <option value="district_admin">District Admin</option>
      </select>
    </div>

    <!-- Admins Table -->
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
          <tr v-for="admin in filteredAdmins" :key="admin.id">
            <td>{{ admin.id }}</td>
            <td>{{ admin.name }}</td>
            <td>{{ admin.email }}</td>
            <td>{{ admin.phone || '-' }}</td>
            <td>{{ admin.ic_number || '-' }}</td>
            <td>
              <span :class="['role-badge', admin.role]">
                {{ getRoleDisplay(admin.role) }}
              </span>
            </td>
            <td>{{ admin.district || '-' }}</td>
            <td>{{ formatDate(admin.created_at) }}</td>
            <td class="actions">
              <button @click="editAdmin(admin)" class="btn-icon" title="Edit">
                ✏️
              </button>
              <button @click="confirmDelete(admin)" class="btn-icon btn-danger" title="Delete">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="filteredAdmins.length === 0" class="no-data">
        Tiada admin dijumpai
      </div>
    </div>

    <!-- Add/Edit Admin Modal -->
    <div v-if="showAddModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Edit Admin' : 'Tambah Admin' }}</h2>
          <button @click="closeModal" class="btn-close">✕</button>
        </div>
        <form @submit.prevent="saveAdmin" class="modal-body">
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
          <p>Adakah anda pasti mahu memadamkan admin <strong>{{ adminToDelete?.name }}</strong>?</p>
          <p class="warning-text">Tindakan ini tidak boleh dibatalkan.</p>
          <div class="modal-actions">
            <button @click="showDeleteModal = false" class="btn-secondary">
              Batal
            </button>
            <button @click="deleteAdmin" :disabled="deleting" class="btn-danger">
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

const admins = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const error = ref('')

const roleFilter = ref('')
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  ic_number: '',
  role: 'district_admin',
  district: '',
  password: ''
})

const adminToDelete = ref(null)
const editingAdminId = ref(null)

const filteredAdmins = computed(() => {
  let filtered = admins.value
  if (roleFilter.value) {
    filtered = filtered.filter(admin => admin.role === roleFilter.value)
  }
  return filtered
})

onMounted(() => {
  loadAdmins()
})

const loadAdmins = async () => {
  loading.value = true
  try {
    const params = { role: 'admin' }
    if (roleFilter.value) {
      params.role = roleFilter.value
    } else {
      params.role = 'district_admin,state_admin,master_admin'
    }
    const response = await api.get('/admin/users', { params })
    admins.value = (response.data.data || response.data).filter(u => u.role !== 'user')
  } catch (err) {
    console.error('Failed to load admins:', err)
    error.value = 'Gagal memuatkan admin'
  } finally {
    loading.value = false
  }
}

const editAdmin = (admin) => {
  editingAdminId.value = admin.id
  formData.value = {
    name: admin.name,
    email: admin.email,
    phone: admin.phone || '',
    ic_number: admin.ic_number || '',
    role: admin.role,
    district: admin.district || ''
  }
  showEditModal.value = true
}

const confirmDelete = (admin) => {
  adminToDelete.value = admin
  showDeleteModal.value = true
}

const saveAdmin = async () => {
  saving.value = true
  error.value = ''
  try {
    if (editingAdminId.value) {
      // Update
      await api.put(`/admin/users/${editingAdminId.value}`, formData.value)
    } else {
      // Create
      await api.post('/admin/users', formData.value)
    }
    loadAdmins()
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan admin'
  } finally {
    saving.value = false
  }
}

const deleteAdmin = async () => {
  deleting.value = true
  try {
    await api.delete(`/admin/users/${adminToDelete.value.id}`)
    loadAdmins()
    showDeleteModal.value = false
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memadam admin'
  } finally {
    deleting.value = false
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  error.value = ''
  editingAdminId.value = null
  formData.value = {
    name: '',
    email: '',
    phone: '',
    ic_number: '',
    role: 'district_admin',
    district: '',
    password: ''
  }
}

const getRoleDisplay = (role) => {
  const roleMap = {
    'district_admin': 'District Admin',
    'state_admin': 'State Admin',
    'master_admin': 'Master Admin'
  }
  return roleMap[role] || role
}

const handleRoleChange = () => {
  if (formData.value.role !== 'district_admin') {
    formData.value.district = ''
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
@import './ManageUsers.vue';
</style>
