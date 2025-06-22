import { fetchData, createData, updateData, deleteData } from '@/axios-auth';

/**
 * Service for managing bookings
 */
class BookingService {
  /**
   * Get all bookings
   * @returns {Promise<Array>} Array of bookings
   */
  async getAllBookings() {
    return await fetchData('/bookings');
  }

  /**
   * Get a booking by ID
   * @param {number} id - Booking ID
   * @returns {Promise<Object>} Booking object
   */
  async getBookingById(id) {
    return await fetchData(`/bookings/${id}`);
  }

  /**
   * Create a new booking
   * @param {Object} bookingData - Booking data to create
   * @returns {Promise<Object>} Created booking object
   */
  async createBooking(bookingData) {
    return await createData('/bookings', bookingData);
  }

  /**
   * Update a booking
   * @param {number} id - Booking ID to update
   * @param {Object} bookingData - Updated booking data
   * @returns {Promise<Object>} Update result
   */
  async updateBooking(id, bookingData) {
    return await updateData(`/bookings/${id}`, bookingData);
  }

  /**
   * Delete a booking
   * @param {number} id - Booking ID to delete
   * @returns {Promise<Object>} Delete result
   */
  async deleteBooking(id) {
    return await deleteData(`/bookings/${id}`);
  }

  /**
   * Change booking status
   * @param {number} id - Booking ID
   * @param {string} status - New status ('active', 'in_use', 'completed', 'cancelled')
   * @returns {Promise<Object>} Update result
   */
  async changeBookingStatus(id, status) {
    return await updateData(`/bookings/${id}`, { status });
  }

  /**
   * Start a booking session
   * @param {number} id - Booking ID
   * @returns {Promise<Object>} Update result
   */
  async startBookingSession(id) {
    return await this.changeBookingStatus(id, 'in_use');
  }

  /**
   * Complete a booking session
   * @param {number} id - Booking ID
   * @returns {Promise<Object>} Update result
   */
  async completeBookingSession(id) {
    return await this.changeBookingStatus(id, 'completed');
  }

  /**
   * Cancel a booking
   * @param {number} id - Booking ID
   * @returns {Promise<Object>} Update result
   */
  async cancelBooking(id) {
    return await this.changeBookingStatus(id, 'cancelled');
  }
}

export default new BookingService();