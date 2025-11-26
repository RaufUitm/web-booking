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
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.navbar-brand {
  display: flex;
  align-items: center;
  text-decoration: none;
  font-weight: 700;
  font-size: 20px;
  color: inherit;
}

.logo {
  font-size: 28px;
  margin-right: 10px;
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
  gap: 30px;
}

.navbar-links {
  display: flex;
  gap: 20px;
}

.nav-link {
  text-decoration: none;
  color: inherit;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 4px;
  transition: all 0.3s;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--accent-color);
  background-color: var(--accent-rgba);
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-menu {
  position: relative;
}

.user-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background-color: rgba(255,255,255,0.12);
  border: none;
  border-radius: 20px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.user-button:hover {
  background-color: rgba(255,255,255,0.18);
}

.user-icon {
  font-size: 20px;
}

.user-name {
  font-weight: 500;
  color: #333;
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
  padding: 8px 20px;
  text-decoration: none;
  border-radius: 4px;
  font-weight: 500;
  transition: all 0.3s;
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

@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: flex;
  }

  .navbar-menu {
    position: fixed;
    top: 70px;
    left: -100%;
    width: 100%;
    height: calc(100vh - 70px);
    background-color: white;
    flex-direction: column;
    padding: 20px;
    transition: left 0.3s;
    gap: 20px;
  }

  .navbar-menu.active {
    left: 0;
  }

  .navbar-links {
    flex-direction: column;
    width: 100%;
  }

  .nav-link {
    width: 100%;
    text-align: center;
    padding: 12px;
  }

  .navbar-actions {
    flex-direction: column;
    width: 100%;
  }

  .btn-login,
  .btn-register {
    width: 100%;
    text-align: center;
  }

  .user-button {
    width: 100%;
    justify-content: center;
  }
}
</style>
