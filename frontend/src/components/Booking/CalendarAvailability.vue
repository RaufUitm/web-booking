<template>
  <div class="calendar-availability"
       :style="{
         '--mdht-green': currentDistrictColor.main,
         '--mdht-light-green': currentDistrictColor.main,
         '--mdht-dark-green': currentDistrictColor.dark || currentDistrictColor.main,
         '--mdht-primary-green': currentDistrictColor.main,
         '--mdht-bg': 'rgba(0,0,0,0.03)'
       }">
    <div class="calendar-header">
      <button @click="previousMonth" class="nav-btn">‹</button>
      <h3>{{ monthYear }}</h3>
      <button @click="nextMonth" class="nav-btn">›</button>
    </div>

    <!-- Month Calendar -->
    <div class="calendar-grid">
      <div v-for="day in daysOfWeek" :key="day" class="day-header">
        {{ day }}
      </div>

      <div
        v-for="date in calendarDays"
        :key="date.date"
        :class="[
          'calendar-day',
          {
            'other-month': !date.isCurrentMonth,
            'past-date': date.isPast,
            'selected': date.isSelected,
            'has-bookings': date.bookingCount > 0,
            'today': date.isToday
          }
        ]"
        @click="selectDate(date)"
      >
        <div class="date-number">{{ date.day }}</div>
        <div v-if="date.bookingCount > 0" class="booking-dots">
          <span
            v-for="i in Math.min(date.bookingCount, 3)"
            :key="i"
            class="dot"
          ></span>
        </div>
      </div>
    </div>

    <!-- Booking List for Selected Date -->
    <div v-if="selectedDate" class="bookings-section">
      <h4>Tempahan untuk {{ formatSelectedDate }}</h4>

      <div v-if="selectedDateBookings.length === 0" class="no-bookings">
        <p>Tiada tempahan pada tarikh ini</p>
      </div>

      <div v-else class="bookings-table">
        <div class="table-header">
          <div class="col-time">Masa</div>
          <div class="col-status">Status</div>
          <div class="col-people">Bilangan</div>
        </div>

        <div
          v-for="booking in selectedDateBookings"
          :key="booking.id"
          class="table-row"
          :class="'status-' + booking.status"
        >
          <div class="col-time">
            <template v-if="booking.booking_type === 'per_day'">Sepanjang Hari</template>
            <template v-else>{{ booking.start_time || '' }} - {{ booking.end_time || '' }}</template>
          </div>
          <div class="col-status">
            <span class="status-badge" :class="booking.status">
              {{ statusLabel(booking.status) }}
            </span>
          </div>
          <div class="col-people">
            {{ booking.number_of_people }} orang
          </div>
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <button @click="$emit('close')" class="btn-secondary">Kembali</button>
      <button
        @click="proceedToBooking"
        class="btn-primary"
        :disabled="!selectedDate || selectedDate.isPast"
      >
        Teruskan Tempahan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDistrictStore } from '@/stores/district'
import api from '@/api/axios'

const props = defineProps({
  facilityId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['close', 'dateSelected'])

const currentDate = ref(new Date())
const selectedDate = ref(null)
const bookingsByDate = ref({})
const loading = ref(false)

const daysOfWeek = ['Ahd', 'Isn', 'Sel', 'Rab', 'Kha', 'Jum', 'Sab']

const monthYear = computed(() => {
  const months = [
    'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
    'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'
  ]
  return `${months[currentDate.value.getMonth()]} ${currentDate.value.getFullYear()}`
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const prevLastDay = new Date(year, month, 0)

  const firstDayOfWeek = firstDay.getDay()
  const lastDate = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()

  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Previous month days
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const day = prevLastDate - i
    const date = new Date(year, month - 1, day)
    days.push(createDayObject(date, false))
  }

  // Current month days
  for (let day = 1; day <= lastDate; day++) {
    const date = new Date(year, month, day)
    days.push(createDayObject(date, true))
  }

  // Next month days
  const remainingDays = 42 - days.length
  for (let day = 1; day <= remainingDays; day++) {
    const date = new Date(year, month + 1, day)
    days.push(createDayObject(date, false))
  }

  return days
})

const createDayObject = (date, isCurrentMonth) => {
  const dateStr = formatDateStr(date)
  const bookings = bookingsByDate.value[dateStr] || []
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dayDate = new Date(date)
  dayDate.setHours(0, 0, 0, 0)

  return {
    date: dateStr,
    day: date.getDate(),
    isCurrentMonth,
    isPast: dayDate < today,
    isToday: dayDate.getTime() === today.getTime(),
    isSelected: selectedDate.value === dateStr,
    bookingCount: bookings.length,
    bookings
  }
}

const selectedDateBookings = computed(() => {
  if (!selectedDate.value) return []
  return bookingsByDate.value[selectedDate.value] || []
})

const formatSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  const date = new Date(selectedDate.value)
  return date.toLocaleDateString('ms-MY', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

const formatDateStr = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const previousMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
  loadBookings()
}

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
  loadBookings()
}

const selectDate = (date) => {
  if (!date.isCurrentMonth || date.isPast) return
  selectedDate.value = date.date
}

const loadBookings = async () => {
  loading.value = true
  try {
    const response = await api.get(`/facilities/${props.facilityId}/bookings`, {
      params: {
        year: currentDate.value.getFullYear(),
        month: currentDate.value.getMonth() + 1
      }
    })

    const bookings = response.data.data || response.data
    console.log('Loaded bookings:', bookings)

    // Group bookings by date
    bookingsByDate.value = {}
    if (Array.isArray(bookings)) {
      bookings.forEach(booking => {
        // Convert booking_date to YYYY-MM-DD format
        let date = booking.booking_date
        if (date.includes('T')) {
          date = date.split('T')[0]
        }
        if (!bookingsByDate.value[date]) {
          bookingsByDate.value[date] = []
        }
        bookingsByDate.value[date].push(booking)
      })
    }
    console.log('Bookings by date:', bookingsByDate.value)
  } catch (error) {
    console.error('Failed to load bookings:', error)
    console.error('Error details:', error.response?.data)
  } finally {
    loading.value = false
  }
}

const statusLabel = (status) => {
  const labels = {
    pending: 'Menunggu',
    confirmed: 'Disahkan',
    cancelled: 'Dibatalkan',
    completed: 'Selesai'
  }
  return labels[status] || status
}

const proceedToBooking = () => {
  if (selectedDate.value) {
    emit('dateSelected', selectedDate.value)
  }
}

onMounted(() => {
  loadBookings()
})

// district color mapping and computed color
const districtStore = useDistrictStore()
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
  'Kuala Terengganu': { main: '#EEBF04', dark: '#a88903', gradient: 'linear-gradient(135deg, #EEBF04 0%, #a88903 100%)' },
  'Kemaman': { main: '#1E3A8A', dark: '#152a61', gradient: 'linear-gradient(135deg, #1E3A8A 0%, #152a61 100%)' },
  'Dungun': { main: '#06B6D4', dark: '#058099', gradient: 'linear-gradient(135deg, #06B6D4 0%, #058099 100%)' },
}
const currentDistrictColor = computed(() => districtColors[districtStore.districtName] || districtColors['Hulu Terengganu'])
</script>

<style scoped>
/* MDHT Green Color Scheme */
:root {
  --mdht-green: #2d5f2e;
  --mdht-light-green: #4a8b4d;
  --mdht-dark-green: #1e4620;
  --mdht-accent: #6ba86e;
  --mdht-bg: #f0f7f0;
}

.calendar-availability {
  background: white;
  border-radius: 8px;
  padding: 28px;
  box-shadow: 0 4px 12px rgba(45, 95, 46, 0.1);
  max-width: 1000px;
  margin: 0 auto;
  border: 1px solid #e5efe5;
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  padding-bottom: 16px;
  border-bottom: 3px solid var(--mdht-green);
}

.calendar-header h3 {
  margin: 0;
  font-size: 1.75rem;
  color: var(--mdht-green);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-btn {
  background: var(--mdht-green);
  border: none;
  font-size: 1.75rem;
  cursor: pointer;
  padding: 8px 20px;
  color: white;
  transition: all 0.3s;
  border-radius: 6px;
  font-weight: bold;
}

.nav-btn:hover {
  background: var(--mdht-light-green);
  transform: scale(1.1);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 32px;
  background: var(--mdht-bg);
  padding: 12px;
  border-radius: 8px;
}

.day-header {
  text-align: center;
  font-weight: 700;
  padding: 12px;
  color: white;
  font-size: 0.9rem;
  background: var(--mdht-green);
  border-radius: 4px;
  text-transform: uppercase;
}

.calendar-day {
  aspect-ratio: 1;
  border: 2px solid #d4e7d4;
  border-radius: 6px;
  padding: 8px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  background: white;
  min-height: 70px;
}

.calendar-day:hover:not(.past-date):not(.other-month) {
  border-color: var(--mdht-light-green);
  background: #f0f7f0;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(45, 95, 46, 0.2);
}

.calendar-day.other-month {
  opacity: 0.3;
  cursor: default;
  background: #fafafa;
}

.calendar-day.past-date {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f5f5f5;
}

.calendar-day.today {
  border: 3px solid #ff9800;
  background: #fff8e1;
  box-shadow: 0 0 0 2px rgba(255, 152, 0, 0.2);
}

.calendar-day.selected {
  background: var(--mdht-green);
  color: white;
  border-color: var(--mdht-dark-green);
  box-shadow: 0 4px 12px rgba(45, 95, 46, 0.4);
  transform: scale(1.05);
}

.calendar-day.has-bookings .date-number {
  font-weight: 700;
  color: var(--mdht-green);
}

.calendar-day.selected.has-bookings .date-number {
  color: white;
}

.date-number {
  font-size: 1.2rem;
  margin-bottom: 4px;
  font-weight: 600;
}

.booking-dots {
  display: flex;
  gap: 3px;
  margin-top: 4px;
}

.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--mdht-light-green);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.calendar-day.selected .dot {
  background: #ffd54f;
}

/* Bookings Section */
.bookings-section {
  margin-bottom: 24px;
  border: 1px solid var(--mdht-light-green);
  border-radius: 8px;
  padding: 20px;
  background: rgba(45, 95, 46, 0.02);
}

.bookings-section h4 {
  margin: 0 0 20px 0;
  font-size: 1.125rem;
  color: var(--mdht-primary-green);
  font-weight: 600;
}

.no-bookings {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.no-bookings p {
  margin: 0;
  font-size: 0.95rem;
}

.bookings-table {
  background: white;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid var(--mdht-light-green);
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1.5fr;
  gap: 16px;
  padding: 12px 16px;
  background: var(--mdht-primary-green);
  font-weight: 600;
  font-size: 0.875rem;
  color: white;
  border-bottom: 2px solid var(--mdht-primary-green);
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1.5fr;
  gap: 16px;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
}

.table-row:last-child {
  border-bottom: none;
}

.table-row:hover {
  background: rgba(45, 95, 46, 0.03);
}

.col-time {
  font-weight: 600;
  color: #1a1a1a;
  display: flex;
  align-items: center;
}

.col-status {
  display: flex;
  align-items: center;
}

.col-people {
  display: flex;
  align-items: center;
  color: #6b7280;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.confirmed {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.completed {
  background: #e0e7ff;
  color: #3730a3;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #991b1b;
}

/* Action Buttons */

.action-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-primary,
.btn-secondary {
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: var(--mdht-primary-green);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #244d25;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(45, 95, 46, 0.2);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: transparent;
  color: var(--mdht-primary-green);
  border: 2px solid var(--mdht-primary-green);
}

.btn-secondary:hover {
  background: rgba(45, 95, 46, 0.05);
  border-color: #244d25;
  color: #244d25;
}

@media (max-width: 768px) {
  .calendar-availability {
    padding: 16px;
  }

  .calendar-header h3 {
    font-size: 1.25rem;
  }

  .calendar-grid {
    gap: 2px;
  }

  .day-header {
    padding: 8px;
    font-size: 0.75rem;
  }

  .calendar-day {
    min-height: 50px;
    padding: 4px;
  }

  .date-number {
    font-size: 0.95rem;
  }

  .dot {
    width: 4px;
    height: 4px;
  }

  .bookings-section {
    padding: 16px;
  }

  .bookings-section h4 {
    font-size: 1rem;
  }

  .table-header,
  .table-row {
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 8px;
    padding: 12px;
    font-size: 0.8rem;
  }

  .status-badge {
    padding: 4px 8px;
    font-size: 0.7rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }
}
</style>
