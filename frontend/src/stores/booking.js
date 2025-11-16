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
        this.bookings.push(response.data)
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchBookings() {
      this.loading = true
      try {
        const response = await api.get('/bookings')
        this.bookings = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    }
  }
})
