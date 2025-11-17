<template>
  <div id="app">
    <nav class="navbar" v-if="!isLandingPage">
      <div class="nav-container">
        <router-link to="/mdht" class="logo">
          <img src="/images/MDHT.png" alt="Logo MDHT" class="logo-img">
          <span class="logo-text">MDHT Booking System</span>
        </router-link>

        <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
          ☰
        </button>

        <div class="nav-links" :class="{ 'mobile-open': mobileMenuOpen }">
          <router-link to="/mdht" @click="closeMobileMenu">Laman Utama</router-link>
          <router-link to="/mdht/facilities" @click="closeMobileMenu">Kemudahan</router-link>

          <template v-if="isAuthenticated">
            <router-link to="/my-bookings" @click="closeMobileMenu">Tempahan Saya</router-link>
            <router-link v-if="isAdmin" to="/admin" @click="closeMobileMenu">Admin</router-link>

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
                <router-link to="/my-bookings" class="dropdown-item" @click="closeUserMenu">
                  📋 Tempahan Saya
                </router-link>
                <router-link to="/profile" class="dropdown-item" @click="closeUserMenu">
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
            <router-link to="/login" class="btn-login" @click="closeMobileMenu">
              Log Masuk
            </router-link>
            <router-link to="/register" class="btn-register" @click="closeMobileMenu">
              Daftar
            </router-link>
          </template>
        </div>
      </div>
    </nav>

    <main>
      <router-view />
    </main>

    <footer class="footer" v-if="!isLandingPage">
      <div class="footer-container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>Sistem Tempahan PBT</h3>
            <p>Platform tempahan online untuk kemudahan awam</p>
          </div>

          <div class="footer-section">
            <h4>Pautan Pantas</h4>
            <ul>
              <li><router-link to="/">Laman Utama</router-link></li>
              <li><router-link to="/facilities">Kemudahan</router-link></li>
              <li><router-link to="/my-bookings">Tempahan Saya</router-link></li>
              <li><a href="https://mdht.gov.my" target="_blank">Portal MDHT</a></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Hubungi Kami</h4>
            <ul class="contact-info">
              <li>📞 +60 9-626 1333</li>
              <li>✉️ info@mdht.terengganu.gov.my</li>
              <li>📍 Majlis Daerah Hulu Terengganu, 21700 Kuala Berang, Terengganu</li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Ikuti Kami</h4>
            <div class="social-links">
              <a href="https://www.facebook.com/MDHuluTerengganu" target="_blank" class="social-icon" title="Facebook">📘</a>
              <a href="https://www.instagram.com/mdhuluterengganu" target="_blank" class="social-icon" title="Instagram">📸</a>
              <a href="https://mdht.terengganu.gov.my" target="_blank" class="social-icon" title="Portal Rasmi">🌐</a>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; {{ currentYear }} Majlis Daerah Hulu Terengganu. Hak Cipta Terpelihara.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { isAuthenticated, isAdmin, userName, user } = storeToRefs(authStore)

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const isLandingPage = computed(() => route.path === '/')

onMounted(() => {
  // Initialize auth from localStorage
  authStore.initializeAuth()
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
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f7fa;
  color: #2c3e50;
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
  background-color: #D77800;
  padding: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.nav-container {
  max-width: 1400px;
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
  gap: 0.75rem;
  color: white;
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
  color: white;
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
  border: 2px solid white;
  margin-left: 0.5rem;
}

.btn-register {
  background: linear-gradient(135deg, #FF8C00 0%, #D77800 100%);
  border: none;
}

.btn-register:hover {
  background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
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
