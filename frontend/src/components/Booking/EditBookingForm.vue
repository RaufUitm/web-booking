<template>
  <div class="modal-backdrop">
    <div class="modal">
      <h3>Edit Tempahan</h3>
      <form @submit.prevent="submit">
        <div class="form-row">
          <label>Tarikh</label>
          <input type="date" v-model="form.booking_date" required />
        </div>

        <div class="form-row">
          <label>Jenis Tempahan</label>
          <select v-model="form.booking_type">
            <option value="per_day">Sepanjang Hari</option>
            <option value="per_hour">Per Jam</option>
          </select>
        </div>

        <div v-if="form.booking_type !== 'per_day'" class="form-row time-row">
          <div>
            <label>Mula</label>
            <input type="time" v-model="form.start_time" required />
          </div>
          <div>
            <label>Tamat</label>
            <input type="time" v-model="form.end_time" required />
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

        <div class="actions">
          <button type="button" class="btn-secondary" @click="$emit('cancel')">Batal</button>
          <button type="submit" class="btn-primary" :disabled="loading">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, toRefs, ref } from 'vue'
import { useBookingStore } from '@/stores/booking'

const props = defineProps({
  booking: { type: Object, required: true }
})
const emit = defineEmits(['saved', 'cancel'])

const bookingStore = useBookingStore()

const form = reactive({
  booking_date: props.booking.booking_date ? props.booking.booking_date.split('T')[0] : '',
  booking_type: props.booking.booking_type || 'per_hour',
  start_time: props.booking.start_time || '',
  end_time: props.booking.end_time || '',
  number_of_people: props.booking.number_of_people || 1,
  notes: props.booking.notes || ''
})

const loading = ref(false)

const submit = async () => {
  loading.value = true
  try {
    const payload = {
      booking_date: form.booking_date,
      booking_type: form.booking_type,
      number_of_people: form.number_of_people,
      notes: form.notes
    }
    if (form.booking_type !== 'per_day') {
      payload.start_time = form.start_time
      payload.end_time = form.end_time
    } else {
      payload.start_time = null
      payload.end_time = null
    }

    const updated = await bookingStore.updateBooking(props.booking.id, payload)
    emit('saved', updated)
  } catch (error) {
    console.error('Failed to update booking:', error)
    alert('Gagal mengemas kini tempahan. Sila cuba lagi.')
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
  width: 480px;
  max-width: 95%;
}
.form-row {
  margin-bottom: 12px;
  display: flex;
  flex-direction: column;
}
.time-row {
  display: flex;
  gap: 12px;
}
label { font-weight: 600; margin-bottom: 6px; }
input, select, textarea { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
textarea { min-height: 80px; }
.actions { display:flex; gap: 12px; justify-content: flex-end; margin-top: 12px; }
.btn-secondary { background: transparent; border: 1px solid #ccc; padding: 8px 12px; border-radius: 4px; }
.btn-primary { background: #FF8C00; color: white; padding: 8px 12px; border: none; border-radius: 4px; }
</style>
