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
              <a :href="districtStore.districtFacebook" target="_blank" class="social-icon facebook" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a :href="districtStore.districtInstagram" target="_blank" class="social-icon instagram" title="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
              <a :href="districtStore.districtWebsite" target="_blank" class="social-icon website" title="Portal Rasmi">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
              </a>
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

  // For admin pages, if no district is selected (clicked from landing), keep black
  // If district is selected (clicked from district home), use district color
  if (route.path.includes('/admin')) {
    if (!districtStore.currentDistrict) {
      return {
        backgroundColor: '#111',
        color: '#fff'
      }
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
  // Clean the hex string
  let hex = color.replace('#', '')
  // Remove alpha channel if present
  if (hex.length > 6) hex = hex.substring(0, 6)
  
  const num = parseInt(hex, 16)
  const factor = 1 - (percent / 100)
  
  let R = Math.floor(((num >> 16) & 0xFF) * factor)
  let G = Math.floor(((num >> 8) & 0xFF) * factor)
  let B = Math.floor((num & 0xFF) * factor)
  
  // Clamp values
  R = Math.max(0, Math.min(255, R))
  G = Math.max(0, Math.min(255, G))
  B = Math.max(0, Math.min(255, B))
  
  return '#' + ((R << 16) | (G << 8) | B).toString(16).padStart(6, '0').toUpperCase()
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
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 clamp(1rem, 2vw, 2rem);
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  font-size: 1.1rem;
}

.logo-img {
  height: 45px;
  width: 45px;
  object-fit: contain;
}

.logo-icon {
  font-size: 1.5rem;
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
  padding: 0.65rem 1rem;
  border-radius: 6px;
  transition: background-color 0.3s ease;
  font-weight: 500;
  font-size: 0.95rem;
  white-space: nowrap;
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
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.95rem;
  white-space: nowrap;
}

.btn-login:hover {
  background-color: rgba(255,255,255,0.08);
}

.btn-register {
  background-color: transparent;
  border: 2px solid #fff;
  color: #fff;
  margin-left: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  white-space: nowrap;
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
  padding: 0.65rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s ease;
  font-size: 0.95rem;
  white-space: nowrap;
}

.user-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.user-icon {
  font-size: 1.1rem;
}

.dropdown-arrow {
  font-size: 0.7rem;
  transition: transform 0.3s ease;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + clamp(0.3rem, 0.5vw, 0.5rem));
  right: 0;
  background: white;
  border-radius: clamp(6px, 0.8vw, 8px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  min-width: clamp(200px, 25vw, 250px);
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
  padding: clamp(0.75rem, 1.2vw, 1rem);
  background-color: #f8f9fa;
}

.user-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.user-email {
  font-size: clamp(0.75rem, 1vw, 0.85rem);
  color: #7f8c8d;
}

.dropdown-divider {
  height: 1px;
  background-color: #ecf0f1;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: clamp(0.7rem, 1vw, 0.875rem) clamp(0.8rem, 1.2vw, 1rem);
  background: none;
  border: none;
  text-align: left;
  color: #2c3e50;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: clamp(0.85rem, 1.1vw, 0.95rem);
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
  padding: clamp(2rem, 4vw, 3rem) 0 clamp(0.75rem, 1.2vw, 1rem);
  margin-top: clamp(2.5rem, 5vw, 4rem);
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 clamp(1rem, 2vw, 2rem);
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: clamp(1.5rem, 2.5vw, 2rem);
  margin-bottom: clamp(1.5rem, 2.5vw, 2rem);
}

.footer-section h3 {
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
  font-size: clamp(1.1rem, 1.6vw, 1.3rem);
}

.footer-section h4 {
  margin-bottom: clamp(0.75rem, 1.2vw, 1rem);
  font-size: clamp(1rem, 1.4vw, 1.1rem);
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
  gap: clamp(0.75rem, 1.2vw, 1rem);
}

.social-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: clamp(36px, 5vw, 42px);
  height: clamp(36px, 5vw, 42px);
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.social-icon svg {
  width: clamp(20px, 3vw, 24px);
  height: clamp(20px, 3vw, 24px);
  color: rgba(255, 255, 255, 0.9);
}

.social-icon:hover {
  transform: translateY(-3px);
}

.social-icon.facebook:hover {
  background-color: #1877f2;
}

.social-icon.instagram:hover {
  background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-icon.website:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.social-icon:hover svg {
  color: #fff;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: clamp(1rem, 1.8vw, 1.5rem);
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
}

/* Tablet & Mobile Responsive */
@media (max-width: 1100px) {
  .nav-container {
    max-width: 100%;
  }
  
  .logo {
    font-size: 1rem;
  }
  
  .logo-img {
    height: 40px;
    width: 40px;
  }
  
  .nav-links a,
  .btn-login,
  .btn-register,
  .user-btn {
    font-size: 0.9rem;
    padding: 0.5rem 0.85rem;
  }
}

@media (max-width: 900px) {
  .logo {
    font-size: 0.95rem;
    gap: 0.5rem;
  }
  
  .logo-img {
    height: 38px;
    width: 38px;
  }
  
  .nav-links {
    gap: 0.35rem;
  }
  
  .nav-links a,
  .btn-login,
  .btn-register,
  .user-btn {
    font-size: 0.85rem;
    padding: 0.45rem 0.75rem;
  }
}

@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }
  
  .logo {
    font-size: 0.9rem;
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
    max-height: calc(100vh - 70px);
    overflow-y: auto;
  }

  .nav-links.mobile-open {
    transform: translateX(0);
  }

  .nav-links a,
  .btn-login,
  .btn-register {
    width: 100%;
    text-align: center;
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
  }

  .user-menu {
    width: 100%;
  }

  .user-btn {
    width: 100%;
    justify-content: center;
    font-size: 0.95rem;
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

@media (max-width: 480px) {
  .nav-container {
    height: 60px;
    padding: 0 1rem;
  }
  
  .logo {
    font-size: 0.85rem;
    gap: 0.4rem;
  }
  
  .logo-img {
    height: 35px;
    width: 35px;
  }
  
  .logo-text {
    font-size: 0.85rem;
  }
  
  .logo-sub {
    font-size: 0.7rem;
  }
  
  .nav-links {
    top: 60px;
  }
}
</style>
