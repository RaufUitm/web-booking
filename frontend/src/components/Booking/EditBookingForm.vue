<template>
  <div class="modal-backdrop">
    <div class="modal">
      <h3>Edit Tempahan</h3>
      <form @submit.prevent="submit">
        <div class="form-row">
          <label>Tarikh Tempahan</label>
          <input type="date" v-model="form.booking_date" :min="minDate" required />
        </div>

        <div class="form-row">
          <label>Jenis Tempahan</label>
          <div class="radio-group">
            <label><input type="radio" value="per_day" v-model="form.booking_type" /> Per Hari</label>
            <label><input type="radio" value="per_hour" v-model="form.booking_type" /> Per Jam</label>
          </div>
        </div>

        <div v-if="form.booking_type === 'per_hour'" class="form-row time-row">
          <div>
            <label>Mulai (jam)</label>
            <select v-model="form.start_time" @change="onStartChange">
              <option value="">Pilih mula</option>
              <option v-for="t in hourOptions" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div>
            <label>Tamat (jam)</label>
            <select v-model="form.end_time">
              <option value="">Pilih tamat</option>
              <option v-for="t in hourOptions" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <label>Bilangan Orang</label>
          <input type="number" v-model.number="form.number_of_people" min="1" />
        </div>

        <div class="form-row">
          <label>Catatan</label>
          <textarea v-model="form.notes"></textarea>
        </div>

        <div v-if="error" class="error-message">{{ error }}</div>

        <div class="actions">
          <button type="button" class="btn-secondary" @click="$emit('cancel')">Batal</button>
          <button type="submit" class="btn-primary" :disabled="loading">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, watch, onMounted } from 'vue'
import { useBookingStore } from '@/stores/booking'

const props = defineProps({
  booking: { type: Object, required: true },
  selectedDate: { type: String, default: null }
})

const emit = defineEmits(['saved', 'cancel'])
const bookingStore = useBookingStore()

const form = ref({
  booking_date: props.booking.booking_date ? props.booking.booking_date.split('T')[0] : '',
  booking_type: props.booking.booking_type || 'per_hour',
  start_time: props.booking.start_time || '',
  end_time: props.booking.end_time || '',
  number_of_people: props.booking.number_of_people || 1,
  notes: props.booking.notes || ''
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

// generate hour options from 07:00 to 24:00 in 1-hour steps
const hourOptions = []
for (let h = 7; h <= 24; h++) {
  const label = h === 24 ? '24:00' : (h < 10 ? `0${h}:00` : `${h}:00`)
  hourOptions.push(label)
}

const onStartChange = () => {
  if (form.value.end_time && form.value.start_time && form.value.end_time < form.value.start_time) {
    form.value.end_time = form.value.start_time
  }
}

const submit = async () => {
  loading.value = true
  error.value = ''

  try {
    const payload = {
      booking_date: form.value.booking_date,
      booking_type: form.value.booking_type,
      number_of_people: form.value.number_of_people,
      notes: form.value.notes
    }

    if (form.value.booking_type === 'per_hour') {
      payload.start_time = form.value.start_time
      payload.end_time = form.value.end_time
      if (!payload.start_time || !payload.end_time) {
        throw new Error('Sila pilih masa mula dan tamat.')
      }
      if (payload.end_time <= payload.start_time) {
        throw new Error('Masa tamat mesti selepas masa mula.')
      }
    } else {
      payload.start_time = null
      payload.end_time = null
    }

    const updated = await bookingStore.updateBooking(props.booking.id, payload)
    emit('saved', updated)
  } catch (err) {
    error.value = err.response?.data?.message || 'Booking failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 520px;
  max-width: 95%;
}
.form-row { margin-bottom: 12px; display: flex; flex-direction: column; }
.time-row { display: flex; gap: 12px; }
.radio-group { display: flex; gap: 12px; }
label { font-weight: 600; margin-bottom: 6px; }
input, select, textarea { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
textarea { min-height: 80px; }
.error-message { padding: 8px; background: #ffebee; color: #c62828; border-radius: 4px; }
.actions { display:flex; gap: 12px; justify-content: flex-end; margin-top: 12px; }
.btn-secondary { background: transparent; border: 1px solid #ccc; padding: 8px 12px; border-radius: 4px; }
.btn-primary { background: #FF8C00; color: white; padding: 8px 12px; border: none; border-radius: 4px; }
</style>
