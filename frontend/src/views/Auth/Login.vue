<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Log Masuk</h1>
        <p>Masukkan email dan kata laluan anda</p>
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label for="email">Email</label>
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
          <label for="password">Kata Laluan</label>
          <input
            type="password"
            id="password"
            v-model="formData.password"
            required
            placeholder="••••••••"
            :disabled="loading"
          />
        </div>

        <div class="form-options">
          <label class="checkbox">
            <input type="checkbox" v-model="rememberMe" />
            <span>Ingat saya</span>
          </label>
          <router-link to="/forgot-password" class="forgot-link">
            Lupa kata laluan?
          </router-link>
        </div>

        <button type="submit" class="btn-submit" :disabled="loading">
          {{ loading ? 'Memuatkan...' : 'Log Masuk' }}
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </form>

      <div class="auth-footer">
        <p>Belum ada akaun? <router-link to="/register">Daftar sekarang</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { loading, error } = storeToRefs(authStore)

const formData = ref({
  email: '',
  password: ''
})

const rememberMe = ref(false)

const handleLogin = async () => {
  try {
    await authStore.login(formData.value)

    // Redirect to intended page or facilities
    const redirect = route.query.redirect || '/facilities'
    router.push(redirect)
  } catch (err) {
    console.error('Login failed:', err)
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
  background: linear-gradient(135deg, #FF8C00 0%, #D77800 100%);
}

.auth-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  padding: 3rem;
  width: 100%;
  max-width: 450px;
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-header h1 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 2rem;
}

.auth-header p {
  color: #7f8c8d;
  font-size: 1rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #2c3e50;
  font-weight: 600;
  font-size: 0.95rem;
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

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #2c3e50;
  font-size: 0.95rem;
}

.checkbox input {
  cursor: pointer;
}

.forgot-link {
  color: #D77800;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

.btn-submit {
  background: linear-gradient(135deg, #FF8C00 0%, #D77800 100%);
  color: white;
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(215, 120, 0, 0.4);
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
}

.auth-footer {
  margin-top: 2rem;
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
