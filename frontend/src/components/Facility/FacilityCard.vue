<template>
  <div class="facility-card" @click="handleClick">
    <div class="facility-image">
      <img :src="facility.image || '/image/default-facility.jpg'" :alt="facility.name" />
      <span v-if="!facility.is_available" class="unavailable-badge">Tidak Tersedia</span>
    </div>
    <div class="facility-info">
      <h3>{{ facility.name }}</h3>
      <p class="category">{{ facility.category?.name }}</p>
      <p class="description">{{ truncateText(facility.description, 100) }}</p>
      <div class="facility-details">
        <span class="location">📍 {{ facility.location }}</span>
        <span class="capacity">👥 {{ facility.capacity }} orang</span>
      </div>
      <div class="facility-footer">
        <span class="price">{{ formattedPricePerHour }} / jam</span>
        <button class="btn-book" @click.stop="$emit('book', facility)">
          Tempah Sekarang
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'
import { useRouter } from 'vue-router'
import useDistrictRoutes from '@/utils/districtRoutes'

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

const { facilityDetailPath } = useDistrictRoutes()

const handleClick = () => {
  router.push(facilityDetailPath(props.facility.id))
}

const currencyFormatter = new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' })
const formattedPricePerHour = computed(() => currencyFormatter.format(Number(props.facility?.price_per_hour ?? 0)))
</script>

<style scoped>
.facility-card {
  background: white;
  border-radius: clamp(6px, 0.8vw, 8px);
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
  height: clamp(180px, 20vw, 200px);
  overflow: hidden;
}

.facility-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.unavailable-badge {
  position: absolute;
  top: clamp(8px, 1vw, 10px);
  right: clamp(8px, 1vw, 10px);
  background: rgba(255, 0, 0, 0.8);
  color: white;
  padding: clamp(4px, 0.6vw, 5px) clamp(8px, 1.2vw, 10px);
  border-radius: clamp(3px, 0.5vw, 4px);
  font-size: clamp(11px, 1.3vw, 12px);
  font-weight: bold;
}

.facility-info {
  padding: clamp(15px, 2.5vw, 20px);
}

h3 {
  margin: 0 0 clamp(6px, 1vw, 8px) 0;
  color: #333;
  font-size: clamp(16px, 2.5vw, 20px);
}

.category {
  color: #FF8C00;
  font-size: clamp(13px, 1.5vw, 14px);
  margin: 0 0 clamp(10px, 1.5vw, 12px) 0;
  font-weight: 500;
}

.description {
  color: #666;
  font-size: clamp(13px, 1.5vw, 14px);
  margin: 0 0 clamp(12px, 1.8vw, 15px) 0;
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
  font-size: clamp(16px, 2.5vw, 20px);
  font-weight: bold;
  color: #FF8C00;
}

.btn-book {
  padding: clamp(6px, 1vw, 8px) clamp(16px, 2.5vw, 20px);
  background-color: #FF8C00;
  color: white;
  border: none;
  border-radius: clamp(3px, 0.5vw, 4px);
  cursor: pointer;
  font-size: clamp(13px, 1.5vw, 14px);
  transition: background-color 0.3s;
}

.btn-book:hover {
  background-color: #E67E00;
}
</style>
