import axios from 'axios'

// Following the Lecture 6F pattern with /api prefix
const instance = axios.create({
  baseURL: 'http://localhost/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

export default instance;