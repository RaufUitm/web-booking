<template>
  <div class="payment-success-page">
    <div class="success-container">
      <!-- Loading State -->
      <div v-if="verifying" class="verification-status">
        <div class="spinner"></div>
        <h2>Mengesahkan Pembayaran...</h2>
        <p>Sila tunggu sementara kami mengesahkan pembayaran anda dengan bank.</p>
      </div>

      <!-- Success State -->
      <div v-else-if="verified" class="success-content">
        <div class="success-icon">✓</div>
        <h1>Pembayaran Berjaya!</h1>
        <p class="success-message">
          Tempahan anda telah disahkan. E-mel pengesahan bersama invois telah dihantar.
        </p>

        <div class="booking-details">
          <h3>Butiran Tempahan</h3>
          <div class="detail-row">
            <span class="label">Rujukan Tempahan:</span>
            <strong>{{ bookingDetails?.reference }}</strong>
          </div>
          <div class="detail-row">
            <span class="label">Kemudahan:</span>
            <strong>{{ bookingDetails?.facility }}</strong>
          </div>
          <div class="detail-row">
            <span class="label">Tarikh:</span>
            <strong>{{ formatDate(bookingDetails?.booking_date) }}</strong>
          </div>
          <div class="detail-row">
            <span class="label">Masa:</span>
            <strong>{{ bookingDetails?.start_time }} - {{ bookingDetails?.end_time }}</strong>
          </div>
          <div class="detail-row total">
            <span class="label">Jumlah Dibayar:</span>
            <strong>RM {{ formatAmount(bookingDetails?.total_amount) }}</strong>
          </div>
        </div>

        <div class="action-buttons">
          <button @click="viewInvoice" class="btn-primary">
            📄 Lihat Invois
          </button>
          <button @click="downloadInvoice" class="btn-primary">
            ⬇️ Muat Turun Invois
          </button>
        </div>

        <div class="action-buttons secondary-actions">
          <button @click="viewBooking" class="btn-secondary">
            Lihat Butiran Tempahan
          </button>
          <button @click="goToHome" class="btn-secondary">
            Kembali ke Laman Utama
          </button>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="error-content">
        <div class="error-icon">✕</div>
        <h1>Pengesahan Gagal</h1>
        <p class="error-message">
          Kami tidak dapat mengesahkan pembayaran anda pada masa ini. Jika wang telah ditolak, ia akan dikembalikan dalam tempoh 3-5 hari bekerja.
        </p>
        <div class="action-buttons">
          <button @click="retryVerification" class="btn-primary">
            Cuba Sahkan Semula
          </button>
          <button @click="contactSupport" class="btn-secondary">
            Hubungi Sokongan
          </button>
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

const verifying = ref(true)
const verified = ref(false)
const bookingDetails = ref(null)
// Accept multiple possible query keys for safety
const bookingReference = ref(
  route.query.booking || route.query.reference || route.query.ref || ''
)

const verifyPayment = async () => {
  try {
    verifying.value = true

    if (!bookingReference.value) {
      // If no booking reference, stop spinner and show error state
      verifying.value = false
      verified.value = false
      return
    }

    // Poll for payment verification
    let attempts = 0
    const maxAttempts = 10
    const pollInterval = 2000 // 2 seconds

    const poll = async () => {
      try {
        const response = await api.get('/payment/verify', {
          params: {
            booking_reference: bookingReference.value
          }
        })

        if (response.data.success) {
          const booking = response.data.booking
          const payment = response.data.payment

          // Check if payment is confirmed (normalized)
          if (booking.status === 'confirmed' && payment && payment.payment_status === 'completed') {
            bookingDetails.value = booking
            verified.value = true
            verifying.value = false
            try {
              // Refresh bookings list so "Tempahan Saya" reflects latest status
              const { useBookingStore } = await import('@/stores/booking')
              const bookingStore = useBookingStore()
              bookingStore.fetchBookings()
            } catch (e) {
              // Non-blocking
              console.warn('Could not refresh bookings store:', e)
            }
            return true
          }
        }

        attempts++
        if (attempts < maxAttempts) {
          setTimeout(poll, pollInterval)
        } else {
          // Max attempts reached
          verifying.value = false
          verified.value = false
        }

      } catch (error) {
        console.error('Payment verification error:', error)
        attempts++
        if (attempts < maxAttempts) {
          setTimeout(poll, pollInterval)
        } else {
          verifying.value = false
          verified.value = false
        }
      }
    }

    await poll()

  } catch (error) {
    console.error('Verification error:', error)
    verifying.value = false
    verified.value = false
  }
}

const retryVerification = () => {
  verifyPayment()
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-MY', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toFixed(2)
}

const viewBooking = () => {
  const district = districtStore.currentDistrict || 'besut'
  router.push(`/${district}/my-bookings`)
}

const goToHome = () => {
  const district = districtStore.currentDistrict || 'besut'
  router.push(`/${district}`)
}

const contactSupport = () => {
  // Navigate to support/contact page or show contact info
  alert('Support: support@pbt.gov.my\nPhone: 1-300-XX-XXXX')
}

onMounted(() => {
  verifyPayment()
})

// View invoice in new window/tab
const viewInvoice = async () => {
  try {
    if (!bookingDetails.value?.id) return
    const response = await api.get(`/bookings/${bookingDetails.value.id}/invoice`, { responseType: 'blob' })
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    // Open in new window
    window.open(url, '_blank')
    // Clean up after a delay
    setTimeout(() => window.URL.revokeObjectURL(url), 100)
  } catch (e) {
    console.error('Failed to view invoice:', e)
    alert('Gagal memaparkan invois. Sila cuba lagi.')
  }
}

// Download invoice helper
const downloadInvoice = async () => {
  try {
    if (!bookingDetails.value?.id) return
    const response = await api.get(`/bookings/${bookingDetails.value.id}/invoice`, { responseType: 'blob' })
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `invoice-${bookingDetails.value.reference || bookingDetails.value.id}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error('Failed to download invoice:', e)
    alert('Gagal memuat turun invois. Sila cuba lagi.')
  }
}
</script>

<style scoped>
.payment-success-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.success-container {
  max-width: 600px;
  width: 100%;
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.verification-status {
  text-align: center;
}

.spinner {
  width: 60px;
  height: 60px;
  margin: 0 auto 20px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.success-content,
.error-content {
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #4CAF50;
  color: white;
  font-size: 48px;
  line-height: 80px;
  border-radius: 50%;
  margin: 0 auto 20px;
  animation: scaleIn 0.5s ease-out;
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
}

@keyframes scaleIn {
  0% { transform: scale(0); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

h1 {
  color: #333;
  margin-bottom: 10px;
  font-size: 28px;
}

h2 {
  color: #555;
  margin-bottom: 10px;
  font-size: 24px;
}

.success-message,
.error-message {
  color: #666;
  margin-bottom: 30px;
  font-size: 16px;
  line-height: 1.5;
}

.booking-details {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 24px;
  margin: 30px 0;
  text-align: left;
}

.booking-details h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
  text-align: center;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row.total {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid #ddd;
  border-bottom: none;
  font-size: 18px;
  color: #4CAF50;
}

.label {
  color: #666;
}

.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.action-buttons.secondary-actions {
  margin-top: 12px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 14px 24px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #f5f5f5;
  color: #666;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

@media (max-width: 768px) {
  .success-container {
    padding: 24px;
  }

  h1 {
    font-size: 24px;
  }

  .action-buttons {
    flex-direction: column;
  }
}
</style>
