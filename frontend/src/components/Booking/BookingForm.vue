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
          <label>Jenis Tempahan</label>
          <div class="radio-group">
            <label><input type="radio" value="per_day" v-model="form.booking_type" /> Per Hari</label>
            <label><input type="radio" value="per_hour" v-model="form.booking_type" /> Per Jam</label>
          </div>

          <!-- Per-hour inputs -->
          <div v-if="form.booking_type === 'per_hour'" class="per-hour">
            <label>Mulai (jam)</label>
            <select v-model="form.start_time">
              <option value="">Pilih mula</option>
              <option v-for="t in hourOptions" :key="t" :value="t">{{ t }}</option>
            </select>

            <label>Tamat (jam)</label>
            <select v-model="form.end_time">
              <option value="">Pilih tamat</option>
              <option v-for="t in hourOptions" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
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
  booking_type: 'per_hour',
  start_time: '',
  end_time: '',
  number_of_people: 1,
  notes: ''
})

onMounted(() => {
  if (props.selectedDate) {
    form.value.booking_date = props.selectedDate
  }
})

const loading = ref(false)
const error = ref('')

const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const duration = computed(() => {
  if (form.value.booking_type === 'per_hour') {
    if (!form.value.start_time || !form.value.end_time) return 0
    const start = new Date(`2000-01-01 ${form.value.start_time}`)
    const end = new Date(`2000-01-01 ${form.value.end_time}`)
    return (end - start) / (1000 * 60 * 60)
  }
  // per_day considered as full day (24 hours)
  if (form.value.booking_type === 'per_day') return 24
  return 0
})

const totalPrice = computed(() => {
  if (!props.facility || !duration.value) return 0
  if (form.value.booking_type === 'per_day') {
    // use price_per_day if available, otherwise fall back to price_per_hour * 24
    const perDay = parseFloat(props.facility.price_per_day || 0)
    if (perDay > 0) return perDay.toFixed(2)
    return (props.facility.price_per_hour * duration.value).toFixed(2)
  }
  return (props.facility.price_per_hour * duration.value).toFixed(2)
})

watch(() => form.value.booking_date, (newDate) => {
  if (newDate) {
    // nothing to do now for per_day/per_hour
  }
})

// loadAvailableSlots removed: per-slot flow is no longer supported

// generate hour options from 07:00 to 24:00 in 1-hour steps
const hourOptions = []
for (let h = 7; h <= 24; h++) {
  const label = h === 24 ? '24:00' : (h < 10 ? `0${h}:00` : `${h}:00`)
  hourOptions.push(label)
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    // Build payload depending on booking_type
    const payload = {
      facility_id: props.facility.id,
      booking_type: form.value.booking_type,
      booking_date: form.value.booking_date,
      number_of_people: form.value.number_of_people,
      notes: form.value.notes
    }

    if (form.value.booking_type === 'per_hour') {
      payload.start_time = form.value.start_time
      payload.end_time = form.value.end_time
    }

    const booking = await bookingStore.createBooking(payload)

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
