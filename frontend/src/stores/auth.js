import { defineStore } from 'pinia'
import axios from '@/axios-auth'
import AuthService from '@/services/AuthService'

// Auth store with localStorage persistence
export const useAuthStore = defineStore('auth', {
  state: () => ({
    username: '',
    token: '',
    role: ''
  }),
  getters: {
    loggedIn: (state) => state.username !== '',
    getToken: (state) => state.token,
    isAdmin: (state) => state.role === 'admin'
  },
  actions: {
    /**
     * Login a user and set up authentication
     * 
     * @param {string} username The username
     * @param {string} password The password
     * @returns {Promise<Object>} The login response
     * @throws {Error} If login fails
     */
    async login(username, password) {
      try {
        // Use the AuthService to handle the login request and JWT setup
        const response = await AuthService.login(username, password);
        
        if (response && response.jwt) {
          // Set state values in the store
          this.username = response.username || username;
          this.token = response.jwt;
          this.role = response.role || 'employee';
          
          // Store user info in localStorage for persistence across page reloads
          localStorage.setItem('user', JSON.stringify({
            username: this.username,
            role: this.role
          }));
          
          return response;
        } else {
          throw new Error('Invalid login response');
        }
      } catch (error) {
        // Clear any partial state on failure
        this.logout();
        throw error;
      }
    },
    /**
     * Logout the current user
     */
    logout() {
      // Clear state in the store
      this.username = '';
      this.token = '';
      this.role = '';
      
      // Clear localStorage data
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // Clear authorization header
      axios.defaults.headers.common['Authorization'] = '';
    },
    /**
     * Attempt to automatically login from stored token
     * @returns {boolean} True if auto-login succeeded
     */
    autoLogin() {
      // Check for stored authentication data
      const token = localStorage.getItem('token');
      const userJson = localStorage.getItem('user');
      
      if (token && userJson) {
        try {
          // Parse the stored user data
          const user = JSON.parse(userJson);
          
          // Restore auth state in the store
          this.token = token;
          this.username = user.username;
          this.role = user.role;
          
          // Set authorization header for API requests
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
          return true;
        } catch (error) {
          // Clean up on any errors
          this.logout();
          return false;
        }
      }
      return false;
    },
    
    /**
     * Change the current user's password
     * 
     * @param {string} currentPassword The current password
     * @param {string} newPassword The new password
     * @returns {Promise<Object>} The server response
     * @throws {Error} If password change fails
     */
    async changePassword(currentPassword, newPassword) {
      try {
        return await AuthService.changePassword(currentPassword, newPassword);
      } catch (error) {
        // Check if the error is due to authentication failure
        if (error.response && error.response.status === 401) {
          this.logout();
        }
        throw error;
      }
    }
  }
})