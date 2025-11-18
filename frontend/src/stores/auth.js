// src/stores/auth.js
import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => {
      const adminRoles = ['admin', 'district_admin', 'state_admin', 'master_admin']
      return adminRoles.includes(state.user?.role)
    },
    userName: (state) => state.user?.name || 'Guest',
    userRole: (state) => state.user?.role || 'user',
    userDistrict: (state) => state.user?.district || null,

    // Authorization getters
    isMasterAdmin: (state) => state.user?.role === 'master_admin',
    isStateAdmin: (state) => state.user?.role === 'state_admin',
    isDistrictAdmin: (state) => state.user?.role === 'district_admin',

    // Permission checkers
    canManageAllDistricts: (state) => {
      return ['master_admin', 'state_admin'].includes(state.user?.role)
    },
    canAssignRoles: (state) => {
      return ['master_admin', 'state_admin'].includes(state.user?.role)
    },
    canAccessDistrict: (state) => (districtName) => {
      if (['master_admin', 'state_admin'].includes(state.user?.role)) {
        return true // Can access all districts
      }
      if (state.user?.role === 'district_admin') {
        return state.user?.district === districtName // Only their district
      }
      return false
    }
  },

  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/register', {
          name: userData.name,
          email: userData.email,
          phone: userData.phone,
          ic_number: userData.ic_number,
          password: userData.password,
          password_confirmation: userData.password_confirmation
        })

        // Handle Laravel response format with data wrapper
        this.token = response.data.data?.token || response.data.token
        this.user = response.data.data?.user || response.data.user
        localStorage.setItem('token', this.token)
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || error.response?.data?.errors || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/login', {
          email: credentials.email,
          password: credentials.password
        })

        // Handle Laravel response format with data wrapper
        this.token = response.data.data?.token || response.data.token
        this.user = response.data.data?.user || response.data.user
        localStorage.setItem('token', this.token)
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        delete api.defaults.headers.common['Authorization']
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const response = await api.get('/me')
        this.user = response.data
      } catch (error) {
        console.error('Fetch user error:', error)
        this.logout()
      }
    },

    initializeAuth() {
      if (this.token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        this.fetchUser()
      }
    }
  }
})
