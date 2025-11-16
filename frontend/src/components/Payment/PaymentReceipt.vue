<template>
  <div class="payment-receipt">
    <div class="receipt-header">
      <div class="success-icon">✓</div>
      <h2>Payment Successful!</h2>
      <p class="subtitle">Thank you for your booking</p>
    </div>

    <div class="receipt-body">
      <div class="receipt-section">
        <h3>Transaction Details</h3>
        <div class="detail-row">
          <span class="label">Transaction ID:</span>
          <span class="value">{{ payment.transaction_id }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Payment Method:</span>
          <span class="value">{{ formatPaymentMethod(payment.payment_method) }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Payment Date:</span>
          <span class="value">{{ formatDate(payment.paid_at || new Date()) }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Status:</span>
          <span class="value status">{{ payment.payment_status }}</span>
        </div>
      </div>

      <div class="receipt-section">
        <h3>Booking Details</h3>
        <div class="detail-row">
          <span class="label">Facility:</span>
          <span class="value">{{ booking.facility?.name }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Location:</span>
          <span class="value">{{ booking.facility?.location }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Date:</span>
          <span class="value">{{ formatDate(booking.booking_date) }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Time:</span>
          <span class="value">
            {{ booking.timeSlot?.start_time }} - {{ booking.timeSlot?.end_time }}
          </span>
        </div>
      </div>

      <div class="receipt-section amount">
        <div class="detail-row total">
          <span class="label">Total Paid:</span>
          <span class="value">${{ payment.amount }}</span>
        </div>
      </div>
    </div>

    <div class="receipt-actions">
      <button @click="printReceipt" class="btn-print">
        🖨️ Print Receipt
      </button>
      <button @click="$emit('close')" class="btn-done">
        Done
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

defineProps({
  payment: {
    type: Object,
    required: true
  },
  booking: {
    type: Object,
    required: true
  }
})

defineEmits(['close'])

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Cash',
    credit_card: 'Credit Card',
    debit_card: 'Debit Card',
    online_banking: 'Online Banking',
    'e-wallet': 'E-Wallet'
  }
  return methods[method] || method
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const printReceipt = () => {
  window.print()
}
</script>

<style scoped>
.payment-receipt {
  max-width: 600px;
  margin: 0 auto;
  padding: 30px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.receipt-header {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 30px;
  border-bottom: 2px dashed #ddd;
}

.success-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background-color: #2d5f2e;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: white;
}

h2 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 28px;
}

.subtitle {
  margin: 0;
  color: #666;
  font-size: 16px;
}

.receipt-body {
  margin-bottom: 30px;
}

.receipt-section {
  margin-bottom: 25px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.receipt-section:last-child {
  border-bottom: none;
}

h3 {
  margin: 0 0 15px 0;
  color: #555;
  font-size: 16px;
  font-weight: 600;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 14px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.label {
  color: #666;
}

.value {
  color: #333;
  font-weight: 500;
  text-align: right;
}

.value.status {
  text-transform: capitalize;
  color: #2d5f2e;
  font-weight: 600;
}

.receipt-section.amount {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border-bottom: none;
}

.detail-row.total {
  font-size: 24px;
  color: #2d5f2e;
  font-weight: 700;
}

.receipt-actions {
  display: flex;
  gap: 15px;
}

.btn-print,
.btn-done {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-print {
  background-color: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
}

.btn-print:hover {
  background-color: #e0e0e0;
}

.btn-done {
  background-color: #2d5f2e;
  color: white;
}

.btn-done:hover {
  background-color: #244d25;
}

@media print {
  .receipt-actions {
    display: none;
  }

  .payment-receipt {
    box-shadow: none;
  }
}

@media (max-width: 768px) {
  .payment-receipt {
    padding: 20px;
  }

  h2 {
    font-size: 24px;
  }

  .success-icon {
    width: 60px;
    height: 60px;
    font-size: 36px;
  }
}
</style>
