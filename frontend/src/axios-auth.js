import axios from 'axios'

// Create axios instance with appropriate configuration
const instance = axios.create({
  baseURL: 'http://localhost/api',
  headers: {
    'Content-Type': 'application/json'
  },
  withCredentials: true
});

// Add request interceptor to include the JWT token in requests
instance.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor to handle common errors
instance.interceptors.response.use(
  (response) => {
    return response;
  }, 
  (error) => {
    // Handle 401 Unauthorized errors (token expired or invalid)
    if (error.response && error.response.status === 401) {
      // Clear token and redirect to login
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // If we're not already on the login page, redirect there
      if (window.location.pathname !== '/employee') {
        window.location.href = '/employee';
      }
    }
    
    return Promise.reject(error);
  }
);

/**
 * Wrapper for making GET requests
 * @param {string} url - The URL to request
 * @param {object} params - Optional query parameters
 * @returns {Promise} - The axios promise
 */
export const fetchData = async (url, params = {}) => {
  try {
    const response = await instance.get(url, { params });
    return response.data;
  } catch (error) {
    throw error;
  }
};

/**
 * Wrapper for making POST requests
 * @param {string} url - The URL to request
 * @param {object} data - The data to send
 * @returns {Promise} - The axios promise
 */
export const createData = async (url, data) => {
  try {
    const response = await instance.post(url, data);
    return response.data;
  } catch (error) {
    throw error;
  }
};

/**
 * Wrapper for making PUT requests
 * @param {string} url - The URL to request
 * @param {object} data - The data to send
 * @returns {Promise} - The axios promise
 */
export const updateData = async (url, data) => {
  try {
    const response = await instance.put(url, data);
    return response.data;
  } catch (error) {
    throw error;
  }
};

/**
 * Wrapper for making DELETE requests
 * @param {string} url - The URL to request
 * @returns {Promise} - The axios promise
 */
export const deleteData = async (url) => {
  try {
    const response = await instance.delete(url);
    return response.data;
  } catch (error) {
    throw error;
  }
};

export default instance;