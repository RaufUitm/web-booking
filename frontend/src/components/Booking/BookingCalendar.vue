<template>
  <div class="booking-calendar">
    <div class="calendar-header">
      <button @click="previousMonth" class="nav-btn">&lt;</button>
      <h3>{{ monthYear }}</h3>
      <button @click="nextMonth" class="nav-btn">&gt;</button>
    </div>

    <div class="calendar-grid">
      <div class="day-header" v-for="day in weekDays" :key="day">
        {{ day }}
      </div>

      <div
        v-for="date in calendarDays"
        :key="date.key"
        :class="['calendar-day', {
          'other-month': !date.isCurrentMonth,
          'today': date.isToday,
          'selected': date.isSelected,
          'past': date.isPast,
          'has-booking': date.hasBooking
        }]"
        @click="selectDate(date)"
      >
        <span class="day-number">{{ date.day }}</span>
        <span v-if="date.hasBooking" class="booking-indicator">●</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  selectedDate: {
    type: String,
    default: ''
  },
  // Optional range selection support
  selectedStartDate: {
    type: String,
    default: ''
  },
  selectedEndDate: {
    type: String,
    default: ''
  },
  bookings: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['date-selected', 'range-selected'])

const currentDate = ref(new Date())
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const monthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  })
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const daysInMonth = lastDay.getDate()
  const startingDayOfWeek = firstDay.getDay()

  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const day = prevMonthLastDay - i
    const date = new Date(year, month - 1, day)
    days.push(createDayObject(date, false))
  }

  // Current month days
  for (let day = 1; day <= daysInMonth; day++) {
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
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const dateStr = date.toISOString().split('T')[0]

  // Range selection helpers
  const inRange = () => {
    if (!props.selectedStartDate || !props.selectedEndDate) return false
    const start = new Date(props.selectedStartDate)
    const end = new Date(props.selectedEndDate)
    start.setHours(0,0,0,0)
    end.setHours(0,0,0,0)
    return date >= start && date <= end
  }

  return {
    day: date.getDate(),
    date: dateStr,
    key: dateStr,
    isCurrentMonth,
    isToday: date.getTime() === today.getTime(),
    isSelected: (dateStr === props.selectedDate) || inRange(),
    isPast: date < today,
    hasBooking: props.bookings.some(b => b.booking_date === dateStr)
  }
}

// Range selection state (local for interaction)
const rangeStart = ref(props.selectedStartDate || '')
const rangeEnd = ref(props.selectedEndDate || '')

const selectDate = (date) => {
  if (date.isPast || !date.isCurrentMonth) return
  // If no start, set start
  if (!rangeStart.value || (rangeStart.value && rangeEnd.value)) {
    rangeStart.value = date.date
    rangeEnd.value = ''
    emit('date-selected', date.date)
    return
  }
  // If start set but no end, define end (ensure chronological order)
  const start = new Date(rangeStart.value)
  const chosen = new Date(date.date)
  if (chosen < start) {
    rangeEnd.value = rangeStart.value
    rangeStart.value = date.date
  } else {
    rangeEnd.value = date.date
  }
  emit('range-selected', { startDate: rangeStart.value, endDate: rangeEnd.value })
}

const previousMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1
  )
}

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1
  )
}
</script>

<style scoped>
.booking-calendar {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.nav-btn {
  background: none;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 32px;
  height: 32px;
  cursor: pointer;
  font-size: 18px;
  color: #666;
  transition: background-color 0.3s;
}

.nav-btn:hover {
  background-color: #f5f5f5;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.day-header {
  text-align: center;
  font-weight: 600;
  color: #666;
  font-size: 14px;
  padding: 10px 0;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px solid #eee;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}

.calendar-day:hover:not(.past):not(.other-month) {
  background-color: #fff5e6;
  border-color: #FF8C00;
}

.calendar-day.other-month {
  color: #ccc;
  cursor: default;
}

.calendar-day.today {
  border-color: #FF8C00;
  font-weight: bold;
}

.calendar-day.selected {
  background-color: #FF8C00;
  color: white;
  border-color: #FF8C00;
}

.calendar-day.past {
  color: #ccc;
  cursor: not-allowed;
}

.day-number {
  font-size: 14px;
}

.booking-indicator {
  position: absolute;
  bottom: 4px;
  color: #ff9800;
  font-size: 8px;
}

.calendar-day.selected .booking-indicator {
  color: white;
}

@media (max-width: 768px) {
  .calendar-grid {
    gap: 3px;
  }

  .day-number {
    font-size: 12px;
  }

  .day-header {
    font-size: 12px;
    padding: 5px 0;
  }
}
</style>
