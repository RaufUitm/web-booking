<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="hero" :style="{ backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(${currentDistrictBg})` }">
      <div class="hero-content">
        <h1>{{ districtStore.pbtName }}</h1>
        <h2 style="font-size: 1.8rem; margin: 1rem 0; color: #ffffff;">Sistem Tempahan Kemudahan Awam</h2>
        <p class="subtitle">Tempah kemudahan awam dengan mudah dan pantas di {{ districtStore.districtName }}, Terengganu</p>
        <div class="hero-buttons">
          <router-link :to="prefixPath('/facilities')" class="btn btn-primary" :style="{ color: currentDistrictColor.main, borderColor: currentDistrictColor.main }">
            Lihat Kemudahan
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
            <span class="facility-count" :style="{ backgroundColor: currentDistrictColor.main + '20', color: currentDistrictColor.main }">{{ category.facility_count || 0 }} kemudahan</span>
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
    <section class="cta" :style="{ background: currentDistrictColor.gradient }">
      <div class="container">
        <h2>Bersedia Untuk Membuat Tempahan?</h2>
        <p>Mulakan tempahan anda hari ini dan nikmati kemudahan awam dengan lebih mudah</p>
        <router-link :to="prefixPath('/facilities')" class="btn btn-large" :style="{ background: currentDistrictColor.main, color: '#fff' }">
          Tempah Sekarang
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFacilityStore } from '@/stores/facility'
import { useDistrictStore } from '@/stores/district'
import { storeToRefs } from 'pinia'
import useDistrictRoutes from '@/utils/districtRoutes'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const facilityStore = useFacilityStore()
const districtStore = useDistrictStore()

// District color mapping for dynamic theme (defined after districtStore)
const districtColors = {
  'Besut': { main: '#DC143C', dark: '#a10e2a', gradient: 'linear-gradient(135deg, #DC143C 0%, #a10e2a 100%)' },
  'Marang': { main: '#8B008B', dark: '#5c005c', gradient: 'linear-gradient(135deg, #8B008B 0%, #5c005c 100%)' },
  'Setiu': { main: '#8B7355', dark: '#5c4c36', gradient: 'linear-gradient(135deg, #8B7355 0%, #5c4c36 100%)' },
  'Hulu Terengganu': { main: '#FF8C00', dark: '#b35f00', gradient: 'linear-gradient(135deg, #FF8C00 0%, #b35f00 100%)' },
  'Kuala Terengganu': { main: '#EEBF04', dark: '#a88903', gradient: 'linear-gradient(135deg, #EEBF04 0%, #a88903 100%)' },
  'Kemaman': { main: '#1E3A8A', dark: '#152a61', gradient: 'linear-gradient(135deg, #1E3A8A 0%, #152a61 100%)' },
  'Dungun': { main: '#06B6D4', dark: '#058099', gradient: 'linear-gradient(135deg, #06B6D4 0%, #058099 100%)' },
}

// District background images
const districtBackgrounds = {
  'Besut': '/image/daerah/besutmain.jpg',
  'Marang': '/image/daerah/marangmain.jpg',
  'Setiu': '/image/daerah/setiumain.jpg',
  'Hulu Terengganu': '/image/daerah/hulumain.jpg',
  'Kuala Terengganu': '/image/daerah/ktmain.jpeg',
  'Kemaman': '/image/daerah/kemamanmain.jpg',
  'Dungun': '/image/daerah/dungunmain.jpg',
}

const currentDistrictColor = computed(() => {
  return districtColors[districtStore.districtName] || districtColors['Hulu Terengganu']
})

const currentDistrictBg = computed(() => {
  return districtBackgrounds[districtStore.districtName] || districtBackgrounds['Hulu Terengganu']
})

const { prefixPath } = useDistrictRoutes()

const { isAuthenticated } = storeToRefs(authStore)
const { categories } = storeToRefs(facilityStore)

// Use categories loaded from backend. The store already enriches
// categories with `icon` and `facility_count` in `fetchCategories()`.
const displayCategories = computed(() => categories.value)

const popularFacilities = ref([])

onMounted(async () => {
  // Check if district parameter is passed in URL
  if (route.query.district) {
    districtStore.setDistrict(route.query.district)
  }

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
    path: prefixPath('/facilities'),
    query: { category: categoryId }
  })
}
</script>

<style scoped>
.home {
  width: 100%;
}

.hero {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  color: white;
  padding: 6rem 2rem;
  text-align: center;
  position: relative;
}

.hero-content h1 {
  font-size: clamp(2rem, 4vw, 3rem);
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
  font-weight: 700;
}

.subtitle {
  font-size: clamp(1.1rem, 1.6vw, 1.3rem);
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
  opacity: 0.95;
}

.hero-buttons {
  display: flex;
  gap: clamp(0.75rem, 1.2vw, 1rem);
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: clamp(0.75rem, 1.2vw, 1rem) clamp(1.5rem, 2.5vw, 2rem);
  border-radius: clamp(6px, 0.8vw, 8px);
  text-decoration: none;
  font-weight: 600;
  font-size: clamp(1rem, 1.4vw, 1.1rem);
  transition: all 0.3s ease;
  display: inline-block;
}

.btn-primary {
  background-color: white;
  color: #D77800;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb), 0.3);
}

.btn-secondary {
  background-color: transparent;
  color: white;
  border: 2px solid white;
}

.btn-secondary:hover {
  background-color: white;
  color: var(--theme-primary);
}

.btn-large {
  padding: clamp(1rem, 1.5vw, 1.2rem) clamp(2rem, 3.5vw, 3rem);
  font-size: clamp(1.1rem, 1.5vw, 1.2rem);
  background-color: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
}

.btn-large:hover {
  background-color: var(--theme-primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(var(--theme-primary-rgb), 0.3);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 clamp(1rem, 2vw, 2rem);
}

section {
  padding: clamp(2.5rem, 5vw, 4rem) 0;
}

section h2 {
  text-align: center;
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  margin-bottom: clamp(2rem, 3.5vw, 3rem);
  color: #2c3e50;
}

.categories {
  background-color: #f8f9fa;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(1.5rem, 2.5vw, 2rem);
}

.category-card {
  background: white;
  padding: clamp(1.5rem, 2.5vw, 2rem);
  border-radius: clamp(10px, 1.2vw, 12px);
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
  font-size: clamp(2.5rem, 4vw, 3.5rem);
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
}

.category-card h3 {
  color: #2c3e50;
  margin-bottom: clamp(0.4rem, 0.6vw, 0.5rem);
  font-size: clamp(1.2rem, 1.8vw, 1.5rem);
}

.category-card p {
  color: #7f8c8d;
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
}

.facility-count {
  display: inline-block;
  background-color: rgba(var(--theme-primary-rgb), 0.1);
  color: var(--theme-primary);
  padding: clamp(0.4rem, 0.6vw, 0.5rem) clamp(0.8rem, 1.2vw, 1rem);
  border-radius: clamp(16px, 2vw, 20px);
  font-size: clamp(0.85rem, 1.1vw, 0.9rem);
  font-weight: 600;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(1.5rem, 2.5vw, 2rem);
}

.feature-card {
  text-align: center;
  padding: clamp(1.5rem, 2.5vw, 2rem);
}

.feature-icon {
  font-size: clamp(2.5rem, 3.5vw, 3rem);
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
}

.feature-card h3 {
  color: #2c3e50;
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
  font-size: clamp(1.1rem, 1.5vw, 1.3rem);
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
  gap: clamp(0.75rem, 1.2vw, 1rem);
}

.step {
  flex: 1;
  min-width: 200px;
  text-align: center;
  padding: clamp(1rem, 1.8vw, 1.5rem);
}

.step-number {
  width: clamp(50px, 7vw, 60px);
  height: clamp(50px, 7vw, 60px);
  background: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: clamp(1.3rem, 2vw, 1.5rem);
  font-weight: bold;
  margin: 0 auto clamp(0.75rem, 1.2vw, 1rem);
}

.step h3 {
  color: #2c3e50;
  margin-bottom: clamp(0.4rem, 0.6vw, 0.5rem);
  font-size: clamp(1.05rem, 1.5vw, 1.2rem);
}

.step p {
  color: #7f8c8d;
  font-size: clamp(0.9rem, 1.1vw, 0.95rem);
}

.step-arrow {
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  color: var(--theme-primary);
  font-weight: bold;
}

.cta {
  background: var(--theme-primary);
  color: var(--theme-primary-contrast, #fff);
  text-align: center;
}

.cta h2 {
  color: white;
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
}

.cta p {
  font-size: clamp(1.05rem, 1.5vw, 1.2rem);
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
  opacity: 0.95;
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: clamp(1.5rem, 3vw, 2rem);
  }

  .subtitle {
    font-size: clamp(1rem, 1.4vw, 1.1rem);
  }

  section h2 {
    font-size: clamp(1.5rem, 2.5vw, 1.8rem);
  }

  .step-arrow {
    display: none;
  }

  .steps {
    flex-direction: column;
  }
}
</style>
