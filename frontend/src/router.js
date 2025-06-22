import { createRouter, createWebHistory } from 'vue-router'
import GuestBookingForm from './components/GuestBookingForm.vue'
import BookingConfirmation from './components/BookingConfirmation.vue'
import EmployeeLogin from './components/EmployeeLogin.vue'
import EmployeeDashboard from './components/EmployeeDashboard.vue'
import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/', component: GuestBookingForm },
  { path: '/confirmation', component: BookingConfirmation },
  { path: '/employee', component: EmployeeLogin },
  { 
    path: '/employee/dashboard', 
    component: EmployeeDashboard,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  // Check for protected routes
  if (to.meta.requiresAuth) {
    // Initialize the auth store
    const authStore = useAuthStore()
    authStore.autoLogin()
    
    // Check if user is authenticated
    if (!authStore.loggedIn) {
      // Redirect to login if not authenticated
      next('/employee')
      return
    }
  }
  // Continue to the route
  next()
})

export default router