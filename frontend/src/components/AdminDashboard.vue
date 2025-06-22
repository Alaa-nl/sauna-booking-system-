<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <div class="dashboard-title">
        <h2>Admin Dashboard</h2>
        <p class="dashboard-subtitle">Amsterdam ID ApartHotel Staff Management</p>
      </div>
      <button class="btn-secondary logout-btn" @click="logout">
        <span class="btn-icon">🚪</span>
        Logout
      </button>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Employee Management</h3>
        <button class="btn-primary add-employee-btn" @click="showAddForm = true">
          <span class="btn-icon">➕</span>
          Add Employee
        </button>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading employees...</p>
      </div>

      <div v-else-if="error" class="error-message">
        <div class="error-icon">⚠️</div>
        <div>{{ error }}</div>
      </div>

      <table v-else class="employee-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.username }}</td>
            <td>
              <span class="role-badge" :class="{'role-admin': user.role === 'admin'}">
                {{ user.role }}
              </span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td class="action-buttons">
              <button class="btn-secondary btn-sm" @click="resetPassword(user.id)">
                Reset Password
              </button>
              <button 
                class="btn-danger btn-sm" 
                @click="deleteUser(user.id)"
                :disabled="user.username === currentUsername"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add Employee Modal -->
    <div v-if="showAddForm" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Add New Employee</h3>
          <button class="modal-close" @click="!formLoading && (showAddForm = false)">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="username">Username</label>
            <input 
              id="username"
              v-model="newUser.username" 
              placeholder="Employee username"
              class="form-control"
            >
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input 
              id="password"
              v-model="newUser.password" 
              type="password" 
              placeholder="Employee password"
              class="form-control"
            >
          </div>
          <div class="form-group">
            <label for="role">Role</label>
            <select id="role" v-model="newUser.role" class="form-control">
              <option value="employee">Employee</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div v-if="formError" class="error-message">
            <div class="error-icon">⚠️</div>
            <div>{{ formError }}</div>
          </div>

          <div class="modal-actions">
            <button class="btn-danger" @click="showAddForm = false" :disabled="formLoading">Cancel</button>
            <button 
              class="btn-primary" 
              @click="addEmployee" 
              :disabled="!newUser.username || !newUser.password || formLoading"
            >
              <span v-if="formLoading" class="btn-loader"></span>
              <span v-else>Add Employee</span>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-overlay" @click="!formLoading && (showAddForm = false)"></div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="showResetForm" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Reset Password</h3>
          <button class="modal-close" @click="!formLoading && (showResetForm = false)">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="new-password">New Password</label>
            <input 
              id="new-password"
              v-model="resetPasswordData.password" 
              type="password" 
              placeholder="New password"
              class="form-control"
            >
          </div>

          <div v-if="formError" class="error-message">
            <div class="error-icon">⚠️</div>
            <div>{{ formError }}</div>
          </div>

          <div class="modal-actions">
            <button class="btn-danger" @click="showResetForm = false" :disabled="formLoading">Cancel</button>
            <button 
              class="btn-primary" 
              @click="confirmResetPassword" 
              :disabled="!resetPasswordData.password || formLoading"
            >
              <span v-if="formLoading" class="btn-loader"></span>
              <span v-else>Reset Password</span>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-overlay" @click="!formLoading && (showResetForm = false)"></div>
    </div>

    <!-- Confirmation Dialog -->
    <div v-if="showConfirmation" class="modal">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h3>Confirm Action</h3>
          <button class="modal-close" @click="!confirmationLoading && (showConfirmation = false)">&times;</button>
        </div>
        <div class="modal-body">
          <p>{{ confirmationMessage }}</p>
          <div class="modal-actions">
            <button class="btn-secondary" @click="showConfirmation = false" :disabled="confirmationLoading">Cancel</button>
            <button class="btn-danger" @click="confirmAction" :disabled="confirmationLoading">
              <span v-if="confirmationLoading" class="btn-loader"></span>
              <span v-else>Confirm</span>
            </button>
          </div>
        </div>
      </div>
      <div class="modal-overlay" @click="!confirmationLoading && (showConfirmation = false)"></div>
    </div>
  </div>
  <!-- Success Toast -->
  <div v-if="showSuccess" class="toast toast-success">
    <div class="toast-content">
      <span class="toast-icon">✓</span>
      <span>{{ successMessage }}</span>
    </div>
    <button class="toast-close" @click="showSuccess = false">&times;</button>
  </div>
  
  <!-- Error Toast -->
  <div v-if="showError" class="toast toast-error">
    <div class="toast-content">
      <span class="toast-icon">⚠️</span>
      <span>{{ errorMessage }}</span>
    </div>
    <button class="toast-close" @click="showError = false">&times;</button>
  </div>
</template>

<script>
import { useAuthStore } from '../stores/auth'
import AuthService from '../services/AuthService'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      users: [],
      loading: true,
      error: null,
      showAddForm: false,
      showResetForm: false,
      showConfirmation: false,
      formError: null,
      formLoading: false,
      confirmationLoading: false,
      showSuccess: false,
      successMessage: '',
      showError: false,
      errorMessage: '',
      newUser: {
        username: '',
        password: '',
        role: 'employee'
      },
      resetPasswordData: {
        userId: null,
        password: ''
      },
      userToDelete: null,
      confirmationMessage: '',
      confirmAction: () => {},
      currentUsername: ''
    }
  },
  computed: {
    isAdmin() {
      const authStore = useAuthStore()
      return authStore.loggedIn && authStore.role === 'admin'
    }
  },
  methods: {
    /**
     * Display a success toast message
     */
    showSuccessMessage(message) {
      this.successMessage = message
      this.showSuccess = true
      // Auto-hide after 3 seconds
      setTimeout(() => {
        this.showSuccess = false
      }, 3000)
    },
    
    /**
     * Display an error toast message
     */
    showErrorMessage(message) {
      this.errorMessage = message
      this.showError = true
      // Auto-hide after 5 seconds
      setTimeout(() => {
        this.showError = false
      }, 5000)
    },
    loadUsers() {
      this.loading = true
      this.error = null
      
      AuthService.getAllUsers()
        .then(response => {
          this.users = response.data
          this.loading = false
        })
        .catch(error => {
          this.loading = false
          
          if (error.response?.status === 403) {
            this.error = 'Access denied. Admin privileges required.'
          } else if (error.response?.status === 401) {
            this.error = 'Authentication expired. Please log in again.'
            // Redirect to login page
            this.logout()
          } else {
            this.error = error.response?.data?.error || 'Failed to load users. Please try again.'
          }
          console.error('Error loading users:', error)
        })
    },
    
    addEmployee() {
      this.formError = null
      
      if (!this.newUser.username || !this.newUser.password) {
        this.formError = 'Username and password are required'
        return
      }
      
      this.formLoading = true
      AuthService.createUser(this.newUser.username, this.newUser.password, this.newUser.role)
        .then(() => {
          this.showAddForm = false
          this.newUser = {
            username: '',
            password: '',
            role: 'employee'
          }
          this.formLoading = false
          this.showSuccessMessage('Employee added successfully')
          this.loadUsers()
        })
        .catch(error => {
          this.formLoading = false
          if (error.response?.status === 403) {
            this.formError = 'Access denied. Admin privileges required.'
          } else if (error.response?.status === 409) {
            this.formError = 'Username already exists. Please choose another username.'
          } else {
            this.formError = error.response?.data?.error || 'Failed to create user. Please try again.'
          }
          console.error('Error creating user:', error)
        })
    },
    
    resetPassword(userId) {
      this.resetPasswordData = {
        userId: userId,
        password: ''
      }
      this.showResetForm = true
      this.formError = null
    },
    
    confirmResetPassword() {
      this.formError = null
      
      if (!this.resetPasswordData.password) {
        this.formError = 'New password is required'
        return
      }
      
      this.formLoading = true
      AuthService.resetPassword(this.resetPasswordData.userId, this.resetPasswordData.password)
        .then(() => {
          this.showResetForm = false
          this.resetPasswordData = {
            userId: null,
            password: ''
          }
          this.formLoading = false
          this.showSuccessMessage('Password reset successfully')
        })
        .catch(error => {
          this.formLoading = false
          if (error.response?.status === 403) {
            this.formError = 'Access denied. Admin privileges required.'
          } else if (error.response?.status === 404) {
            this.formError = 'User not found. They may have been deleted.'
          } else {
            this.formError = error.response?.data?.error || 'Failed to reset password. Please try again.'
          }
          console.error('Error resetting password:', error)
        })
    },
    
    deleteUser(userId) {
      const user = this.users.find(u => u.id === userId)
      if (!user) return
      
      this.userToDelete = userId
      this.confirmationMessage = `Are you sure you want to delete ${user.username}?`
      this.confirmAction = this.confirmDeleteUser
      this.showConfirmation = true
    },
    
    confirmDeleteUser() {
      if (!this.userToDelete) return
      
      this.confirmationLoading = true
      AuthService.deleteUser(this.userToDelete)
        .then(() => {
          this.showConfirmation = false
          this.userToDelete = null
          this.confirmationLoading = false
          this.showSuccessMessage('User deleted successfully')
          this.loadUsers()
        })
        .catch(error => {
          this.confirmationLoading = false
          this.showConfirmation = false
          
          let errorMessage = 'Failed to delete user';
          if (error.response?.status === 403) {
            errorMessage = 'Access denied. Admin privileges required.'
          } else if (error.response?.status === 404) {
            errorMessage = 'User not found. They may have been deleted already.'
          } else if (error.response?.status === 400) {
            errorMessage = error.response.data?.error || 'Cannot delete your own account'
          } else {
            errorMessage = error.response?.data?.error || 'Failed to delete user. Please try again.'
          }
          
          this.showErrorMessage(errorMessage)
          console.error('Error deleting user:', error)
        })
    },
    
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString()
    },
    
    logout() {
      const authStore = useAuthStore()
      authStore.logout()
      this.$router.push('/employee')
    }
  },
  mounted() {
    // Check authentication using the auth store
    const authStore = useAuthStore()
    
    if (!authStore.loggedIn) {
      this.$router.push('/employee')
      return
    }
    
    this.currentUsername = authStore.username
    
    // Auth header will be set by axios interceptor
    
    // Load users
    this.loadUsers()
  }
}
</script>

<style scoped>
.admin-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-title {
  text-align: left;
}

.dashboard-subtitle {
  color: var(--text-medium);
  margin-top: 0.25rem;
}

.logout-btn {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-weight: 500;
}

.card {
  background-color: var(--bg-card);
  border-radius: 12px;
  box-shadow: var(--shadow);
  overflow: hidden;
  border: 1px solid var(--border-color);
  margin-bottom: 2rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.card-header h3 {
  margin: 0;
  color: var(--primary);
}

.add-employee-btn {
  display: flex;
  align-items: center;
}

.btn-icon {
  margin-right: 0.5rem;
}

.employee-table {
  width: 100%;
  border-collapse: collapse;
}

.employee-table th, 
.employee-table td {
  padding: 1rem 1.5rem;
  text-align: left;
}

.employee-table th {
  background-color: var(--bg-main);
  font-weight: 600;
  color: var(--text-medium);
}

.employee-table tr {
  border-bottom: 1px solid var(--border-color);
}

.employee-table tr:last-child {
  border-bottom: none;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background-color: var(--bg-main);
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-medium);
}

.role-admin {
  background-color: var(--primary-light);
  color: white;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--text-medium);
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spinner 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  color: var(--danger);
  background-color: var(--danger-light);
  border-radius: 0;
  margin: 0;
}

.error-icon {
  font-size: 1.25rem;
}

/* Modal Styling */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
}

.modal-content {
  background-color: var(--bg-card);
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  z-index: 1001;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--border-color);
}

.modal-sm {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  margin: 0;
  color: var(--primary);
}

.modal-body {
  padding: 1.5rem;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-medium);
  cursor: pointer;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background-color: var(--bg-main);
  color: var(--danger);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background-color: var(--bg-card);
  color: var(--text-dark);
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
}

/* Button styles */
button {
  cursor: pointer;
  border: none;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-loader {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spinner 0.8s linear infinite;
  margin-right: 0.5rem;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
  padding: 0.75rem 1.25rem;
}

.btn-primary:hover:not(:disabled) {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
  background-color: var(--bg-main);
  color: var(--text-medium);
  border: 1px solid var(--border-color);
  padding: 0.75rem 1.25rem;
}

.btn-secondary:hover:not(:disabled) {
  color: var(--primary);
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.btn-danger {
  background-color: var(--danger);
  color: white;
  padding: 0.75rem 1.25rem;
}

.btn-danger:hover:not(:disabled) {
  background-color: var(--danger-dark, #c82333);
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Toast styling */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  min-width: 300px;
  max-width: 400px;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 2000;
  animation: slide-in 0.3s ease-out forwards;
}

.toast-success {
  background-color: var(--success, #28a745);
  color: white;
}

.toast-error {
  background-color: var(--danger);
  color: white;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.toast-icon {
  font-size: 1.25rem;
}

.toast-close {
  background: none;
  border: none;
  color: white;
  opacity: 0.7;
  cursor: pointer;
  padding: 0.25rem;
  margin-left: 1rem;
  font-size: 1.25rem;
  transition: opacity 0.2s ease;
}

.toast-close:hover {
  opacity: 1;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive design */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .logout-btn {
    align-self: flex-end;
  }
  
  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .add-employee-btn {
    width: 100%;
    justify-content: center;
  }
  
  .employee-table {
    display: block;
    overflow-x: auto;
  }
  
  .employee-table th, 
  .employee-table td {
    padding: 0.75rem 1rem;
    white-space: nowrap;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .btn-sm {
    width: 100%;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
  }
  
  .modal-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .modal-actions button {
    width: 100%;
  }
}
</style>