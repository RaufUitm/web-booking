<template>
  <div class="payment-form">
    <h2>Payment Details</h2>

    <div class="payment-summary">
      <h3>Order Summary</h3>
      <div class="summary-row">
        <span>Facility:</span>
        <strong>{{ booking.facility?.name }}</strong>
      </div>
      <div class="summary-row">
        <span>Date:</span>
        <strong>{{ formatDate(booking.booking_date) }}</strong>
      </div>
      <div class="summary-row">
        <span>Time:</span>
        <strong>{{ booking.timeSlot?.start_time }} - {{ booking.timeSlot?.end_time }}</strong>
      </div>
      <div class="summary-row total">
        <span>Total Amount:</span>
        <strong>${{ amount }}</strong>
      </div>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Payment Method</label>
        <div class="payment-methods">
          <label
            v-for="method in paymentMethods"
            :key="method.value"
            :class="['payment-method', { selected: form.payment_method === method.value }]"
          >
            <input
              type="radio"
              :value="method.value"
              v-model="form.payment_method"
            />
            <span class="method-icon">{{ method.icon }}</span>
            <span class="method-name">{{ method.label }}</span>
          </label>
        </div>
      </div>

      <div v-if="form.payment_method === 'credit_card' || form.payment_method === 'debit_card'" class="card-details">
        <div class="form-group">
          <label for="card_number">Card Number</label>
          <input
            id="card_number"
            v-model="form.card_number"
            type="text"
            placeholder="1234 5678 9012 3456"
            maxlength="19"
            required
          />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="expiry">Expiry Date</label>
            <input
              id="expiry"
              v-model="form.expiry"
              type="text"
              placeholder="MM/YY"
              maxlength="5"
              required
            />
          </div>

          <div class="form-group">
            <label for="cvv">CVV</label>
            <input
              id="cvv"
              v-model="form.cvv"
              type="text"
              placeholder="123"
              maxlength="4"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="cardholder_name">Cardholder Name</label>
          <input
            id="cardholder_name"
            v-model="form.cardholder_name"
            type="text"
            placeholder="John Doe"
            required
          />
        </div>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-cancel">
          Cancel
        </button>
        <button type="submit" :disabled="loading" class="btn-submit">
          {{ loading ? 'Processing...' : `Pay $${amount}` }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'

const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  amount: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['cancel', 'success'])

const form = ref({
  payment_method: 'cash',
  card_number: '',
  expiry: '',
  cvv: '',
  cardholder_name: ''
})

const paymentMethods = [
  { value: 'cash', label: 'Cash', icon: '💵' },
  { value: 'credit_card', label: 'Credit Card', icon: '💳' },
  { value: 'debit_card', label: 'Debit Card', icon: '💳' },
  { value: 'online_banking', label: 'Online Banking', icon: '🏦' },
  { value: 'e-wallet', label: 'E-Wallet', icon: '📱' }
]

const loading = ref(false)
const error = ref('')

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    // Simulate payment processing
    await new Promise(resolve => setTimeout(resolve, 1500))

    const paymentData = {
      booking_id: props.booking.id,
      amount: props.amount,
      payment_method: form.value.payment_method,
      payment_status: 'completed',
      transaction_id: 'TXN' + Date.now()
    }

    emit('success', paymentData)
  } catch {
    error.value = 'Payment failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.payment-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 30px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.payment-summary {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
}

.payment-summary h3 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 18px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 14px;
}

.summary-row.total {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
  font-size: 18px;
  color: #2d5f2e;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
}

.payment-methods {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 10px;
}

.payment-method {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.payment-method:hover {
  border-color: #2d5f2e;
  background-color: #f1f8f4;
}

.payment-method.selected {
  border-color: #2d5f2e;
  background-color: #e8f5e9;
}

.payment-method input[type="radio"] {
  display: none;
}

.method-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.method-name {
  font-size: 12px;
  text-align: center;
  color: #666;
}

.card-details {
  margin-top: 20px;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
}

input[type="text"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

input:focus {
  outline: none;
  border-color: #2d5f2e;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 15px;
}

.error-message {
  padding: 10px;
  background-color: #ffebee;
  color: #c62828;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-cancel {
  background-color: #f5f5f5;
  color: #666;
}

.btn-cancel:hover {
  background-color: #e0e0e0;
}

.btn-submit {
  background-color: #2d5f2e;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background-color: #244d25;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .payment-methods {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
