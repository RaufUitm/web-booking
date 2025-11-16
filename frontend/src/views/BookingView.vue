<template>
  <div class="booking-container">
    <h1>Book Your Appointment</h1>

    <div v-if="bookingSuccess" class="success-message">
      <h2>✓ Booking Confirmed!</h2>
      <p>Your booking has been successfully created. You will receive a confirmation email shortly.</p>
      <button @click="resetForm">Make Another Booking</button>
    </div>

    <form v-else @submit.prevent="submitBooking" class="booking-form">
      <!-- Step 1: Select Service -->
      <div class="form-section">
        <h2>1. Select a Service</h2>
        <div class="services-grid">
          <div
            v-for="service in services"
            :key="service.id"
            class="service-card"
            :class="{ selected: selectedService?.id === service.id }"
            @click="selectService(service)"
          >
            <h3>{{ service.name }}</h3>
            <p>{{ service.description }}</p>
            <p class="price">${{ service.price }}</p>
            <p class="duration">{{ service.duration }} minutes</p>
          </div>
        </div>
        <p v-if="!services.length && !loading" class="no-data">No services available</p>
      </div>

      <!-- Step 2: Select Date and Time -->
      <div v-if="selectedService" class="form-section">
        <h2>2. Select Date & Time</h2>
        <div class="date-picker">
          <label for="date">Select Date:</label>
          <input
            type="date"
            id="date"
            v-model="selectedDate"
            :min="today"
            @change="loadTimeSlots"
            required
          />
        </div>

        <div v-if="selectedDate" class="time-slots">
          <h3>Available Time Slots</h3>
          <div class="time-grid">
            <button
              v-for="slot in timeSlots"
              :key="slot.id"
              type="button"
              class="time-slot"
              :class="{ selected: selectedTimeSlot?.id === slot.id }"
              @click="selectTimeSlot(slot)"
            >
              {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
            </button>
          </div>
          <p v-if="!timeSlots.length && !loading" class="no-data">No available time slots for this date</p>
        </div>
      </div>

      <!-- Step 3: Customer Details -->
      <div v-if="selectedTimeSlot" class="form-section">
        <h2>3. Your Details</h2>
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input
            type="text"
            id="name"
            v-model="customerData.name"
            required
            placeholder="John Doe"
          />
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input
            type="email"
            id="email"
            v-model="customerData.email"
            required
            placeholder="john@example.com"
          />
        </div>

        <div class="form-group">
          <label for="phone">Phone Number *</label>
          <input
            type="tel"
            id="phone"
            v-model="customerData.phone"
            required
            placeholder="+1234567890"
          />
        </div>

        <div class="form-group">
          <label for="notes">Additional Notes (Optional)</label>
          <textarea
            id="notes"
            v-model="customerData.notes"
            rows="4"
            placeholder="Any special requests or information..."
          ></textarea>
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          {{ loading ? 'Processing...' : 'Confirm Booking' }}
        </button>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </form>

    <div v-if="loading" class="loading">Loading...</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'
import { storeToRefs } from 'pinia'

const bookingStore = useBookingStore()
const { services, timeSlots, loading, error } = storeToRefs(bookingStore)

const selectedService = ref(null)
const selectedTimeSlot = ref(null)
const selectedDate = ref('')
const bookingSuccess = ref(false)

const customerData = ref({
  name: '',
  email: '',
  phone: '',
  notes: ''
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

onMounted(() => {
  bookingStore.fetchServices()
})

const selectService = (service) => {
  selectedService.value = service
  selectedTimeSlot.value = null
  selectedDate.value = ''
}

const selectTimeSlot = (slot) => {
  selectedTimeSlot.value = slot
}

const loadTimeSlots = () => {
  selectedTimeSlot.value = null
  if (selectedDate.value) {
    bookingStore.fetchTimeSlots(selectedDate.value)
  }
}

const formatTime = (time) => {
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

const submitBooking = async () => {
  try {
    await bookingStore.createBooking({
      service_id: selectedService.value.id,
      time_slot_id: selectedTimeSlot.value.id,
      customer_name: customerData.value.name,
      customer_email: customerData.value.email,
      customer_phone: customerData.value.phone,
      notes: customerData.value.notes
    })
    bookingSuccess.value = true
  } catch (err) {
    console.error('Booking failed:', err)
  }
}

const resetForm = () => {
  selectedService.value = null
  selectedTimeSlot.value = null
  selectedDate.value = ''
  customerData.value = {
    name: '',
    email: '',
    phone: '',
    notes: ''
  }
  bookingSuccess.value = false
  bookingStore.fetchServices()
}
</script>

<style scoped>
.booking-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 2rem;
}

.booking-form {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.form-section {
  margin-bottom: 3rem;
}

.form-section h2 {
  color: #34495e;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3498db;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.service-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.service-card:hover {
  border-color: #3498db;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

.service-card.selected {
  border-color: #3498db;
  background-color: #e3f2fd;
}

.service-card h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.service-card p {
  color: #7f8c8d;
  margin: 0.5rem 0;
}

.price {
  font-size: 1.5rem;
  font-weight: bold;
  color: #27ae60;
  margin-top: 1rem;
}

.duration {
  font-size: 0.9rem;
  color: #95a5a6;
}

.date-picker {
  margin-bottom: 2rem;
}

.date-picker label {
  display: block;
  margin-bottom: 0.5rem;
  color: #34495e;
  font-weight: 500;
}

.date-picker input {
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  font-size: 1rem;
  width: 100%;
  max-width: 300px;
}

.time-slots h3 {
  color: #34495e;
  margin-bottom: 1rem;
}

.time-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.time-slot {
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.time-slot:hover {
  border-color: #3498db;
  background-color: #f8f9fa;
}

.time-slot.selected {
  border-color: #3498db;
  background-color: #3498db;
  color: white;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #34495e;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.submit-btn {
  background-color: #27ae60;
  color: white;
  padding: 1rem 2rem;
  border: none;
  border-radius: 4px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
  transition: background-color 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background-color: #229954;
}

.submit-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.success-message {
  background-color: #d4edda;
  border: 2px solid #28a745;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
}

.success-message h2 {
  color: #155724;
  margin-bottom: 1rem;
}

.success-message p {
  color: #155724;
  margin-bottom: 1.5rem;
}

.success-message button {
  background-color: #3498db;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  padding: 1rem;
  border-radius: 4px;
  margin-top: 1rem;
  border: 1px solid #f5c6cb;
}

.no-data {
  color: #7f8c8d;
  text-align: center;
  padding: 2rem;
  font-style: italic;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
}
</style>
