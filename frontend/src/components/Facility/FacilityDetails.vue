<template>
  <div class="facility-details" v-if="facility">
    <div class="facility-header">
      <div class="facility-main-image">
        <img :src="facility.image || '/image/default-facility.jpg'" :alt="facility.name" />
      </div>
      <div class="facility-header-info">
        <h1>{{ facility.name }}</h1>
        <p class="category">{{ facility.category?.name }}</p>
        <div class="facility-meta">
          <span class="location">📍 {{ facility.location }}</span>
          <span class="capacity">👥 Capacity: {{ facility.capacity }} people</span>
          <span class="price">💰 ${{ facility.price_per_hour }}/hour</span>
        </div>
        <div class="availability">
          <span :class="['status', facility.is_available ? 'available' : 'unavailable']">
            {{ facility.is_available ? '✓ Available' : '✗ Not Available' }}
          </span>
        </div>
      </div>
    </div>

    <div class="facility-body">
      <div class="section">
        <h2>Description</h2>
        <p>{{ facility.description }}</p>
      </div>

      <div class="section" v-if="facility.timeSlots && facility.timeSlots.length > 0">
        <h2>Available Time Slots</h2>
        <div class="time-slots">
          <div
            v-for="slot in facility.timeSlots"
            :key="slot.id"
            class="time-slot"
          >
            {{ slot.start_time }} - {{ slot.end_time }}
          </div>
        </div>
      </div>

      <div class="section actions">
        <button
          @click="$emit('book', facility)"
          class="btn-book"
          :disabled="!facility.is_available"
        >
          {{ facility.is_available ? 'Book This Facility' : 'Currently Unavailable' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineProps({
  facility: {
    type: Object,
    default: null
  }
})

defineEmits(['book'])
</script>

<style scoped>
.facility-details {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.facility-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 40px;
}

.facility-main-image {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.facility-main-image img {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

.facility-header-info {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

h1 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 32px;
}

.category {
  color: #FF8C00;
  font-size: 16px;
  margin: 0 0 20px 0;
  font-weight: 500;
}

.facility-meta {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.facility-meta span {
  font-size: 16px;
  color: #666;
}

.availability {
  margin-top: 20px;
}

.status {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
}

.status.available {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status.unavailable {
  background-color: #ffebee;
  color: #c62828;
}

.facility-body {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.section {
  margin-bottom: 30px;
}

.section:last-child {
  margin-bottom: 0;
}

h2 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 24px;
}

p {
  line-height: 1.6;
  color: #666;
  margin: 0;
}

.time-slots {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
}

.time-slot {
  padding: 12px;
  background-color: #f5f5f5;
  border-radius: 4px;
  text-align: center;
  font-size: 14px;
  color: #333;
}

.actions {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.btn-book {
  padding: 15px 50px;
  background-color: #FF8C00;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 18px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-book:hover:not(:disabled) {
  background-color: #E67E00;
}

.btn-book:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .facility-header {
    grid-template-columns: 1fr;
  }

  .facility-main-image img {
    height: 250px;
  }

  h1 {
    font-size: 24px;
  }

  .time-slots {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
