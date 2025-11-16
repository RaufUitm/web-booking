<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <h1>Majlis Daerah Hulu Terengganu</h1>
        <h2 style="font-size: 1.8rem; margin: 1rem 0; color: #2c3e50;">Sistem Tempahan Kemudahan Awam</h2>
        <p class="subtitle">Tempah kemudahan awam dengan mudah dan pantas di Kuala Berang, Terengganu</p>
        <div class="hero-buttons">
          <router-link to="/facilities" class="btn btn-primary">
            Lihat Kemudahan
          </router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="btn btn-secondary">
            Daftar Sekarang
          </router-link>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
      <div class="container">
        <h2>Kemudahan Yang Tersedia</h2>
        <div class="categories-grid">
          <div
            v-for="category in displayCategories"
            :key="category.id"
            class="category-card"
            @click="goToFacilities(category.id)"
          >
            <div class="category-icon">{{ category.icon }}</div>
            <h3>{{ category.name }}</h3>
            <p>{{ category.description }}</p>
            <span class="facility-count">{{ category.facility_count || 0 }} kemudahan</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2>Mengapa Pilih Kami?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">🕐</div>
            <h3>Tempahan 24/7</h3>
            <p>Buat tempahan bila-bila masa, di mana sahaja</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">✅</div>
            <h3>Pengesahan Pantas</h3>
            <p>Dapatkan pengesahan tempahan dengan segera</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Bayaran Mudah</h3>
            <p>Pelbagai kaedah pembayaran tersedia</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Mesra Pengguna</h3>
            <p>Sistem yang mudah digunakan pada semua peranti</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
      <div class="container">
        <h2>Cara Membuat Tempahan</h2>
        <div class="steps">
          <div class="step">
            <div class="step-number">1</div>
            <h3>Pilih Kemudahan</h3>
            <p>Lihat dan pilih kemudahan yang anda perlukan</p>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">2</div>
            <h3>{{ isAuthenticated ? 'Pilih Tarikh & Masa' : 'Daftar/Log Masuk' }}</h3>
            <p>{{ isAuthenticated ? 'Pilih tarikh dan masa yang sesuai' : 'Daftar akaun atau log masuk ke sistem' }}</p>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">3</div>
            <h3>{{ isAuthenticated ? 'Sahkan Tempahan' : 'Pilih Tarikh' }}</h3>
            <p>{{ isAuthenticated ? 'Isi maklumat dan sahkan tempahan anda' : 'Pilih tarikh dan masa yang dikehendaki' }}</p>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">4</div>
            <h3>Bayar & Selesai</h3>
            <p>Buat pembayaran dan tempahan anda sah!</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
      <div class="container">
        <h2>Bersedia Untuk Membuat Tempahan?</h2>
        <p>Mulakan tempahan anda hari ini dan nikmati kemudahan awam dengan lebih mudah</p>
        <router-link to="/facilities" class="btn btn-large">
          Tempah Sekarang
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFacilityStore } from '@/stores/facility'
import { storeToRefs } from 'pinia'

const router = useRouter()
const authStore = useAuthStore()
const facilityStore = useFacilityStore()

const { isAuthenticated } = storeToRefs(authStore)
const { categories } = storeToRefs(facilityStore)

// Default categories if not loaded from backend
const defaultCategories = ref([
  {
    id: 1,
    name: 'Dewan Serbaguna',
    description: 'Untuk majlis dan acara rasmi',
    icon: '🏛️',
    facility_count: 5
  },
  {
    id: 2,
    name: 'Padang & Gelanggang',
    description: 'Kemudahan sukan dan rekreasi',
    icon: '⚽',
    facility_count: 8
  },
  {
    id: 3,
    name: 'Bilik Seminar',
    description: 'Untuk mesyuarat dan pembentangan',
    icon: '💼',
    facility_count: 4
  },
  {
    id: 4,
    name: 'Kemudahan Rekreasi',
    description: 'Taman dan kawasan rekreasi',
    icon: '🎡',
    facility_count: 6
  }
])

const displayCategories = computed(() => {
  return categories.value.length > 0 ? categories.value : defaultCategories.value
})

const popularFacilities = ref([])

onMounted(async () => {
  await facilityStore.fetchCategories()
  // Fetch facilities to show popular ones
  try {
    await facilityStore.fetchFacilities()
    popularFacilities.value = facilityStore.facilities.slice(0, 6)
  } catch (error) {
    console.error('Failed to load facilities:', error)
  }
})

const goToFacilities = (categoryId) => {
  router.push({
    name: 'facilities',
    query: { category: categoryId }
  })
}
</script>

<style scoped>
.home {
  width: 100%;
}

.hero {
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  color: white;
  padding: 6rem 2rem;
  text-align: center;
}

.hero-content h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.subtitle {
  font-size: 1.3rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: 1rem 2rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  display: inline-block;
}

.btn-primary {
  background-color: white;
  color: #2d5f2e;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(45, 95, 46, 0.3);
}

.btn-secondary {
  background-color: transparent;
  color: white;
  border: 2px solid white;
}

.btn-secondary:hover {
  background-color: white;
  color: #2d5f2e;
}

.btn-large {
  padding: 1.2rem 3rem;
  font-size: 1.2rem;
  background-color: #2d5f2e;
  color: white;
}

.btn-large:hover {
  background-color: #244d25;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(45, 95, 46, 0.3);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

section {
  padding: 4rem 0;
}

section h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 3rem;
  color: #2c3e50;
}

.categories {
  background-color: #f8f9fa;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.category-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.category-icon {
  font-size: 3.5rem;
  margin-bottom: 1rem;
}

.category-card h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 1.5rem;
}

.category-card p {
  color: #7f8c8d;
  margin-bottom: 1rem;
}

.facility-count {
  display: inline-block;
  background-color: rgba(45, 95, 46, 0.1);
  color: #2d5f2e;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.feature-card {
  text-align: center;
  padding: 2rem;
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.3rem;
}

.feature-card p {
  color: #7f8c8d;
  line-height: 1.6;
}

.how-it-works {
  background-color: #f8f9fa;
}

.steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.step {
  flex: 1;
  min-width: 200px;
  text-align: center;
  padding: 1.5rem;
}

.step-number {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 auto 1rem;
}

.step h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 1.2rem;
}

.step p {
  color: #7f8c8d;
  font-size: 0.95rem;
}

.step-arrow {
  font-size: 2rem;
  color: #4a8b4d;
  font-weight: bold;
}

.cta {
  background: linear-gradient(135deg, #4a8b4d 0%, #2d5f2e 100%);
  color: white;
  text-align: center;
}

.cta h2 {
  color: white;
  margin-bottom: 1rem;
}

.cta p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2rem;
  }

  .subtitle {
    font-size: 1.1rem;
  }

  section h2 {
    font-size: 1.8rem;
  }

  .step-arrow {
    display: none;
  }

  .steps {
    flex-direction: column;
  }
}
</style>
