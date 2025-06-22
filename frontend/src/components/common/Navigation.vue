<template>
  <nav class="main-navigation">
    <div class="nav-container">
      <div class="nav-brand">
        <router-link to="/" class="brand-link">
          <span class="brand-icon">🧖</span>
          <span class="brand-name">Sauna Booking</span>
        </router-link>
      </div>

      <div class="nav-links">
        <template v-if="isLoggedIn">
          <!-- Authenticated user links -->
          <router-link to="/employee/dashboard" class="nav-link">Dashboard</router-link>
          <div v-if="isAdmin" class="nav-link-group">
            <router-link to="/admin/dashboard" class="nav-link">Admin</router-link>
          </div>
          <button @click="logout" class="nav-button logout-btn">
            <span class="btn-icon">🚪</span> Logout
          </button>
        </template>
        
        <template v-else>
          <!-- Guest links -->
          <router-link to="/" class="nav-link">Guest Booking</router-link>
          <router-link to="/employee" class="nav-link employee-btn">
            <span class="btn-icon">👤</span> Employee Login
          </router-link>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'Navigation',
  computed: {
    authStore() {
      return useAuthStore()
    },
    isLoggedIn() {
      return this.authStore.loggedIn
    },
    isAdmin() {
      return this.authStore.isAdmin
    },
    username() {
      return this.authStore.username
    }
  },
  methods: {
    logout() {
      this.authStore.logout()
      this.$router.push('/employee')
    }
  }
}
</script>

<style scoped>
.main-navigation {
  background-color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  padding: 0.75rem 1rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand {
  display: flex;
  align-items: center;
}

.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--primary);
  font-weight: 600;
  font-size: 1.25rem;
}

.brand-icon {
  font-size: 1.5rem;
  margin-right: 0.5rem;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-link {
  color: var(--text-dark);
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 0;
  position: relative;
  transition: color 0.2s ease;
}

.nav-link:hover {
  color: var(--primary);
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: var(--primary);
  transition: width 0.2s ease;
}

.nav-link:hover::after,
.router-link-active::after {
  width: 100%;
}

.router-link-active {
  color: var(--primary);
  font-weight: 600;
}

.nav-button {
  background-color: transparent;
  border: none;
  padding: 0.5rem 1rem;
  font-weight: 500;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.logout-btn {
  color: var(--danger);
}

.logout-btn:hover {
  background-color: var(--danger-light);
}

.employee-btn {
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: 4px;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
}

.employee-btn:hover {
  background-color: var(--primary-light);
  color: white;
}

.btn-icon {
  margin-right: 0.5rem;
}

@media (max-width: 768px) {
  .brand-name {
    display: none;
  }
  
  .nav-links {
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .nav-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-links {
    width: 100%;
    justify-content: center;
  }
  
  .brand-name {
    display: inline;
  }
}
</style>