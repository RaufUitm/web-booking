// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/Home.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/Auth/Login.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/Auth/Register.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/facilities',
      name: 'facilities',
      component: () => import('../views/Facilities/FacilitiesView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/facilities/:id',
      name: 'facility-detail',
      component: () => import('../views/Facilities/FacilityDetailView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/booking/:facilityId',
      name: 'booking',
      component: () => import('../views/Booking/BookingView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/my-bookings',
      name: 'my-bookings',
      component: () => import('../views/Booking/MyBookingsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/booking-confirmation/:id',
      name: 'booking-confirmation',
      component: () => import('../views/Booking/BookingConfirmationView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/Admin/Dashboard.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/facilities',
      name: 'admin-facilities',
      component: () => import('../views/Admin/ManageFacilities.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/bookings',
      name: 'admin-bookings',
      component: () => import('../views/Admin/ManageBookings.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('../views/Admin/ManageUsers.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/reports',
      name: 'admin-reports',
      component: () => import('../views/Admin/Reports.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/categories',
      name: 'admin-categories',
      component: () => import('../views/Admin/ManageCategories.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated
  const isAdmin = authStore.isAdmin

  // Redirect authenticated users away from guest-only pages
  if (to.meta.guestOnly && isAuthenticated) {
    // Admins go to admin dashboard, regular users go to home
    return next(isAdmin ? '/admin' : '/')
  }

  // Redirect admins trying to access regular user pages to admin dashboard
  // (except public pages like facilities browsing)
  if (isAdmin && to.meta.requiresAuth && !to.meta.requiresAdmin && to.path !== '/') {
    return next('/admin')
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({
      name: 'login',
      query: { redirect: to.fullPath }
    })
  }

  // Check if route requires admin
  if (to.meta.requiresAdmin && !isAdmin) {
    return next('/')
  }

  next()
})

export default router
