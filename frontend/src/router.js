import { createRouter, createWebHistory } from 'vue-router'
import GuestBookingForm from './components/GuestBookingForm.vue'
import BookingConfirmation from './components/BookingConfirmation.vue'
import EmployeeLogin from './components/EmployeeLogin.vue'
import EmployeeDashboard from './components/EmployeeDashboard.vue'
import AdminDashboard from './components/AdminDashboard.vue'
import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/', component: GuestBookingForm },
  { path: '/confirmation', component: BookingConfirmation },
  { path: '/employee', component: EmployeeLogin },
  { 
    path: '/employee/dashboard', 
    component: EmployeeDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/dashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  // Initialize the auth store
  const authStore = useAuthStore()
  
  // Check for protected routes
  if (to.meta.requiresAuth) {
    // Check if user is authenticated
    if (!authStore.loggedIn) {
      // Redirect to login if not authenticated
      next('/employee')
      return
    }
    
    // Check for admin-only routes
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      // Redirect to employee dashboard if not admin
      next('/employee/dashboard')
      return
    }
  }
  
  // If user is logged in and trying to access login page, redirect to dashboard
  if (to.path === '/employee' && authStore.loggedIn) {
    if (authStore.isAdmin) {
      next('/admin/dashboard')
    } else {
      next('/employee/dashboard')
    }
    return
  }
  
  // Continue to the route
  next()
})

export default router