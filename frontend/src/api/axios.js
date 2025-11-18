import axios from 'axios'
import router from '@/router'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      try {
        // Resolve district-aware login path at runtime (Pinia should be initialized by then)
        const useDistrictRoutes = (await import('@/utils/districtRoutes')).default
        const { prefixPath } = useDistrictRoutes()
        router.push(prefixPath('/login'))
      } catch {
        // If something goes wrong (Pinia not ready), fall back to plain login route
        router.push('/login')
      }
    }
    return Promise.reject(error)
  }
)

export default api
