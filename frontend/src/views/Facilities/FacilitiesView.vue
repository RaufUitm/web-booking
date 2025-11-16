<template>
  <div class="facilities-page">
    <div class="page-header">
      <h1>Kemudahan Tersedia</h1>
      <p>Pilih kemudahan yang anda perlukan untuk tempahan</p>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="container">
        <div class="filters">
          <!-- Search -->
          <div class="search-box">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari kemudahan..."
              @input="handleSearch"
            />
            <span class="search-icon">🔍</span>
          </div>

          <!-- Category Filter -->
          <div class="filter-group">
            <label>Kategori:</label>
            <select v-model="selectedCategory" @change="handleCategoryChange">
              <option :value="null">Semua Kategori</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Availability Filter -->
          <div class="filter-group">
            <label>Status:</label>
            <select v-model="availabilityFilter" @change="handleAvailabilityChange">
              <option :value="null">Semua</option>
              <option :value="true">Tersedia</option>
              <option :value="false">Tidak Tersedia</option>
            </select>
          </div>

          <button @click="clearFilters" class="btn-clear">
            Kosongkan
          </button>
        </div>
      </div>
    </div>

    <!-- Facilities Grid -->
    <div class="container">
      <div v-if="loading" class="loading">
        <p>Memuatkan kemudahan...</p>
      </div>

      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
      </div>

      <div v-else-if="displayFacilities.length === 0" class="no-results">
        <p>Tiada kemudahan dijumpai</p>
        <button @click="clearFilters" class="btn-secondary">Kosongkan Penapis</button>
      </div>

      <div v-else class="facilities-grid">
        <div
          v-for="facility in displayFacilities"
          :key="facility.id"
          class="facility-card"
          @click="viewDetails(facility.id)"
        >
          <div class="facility-image">
            <img :src="facility.image || '/images/placeholder.jpg'" :alt="facility.name" />
            <span v-if="!facility.is_available" class="badge-unavailable">Tidak Tersedia</span>
            <span v-else class="badge-available">Tersedia</span>
          </div>

          <div class="facility-content">
            <div class="facility-category">{{ getCategoryName(facility.category_id) }}</div>
            <h3>{{ facility.name }}</h3>
            <p class="facility-description">{{ facility.description }}</p>

            <div class="facility-details">
              <div class="detail-item">
                <span class="icon">👥</span>
                <span>{{ facility.capacity }} orang</span>
              </div>
              <div class="detail-item">
                <span class="icon">⏱️</span>
                <span>{{ facility.min_booking_hours }} jam minimum</span>
              </div>
            </div>

            <div class="facility-footer">
              <div class="price">
                <span class="price-label">Harga:</span>
                <span class="price-amount">RM {{ facility.price_per_hour }}/jam</span>
              </div>
              <button class="btn-book" @click.stop="bookNow(facility.id)">
                {{ isAuthenticated ? 'Tempah' : 'Lihat' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFacilityStore } from '@/stores/facility'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

const router = useRouter()
const route = useRoute()
const facilityStore = useFacilityStore()
const authStore = useAuthStore()

const { loading, error, categories } = storeToRefs(facilityStore)
const { isAuthenticated } = storeToRefs(authStore)

const searchQuery = ref('')
const selectedCategory = ref(null)
const availabilityFilter = ref(null)
const facilities = ref([])

// Mock data for demo (fallback only)
const mockFacilities = ref([
  {
    id: 1,
    name: 'Dewan Serbaguna Utama',
    description: 'Dewan besar dengan kapasiti 500 orang. Sesuai untuk majlis perkahwinan, seminar dan persidangan.',
    category_id: 1,
    price_per_hour: 150,
    capacity: 500,
    min_booking_hours: 4,
    is_available: true,
    image: null
  },
  {
    id: 2,
    name: 'Padang Futsal',
    description: 'Padang futsal berkualiti dengan lampu malam. Lantai sintetik dan berpagar.',
    category_id: 2,
    price_per_hour: 50,
    capacity: 20,
    min_booking_hours: 1,
    is_available: true,
    image: null
  },
  {
    id: 3,
    name: 'Bilik Seminar A',
    description: 'Bilik seminar lengkap dengan projektor dan sistem audio. Kapasiti 50 orang.',
    category_id: 3,
    price_per_hour: 80,
    capacity: 50,
    min_booking_hours: 2,
    is_available: true,
    image: null
  },
  {
    id: 4,
    name: 'Gelanggang Badminton',
    description: 'Gelanggang badminton dalaman ber-air-conditioned. 4 court tersedia.',
    category_id: 2,
    price_per_hour: 40,
    capacity: 40,
    min_booking_hours: 1,
    is_available: false,
    image: null
  },
  {
    id: 5,
    name: 'Dewan Komuniti',
    description: 'Dewan komuniti untuk acara kecil dan mesyuarat. Kapasiti 100 orang.',
    category_id: 1,
    price_per_hour: 80,
    capacity: 100,
    min_booking_hours: 3,
    is_available: true,
    image: null
  },
  {
    id: 6,
    name: 'Taman Rekreasi',
    description: 'Kawasan rekreasi dengan kemudahan BBQ dan tempat duduk. Sesuai untuk picnic keluarga.',
    category_id: 4,
    price_per_hour: 30,
    capacity: 200,
    min_booking_hours: 3,
    is_available: true,
    image: null
  }
])

const displayFacilities = computed(() => {
  // Use real facilities from store, fallback to mock for demo
  const allFacilities = facilities.value.length > 0 ? facilities.value : mockFacilities.value
  let filtered = allFacilities

  // Search filter
  if (searchQuery.value) {
    const search = searchQuery.value.toLowerCase()
    filtered = filtered.filter(f =>
      f.name.toLowerCase().includes(search) ||
      f.description.toLowerCase().includes(search)
    )
  }

  // Category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(f => f.category_id === selectedCategory.value)
  }

  // Availability filter
  if (availabilityFilter.value !== null) {
    filtered = filtered.filter(f => f.is_available === availabilityFilter.value)
  }

  return filtered
})

onMounted(async () => {
  // Load from query params
  if (route.query.category) {
    selectedCategory.value = parseInt(route.query.category)
  }

  // Fetch categories and facilities from API
  try {
    await facilityStore.fetchCategories()
    await facilityStore.fetchFacilities()
    // Handle paginated response
    if (facilityStore.facilities.data) {
      facilities.value = facilityStore.facilities.data
    } else {
      facilities.value = facilityStore.facilities
    }
  } catch (error) {
    console.error('Failed to load facilities:', error)
  }
})

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId) ||
                   mockFacilities.value.find(c => c.id === categoryId)
  return category ? category.name : 'Lain-lain'
}

const handleSearch = () => {
  // Search is reactive through computed property
}

const handleCategoryChange = () => {
  // Update query params
  router.push({ query: { ...route.query, category: selectedCategory.value } })
}

const handleAvailabilityChange = () => {
  // Filter is reactive through computed property
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = null
  availabilityFilter.value = null
  router.push({ query: {} })
}

const viewDetails = (facilityId) => {
  router.push({ name: 'facility-detail', params: { id: facilityId } })
}

const bookNow = (facilityId) => {
  if (!isAuthenticated.value) {
    router.push({ name: 'login', query: { redirect: `/booking/${facilityId}` } })
  } else {
    router.push({ name: 'booking', params: { facilityId } })
  }
}
</script>

<style scoped>
.facilities-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.page-header {
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  color: white;
  padding: 3rem 2rem;
  text-align: center;
}

.page-header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.page-header p {
  font-size: 1.2rem;
  opacity: 0.9;
}

.filters-section {
  background: white;
  padding: 1.5rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
}

.search-box input:focus {
  outline: none;
  border-color: #2d5f2e;
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 600;
  color: #2c3e50;
  white-space: nowrap;
}

.filter-group select {
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  background-color: white;
}

.filter-group select:focus {
  outline: none;
  border-color: #2d5f2e;
}

.btn-clear {
  padding: 0.75rem 1.5rem;
  background-color: #e0e0e0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.btn-clear:hover {
  background-color: #d0d0d0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.loading,
.error-message,
.no-results {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  margin: 2rem 0;
}

.error-message {
  background-color: #fee;
  color: #c33;
  border: 1px solid #fcc;
}

.no-results p {
  color: #7f8c8d;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background-color: #2d5f2e;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background-color: #244d25;
  transform: translateY(-1px);
}

.facilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  padding: 2rem 0;
}

.facility-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.facility-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.facility-image {
  position: relative;
  height: 200px;
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 3rem;
}

.facility-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.badge-available,
.badge-unavailable {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: bold;
}

.badge-available {
  background-color: #2d5f2e;
  color: white;
}

.badge-unavailable {
  background-color: #e74c3c;
  color: white;
}

.facility-content {
  padding: 1.5rem;
}

.facility-category {
  display: inline-block;
  background-color: rgba(45, 95, 46, 0.1);
  color: #2d5f2e;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.facility-content h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 1.3rem;
}

.facility-description {
  color: #7f8c8d;
  line-height: 1.6;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.facility-details {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem 0;
  border-top: 1px solid #ecf0f1;
  border-bottom: 1px solid #ecf0f1;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #2c3e50;
  font-size: 0.9rem;
}

.icon {
  font-size: 1.2rem;
}

.facility-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price {
  display: flex;
  flex-direction: column;
}

.price-label {
  color: #7f8c8d;
  font-size: 0.85rem;
}

.price-amount {
  color: #2d5f2e;
  font-size: 1.5rem;
  font-weight: bold;
}

.btn-book {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-book:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(45, 95, 46, 0.3);
}

@media (max-width: 768px) {
  .page-header h1 {
    font-size: 1.8rem;
  }

  .filters {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-group {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group select {
    width: 100%;
  }

  .facilities-grid {
    grid-template-columns: 1fr;
  }
}
</style>
