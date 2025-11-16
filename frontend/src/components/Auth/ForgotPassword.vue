<template>
  <div class="forgot-password-form">
    <h2>Forgot Password</h2>
    <p class="subtitle">Enter your email address and we'll send you a reset link.</p>

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          placeholder="Enter your email"
        />
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="success" class="success-message">
        {{ success }}
      </div>

      <button type="submit" :disabled="loading" class="btn-primary">
        {{ loading ? 'Sending...' : 'Send Reset Link' }}
      </button>

      <div class="form-links">
        <router-link to="/login">Back to Login</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = ref({
  email: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    await authStore.forgotPassword(form.value.email)
    success.value = 'Password reset link sent to your email!'
    form.value.email = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to send reset link. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-password-form {
  max-width: 400px;
  margin: 50px auto;
  padding: 30px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  margin-bottom: 10px;
  color: #333;
}

.subtitle {
  text-align: center;
  margin-bottom: 30px;
  color: #666;
  font-size: 14px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

input:focus {
  outline: none;
  border-color: #2d5f2e;
}

.error-message {
  padding: 10px;
  background-color: #ffebee;
  color: #c62828;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 14px;
}

.success-message {
  padding: 10px;
  background-color: #e8f5e9;
  color: #2e7d32;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 14px;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background-color: #2d5f2e;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #244d25;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-links {
  margin-top: 20px;
  text-align: center;
  font-size: 14px;
}

.form-links a {
  color: #2d5f2e;
  text-decoration: none;
}

.form-links a:hover {
  text-decoration: underline;
}
</style>
