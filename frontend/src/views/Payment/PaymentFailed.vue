<template>
  <div class="payment-failed-page">
    <div class="failed-container">
      <div class="error-icon">✕</div>
      <h1>Pembayaran Gagal</h1>
      
      <p class="error-message">
        Maaf, pembayaran anda tidak dapat diproses pada masa ini.
      </p>

      <div class="failure-info">
        <h3>Apa yang berlaku?</h3>
        <ul>
          <li>Pembayaran dibatalkan oleh pengguna</li>
          <li>Masa tamat pintu pembayaran</li>
          <li>Dana tidak mencukupi</li>
          <li>Bank menolak transaksi</li>
        </ul>
      </div>

      <div v-if="bookingReference" class="booking-info">
        <p><strong>Rujukan Tempahan:</strong> {{ bookingReference }}</p>
        <p class="notice">Tempahan anda masih dalam pengesahan. Anda boleh cuba bayar semula dari halaman tempahan anda.</p>
      </div>

      <div class="action-buttons">
        <button @click="retryPayment" class="btn-primary">
          <span class="icon">🔄</span>
          Cuba Bayar Semula
        </button>
        <button @click="viewBookings" class="btn-secondary">
          <span class="icon">📋</span>
          Lihat Tempahan Saya
        </button>
        <button @click="goToHome" class="btn-tertiary">
          Kembali ke Laman Utama
        </button>
      </div>

      <div class="support-info">
        <h4>Perlukan Bantuan?</h4>
        <p>
          Jika anda terus mengalami masalah, sila hubungi pasukan sokongan kami:
        </p>
        <div class="contact-details">
          <p>📧 E-mel: support@pbt.gov.my</p>
          <p>📞 Telefon: 1-300-XX-XXXX</p>
          <p>🕒 Waktu Operasi: Isnin-Jumaat, 9:00 Pagi - 5:00 Petang</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDistrictStore } from '@/stores/district'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()
const districtStore = useDistrictStore()

const bookingReference = ref(route.query.booking || '')
const bookingId = ref(null)

const loadBookingDetails = async () => {
  if (!bookingReference.value) return

  try {
    const response = await api.get('/payment/verify', {
      params: {
        booking_reference: bookingReference.value
      }
    })

    if (response.data.success) {
      // Store booking ID for retry
      bookingId.value = response.data.booking.id
    }
  } catch (error) {
    console.error('Failed to load booking details:', error)
  }
}

const retryPayment = async () => {
  if (!bookingId.value && !bookingReference.value) {
    goToHome()
    return
  }

  try {
    // If we have booking ID, create new payment
    if (bookingId.value) {
      const response = await api.post('/payment/create', {
        booking_id: bookingId.value
      })

      if (response.data.success && response.data.payment_url) {
        window.location.href = response.data.payment_url
      }
    } else {
      // Otherwise redirect to bookings page
      viewBookings()
    }
  } catch (error) {
    console.error('Retry payment error:', error)
    alert('Failed to retry payment. Please try again from your bookings page.')
    viewBookings()
  }
}

const viewBookings = () => {
  const district = districtStore.currentDistrict || 'besut'
  router.push(`/${district}/bookings`)
}

const goToHome = () => {
  const district = districtStore.currentDistrict || 'besut'
  router.push(`/${district}`)
}

onMounted(() => {
  loadBookingDetails()
})
</script>

<style scoped>
.payment-failed-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  padding: 20px;
}

.failed-container {
  max-width: 600px;
  width: 100%;
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  text-align: center;
}

.error-icon {
  width: 80px;
  height: 80px;
  background: #f44336;
  color: white;
  font-size: 48px;
  line-height: 80px;
  border-radius: 50%;
  margin: 0 auto 20px;
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

h1 {
  color: #333;
  margin-bottom: 10px;
  font-size: 28px;
}

.error-message {
  color: #666;
  margin-bottom: 30px;
  font-size: 16px;
  line-height: 1.5;
}

.failure-info {
  background: #fff3f3;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 24px;
  text-align: left;
}

.failure-info h3 {
  color: #d32f2f;
  margin: 0 0 12px 0;
  font-size: 16px;
}

.failure-info ul {
  margin: 0;
  padding-left: 20px;
  color: #666;
  font-size: 14px;
  line-height: 1.8;
}

.booking-info {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 24px;
}

.booking-info p {
  margin: 8px 0;
  color: #666;
}

.booking-info .notice {
  color: #ff9800;
  font-size: 14px;
  margin-top: 12px;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 24px 0;
}

.btn-primary,
.btn-secondary,
.btn-tertiary {
  width: 100%;
  padding: 14px 24px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background: #f44336;
  color: white;
}

.btn-primary:hover {
  background: #d32f2f;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
}

.btn-secondary {
  background: #2196F3;
  color: white;
}

.btn-secondary:hover {
  background: #1976D2;
}

.btn-tertiary {
  background: #f5f5f5;
  color: #666;
}

.btn-tertiary:hover {
  background: #e0e0e0;
}

.icon {
  font-size: 18px;
}

.support-info {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 2px solid #f0f0f0;
}

.support-info h4 {
  color: #333;
  margin: 0 0 12px 0;
  font-size: 18px;
}

.support-info > p {
  color: #666;
  font-size: 14px;
  margin-bottom: 16px;
}

.contact-details {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 16px;
  text-align: left;
}

.contact-details p {
  margin: 8px 0;
  color: #555;
  font-size: 14px;
}

@media (max-width: 768px) {
  .failed-container {
    padding: 24px;
  }

  h1 {
    font-size: 24px;
  }

  .action-buttons {
    gap: 10px;
  }
}
</style>
