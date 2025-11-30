<template>
  <div class="facility-list">
    <div v-if="loading" class="loading">Memuatkan kemudahan...</div>

    <div v-else-if="facilities.length === 0" class="no-facilities">
      <p>Tiada kemudahan ditemui.</p>
    </div>

    <div v-else class="facilities-grid">
      <FacilityCard
        v-for="facility in facilities"
        :key="facility.id"
        :facility="facility"
        @book="handleBook"
      />
    </div>

    <div v-if="hasMore && !loading" class="load-more">
      <button @click="loadMore" class="btn-load-more">
        Muat Lagi
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import FacilityCard from './FacilityCard.vue'

defineProps({
  facilities: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  hasMore: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['book', 'load-more'])

const handleBook = (facility) => {
  emit('book', facility)
}

const loadMore = () => {
  emit('load-more')
}
</script>

<style scoped>
.facility-list {
  width: 100%;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #666;
  font-size: 18px;
}

.no-facilities {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.no-facilities p {
  font-size: 18px;
}

.facilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.load-more {
  text-align: center;
  margin-top: 30px;
}

.btn-load-more {
  padding: 12px 30px;
  background-color: #FF8C00;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-load-more:hover {
  background-color: #E67E00;
}

@media (max-width: 768px) {
  .facilities-grid {
    grid-template-columns: 1fr;
  }
}
</style>
