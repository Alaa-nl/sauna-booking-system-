import { defineStore } from 'pinia'
import axios from '../axios-auth'

// Auth store following Lecture 6F pattern
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
            this.username = res.data.username || username;
            this.token = res.data.jwt;
            this.role = res.data.role || 'employee';
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
      this.username = '';
      this.token = '';
      this.role = '';
      axios.defaults.headers.common['Authorization'] = '';
    },
    // Since we're not using localStorage, we can't autoLogin from previous sessions
    // This method is kept to maintain compatibility with existing code
    autoLogin() {
      // No implementation needed as we don't use localStorage
      return false;
    }
  }
})