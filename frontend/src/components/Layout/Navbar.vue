<template>
  <nav class="navbar">
    <div class="navbar-container">
      <router-link to="/mdht" class="navbar-brand">
        <span class="logo">🏛️</span>
        <span class="brand-name">MDHT Booking</span>
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
            <router-link to="/mdht/admin" class="nav-link" @click="closeMobileMenu">
              Dashboard
            </router-link>
            <router-link to="/mdht/admin/bookings" class="nav-link" @click="closeMobileMenu">
              Tempahan
            </router-link>
            <router-link to="/mdht/admin/facilities" class="nav-link" @click="closeMobileMenu">
              Kemudahan
            </router-link>
            <router-link to="/mdht/admin/users" class="nav-link" @click="closeMobileMenu">
              Pengguna
            </router-link>
            <router-link to="/mdht/admin/categories" class="nav-link" @click="closeMobileMenu">
              Kategori
            </router-link>
            <router-link to="/mdht/admin/reports" class="nav-link" @click="closeMobileMenu">
              Laporan
            </router-link>
          </template>
          <!-- Regular User Navigation -->
          <template v-else>
            <router-link to="/mdht" class="nav-link" @click="closeMobileMenu">
              Home
            </router-link>
            <router-link to="/mdht/facilities" class="nav-link" @click="closeMobileMenu">
              Facilities
            </router-link>
            <router-link v-if="isAuthenticated" to="/mdht/my-bookings" class="nav-link" @click="closeMobileMenu">
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
                <router-link to="/mdht/profile" class="dropdown-item" @click="closeUserMenu">
                  Profile
                </router-link>
                <router-link to="/mdht/my-bookings" class="dropdown-item" @click="closeUserMenu">
                  My Bookings
                </router-link>
                <button @click="handleLogout" class="dropdown-item logout">
                  Logout
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link to="/mdht/login" class="btn-login" @click="closeMobileMenu">
              Login
            </router-link>
            <router-link to="/mdht/register" class="btn-register" @click="closeMobileMenu">
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

defineOptions({
  name: 'AppNavbar'
})

const router = useRouter()
const authStore = useAuthStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

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
  color: #333;
}

.logo {
  font-size: 28px;
  margin-right: 10px;
}

.brand-name {
  color: #FF8C00;
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
  background-color: #333;
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
  color: #666;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 4px;
  transition: all 0.3s;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: #FF8C00;
  background-color: rgba(255, 140, 0, 0.08);
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
  background-color: #f5f5f5;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.user-button:hover {
  background-color: #e0e0e0;
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
  color: #FF8C00;
  border: 2px solid #FF8C00;
}

.btn-login:hover {
  background-color: #FF8C00;
  color: white;
}

.btn-register {
  background-color: #FF8C00;
  color: white;
}

.btn-register:hover {
  background-color: #E67E00;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(255, 140, 0, 0.3);
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
