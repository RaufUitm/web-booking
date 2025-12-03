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
      meta: { requiresAuth: false, guestOnly: true, hideNav: true }
    },
    {
      path: '/mdht/register',
      name: 'mdht-register',
      component: () => import('../views/Auth/Register.vue'),
      meta: { requiresAuth: false, guestOnly: true, hideNav: true }
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
    {
      path: '/mdht/profile',
      name: 'mdht-profile',
      component: () => import('../views/Auth/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    // Generic district-prefixed routes (multi-tenancy)
    {
      path: '/:district',
      name: 'district-home',
      component: () => import('../views/Home.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/:district/login',
      name: 'district-login',
      component: () => import('../views/Auth/Login.vue'),
      meta: { requiresAuth: false, guestOnly: true, hideNav: true }
    },
    {
      path: '/:district/register',
      name: 'district-register',
      component: () => import('../views/Auth/Register.vue'),
      meta: { requiresAuth: false, guestOnly: true, hideNav: true }
    },
    {
      path: '/:district/facilities',
      name: 'district-facilities',
      component: () => import('../views/Facilities/FacilitiesView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/:district/facilities/:id',
      name: 'district-facility-detail',
      component: () => import('../views/Facilities/FacilityDetailView.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/:district/booking/:facilityId',
      name: 'district-booking',
      component: () => import('../views/Booking/BookingView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:district/my-bookings',
      name: 'district-my-bookings',
      component: () => import('../views/Booking/MyBookingsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:district/profile',
      name: 'district-profile',
      component: () => import('../views/Auth/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:district/booking-confirmation/:id',
      name: 'district-booking-confirmation',
      component: () => import('../views/Booking/BookingConfirmationView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:district/payment/success',
      name: 'district-payment-success',
      component: () => import('../views/Payment/PaymentSuccess.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/:district/payment/failed',
      name: 'district-payment-failed',
      component: () => import('../views/Payment/PaymentFailed.vue'),
      meta: { requiresAuth: false }
    },
    // Legacy routes (redirect to MDHT for backward compatibility)
    {
      path: '/login',
      name: 'login',
      redirect: { name: 'mdht-login' }
    },
    {
      path: '/register',
      name: 'register',
      redirect: { name: 'mdht-register' }
    },
    {
      path: '/facilities',
      redirect: { name: 'mdht-facilities' }
    },
    {
      path: '/my-bookings',
      redirect: { name: 'mdht-my-bookings' }
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
    {
      path: '/mdht/admin/admins',
      name: 'mdht-admin-admins',
      component: () => import('../views/Admin/ManageAdmins.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    // Legacy admin routes (redirect to MDHT admin)
    {
      path: '/admin',
      redirect: { name: 'mdht-admin' }
    },
    {
      path: '/admin/facilities',
      redirect: { name: 'mdht-admin-facilities' }
    },
    {
      path: '/admin/bookings',
      redirect: { name: 'mdht-admin-bookings' }
    },
    {
      path: '/admin/users',
      redirect: { name: 'mdht-admin-users' }
    },
    {
      path: '/admin/reports',
      redirect: { name: 'mdht-admin-reports' }
    },
    {
      path: '/admin/categories',
      redirect: { name: 'mdht-admin-categories' }
    },
    {
      path: '/admin/admins',
      redirect: { name: 'mdht-admin-admins' }
    }
    ,
    // District-prefixed admin routes (allow district admins to manage their own district)
    {
      path: '/:district/admin',
      name: 'district-admin',
      component: () => import('../views/Admin/Dashboard.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/facilities',
      name: 'district-admin-facilities',
      component: () => import('../views/Admin/ManageFacilities.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/bookings',
      name: 'district-admin-bookings',
      component: () => import('../views/Admin/ManageBookings.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/users',
      name: 'district-admin-users',
      component: () => import('../views/Admin/ManageUsers.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/reports',
      name: 'district-admin-reports',
      component: () => import('../views/Admin/Reports.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/categories',
      name: 'district-admin-categories',
      component: () => import('../views/Admin/ManageCategories.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/:district/admin/admins',
      name: 'district-admin-admins',
      component: () => import('../views/Admin/ManageAdmins.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isAuthenticated
  const isAdmin = authStore.isAdmin
  const isDistrictAdmin = authStore.isDistrictAdmin

  // Redirect authenticated users away from guest-only pages
  if (to.meta.guestOnly && isAuthenticated) {
    // Admins go to admin dashboard, regular users go to home
    return next(isAdmin ? { name: 'mdht-admin' } : { name: 'mdht-home' })
  }

  // Prevent district admins from accessing MDHT/global admin pages
  // MDHT admin routes are the system/global admin panel; district admins should not use them.
  if (isAuthenticated && isDistrictAdmin && to.meta.requiresAdmin && to.path.startsWith('/mdht')) {
    // Redirect district admin to their district home if available, otherwise landing
    const userDistrict = authStore.userDistrict
    if (userDistrict) {
      const slug = userDistrict.toLowerCase().replace(/\s+/g, '-')
      return next({ name: 'district-home', params: { district: slug } })
    }
    return next({ name: 'landing' })
  }

    // Previously we redirected admins away from regular user pages to the admin dashboard.
    // That prevented admins from accessing their own bookings. We no longer perform that redirect,
    // so admins can view regular authenticated pages like 'my-bookings'.

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
