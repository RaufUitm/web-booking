import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useBookingStore = defineStore('booking', {
  state: () => ({
    services: [],
    timeSlots: [],
    bookings: [],
    selectedService: null,
    selectedTimeSlot: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchServices() {
      this.loading = true
      try {
        const response = await api.get('/services')
        this.services = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async fetchTimeSlots(date = null) {
      this.loading = true
      try {
        const params = date ? { date } : {}
        const response = await api.get('/time-slots', { params })
        this.timeSlots = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async createBooking(bookingData) {
      this.loading = true
      try {
        const response = await api.post('/bookings', bookingData)
        const booking = response.data.data || response.data
        this.bookings.push(booking)
        return booking
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchBookings() {
      this.loading = true
      try {
        const response = await api.get('/bookings')
        const data = response.data.data || response.data
        // Handle pagination
        if (data.data && Array.isArray(data.data)) {
          this.bookings = data.data
        } else if (Array.isArray(data)) {
          this.bookings = data
        } else {
          this.bookings = []
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message
      } finally {
        this.loading = false
      }
    },

    async cancelBooking(id) {
      this.loading = true
      try {
        const response = await api.post(`/bookings/${id}/cancel`)
        const cancelledBooking = response.data.data || response.data
        // Update the booking in the bookings array
        const index = this.bookings.findIndex(b => b.id === id)
        if (index !== -1) {
          this.bookings[index] = cancelledBooking
        }
        return cancelledBooking
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
