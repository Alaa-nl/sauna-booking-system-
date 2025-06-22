import { defineStore } from 'pinia'
import axios from '../axios-auth'

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
    login(username, password) {
      return new Promise((resolve, reject) => {
        axios.post("/users/login", {
          username: username,
          password: password,
        })
        .then((res) => {
          if (res.data && res.data.jwt) {
            // Set state values
            this.username = res.data.username || username;
            this.token = res.data.jwt;
            this.role = res.data.role || 'employee';
            
            // Store in localStorage for persistence
            localStorage.setItem('token', res.data.jwt);
            localStorage.setItem('user', JSON.stringify({
              username: this.username,
              role: this.role
            }));
            
            // Set authorization header
            axios.defaults.headers.common['Authorization'] = "Bearer " + res.data.jwt;
            resolve()
          } else {
            reject(new Error('Invalid login response'))
          }
        })
        .catch((error) => {
          reject(error)
        });
      });
    },
    logout() {
      // Clear state
      this.username = '';
      this.token = '';
      this.role = '';
      
      // Clear localStorage
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // Clear authorization header
      axios.defaults.headers.common['Authorization'] = '';
    },
    autoLogin() {
      // Attempt to load user data from localStorage
      const token = localStorage.getItem('token');
      const userJson = localStorage.getItem('user');
      
      if (token && userJson) {
        try {
          const user = JSON.parse(userJson);
          
          // Restore state from localStorage
          this.token = token;
          this.username = user.username;
          this.role = user.role;
          
          // Set authorization header
          axios.defaults.headers.common['Authorization'] = "Bearer " + token;
          return true;
        } catch (error) {
          console.error('Error parsing stored user data', error);
          this.logout();
          return false;
        }
      }
      return false;
    }
  }
})