<template>
  <div class="login-container">
    <div class="card login-card">
      <div class="login-header">
        <div class="login-icon">👤</div>
        <h2>Employee Login</h2>
        <p class="login-subheading">Amsterdam ID ApartHotel Staff</p>
      </div>
      
      <form @submit.prevent="login">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <span class="input-icon">👤</span>
            <input 
              type="text" 
              id="username" 
              v-model="username" 
              placeholder="Enter your username"
              required
            >
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <span class="input-icon">🔒</span>
            <input 
              type="password" 
              id="password" 
              v-model="password" 
              placeholder="Enter your password"
              required
            >
          </div>
        </div>
        
        <div class="error-message" v-if="errorMessage">
          <div class="error-icon">⚠️</div>
          <div>{{ errorMessage }}</div>
        </div>
        
        <button type="submit" class="btn-primary login-btn" :disabled="isLoading">
          {{ isLoading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
      
      <div class="login-footer">
        <router-link to="/" class="back-link">
          <span class="back-icon">←</span>
          Back to Guest Booking
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../axios-auth'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'EmployeeLogin',
  data() {
    return {
      username: '',
      password: '',
      errorMessage: '',
      isLoading: false
    }
  },
  methods: {
    login() {
      this.isLoading = true
      this.errorMessage = ''
      
      const authStore = useAuthStore()
      
      authStore.login(this.username, this.password)
        .then(() => {
          this.isLoading = false
          // Navigate to dashboard
          this.$router.push('/employee/dashboard')
        })
        .catch((error) => {
          this.isLoading = false
          this.errorMessage = error.response?.data?.error || 'Invalid username or password'
          console.log(error)
        })
    }
  },
  mounted() {
    // Check if already logged in using the auth store
    const authStore = useAuthStore()
    authStore.autoLogin()
    
    if (authStore.loggedIn) {
      this.$router.push('/employee/dashboard')
    }
  }
}
</script>

<style scoped>
.login-container {
  max-width: 450px;
  margin: 2rem auto;
  padding: 1rem;
}

.login-card {
  padding: 0;
  overflow: hidden;
  border-top: 4px solid var(--primary);
}

.login-header {
  background-color: var(--bg-main);
  padding: 2rem;
  text-align: center;
  border-bottom: 1px solid var(--border-color);
}

.login-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--primary);
  background-color: rgba(26, 95, 122, 0.1);
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.login-subheading {
  color: var(--text-medium);
  margin-top: 0.5rem;
}

form {
  padding: 2rem;
}

.input-with-icon {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.1rem;
  color: var(--text-medium);
}

input {
  padding-left: 3rem;
}

.login-btn {
  width: 100%;
  margin-top: 1rem;
  padding: 0.875rem;
  font-size: 1.1rem;
  transition: all 0.3s ease;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.login-footer {
  background-color: var(--bg-main);
  padding: 1.25rem;
  border-top: 1px solid var(--border-color);
  text-align: center;
}

.back-link {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s ease;
}

.back-icon {
  margin-right: 0.5rem;
  font-size: 0.9rem;
}

.back-link:hover {
  color: var(--primary-dark);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background-color: var(--danger-light);
  border-left: 4px solid var(--danger);
  color: var(--danger);
  border-radius: 6px;
  margin-bottom: 1rem;
}

.error-icon {
  font-size: 1.25rem;
}
</style>