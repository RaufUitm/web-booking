// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('../views/LandingPage.vue'),
      meta: { requiresAuth: false }
    },
    // MDHT (Hulu Terengganu) Routes
    {
      path: '/mdht',
      name: 'mdht-home',
      component: () => import('../views/Home.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/mdht/login',
      name: 'mdht-login',
      component: () => import('../views/Auth/Login.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/mdht/register',
      name: 'mdht-register',
      component: () => import('../views/Auth/Register.vue'),
      meta: { requiresAuth: false, guestOnly: true }
    },
    {
      path: '/mdht/facilities',
      name: 'mdht-facilities',
      component: () => import('../views/Facilities/FacilitiesView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/mdht/facilities/:id',
      name: 'mdht-facility-detail',
      component: () => import('../views/Facilities/FacilityDetailView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/mdht/booking/:facilityId',
      name: 'mdht-booking',
      component: () => import('../views/Booking/BookingView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/mdht/my-bookings',
      name: 'mdht-my-bookings',
      component: () => import('../views/Booking/MyBookingsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/mdht/booking-confirmation/:id',
      name: 'mdht-booking-confirmation',
      component: () => import('../views/Booking/BookingConfirmationView.vue'),
      meta: { requiresAuth: true }
    },
    // Legacy routes (redirect to MDHT for backward compatibility)
    {
      path: '/login',
      redirect: '/mdht/login'
    },
    {
      path: '/register',
      redirect: '/mdht/register'
    },
    {
      path: '/facilities',
      redirect: '/mdht/facilities'
    },
    {
      path: '/my-bookings',
      redirect: '/mdht/my-bookings'
    },
    // MDHT Admin Routes
    {
      path: '/mdht/admin',
      name: 'mdht-admin',
      component: () => import('../views/Admin/Dashboard.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/mdht/admin/facilities',
      name: 'mdht-admin-facilities',
      component: () => import('../views/Admin/ManageFacilities.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/mdht/admin/bookings',
      name: 'mdht-admin-bookings',
      component: () => import('../views/Admin/ManageBookings.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/mdht/admin/users',
      name: 'mdht-admin-users',
      component: () => import('../views/Admin/ManageUsers.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/mdht/admin/reports',
      name: 'mdht-admin-reports',
      component: () => import('../views/Admin/Reports.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/mdht/admin/categories',
      name: 'mdht-admin-categories',
      component: () => import('../views/Admin/ManageCategories.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    // Legacy admin routes (redirect to MDHT admin)
    {
      path: '/admin',
      redirect: '/mdht/admin'
    },
    {
      path: '/admin/facilities',
      redirect: '/mdht/admin/facilities'
    },
    {
      path: '/admin/bookings',
      redirect: '/mdht/admin/bookings'
    },
    {
      path: '/admin/users',
      redirect: '/mdht/admin/users'
    },
    {
      path: '/admin/reports',
      redirect: '/mdht/admin/reports'
    },
    {
      path: '/admin/categories',
      redirect: '/mdht/admin/categories'
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
    return next(isAdmin ? '/mdht/admin' : '/mdht')
  }

  // Redirect admins trying to access regular user pages to admin dashboard
  // (except public pages like facilities browsing and main landing page)
  if (isAdmin && to.meta.requiresAuth && !to.meta.requiresAdmin && to.path !== '/' && !to.path.startsWith('/mdht')) {
    return next('/mdht/admin')
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
