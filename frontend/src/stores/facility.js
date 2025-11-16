// src/stores/facility.js
import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useFacilityStore = defineStore('facility', {
  state: () => ({
    facilities: [],
    categories: [],
    selectedFacility: null,
    loading: false,
    error: null,
    filters: {
      category: null,
      search: '',
      availability: null
    }
  }),

  getters: {
    filteredFacilities: (state) => {
      let filtered = state.facilities

      // Filter by category
      if (state.filters.category) {
        filtered = filtered.filter(f => f.category_id === state.filters.category)
      }

      // Filter by search
      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        filtered = filtered.filter(f =>
          f.name.toLowerCase().includes(search) ||
          f.description?.toLowerCase().includes(search)
        )
      }

      // Filter by availability
      if (state.filters.availability !== null) {
        filtered = filtered.filter(f => f.is_available === state.filters.availability)
      }

      return filtered
    },

    facilitiesByCategory: (state) => {
      const grouped = {}
      state.categories.forEach(cat => {
        grouped[cat.name] = state.facilities.filter(f => f.category_id === cat.id)
      })
      return grouped
    }
  },

  actions: {
    async fetchFacilities() {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/facilities')
        const data = response.data.data || response.data
        // Handle pagination - extract data array from paginated response
        if (data.data && Array.isArray(data.data)) {
          this.facilities = data.data
        } else if (Array.isArray(data)) {
          this.facilities = data
        } else {
          this.facilities = []
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch facilities'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchFacility(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/facilities/${id}`)
        this.selectedFacility = response.data.data || response.data
        return this.selectedFacility
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch facility'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchCategories() {
      try {
        const response = await api.get('/categories')
        const iconMap = {
          'Dewan & Panggung': '🏛️',
          'Padang & Gelanggang': '⚽',
          'Pusat Komuniti': '🏘️',
          'Kemudahan Sukan': '🏸'
        }
        // Add icons to categories and use facilities_count
        this.categories = (response.data.data || response.data).map(cat => ({
          ...cat,
          icon: iconMap[cat.name] || '📍',
          facility_count: cat.facilities_count || 0
        }))
      } catch (error) {
        console.error('Failed to fetch categories:', error)
      }
    },

    setFilter(filterType, value) {
      this.filters[filterType] = value
    },

    clearFilters() {
      this.filters = {
        category: null,
        search: '',
        availability: null
      }
    }
  }
})
