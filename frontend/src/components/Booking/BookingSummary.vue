<template>
  <div class="booking-summary">
    <h2>Ringkasan Tempahan</h2>

    <div class="summary-section">
      <h3>Butiran Kemudahan</h3>
      <div class="detail-row">
        <span class="label">Kemudahan:</span>
        <span class="value">{{ booking.facility?.name }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Lokasi:</span>
        <span class="value">{{ booking.facility?.location }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Kategori:</span>
        <span class="value">{{ booking.facility?.category?.name }}</span>
      </div>
    </div>

    <div class="summary-section">
      <h3>Maklumat Tempahan</h3>
      <div class="detail-row">
        <span class="label">Tarikh:</span>
        <span class="value">{{ formatDate(booking.booking_date) }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Masa:</span>
        <span class="value">
          <template v-if="booking.booking_type === 'per_day'">Sepanjang Hari</template>
          <template v-else>{{ booking.start_time || '' }} - {{ booking.end_time || '' }}</template>
        </span>
      </div>
      <div class="detail-row">
        <span class="label">Bilangan Orang:</span>
        <span class="value">{{ booking.number_of_people }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Status:</span>
        <span :class="['value', 'status', booking.status]" :style="{ color: districtColor, borderColor: districtColor }">
          {{ formatStatus(booking.status) }}
        </span>
      </div>
      <div v-if="booking.notes" class="detail-row notes">
        <span class="label">Catatan:</span>
        <span class="value">{{ booking.notes }}</span>
      </div>
    </div>

    <div class="summary-section pricing">
      <h3>Harga</h3>
      <div class="detail-row">
        <span class="label">Harga setiap jam:</span>
        <span class="value">{{ formattedPricePerHour }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Tempoh:</span>
        <span class="value">{{ duration }} jam</span>
      </div>
      <div class="detail-row total">
        <span class="label">Jumlah:</span>
        <span class="value" :style="{ color: districtColor }">{{ formattedTotal }}</span>
      </div>
    </div>

    <div v-if="showActions" class="summary-actions">
      <button
        v-if="showPayButton"
        @click="$emit('payment')"
        class="btn-payment"
        :style="{ backgroundColor: districtColor, borderColor: districtColor }"
      >
        Sahkan Pembayaran
      </button>
      <button
        v-if="canEdit"
        @click="$emit('edit')"
        class="btn-edit"
        :style="{ backgroundColor: districtColor, borderColor: districtColor }"
      >
        Edit Tempahan
      </button>
      <button
        v-if="canCancel"
        @click="$emit('cancel')"
        class="btn-cancel"
      >
        Batal Tempahan
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

defineEmits(['cancel', 'edit', 'payment'])

const districtStore = useDistrictStore()
const districtColor = computed(() => districtStore.districtColor)

const showPayButton = computed(() => {
  return props.booking.status === 'pending' && props.booking.payment_status !== 'PAID'
})

const duration = computed(() => {
  if (props.booking.booking_type === 'per_day') return 24

  const startStr = props.booking.start_time || ''
  const endStr = props.booking.end_time || ''
  if (!startStr || !endStr) return 0

  const start = new Date(`2000-01-01 ${startStr}`)
  const end = new Date(`2000-01-01 ${endStr}`)
  return (end - start) / (1000 * 60 * 60)
})

const parseNumber = (value) => {
  const n = Number(value ?? 0)
  return Number.isNaN(n) ? 0 : n
}

const pricePerHour = computed(() => parseNumber(props.booking.facility?.price_per_hour))
const pricePerDay = computed(() => parseNumber(props.booking.facility?.price_per_day))

// Numeric total (used for calculations) and formatted currency strings
const totalPriceNumber = computed(() => {
  if (!props.booking.facility) return 0
  if (props.booking.booking_type === 'per_day') return pricePerDay.value
  if (!duration.value) return 0
  return pricePerHour.value * duration.value
})

const currencyFormatter = new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' })

const formattedTotal = computed(() => currencyFormatter.format(totalPriceNumber.value))
const formattedPricePerHour = computed(() => currencyFormatter.format(pricePerHour.value))

const canCancel = computed(() => {
  return ['pending', 'confirmed'].includes(props.booking.status)
})

const canEdit = computed(() => {
  return props.booking.status === 'pending'
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ms-MY', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    pending: 'Menunggu',
    confirmed: 'Disahkan',
    cancelled: 'Dibatalkan',
    completed: 'Selesai'
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
.btn-edit,
.btn-payment {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-payment {
  background-color: #4CAF50;
  color: white;
  flex: 2;
}

.btn-payment:hover {
  background-color: #45a049;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
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
