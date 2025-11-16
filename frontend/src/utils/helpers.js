export const formatDate = (date, format = 'default') => {
  if (!date) return ''

  const d = new Date(date)

  const formats = {
    default: {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    },
    short: {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    },
    time: {
      hour: '2-digit',
      minute: '2-digit'
    },
    full: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }
  }

  return d.toLocaleDateString('en-US', formats[format] || formats.default)
}

export const formatCurrency = (amount, currency = 'USD') => {
  if (!amount && amount !== 0) return ''

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency
  }).format(amount)
}

export const formatTime = (time) => {
  if (!time) return ''

  const parts = time.split(':')
  const hour = parseInt(parts[0])
  const minute = parts[1]

  const period = hour >= 12 ? 'PM' : 'AM'
  const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour)

  return `${displayHour}:${minute} ${period}`
}

export const truncateText = (text, length = 100) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

export const debounce = (func, wait = 300) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

export const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

export const getStatusColor = (status) => {
  const colors = {
    pending: '#f57c00',
    confirmed: '#2e7d32',
    cancelled: '#c62828',
    completed: '#1976d2'
  }
  return colors[status] || '#666'
}

export const validateEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

export const validatePhone = (phone) => {
  const re = /^[\d\s\-+()]+$/
  return re.test(phone)
}

export const generateId = () => {
  return Date.now().toString(36) + Math.random().toString(36).substr(2)
}

export const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

export const isToday = (date) => {
  const today = new Date()
  const d = new Date(date)
  return d.getDate() === today.getDate() &&
         d.getMonth() === today.getMonth() &&
         d.getFullYear() === today.getFullYear()
}

export const isFutureDate = (date) => {
  return new Date(date) > new Date()
}

export const isPastDate = (date) => {
  return new Date(date) < new Date()
}
