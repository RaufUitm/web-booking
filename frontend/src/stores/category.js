import { defineStore } from 'pinia'
import axios from '@/api/axios'

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [],
    currentCategory: null,
    loading: false,
    error: null
  }),

  getters: {
    getCategoryById: (state) => (id) => {
      return state.categories.find(category => category.id === id)
    }
  },

  actions: {
    async fetchCategories() {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get('/categories')
        if (response.data.success) {
          this.categories = response.data.data
        }
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch categories'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchCategoryById(id) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get(`/categories/${id}`)
        if (response.data.success) {
          this.currentCategory = response.data.data
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch category'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createCategory(categoryData) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.post('/categories', categoryData)
        if (response.data.success) {
          this.categories.push(response.data.data)
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create category'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateCategory(id, categoryData) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.put(`/categories/${id}`, categoryData)
        if (response.data.success) {
          const index = this.categories.findIndex(c => c.id === id)
          if (index !== -1) {
            this.categories[index] = response.data.data
          }
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update category'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteCategory(id) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.delete(`/categories/${id}`)
        if (response.data.success) {
          this.categories = this.categories.filter(c => c.id !== id)
          return true
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete category'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
