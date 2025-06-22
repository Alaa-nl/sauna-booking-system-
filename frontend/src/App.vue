<template>
  <div id="app">
    <header class="app-header">
      <div class="logo-container">
        <span class="sauna-icon">♨️</span>
        <h1>Amsterdam ID ApartHotel</h1>
      </div>
      <nav>
        <router-link to="/">Guest Booking</router-link>
        <router-link to="/employee">Employee Login</router-link>
      </nav>
    </header>
    <main>
      <router-view></router-view>
    </main>
    <footer>
      <p>© 2025 Amsterdam ID ApartHotel - Sauna Booking System</p>
    </footer>
  </div>
</template>

<script>
import { useAuthStore } from './stores/auth'

export default {
  name: 'App',
  mounted() {
    // Try to automatically log in when the app starts
    const authStore = useAuthStore()
    authStore.autoLogin()
  }
}
</script>

<style>
/* CSS Reset & Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Elegant Color Palette */
:root {
  --primary: #1a5f7a;        /* Deep elegant blue */
  --primary-light: #277fa3;  /* Lighter version of primary for hover states */
  --primary-dark: #0f4a61;   /* Darker version of primary */
  
  --accent: #d4a12a;         /* Warm gold/amber */
  --accent-light: #f2c462;   /* Lighter version of accent */
  --accent-dark: #b28523;    /* Darker version of accent */
  
  --bg-main: #f8f9fa;        /* Soft off-white for main background */
  --bg-card: #ffffff;        /* Pure white for cards */
  
  --success: #4c956c;        /* Soft green for available status */
  --success-light: #e8f5e9;  /* Light green background */
  
  --warning: #e6a157;        /* Warm orange for busy status */
  --warning-light: #fff3e0;  /* Light orange background */
  
  --danger: #c95d63;         /* Muted red for out-of-order/unavailable */
  --danger-light: #ffebee;   /* Light red background */
  
  --text-dark: #2c3e50;      /* Main text color */
  --text-medium: #546e7a;    /* Secondary text color */
  --text-light: #78909c;     /* Muted text color */
  
  --border-color: #e0e0e0;   /* Standard border color */
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Elegant shadow */
}

/* Typography */
body {
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: 16px;
  line-height: 1.6;
  color: var(--text-dark);
  background-color: var(--bg-main);
}

h1, h2, h3, h4, h5, h6 {
  font-weight: 600;
  line-height: 1.3;
  margin-bottom: 0.5em;
  color: var(--primary);
}

h1 { font-size: 1.75rem; }
h2 { font-size: 1.5rem; }
h3 { font-size: 1.25rem; }

p {
  margin-bottom: 1rem;
}

/* Layout */
#app {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
  padding: 2rem 0;
}

/* Header Styles */
.app-header {
  padding: 1.5rem 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
}

.logo-container {
  display: flex;
  align-items: center;
}

.sauna-icon {
  font-size: 1.75rem;
  margin-right: 0.75rem;
}

.app-header h1 {
  font-size: 1.5rem;
  margin: 0;
  color: var(--primary);
}

nav {
  display: flex;
  gap: 1.5rem;
}

nav a {
  color: var(--text-medium);
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 0;
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease;
}

nav a:hover {
  color: var(--primary);
}

nav a.router-link-active {
  color: var(--primary);
  border-bottom: 2px solid var(--primary);
}

/* Footer Styles */
footer {
  padding: 1.5rem 0;
  border-top: 1px solid var(--border-color);
  color: var(--text-light);
  font-size: 0.875rem;
  text-align: center;
}

/* Card Styles */
.card {
  background-color: var(--bg-card);
  border-radius: 8px;
  box-shadow: var(--shadow);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

/* Form Styles */
.form-group {
  margin-bottom: 1.5rem;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--text-dark);
}

input, select, textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
}

/* Button Styles */
button {
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  transition: all 0.2s ease;
  border: none;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-light);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-primary:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-primary:disabled {
  background-color: #a0aec0;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-secondary {
  background-color: #f8f9fa;
  border: 1px solid var(--border-color);
  color: var(--text-medium);
}

.btn-secondary:hover {
  background-color: #e9ecef;
  color: var(--text-dark);
}

.btn-accent {
  background-color: var(--accent);
  color: white;
}

.btn-accent:hover {
  background-color: var(--accent-light);
}

.btn-success {
  background-color: var(--success);
  color: white;
}

.btn-success:hover {
  background-color: #3d7a57;
}

.btn-warning {
  background-color: var(--warning);
  color: white;
}

.btn-warning:hover {
  background-color: #d08c44;
}

.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  background-color: #b34d52;
}

/* Status Colors */
.status-available {
  color: var(--success);
  background-color: var(--success-light);
}

.status-busy {
  color: var(--warning);
  background-color: var(--warning-light);
}

.status-out-of-order {
  color: var(--danger);
  background-color: var(--danger-light);
}

/* Responsive Design */
@media (max-width: 768px) {
  .app-header {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 0;
  }
  
  nav {
    width: 100%;
    justify-content: center;
  }
  
  h1 { font-size: 1.5rem; }
  h2 { font-size: 1.25rem; }
  h3 { font-size: 1rem; }
  
  .card {
    padding: 1rem;
  }
}
</style>