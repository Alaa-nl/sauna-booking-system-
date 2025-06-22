import axios from 'axios'

const instance = axios.create({
  baseURL: '/api'
})

// Add a request interceptor to handle authentication errors
instance.interceptors.response.use(
  response => response,
  error => {
    // Handle 401 errors (Unauthorized)
    if (error.response && error.response.status === 401) {
      // Clear local storage
      localStorage.removeItem('token')
      localStorage.removeItem('username')
      
      // If not on the login page, redirect to login
      if (window.location.pathname !== '/employee') {
        window.location.href = '/employee'
      }
    }
    return Promise.reject(error)
  }
)

export default instance