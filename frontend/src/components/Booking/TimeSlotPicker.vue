<template>
  <div class="time-slot-picker">
    <h3>Select Time Slot</h3>

    <div v-if="loading" class="loading">
      Loading available time slots...
    </div>

    <div v-else-if="timeSlots.length === 0" class="no-slots">
      <p>No time slots available for this date.</p>
    </div>

    <div v-else class="time-slots-grid">
      <div
        v-for="slot in timeSlots"
        :key="slot.id"
        :class="['time-slot', {
          'selected': selectedSlot === slot.id,
          'booked': slot.is_booked,
          'available': !slot.is_booked
        }]"
        @click="selectSlot(slot)"
      >
        <div class="slot-time">
          {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
        </div>
        <div class="slot-status">
          {{ slot.is_booked ? 'Booked' : 'Available' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineProps({
  timeSlots: {
    type: Array,
    default: () => []
  },
  selectedSlot: {
    type: [Number, String],
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['select'])

const selectSlot = (slot) => {
  if (slot.is_booked) return
  emit('select', slot.id)
}

const formatTime = (time) => {
  if (!time) return ''

  // Handle HH:mm:ss or HH:mm format
  const parts = time.split(':')
  const hour = parseInt(parts[0])
  const minute = parts[1]

  const period = hour >= 12 ? 'PM' : 'AM'
  const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour)

  return `${displayHour}:${minute} ${period}`
}
</script>

<style scoped>
.time-slot-picker {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
}

.loading,
.no-slots {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.time-slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.time-slot {
  padding: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.time-slot.available:hover {
  border-color: #2d5f2e;
  background-color: rgba(45, 95, 46, 0.05);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(45, 95, 46, 0.2);
}

.time-slot.selected {
  border-color: #2d5f2e;
  background-color: #2d5f2e;
  color: white;
}

.time-slot.booked {
  border-color: #ffcdd2;
  background-color: #ffebee;
  cursor: not-allowed;
  opacity: 0.6;
}

.slot-time {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 5px;
}

.slot-status {
  font-size: 12px;
  opacity: 0.8;
}

.time-slot.selected .slot-status {
  opacity: 1;
}

@media (max-width: 768px) {
  .time-slots-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
  }

  .time-slot {
    padding: 12px;
  }

  .slot-time {
    font-size: 12px;
  }
}
</style>
