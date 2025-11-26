<template>
  <div id="app">
    <nav class="navbar" v-if="!$route.meta.hideNav" :style="navbarStyle">
      <div class="nav-container">
        <router-link to="/" class="logo">
          <template v-if="districtStore.currentDistrict">
            <img :src="logoSrc"
                 :alt="`Logo ${districtStore.districtAbbreviation}`"
                 class="logo-img">
            <div>
              <span class="logo-text">{{ districtStore.pbtName }}</span>
              <div class="logo-sub">({{ districtStore.districtAbbreviation }} Booking)</div>
            </div>
          </template>
          <template v-else>
            <img src="/images/terengganu-flag-bw.png" alt="Terengganu Flag" class="logo-img" style="height:2.5vw; width:auto; object-fit:contain;" />
            <div>
              <span class="logo-text">T-Smart Booking</span>
              <div class="logo-sub">Sistem Tempahan Negeri</div>
            </div>
          </template>
        </router-link>

        <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
          ☰
        </button>

        <div class="nav-links" :class="{ 'mobile-open': mobileMenuOpen }">
          <router-link to="/" @click="closeMobileMenu">Laman Utama</router-link>
          <router-link v-if="districtStore.currentDistrict" :to="prefixPath('/facilities')" @click="closeMobileMenu">Kemudahan</router-link>

          <template v-if="isAuthenticated">
            <router-link v-if="districtStore.currentDistrict" :to="prefixPath('/my-bookings')" @click="closeMobileMenu">Tempahan Saya</router-link>
            <router-link v-if="isAdmin" :to="isDistrictAdmin ? prefixPath('/admin') : { name: 'mdht-admin' }" @click="closeMobileMenu">Admin</router-link>

            <div class="user-menu">
              <button class="user-btn" @click="userMenuOpen = !userMenuOpen">
                <span class="user-icon">👤</span>
                <span>{{ userName }}</span>
                <span class="dropdown-arrow">▼</span>
              </button>

              <div v-if="userMenuOpen" class="user-dropdown">
                <div class="user-info">
                  <p class="user-name">{{ userName }}</p>
                  <p class="user-email">{{ user?.email }}</p>
                </div>
                <div class="dropdown-divider"></div>
                <router-link v-if="districtStore.currentDistrict" :to="prefixPath('/my-bookings')" class="dropdown-item" @click="closeUserMenu">
                  📋 Tempahan Saya
                </router-link>
                <router-link :to="prefixPath('/profile')" class="dropdown-item" @click="closeUserMenu">
                  ⚙️ Tetapan Profil
                </router-link>
                <div class="dropdown-divider"></div>
                <button @click="handleLogout" class="dropdown-item logout-btn">
                  🚪 Log Keluar
                </button>
              </div>
            </div>
          </template>

          <template v-else>
            <router-link :to="prefixPath('/login')" class="btn-login" @click="closeMobileMenu">
              Log Masuk
            </router-link>
            <router-link :to="prefixPath('/register')" class="btn-register" @click="closeMobileMenu">
              Daftar
            </router-link>
          </template>
        </div>
      </div>
    </nav>

    <main>
      <router-view />
    </main>

    <footer class="footer" v-if="districtStore.currentDistrict && !$route.meta.hideNav" :style="footerStyle">
      <div class="footer-container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>Sistem Tempahan PBT</h3>
            <p>Platform tempahan online untuk kemudahan awam</p>
          </div>

          <div class="footer-section">
            <h4>Pautan Pantas</h4>
            <ul>
              <li><router-link :to="{ name: 'landing' }">Laman Utama</router-link></li>
              <li><router-link :to="prefixPath('/my-bookings')">Tempahan Saya</router-link></li>
              <li><a :href="districtStore.districtWebsite" target="_blank">Portal {{ districtStore.districtAbbreviation }}</a></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Hubungi Kami</h4>
            <ul class="contact-info">
              <li>📞 {{ districtStore.districtPhone }}</li>
              <li>✉️ {{ districtStore.districtEmail }}</li>
              <li>📍 {{ districtStore.districtAddress }}</li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Ikuti Kami</h4>
            <div class="social-links">
              <a :href="districtStore.districtFacebook" target="_blank" class="social-icon" title="Facebook">📘</a>
              <a :href="districtStore.districtInstagram" target="_blank" class="social-icon" title="Instagram">📸</a>
              <a :href="districtStore.districtWebsite" target="_blank" class="social-icon" title="Portal Rasmi">🌐</a>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; {{ currentYear }} {{ districtStore.pbtName }}. Hak Cipta Terpelihara.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDistrictStore } from '@/stores/district'
import { storeToRefs } from 'pinia'
import useDistrictRoutes from '@/utils/districtRoutes'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const districtStore = useDistrictStore()
const { isAuthenticated, isAdmin, isDistrictAdmin, userName, user } = storeToRefs(authStore)
const { prefixPath } = useDistrictRoutes()

// const baseUrl = import.meta.env.BASE_URL || '/'

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const currentYear = computed(() => new Date().getFullYear())

// Logo probing: try multiple extensions so images can be .png/.jpg/.jpeg/.svg
const logoSrc = ref('/images/terengganu-flag-bw.png')

const tryImage = (url) => {
  return new Promise((resolve) => {
    const img = new Image()
    img.onload = () => resolve(true)
    img.onerror = () => resolve(false)
    img.src = url
  })
}

const probeLogo = async (abbr) => {
  if (!abbr) return '/images/terengganu-flag-bw.png'
  const exts = ['.png', '.jpg', '.jpeg', '.svg']
  for (const ext of exts) {
    const path = `/images/${abbr}${ext}`
    // eslint-disable-next-line no-await-in-loop
    const ok = await tryImage(path)
    if (ok) return path
  }
  return `/images/${abbr}.png`
}

onMounted(async () => {
  // initial logo probe based on any current district
  if (districtStore.currentDistrict) {
    const abbr = districtStore.districtAbbreviation
    logoSrc.value = await probeLogo(abbr)
  }
})

// update logo when district changes
watch(() => districtStore.currentDistrict, async (newVal) => {
  if (!newVal) {
    logoSrc.value = '/images/terengganu-flag-bw.png'
    return
  }
  const abbr = districtStore.districtAbbreviation
  try {
    logoSrc.value = await probeLogo(abbr)
  } catch (e) {
    console.error('Logo probe failed', e)
    logoSrc.value = `/images/${abbr}.png`
  }
})

// Navbar style uses current district color
function hexToRgb(hex) {
  const sanitized = hex.replace('#', '')
  const bigint = parseInt(sanitized, 16)
  const r = (bigint >> 16) & 255
  const g = (bigint >> 8) & 255
  const b = bigint & 255
  return { r, g, b }
}

function getContrastColor(hex) {
  const { r, g, b } = hexToRgb(hex)
  const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return lum > 0.6 ? '#111' : '#fff'
}

const navbarStyle = computed(() => {
  // Keep navbar black on the public landing page for visual consistency
  if (route.name === 'landing' || route.path === '/') {
    return {
      backgroundColor: '#111',
      color: '#fff'
    }
  }

  const main = districtStore.districtColor || '#111'
  return {
    backgroundColor: main,
    color: getContrastColor(main)
  }
})

const footerStyle = computed(() => {
  const color = districtStore.districtColor || '#D77800'
  // Darken the color for footer
  return {
    backgroundColor: darkenColor(color, 30)
  }
})

// Helper function to darken a hex color
const darkenColor = (color, percent) => {
  const num = parseInt(color.replace('#', ''), 16)
  const amt = Math.round(2.55 * percent)
  const R = (num >> 16) - amt
  const G = (num >> 8 & 0x00FF) - amt
  const B = (num & 0x0000FF) - amt
  return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
    (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
    (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1).toUpperCase()
}

onMounted(() => {
  // Initialize auth from localStorage
  authStore.initializeAuth()

  // Check if district parameter is in URL
  if (route.query.district) {
    districtStore.setDistrict(route.query.district)
  }
})

const handleLogout = async () => {
  await authStore.logout()
  userMenuOpen.value = false
  router.push('/')
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const closeUserMenu = () => {
  userMenuOpen.value = false
  closeMobileMenu()
}

// landing navigation uses plain anchors (full page navigation) via `baseUrl`

// Close dropdowns when clicking outside
if (typeof window !== 'undefined') {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.user-menu')) {
      userMenuOpen.value = false
    }
  })
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background-color: #f5f7fa;
  color: #2c3e50;
}

/* Headings */
h1, h2, h3, h4, h5 {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  font-weight: 700;
  letter-spacing: -0.01em;
}

/* Body text weights */
p, a, li, input, button {
  font-weight: 400;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}

/* Navbar */
.navbar {
  background-color: #111;
  padding: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  position: sticky;
  top: 0;
  z-index: 1000;
  color: #fff;
}

.nav-container {
  max-width: 70vw;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  font-size: 1.2rem;
}

.logo-img {
  height: 50px;
  width: 50px;
  object-fit: contain;
}

.logo-icon {
  font-size: 1.8rem;
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.nav-links {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.nav-links a {
  color: #fff;
  text-decoration: none;
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  transition: background-color 0.3s ease;
  font-weight: 500;
}

.nav-links a:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-links a.router-link-active {
  background-color: rgba(255, 255, 255, 0.15);
}

.btn-login {
  background-color: transparent;
  border: 2px solid #fff;
  color: #fff;
  margin-left: 0.5rem;
  padding: 0.5rem 0.9rem;
  border-radius: 6px;
}

.btn-register {
  /* Match login button: white outline, transparent background */
  background-color: transparent;
  border: 2px solid #fff;
  color: #fff;
  margin-left: 0.5rem;
  padding: 0.5rem 0.9rem;
  border-radius: 6px;
  font-weight: 600;
}

.btn-register:hover {
  background-color: rgba(255,255,255,0.08);
  transform: translateY(-1px);
}

/* User Menu */
.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.user-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.user-icon {
  font-size: 1.2rem;
}

.dropdown-arrow {
  font-size: 0.7rem;
  transition: transform 0.3s ease;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  min-width: 250px;
  overflow: hidden;
  animation: dropdownFade 0.2s ease;
}

@keyframes dropdownFade {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.user-info {
  padding: 1rem;
  background-color: #f8f9fa;
}

.user-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.user-email {
  font-size: 0.85rem;
  color: #7f8c8d;
}

.dropdown-divider {
  height: 1px;
  background-color: #ecf0f1;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.875rem 1rem;
  background: none;
  border: none;
  text-align: left;
  color: #2c3e50;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 0.95rem;
  color: rgba(255,255,255,0.85);
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.logout-btn {
  color: #e74c3c;
  font-weight: 600;
}

/* Footer */
.footer {
  background-color: #B36200;
  color: white;
  padding: 3rem 0 1rem;
  margin-top: 4rem;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-section h3 {
  margin-bottom: 1rem;
  font-size: 1.3rem;
}

.footer-section h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
  color: #ecf0f1;
}

.footer-section p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

.footer-section ul {
  list-style: none;
}

.footer-section ul li {
  margin-bottom: 0.5rem;
}

.footer-section a {
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-section a:hover {
  color: white;
}

.contact-info li {
  color: rgba(255, 255, 255, 0.8);
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-icon {
  font-size: 1.5rem;
  transition: transform 0.3s ease;
}

.social-icon:hover {
  transform: translateY(-3px);
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1.5rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }

  .nav-links {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    background-color: #2c3e50;
    flex-direction: column;
    padding: 1rem;
    gap: 0.5rem;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .nav-links.mobile-open {
    transform: translateX(0);
  }

  .nav-links a {
    width: 100%;
    text-align: center;
  }

  .user-menu {
    width: 100%;
  }

  .user-btn {
    width: 100%;
    justify-content: center;
  }

  .user-dropdown {
    position: static;
    box-shadow: none;
    margin-top: 0.5rem;
  }

  .footer-content {
    grid-template-columns: 1fr;
  }
}
</style>
