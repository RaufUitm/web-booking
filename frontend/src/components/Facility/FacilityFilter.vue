<template>
  <div class="facility-filter">
    <div class="filter-section">
      <label>Category</label>
      <select v-model="filters.category_id" @change="applyFilters">
        <option value="">All Categories</option>
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
        >
          {{ category.name }}
        </option>
      </select>
    </div>

    <div class="filter-section">
      <label>Search</label>
      <input
        v-model="filters.search"
        type="text"
        placeholder="Search facilities..."
        @input="debouncedSearch"
      />
    </div>

    <div class="filter-section">
      <label>Availability</label>
      <select v-model="filters.available" @change="applyFilters">
        <option value="">All</option>
        <option value="1">Available</option>
        <option value="0">Unavailable</option>
      </select>
    </div>

    <div class="filter-actions">
      <button @click="clearFilters" class="btn-clear">
        Clear Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, defineEmits, defineProps, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/category'

const props = defineProps({
  initialFilters: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['filter'])
const categoryStore = useCategoryStore()

const filters = ref({
  category_id: props.initialFilters.category_id || '',
  search: props.initialFilters.search || '',
  available: props.initialFilters.available || ''
})

const categories = ref([])

let searchTimeout = null

onMounted(async () => {
  await loadCategories()
})

const loadCategories = async () => {
  try {
    await categoryStore.fetchCategories()
    categories.value = categoryStore.categories
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const applyFilters = () => {
  emit('filter', { ...filters.value })
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const clearFilters = () => {
  filters.value = {
    category_id: '',
    search: '',
    available: ''
  }
  applyFilters()
}
</script>

<style scoped>
.facility-filter {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  align-items: end;
}

.filter-section {
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
  font-size: 14px;
}

select,
input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background-color: white;
}

select:focus,
input:focus {
  outline: none;
  border-color: #2d5f2e;
}

.filter-actions {
  display: flex;
  align-items: flex-end;
}

.btn-clear {
  padding: 10px 20px;
  background-color: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
  width: 100%;
}

.btn-clear:hover {
  background-color: #e0e0e0;
}

@media (max-width: 768px) {
  .facility-filter {
    grid-template-columns: 1fr;
  }
}
</style>
