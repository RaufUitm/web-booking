<template>
  <div class="booking-summary">
    <h2>Booking Summary</h2>

    <div class="summary-section">
      <h3>Facility Details</h3>
      <div class="detail-row">
        <span class="label">Facility:</span>
        <span class="value">{{ booking.facility?.name }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Location:</span>
        <span class="value">{{ booking.facility?.location }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Category:</span>
        <span class="value">{{ booking.facility?.category?.name }}</span>
      </div>
    </div>

    <div class="summary-section">
      <h3>Booking Information</h3>
      <div class="detail-row">
        <span class="label">Date:</span>
        <span class="value">{{ formatDate(booking.booking_date) }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Time:</span>
        <span class="value">
          {{ booking.timeSlot?.start_time }} - {{ booking.timeSlot?.end_time }}
        </span>
      </div>
      <div class="detail-row">
        <span class="label">Number of People:</span>
        <span class="value">{{ booking.number_of_people }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Status:</span>
        <span :class="['value', 'status', booking.status]" :style="{ color: districtColor, borderColor: districtColor }">
          {{ formatStatus(booking.status) }}
        </span>
      </div>
      <div v-if="booking.notes" class="detail-row notes">
        <span class="label">Notes:</span>
        <span class="value">{{ booking.notes }}</span>
      </div>
    </div>

    <div class="summary-section pricing">
      <h3>Pricing</h3>
      <div class="detail-row">
        <span class="label">Price per hour:</span>
        <span class="value">${{ booking.facility?.price_per_hour }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Duration:</span>
        <span class="value">{{ duration }} hour(s)</span>
      </div>
      <div class="detail-row total">
        <span class="label">Total Amount:</span>
        <span class="value" :style="{ color: districtColor }">${{ totalPrice }}</span>
      </div>
    </div>

    <div v-if="showActions" class="summary-actions">
      <button
        v-if="canCancel"
        @click="$emit('cancel')"
        class="btn-cancel"
        :style="{ backgroundColor: districtColor, borderColor: districtColor }"
      >
        Cancel Booking
      </button>
      <button
        v-if="canEdit"
        @click="$emit('edit')"
        class="btn-edit"
        :style="{ backgroundColor: districtColor, borderColor: districtColor }"
      >
        Edit Booking
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps, defineEmits } from 'vue'
import { useDistrictStore } from '@/stores/district'

const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: false
  }
})

defineEmits(['cancel', 'edit'])

const districtStore = useDistrictStore()
const districtColor = computed(() => districtStore.districtColor)

const duration = computed(() => {
  if (!props.booking.timeSlot) return 0

  const start = new Date(`2000-01-01 ${props.booking.timeSlot.start_time}`)
  const end = new Date(`2000-01-01 ${props.booking.timeSlot.end_time}`)
  return (end - start) / (1000 * 60 * 60)
})

const totalPrice = computed(() => {
  if (!props.booking.facility || !duration.value) return 0
  return (props.booking.facility.price_per_hour * duration.value).toFixed(2)
})

const canCancel = computed(() => {
  return ['pending', 'confirmed'].includes(props.booking.status)
})

const canEdit = computed(() => {
  return props.booking.status === 'pending'
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    completed: 'Completed'
  }
  return statusMap[status] || status
}
</script>

<style scoped>
.booking-summary {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  margin: 0 0 30px 0;
  color: #333;
  font-size: 24px;
  text-align: center;
}

.summary-section {
  margin-bottom: 25px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.summary-section:last-of-type {
  border-bottom: none;
}

h3 {
  margin: 0 0 15px 0;
  color: #555;
  font-size: 16px;
  font-weight: 600;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 14px;
}

.detail-row.notes {
  flex-direction: column;
  gap: 5px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.label {
  color: #666;
}

.value {
  color: #333;
  font-weight: 500;
  text-align: right;
}

.detail-row.notes .value {
  text-align: left;
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 4px;
}

.status {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status.pending {
  background-color: #fff3e0;
  color: #f57c00;
}

.status.confirmed {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status.cancelled {
  background-color: #ffebee;
  color: #c62828;
}

.status.completed {
  background-color: #e3f2fd;
  color: #1976d2;
}

.pricing {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border-bottom: none;
}

.detail-row.total {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
  font-size: 18px;
}

.detail-row.total .value {
  color: #FF8C00;
  font-weight: 700;
}

.summary-actions {
  display: flex;
  gap: 15px;
  margin-top: 25px;
}

.btn-cancel,
.btn-edit {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-cancel {
  background-color: #f44336;
  color: white;
}

.btn-cancel:hover {
  background-color: #d32f2f;
}

.btn-edit {
  background-color: #2196F3;
  color: white;
}

.btn-edit:hover {
  background-color: #1976D2;
}

@media (max-width: 768px) {
  .booking-summary {
    padding: 20px;
  }

  .detail-row {
    flex-direction: column;
    gap: 5px;
  }

  .value {
    text-align: left;
  }
}
</style>
