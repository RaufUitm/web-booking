import { createRouter, createWebHistory } from 'vue-router'
import BookingView from '../views/BookingView.vue'
import BookingsListView from '../views/BookingsListView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'booking',
      component: BookingView
    },
    {
      path: '/bookings',
      name: 'bookings',
      component: BookingsListView
    }
  ]
})

export default router
