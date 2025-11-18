<template>
  <div class="booking-form">
    <h2>Tempah {{ facility?.name }}</h2>

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="booking_date">Tarikh Tempahan</label>
        <input
          id="booking_date"
          v-model="form.booking_date"
          type="date"
          required
          :min="minDate"
          @change="loadAvailableSlots"
        />
      </div>

      <div class="form-group">
        <label for="time_slot_id">Waktu</label>
        <select
          id="time_slot_id"
          v-model="form.time_slot_id"
          required
          :disabled="!form.booking_date || loadingSlots"
        >
          <option value="">Pilih waktu</option>
          <option
            v-for="slot in availableSlots"
            :key="slot.id"
            :value="slot.id"
            :disabled="slot.is_booked"
          >
            {{ slot.start_time }} - {{ slot.end_time }}
            {{ slot.is_booked ? '(Booked)' : '' }}
          </option>
        </select>
        <p v-if="loadingSlots" class="loading-text">Loading available slots...</p>
      </div>

      <div class="form-group">
        <label for="number_of_people">Bilangan Orang</label>
        <input
          id="number_of_people"
          v-model.number="form.number_of_people"
          type="number"
          required
          min="1"
          :max="facility?.capacity"
        />
        <small v-if="facility">Kapasiti maksimum: {{ facility.capacity }} orang</small>
      </div>

      <div class="form-group">
        <label for="notes">Catatan Tambahan (Pilihan)</label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="4"
          placeholder="Keperluan khas atau catatan..."
        ></textarea>
      </div>

      <div class="booking-summary">
        <h3>Ringkasan Tempahan</h3>
        <div class="summary-row">
          <span>Kemudahan:</span>
          <strong>{{ facility?.name }}</strong>
        </div>
        <div class="summary-row">
          <span>Harga sejam:</span>
          <strong>RM {{ facility?.price_per_hour }}</strong>
        </div>
        <div class="summary-row">
          <span>Tempoh:</span>
          <strong>{{ duration }} jam</strong>
        </div>
        <div class="summary-row total">
          <span>Jumlah:</span>
          <strong :style="{ color: currentDistrictColor.main }">RM {{ totalPrice }}</strong>
        </div>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-cancel">
          Batal
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="btn-submit"
          :style="{ background: currentDistrictColor.main, color: '#fff' }"
        >
          {{ loading ? 'Memproses...' : 'Sahkan Tempahan' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '@/stores/booking'
import { useDistrictStore } from '@/stores/district'
import useDistrictRoutes from '@/utils/districtRoutes'
import axios from '@/api/axios'

const props = defineProps({
  facility: {
    type: Object,
    required: true
  },
  selectedDate: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['cancel', 'success'])
const bookingStore = useBookingStore()
const districtStore = useDistrictStore()
const router = useRouter()
const { prefixPath } = useDistrictRoutes()

const form = ref({
  booking_date: '',
  time_slot_id: '',
  number_of_people: 1,
  notes: ''
})

onMounted(() => {
  if (props.selectedDate) {
    form.value.booking_date = props.selectedDate
    loadAvailableSlots()
  }
})

const loading = ref(false)
const loadingSlots = ref(false)
const error = ref('')
const availableSlots = ref([])

const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const duration = computed(() => {
  const slot = availableSlots.value.find(s => s.id === form.value.time_slot_id)
  if (!slot) return 0

  const start = new Date(`2000-01-01 ${slot.start_time}`)
  const end = new Date(`2000-01-01 ${slot.end_time}`)
  return (end - start) / (1000 * 60 * 60)
})

const totalPrice = computed(() => {
  if (!props.facility || !duration.value) return 0
  return (props.facility.price_per_hour * duration.value).toFixed(2)
})

watch(() => form.value.booking_date, (newDate) => {
  if (newDate) {
    form.value.time_slot_id = ''
    loadAvailableSlots()
  }
})

const loadAvailableSlots = async () => {
  if (!form.value.booking_date) return

  loadingSlots.value = true
  try {
    const response = await axios.get('/time-slots/available', {
      params: {
        facility_id: props.facility.id,
        date: form.value.booking_date
      }
    })
    availableSlots.value = response.data.data
  } catch (err) {
    console.error('Failed to load time slots:', err)
    error.value = 'Failed to load available time slots'
  } finally {
    loadingSlots.value = false
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    const booking = await bookingStore.createBooking({
      facility_id: props.facility.id,
      ...form.value
    })

    // Navigate to booking confirmation page for the current district
    const confPath = prefixPath(`/booking-confirmation/${booking.id}`)
    router.push(confPath)
    emit('success')
  } catch (err) {
    error.value = err.response?.data?.message || 'Booking failed. Please try again.'
  } finally {
    loading.value = false
  }
}

const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])
</script>

<style scoped>
.booking-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 30px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
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

input,
select,
textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #FF8C00;
  box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
}

select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

small {
  display: block;
  margin-top: 5px;
  color: #666;
  font-size: 12px;
}

.loading-text {
  margin-top: 5px;
  color: #FF8C00;
  font-size: 12px;
}

.booking-summary {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 4px;
  margin: 20px 0;
}

.booking-summary h3 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 18px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 14px;
}

.summary-row.total {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
  font-size: 18px;
  color: #FF8C00;
}

.error-message {
  padding: 10px;
  background-color: #ffebee;
  color: #c62828;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-cancel {
  background-color: #f5f5f5;
  color: #666;
}

.btn-cancel:hover {
  background-color: #e0e0e0;
}

.btn-submit {
  background-color: #FF8C00;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background-color: #E67E00;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(255, 140, 0, 0.3);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
