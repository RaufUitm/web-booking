<template>
  <div class="facility-card" @click="handleClick">
    <div class="facility-image">
      <img :src="facility.image || '/image/default-facility.jpg'" :alt="facility.name" />
      <span v-if="!facility.is_available" class="unavailable-badge">Unavailable</span>
    </div>
    <div class="facility-info">
      <h3>{{ facility.name }}</h3>
      <p class="category">{{ facility.category?.name }}</p>
      <p class="description">{{ truncateText(facility.description, 100) }}</p>
      <div class="facility-details">
        <span class="location">📍 {{ facility.location }}</span>
        <span class="capacity">👥 {{ facility.capacity }} people</span>
      </div>
      <div class="facility-footer">
        <span class="price">${{ facility.price_per_hour }}/hour</span>
        <button class="btn-book" @click.stop="$emit('book', facility)">
          Book Now
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  facility: {
    type: Object,
    required: true
  }
})

defineEmits(['book'])
const router = useRouter()

const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const handleClick = () => {
  router.push(`/facilities/${props.facility.id}`)
}
</script>

<style scoped>
.facility-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
}

.facility-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.facility-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.facility-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.unavailable-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 0, 0, 0.8);
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.facility-info {
  padding: 20px;
}

h3 {
  margin: 0 0 8px 0;
  color: #333;
  font-size: 20px;
}

.category {
  color: #2d5f2e;
  font-size: 14px;
  margin: 0 0 12px 0;
  font-weight: 500;
}

.description {
  color: #666;
  font-size: 14px;
  margin: 0 0 15px 0;
  line-height: 1.5;
}

.facility-details {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  font-size: 14px;
  color: #666;
}

.facility-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.price {
  font-size: 20px;
  font-weight: bold;
  color: #2d5f2e;
}

.btn-book {
  padding: 8px 20px;
  background-color: #2d5f2e;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.btn-book:hover {
  background-color: #244d25;
}
</style>
