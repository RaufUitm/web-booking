<template>
  <nav class="navbar" :style="navbarStyle">
    <div class="navbar-container">
      <router-link to="/" class="navbar-brand">
        <span class="logo">🏛️</span>
        <span class="brand-name">{{ districtStore.pbtName }}</span>
      </router-link>

      <button class="mobile-menu-toggle" @click="toggleMobileMenu">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <div :class="['navbar-menu', { active: mobileMenuOpen }]">
        <div class="navbar-links">
          <!-- Admin Navigation -->
          <template v-if="isAdmin">
            <router-link :to="prefixPath('/admin')" class="nav-link" @click="closeMobileMenu">
              Dashboard
            </router-link>
            <router-link :to="prefixPath('/admin/bookings')" class="nav-link" @click="closeMobileMenu">
              Tempahan
            </router-link>
            <router-link :to="prefixPath('/admin/facilities')" class="nav-link" @click="closeMobileMenu">
              Kemudahan
            </router-link>
            <router-link :to="prefixPath('/admin/users')" class="nav-link" @click="closeMobileMenu">
              Pengguna
            </router-link>
            <router-link :to="prefixPath('/admin/categories')" class="nav-link" @click="closeMobileMenu">
              Kategori
            </router-link>
            <router-link :to="prefixPath('/admin/reports')" class="nav-link" @click="closeMobileMenu">
              Laporan
            </router-link>
          </template>
          <!-- Regular User Navigation -->
          <template v-else>
            <router-link to="/" class="nav-link" @click="closeMobileMenu">
              Laman Utama
            </router-link>
            <router-link :to="prefixPath('/facilities')" class="nav-link" @click="closeMobileMenu">
              Facilities
            </router-link>
            <router-link v-if="isAuthenticated" :to="prefixPath('/my-bookings')" class="nav-link" @click="closeMobileMenu">
              My Bookings
            </router-link>
          </template>
        </div>

        <div class="navbar-actions">
          <template v-if="isAuthenticated">
            <div class="user-menu">
              <button @click="toggleUserMenu" class="user-button">
                <span class="user-icon">👤</span>
                <span class="user-name">{{ user?.name }}</span>
              </button>
              <div v-if="userMenuOpen" class="user-dropdown">
                <router-link :to="prefixPath('/profile')" class="dropdown-item" @click="closeUserMenu">
                  Profile
                </router-link>
                <router-link :to="prefixPath('/my-bookings')" class="dropdown-item" @click="closeUserMenu">
                  My Bookings
                </router-link>
                <button @click="handleLogout" class="dropdown-item logout">
                  Logout
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link :to="prefixPath('/login')" class="btn-login" @click="closeMobileMenu">
              Login
            </router-link>
            <router-link :to="prefixPath('/register')" class="btn-register" @click="closeMobileMenu">
              Register
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import useDistrictRoutes from '@/utils/districtRoutes'
import { useDistrictStore } from '@/stores/district'

defineOptions({
  name: 'AppNavbar'
})

const router = useRouter()
const authStore = useAuthStore()
const { prefixPath } = useDistrictRoutes()
const districtStore = useDistrictStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const currentDistrictColor = computed(() => districtStore.districtColor || '#FF8C00')

function hexToRgb(hex) {
  const sanitized = hex.replace('#', '')
  const bigint = parseInt(sanitized, 16)
  const r = (bigint >> 16) & 255
  const g = (bigint >> 8) & 255
  const b = bigint & 255
  return { r, g, b }
}

function hexToRgbaStr(hex, alpha = 1) {
  const { r, g, b } = hexToRgb(hex)
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

function getContrastColor(hex) {
  const { r, g, b } = hexToRgb(hex)
  // Perceived luminance
  const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return lum > 0.6 ? '#111' : '#ffffff'
}

const navbarStyle = computed(() => {
  const main = currentDistrictColor.value
  return {
    background: main,
    color: getContrastColor(main),
    '--accent-color': main,
    '--accent-rgba': hexToRgbaStr(main, 0.08)
  }
})

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const isAdmin = computed(() => authStore.user?.role === 'admin')

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const closeUserMenu = () => {
  userMenuOpen.value = false
  closeMobileMenu()
}

const handleLogout = async () => {
  await authStore.logout()
  closeUserMenu()
  router.push('/')
}

const handleClickOutside = (event) => {
  const userMenu = document.querySelector('.user-menu')
  if (userMenu && !userMenu.contains(event.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.navbar {
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 0 clamp(8px, 1.5vw, 20px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 64px;
}

.navbar-brand {
  display: flex;
  align-items: center;
  text-decoration: none;
  font-weight: 700;
  font-size: clamp(11px, 1.2vw, 16px);
  color: inherit;
  white-space: nowrap;
  flex-shrink: 0;
  gap: clamp(4px, 0.5vw, 8px);
}

.logo {
  font-size: clamp(18px, 2vw, 26px);
  margin-right: 0;
}

.brand-name {
  color: inherit;
}

.mobile-menu-toggle {
  display: none;
  flex-direction: column;
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
}

.mobile-menu-toggle span {
  width: 25px;
  height: 3px;
  background-color: currentColor;
  margin: 3px 0;
  transition: 0.3s;
}

.navbar-menu {
  display: flex;
  align-items: center;
  gap: clamp(12px, 2vw, 24px);
  flex: 1;
  justify-content: flex-end;
}

.navbar-links {
  display: flex;
  gap: clamp(1px, 0.3vw, 8px);
  flex-wrap: nowrap;
  align-items: center;
}

.nav-link {
  text-decoration: none;
  color: inherit;
  font-weight: 500;
  font-size: clamp(10px, 1vw, 14px);
  padding: clamp(3px, 0.5vw, 6px) clamp(4px, 0.8vw, 10px);
  border-radius: 4px;
  transition: all 0.3s;
  white-space: nowrap;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--accent-color);
  background-color: var(--accent-rgba);
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: clamp(4px, 0.6vw, 10px);
  flex-shrink: 0;
}

.user-menu {
  position: relative;
}

.user-button {
  display: flex;
  align-items: center;
  gap: clamp(3px, 0.4vw, 6px);
  padding: clamp(4px, 0.6vw, 6px) clamp(8px, 1vw, 12px);
  background-color: rgba(255,255,255,0.12);
  border: none;
  border-radius: 20px;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: clamp(10px, 1vw, 13px);
  white-space: nowrap;
}

.user-button:hover {
  background-color: rgba(255,255,255,0.18);
}

.user-icon {
  font-size: clamp(14px, 1.5vw, 18px);
}

.user-name {
  font-weight: 500;
  color: #333;
  max-width: clamp(60px, 8vw, 100px);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: clamp(10px, 1vw, 13px);
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  overflow: hidden;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 12px 20px;
  text-align: left;
  text-decoration: none;
  color: #333;
  background: none;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: 14px;
}

.dropdown-item:hover {
  background-color: #f5f5f5;
}

.dropdown-item.logout {
  color: #f44336;
  border-top: 1px solid #eee;
}

.btn-login,
.btn-register {
  padding: clamp(4px, 0.6vw, 6px) clamp(8px, 1.2vw, 16px);
  text-decoration: none;
  border-radius: 4px;
  font-weight: 500;
  font-size: clamp(10px, 1vw, 13px);
  transition: all 0.3s;
  white-space: nowrap;
}

.btn-login {
  color: var(--accent-color);
  border: 2px solid var(--accent-color);
}

.btn-login:hover {
  background-color: var(--accent-color);
  color: white;
}

.btn-register {
  background-color: var(--accent-color);
  color: white;
}

.btn-register:hover {
  filter: brightness(0.95);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

/* ========================================
   RESPONSIVE BREAKPOINTS
   Following mobile-first approach
   ======================================== */

/* Large Desktop (1200px+) - Default styles above apply */

/* Desktop/Laptop (max-width: 1300px) */
@media (max-width: 1300px) {
  .navbar-container {
    padding: 0 10px;
  }

  .navbar-brand {
    font-size: 11px;
  }

  .logo {
    font-size: 20px;
  }

  .navbar-links {
    gap: 1px;
  }

  .nav-link {
    font-size: 10px;
    padding: 3px 5px;
  }

  .user-button {
    padding: 4px 8px;
    font-size: 10px;
  }

  .user-icon {
    font-size: 14px;
  }

  .user-name {
    max-width: 50px;
    font-size: 9px;
  }

  .btn-login,
  .btn-register {
    padding: 4px 8px;
    font-size: 10px;
  }
}

/* Tablet/Small Laptop (max-width: 1100px) - Hide user name */
@media (max-width: 1100px) {
  .navbar-container {
    height: 60px;
    padding: 0 8px;
  }

  .navbar-brand {
    font-size: 10px;
  }

  .logo {
    font-size: 18px;
  }

  .navbar-menu {
    gap: 6px;
  }

  .navbar-links {
    gap: 0px;
  }

  .nav-link {
    font-size: 9px;
    padding: 2px 4px;
  }

  /* Hide user name on smaller screens to save space */
  .user-name {
    display: none !important;
  }

  .user-button {
    padding: 5px 6px;
  }

  .btn-login,
  .btn-register {
    padding: 3px 6px;
    font-size: 9px;
  }
}

/* Mobile/Tablet (max-width: 767px) - Mobile Menu */
@media (max-width: 767px) {
  .mobile-menu-toggle {
    display: flex;
    width: 30px;
    height: 24px;
    justify-content: space-around;
    padding: 0;
    z-index: 10;
    transition: transform 0.3s ease;
  }

  .mobile-menu-toggle:hover {
    transform: scale(1.1);
  }

  .mobile-menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: currentColor;
    border-radius: 10px;
    transition: all 0.3s linear;
    transform-origin: 1px;
  }

  /* Animated hamburger to X */
  .mobile-menu-toggle.active span:nth-child(1) {
    transform: rotate(45deg);
  }

  .mobile-menu-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: translateX(20px);
  }

  .mobile-menu-toggle.active span:nth-child(3) {
    transform: rotate(-45deg);
  }

  .navbar-container {
    height: clamp(54px, 7vh, 64px);
  }

  .navbar-menu {
    position: fixed;
    top: clamp(54px, 7vh, 64px);
    right: -100%;
    width: 100%;
    height: calc(100vh - clamp(54px, 7vh, 64px));
    background-color: white;
    flex-direction: column;
    padding: clamp(16px, 3vw, 24px);
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    gap: clamp(16px, 3vw, 24px);
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .navbar-menu.active {
    right: 0;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .navbar-links {
    flex-direction: column;
    width: 100%;
    gap: clamp(6px, 1.5vw, 10px);
  }

  .nav-link {
    width: 100%;
    text-align: center;
    padding: clamp(10px, 2vw, 14px);
    font-size: clamp(14px, 2.5vw, 16px);
    border-radius: 8px;
    font-weight: 500;
  }

  .navbar-actions {
    flex-direction: column;
    width: 100%;
    gap: clamp(8px, 2vw, 12px);
    padding-top: clamp(8px, 2vw, 12px);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }

  .btn-login,
  .btn-register {
    width: 100%;
    text-align: center;
    padding: clamp(10px, 2vw, 12px);
    font-size: clamp(14px, 2.5vw, 16px);
    font-weight: 600;
  }

  .user-button {
    width: 100%;
    justify-content: center;
    padding: clamp(10px, 2vw, 12px);
    font-size: clamp(14px, 2.5vw, 16px);
  }

  .user-name {
    display: inline;
    max-width: none;
    font-size: clamp(14px, 2.5vw, 16px);
  }

  .user-icon {
    font-size: clamp(18px, 3vw, 22px);
  }

  .user-dropdown {
    position: static;
    margin-top: clamp(8px, 2vw, 12px);
    box-shadow: none;
    border: 1px solid rgba(0, 0, 0, 0.1);
  }
}

/* Small Mobile (max-width: 480px) */
@media (max-width: 480px) {
  .navbar-container {
    height: 52px;
    padding: 0 12px;
  }

  .navbar-brand {
    font-size: 12px;
  }

  .logo {
    font-size: 18px;
    margin-right: 4px;
  }

  .navbar-menu {
    top: 52px;
    height: calc(100vh - 52px);
    padding: 16px;
    gap: 12px;
  }

  .navbar-links {
    gap: 6px;
  }

  .nav-link {
    padding: 10px;
    font-size: 14px;
  }

  .btn-login,
  .btn-register {
    padding: 10px;
    font-size: 14px;
  }

  .user-button {
    padding: 10px;
    font-size: 14px;
  }

  .user-icon {
    font-size: 18px;
  }
}

/* Menu fade animation */
@keyframes menuFadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.navbar-menu.active {
  animation: menuFadeIn 0.3s ease;
}
</style>
