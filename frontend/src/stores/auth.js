import { defineStore } from 'pinia'
import axios from '../axios-auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    username: '',
    token: ''
  }),
  getters: {
    loggedIn: (state) => state.username !== '',
    getToken: (state) => state.token
  },
  actions: {
    login(username, password) {
      return new Promise((resolve, reject) => {
        axios.post("/users/login", {
          username: username,
          password: password,
        })
        .then((res) => {
          this.username = res.data.username;
          this.token = res.data.jwt;
          axios.defaults.headers.common['Authorization'] = "Bearer " + res.data.jwt;
          localStorage.setItem('token', res.data.jwt);
          localStorage.setItem('username', res.data.username);
          resolve()
        })
        .catch((error) => reject(error));
      });
    },
    logout() {
      this.username = '';
      this.token = '';
      axios.defaults.headers.common['Authorization'] = '';
      localStorage.removeItem('token');
      localStorage.removeItem('username');
    },
    autoLogin() {
      const token = localStorage.getItem('token');
      const username = localStorage.getItem('username');
      if (token && username) {
        axios.defaults.headers.common["Authorization"] = "Bearer " + token;
        this.token = token;
        this.username = username;
      }
    }
  }
})