<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Daftar Akaun Baharu</h1>
        <p>Isikan maklumat untuk membuat akaun</p>
      </div>



      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label for="name">Nama Penuh <span class="required">*</span></label>
          <input
            type="text"
            id="name"
            v-model="formData.name"
            required
            placeholder="Ahmad Bin Ali"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="ic_number">No. Kad Pengenalan</label>
          <input
            type="text"
            id="ic_number"
            v-model="formData.ic_number"
            placeholder="990101-01-1234 (Pilihan)"
            maxlength="14"
            :disabled="loading"
            @input="formatIC"
          />
          <small>Format: YYMMDD-PP-NNNN (Pilihan)</small>
        </div>

        <div class="form-group">
          <label for="phone">No. Telefon</label>
          <input
            type="tel"
            id="phone"
            v-model="formData.phone"
            placeholder="0123456789 (Pilihan)"
            :disabled="loading"
          />
          <small>Format: 012-3456789</small>
        </div>

        <div class="form-group">
          <label for="email">Email <span class="required">*</span></label>
          <input
            type="email"
            id="email"
            v-model="formData.email"
            required
            placeholder="nama@example.com"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Laluan <span class="required">*</span></label>
          <input
            type="password"
            id="password"
            v-model="formData.password"
            required
            placeholder="Minimum 8 aksara"
            minlength="8"
            :disabled="loading"
          />
          <small>Minimum 8 aksara</small>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Sahkan Kata Laluan <span class="required">*</span></label>
          <input
            type="password"
            id="password_confirmation"
            v-model="formData.password_confirmation"
            required
            placeholder="Ulang kata laluan"
            :disabled="loading"
          />
        </div>

        <div class="form-group checkbox-group">
          <label class="checkbox">
            <input type="checkbox" v-model="agreedToTerms" required />
            <span>Saya bersetuju dengan <a href="#" @click.prevent>Terma & Syarat</a></span>
          </label>
        </div>

        <button type="submit" class="btn-submit" :disabled="loading || !agreedToTerms">
          {{ loading ? 'Mendaftar...' : 'Daftar' }}
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div v-if="validationError" class="error-message">
          {{ validationError }}
        </div>
      </form>

      <div class="auth-footer">
        <p>Sudah ada akaun? <router-link :to="prefixPath('/login')">Log masuk di sini</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import useDistrictRoutes from '@/utils/districtRoutes'

const router = useRouter()
const authStore = useAuthStore()
const { loading, error } = storeToRefs(authStore)
const { prefixPath } = useDistrictRoutes()

const formData = ref({
  name: '',
  ic_number: '',
  phone: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const agreedToTerms = ref(false)
const validationError = ref('')

const formatIC = (e) => {
  let value = e.target.value.replace(/\D/g, '')
  if (value.length > 6) {
    value = value.slice(0, 6) + '-' + value.slice(6)
  }
  if (value.length > 9) {
    value = value.slice(0, 9) + '-' + value.slice(9, 13)
  }
  formData.value.ic_number = value
}

const handleRegister = async () => {
  validationError.value = ''

  // Validate passwords match
  if (formData.value.password !== formData.value.password_confirmation) {
    validationError.value = 'Kata laluan tidak sepadan'
    return
  }

  // Validate IC format if provided
  if (formData.value.ic_number) {
    const icPattern = /^[0-9]{6}-[0-9]{2}-[0-9]{4}$/
    if (!icPattern.test(formData.value.ic_number)) {
      validationError.value = 'Format No. Kad Pengenalan tidak sah (YYMMDD-PP-NNNN)'
      return
    }
  }

  try {
    await authStore.register(formData.value)
    router.push('/')
  } catch (err) {
    console.error('Registration failed:', err)
  }
}
</script>

<style scoped>
.auth-container {
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #f5f5f5;
}

.auth-card {
  background: #f7f7f7;
  border-radius: clamp(10px, 1.2vw, 12px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  padding: clamp(2rem, 3.5vw, 3rem);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  border: 2px solid #222;
}

.auth-header {
  text-align: center;
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
}

.auth-header h1 {
  color: #2c3e50;
  margin-bottom: clamp(0.4rem, 0.6vw, 0.5rem);
  font-size: clamp(1.4rem, 2.2vw, 1.8rem);
}

.auth-header p {
  color: #7f8c8d;
  font-size: clamp(0.9rem, 1.1vw, 0.95rem);
}

.flag-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 2rem;
}

.flag-img {
  width: 110px;
  height: auto;
  filter: grayscale(1) contrast(1.2);
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: clamp(1rem, 1.5vw, 1.25rem);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: clamp(0.4rem, 0.6vw, 0.5rem);
}

.form-group label {
  color: #2c3e50;
  font-weight: 600;
  font-size: clamp(0.9rem, 1.1vw, 0.95rem);
}

.required {
  color: #e74c3c;
}

.form-group input {
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #D77800;
  box-shadow: 0 0 0 3px rgba(215, 120, 0, 0.1);
}

.form-group input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.form-group small {
  color: #7f8c8d;
  font-size: 0.85rem;
}

.checkbox-group {
  margin-top: 0.5rem;
}

.checkbox {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  cursor: pointer;
  color: #2c3e50;
  font-size: 0.95rem;
  line-height: 1.5;
}

.checkbox input {
  margin-top: 0.25rem;
  cursor: pointer;
}

.checkbox a {
  color: #D77800;
  text-decoration: none;
}

.checkbox a:hover {
  text-decoration: underline;
}

.btn-submit {
  background: #111;
  color: #fff;
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin-top: 0.5rem;
  letter-spacing: 1px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}

.btn-submit:hover:not(:disabled) {
  background: #222;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background-color: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  border: 1px solid #fcc;
  font-size: 0.95rem;
}

.auth-footer {
  margin-top: 1.5rem;
  text-align: center;
  color: #7f8c8d;
}

.auth-footer a {
  color: #D77800;
  text-decoration: none;
  font-weight: 600;
}

.auth-footer a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 2rem 1.5rem;
  }

  .auth-header h1 {
    font-size: 1.5rem;
  }
}
</style>
