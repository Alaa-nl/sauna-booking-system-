import { createData, updateData } from '@/axios-auth';
import axios from '@/axios-auth';

/**
 * Service for handling authentication
 */
class AuthService {
  /**
   * Login a user
   * @param {string} username - User's username
   * @param {string} password - User's password
   * @returns {Promise<Object>} Login response with JWT token
   */
  async login(username, password) {
    try {
      const response = await createData('/users/login', { username, password });
      
      // Set global authorization header for subsequent requests
      if (response && response.jwt) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.jwt}`;
        localStorage.setItem('token', response.jwt);
      }
      
      return response;
    } catch (error) {
      // Clear any existing auth data on login failure
      localStorage.removeItem('token');
      axios.defaults.headers.common['Authorization'] = '';
      throw error;
    }
  }

  /**
   * Change user's password
   * @param {string} currentPassword - Current password
   * @param {string} newPassword - New password
   * @returns {Promise<Object>} Response result
   */
  async changePassword(currentPassword, newPassword) {
    return await updateData('/users/change-password', { 
      currentPassword, 
      newPassword 
    });
  }

  /**
   * Get all users (admin only)
   * @returns {Promise<Array>} Array of users
   */
  async getAllUsers() {
    return await axios.get('/users');
  }

  /**
   * Create a new user (admin only)
   * @param {string} username - New user's username
   * @param {string} password - New user's password
   * @param {string} role - User's role ('admin' or 'employee')
   * @returns {Promise<Object>} Created user
   */
  async createUser(username, password, role = 'employee') {
    return await createData('/users', { username, password, role });
  }

  /**
   * Reset a user's password (admin only)
   * @param {number} userId - User ID
   * @param {string} newPassword - New password
   * @returns {Promise<Object>} Response result
   */
  async resetPassword(userId, newPassword) {
    return await updateData(`/users/${userId}/reset-password`, { 
      password: newPassword 
    });
  }

  /**
   * Delete a user (admin only)
   * @param {number} userId - User ID to delete
   * @returns {Promise<Object>} Response result
   */
  async deleteUser(userId) {
    return await axios.delete(`/users/${userId}`);
  }
}

export default new AuthService();