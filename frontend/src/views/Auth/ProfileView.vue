<template>
  <div class="profile-page">
    <div class="container">
      <div class="page-header">
        <h1>Tetapan Profil</h1>
        <p>Kemaskini maklumat peribadi dan kata laluan anda</p>
      </div>

      <div class="profile-grid">
        <!-- Profile Information Card -->
        <div class="profile-card">
          <div class="card-header">
            <h2>Maklumat Profil</h2>
            <p>Kemaskini nama, emel dan maklumat hubungan anda</p>
          </div>

          <form @submit.prevent="updateProfile" class="profile-form">
            <div class="form-group">
              <label for="name">Nama Penuh <span class="required">*</span></label>
              <input
                type="text"
                id="name"
                v-model="profileForm.name"
                placeholder="Masukkan nama penuh"
                required
                :disabled="isUpdatingProfile"
              />
              <span v-if="profileErrors.name" class="error-message">{{ profileErrors.name[0] }}</span>
            </div>

            <div class="form-group">
              <label for="email">Emel <span class="required">*</span></label>
              <input
                type="email"
                id="email"
                v-model="profileForm.email"
                placeholder="Masukkan alamat emel"
                required
                :disabled="isUpdatingProfile"
              />
              <span v-if="profileErrors.email" class="error-message">{{ profileErrors.email[0] }}</span>
            </div>

            <div class="form-group">
              <label for="phone">No. Telefon</label>
              <input
                type="tel"
                id="phone"
                v-model="profileForm.phone"
                placeholder="Contoh: 0123456789"
                :disabled="isUpdatingProfile"
              />
              <span v-if="profileErrors.phone" class="error-message">{{ profileErrors.phone[0] }}</span>
            </div>

            <div class="form-group">
              <label for="ic_number">No. Kad Pengenalan</label>
              <input
                type="text"
                id="ic_number"
                v-model="profileForm.ic_number"
                placeholder="Contoh: 901234-12-3456"
                :disabled="isUpdatingProfile"
              />
              <span v-if="profileErrors.ic_number" class="error-message">{{ profileErrors.ic_number[0] }}</span>
            </div>

            <div class="form-actions">
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="isUpdatingProfile"
              >
                <span v-if="isUpdatingProfile">Menyimpan...</span>
                <span v-else>Simpan Perubahan</span>
              </button>
            </div>

            <div v-if="profileSuccess" class="success-message">
              ✓ {{ profileSuccess }}
            </div>
          </form>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card">
          <div class="card-header">
            <h2>Tukar Kata Laluan</h2>
            <p>Pastikan akaun anda menggunakan kata laluan yang selamat</p>
          </div>

          <form @submit.prevent="updatePassword" class="profile-form">
            <div class="form-group">
              <label for="current_password">Kata Laluan Semasa <span class="required">*</span></label>
              <div class="password-input">
                <input
                  :type="showCurrentPassword ? 'text' : 'password'"
                  id="current_password"
                  v-model="passwordForm.current_password"
                  placeholder="Masukkan kata laluan semasa"
                  required
                  :disabled="isUpdatingPassword"
                />
                <button 
                  type="button" 
                  class="toggle-password"
                  @click="showCurrentPassword = !showCurrentPassword"
                >
                  {{ showCurrentPassword ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
              <span v-if="passwordErrors.current_password" class="error-message">{{ passwordErrors.current_password[0] }}</span>
            </div>

            <div class="form-group">
              <label for="new_password">Kata Laluan Baru <span class="required">*</span></label>
              <div class="password-input">
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  id="new_password"
                  v-model="passwordForm.new_password"
                  placeholder="Minimum 8 aksara"
                  required
                  minlength="8"
                  :disabled="isUpdatingPassword"
                />
                <button 
                  type="button" 
                  class="toggle-password"
                  @click="showNewPassword = !showNewPassword"
                >
                  {{ showNewPassword ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
              <span v-if="passwordErrors.new_password" class="error-message">{{ passwordErrors.new_password[0] }}</span>
            </div>

            <div class="form-group">
              <label for="new_password_confirmation">Sahkan Kata Laluan Baru <span class="required">*</span></label>
              <div class="password-input">
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="new_password_confirmation"
                  v-model="passwordForm.new_password_confirmation"
                  placeholder="Taip semula kata laluan baru"
                  required
                  minlength="8"
                  :disabled="isUpdatingPassword"
                />
                <button 
                  type="button" 
                  class="toggle-password"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  {{ showConfirmPassword ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>

            <div class="form-actions">
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="isUpdatingPassword"
              >
                <span v-if="isUpdatingPassword">Menukar...</span>
                <span v-else>Tukar Kata Laluan</span>
              </button>
            </div>

            <div v-if="passwordSuccess" class="success-message">
              ✓ {{ passwordSuccess }}
            </div>

            <div v-if="passwordGeneralError" class="error-message">
              {{ passwordGeneralError }}
            </div>
          </form>
        </div>

        <!-- Account Information Card -->
        <div class="profile-card">
          <div class="card-header">
            <h2>Maklumat Akaun</h2>
            <p>Butiran akaun dan status anda</p>
          </div>

          <div class="account-info">
            <div class="info-row">
              <span class="info-label">Peranan:</span>
              <span class="info-value">
                <span class="badge" :class="roleBadgeClass">{{ roleDisplay }}</span>
              </span>
            </div>
            <div class="info-row" v-if="user?.district">
              <span class="info-label">Daerah:</span>
              <span class="info-value">{{ user.district }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Tarikh Daftar:</span>
              <span class="info-value">{{ formatDate(user?.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import axios from '@/api/axios'

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

// Profile Form
const profileForm = ref({
  name: '',
  email: '',
  phone: '',
  ic_number: ''
})

const profileErrors = ref({})
const profileSuccess = ref('')
const isUpdatingProfile = ref(false)

// Password Form
const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const passwordErrors = ref({})
const passwordSuccess = ref('')
const passwordGeneralError = ref('')
const isUpdatingPassword = ref(false)

// Password visibility toggles
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// Load user data into form
onMounted(() => {
  if (user.value) {
    profileForm.value = {
      name: user.value.name || '',
      email: user.value.email || '',
      phone: user.value.phone || '',
      ic_number: user.value.ic_number || ''
    }
  }
})

// Role display
const roleDisplay = computed(() => {
  const roleMap = {
    user: 'Pengguna',
    district_admin: 'Admin Daerah',
    state_admin: 'Admin Negeri',
    master_admin: 'Admin Utama'
  }
  return roleMap[user.value?.role] || 'Pengguna'
})

const roleBadgeClass = computed(() => {
  const classMap = {
    user: 'badge-user',
    district_admin: 'badge-district',
    state_admin: 'badge-state',
    master_admin: 'badge-master'
  }
  return classMap[user.value?.role] || 'badge-user'
})

// Update Profile
const updateProfile = async () => {
  profileErrors.value = {}
  profileSuccess.value = ''
  isUpdatingProfile.value = true

  try {
    const response = await axios.put('/profile', profileForm.value)
    
    if (response.data.success) {
      profileSuccess.value = response.data.message
      // Update user in store
      authStore.user = response.data.data
      
      // Clear success message after 3 seconds
      setTimeout(() => {
        profileSuccess.value = ''
      }, 3000)
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      profileErrors.value = error.response.data.errors
    } else {
      profileErrors.value = { general: [error.response?.data?.message || 'Ralat semasa mengemaskini profil'] }
    }
  } finally {
    isUpdatingProfile.value = false
  }
}

// Update Password
const updatePassword = async () => {
  passwordErrors.value = {}
  passwordSuccess.value = ''
  passwordGeneralError.value = ''
  isUpdatingPassword.value = true

  try {
    const response = await axios.put('/profile/password', passwordForm.value)
    
    if (response.data.success) {
      passwordSuccess.value = response.data.message
      
      // Clear form
      passwordForm.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      }
      
      // Clear success message after 3 seconds
      setTimeout(() => {
        passwordSuccess.value = ''
      }, 3000)
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors
    } else {
      passwordGeneralError.value = error.response?.data?.message || 'Ralat semasa menukar kata laluan'
    }
  } finally {
    isUpdatingPassword.value = false
  }
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('ms-MY', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}
</script>

<style scoped>
.profile-page {
  min-height: calc(100vh - 70px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 1rem;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #7f8c8d;
  font-size: 1.1rem;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.profile-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
}

.card-header h2 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.card-header p {
  opacity: 0.9;
  font-size: 0.95rem;
}

.profile-form {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.required {
  color: #e74c3c;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e0e6ed;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled {
  background-color: #f5f7fa;
  cursor: not-allowed;
}

.password-input {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem;
}

.form-actions {
  margin-top: 2rem;
}

.btn {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  width: 100%;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  display: block;
  color: #e74c3c;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.success-message {
  background-color: #d4edda;
  color: #155724;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
  border: 1px solid #c3e6cb;
}

.account-info {
  padding: 2rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #e0e6ed;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 600;
  color: #7f8c8d;
}

.info-value {
  color: #2c3e50;
  font-weight: 500;
}

.badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
}

.badge-user {
  background-color: #e3f2fd;
  color: #1976d2;
}

.badge-district {
  background-color: #f3e5f5;
  color: #7b1fa2;
}

.badge-state {
  background-color: #fff3e0;
  color: #f57c00;
}

.badge-master {
  background-color: #fce4ec;
  color: #c2185b;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .profile-card {
    border-radius: 8px;
  }

  .card-header,
  .profile-form,
  .account-info {
    padding: 1.5rem;
  }
}

@media (max-width: 480px) {
  .profile-page {
    padding: 1rem 0.5rem;
  }

  .page-header h1 {
    font-size: 1.75rem;
  }

  .page-header p {
    font-size: 1rem;
  }

  .card-header h2 {
    font-size: 1.25rem;
  }

  .form-group input {
    font-size: 0.95rem;
  }
}
</style>
