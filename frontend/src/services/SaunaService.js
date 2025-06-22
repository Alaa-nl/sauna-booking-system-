import { fetchData, updateData } from '@/axios-auth';

/**
 * Service for managing sauna status
 */
class SaunaService {
  /**
   * Get current sauna status
   * @returns {Promise<Object>} Sauna status object
   */
  async getSaunaStatus() {
    return await fetchData('/sauna/status');
  }

  /**
   * Update sauna status
   * @param {string} status - New status ('available', 'busy', 'out_of_order')
   * @param {string|null} reason - Reason for status change (required for out_of_order)
   * @param {number|null} bookingId - Booking ID (required for busy)
   * @returns {Promise<Object>} Update result
   */
  async updateSaunaStatus(status, reason = null, bookingId = null) {
    const data = { status };
    
    if (reason) {
      data.reason = reason;
    }
    
    if (bookingId) {
      data.booking_id = bookingId;
    }
    
    return await updateData('/sauna/status', data);
  }

  /**
   * Set sauna as available
   * @returns {Promise<Object>} Update result
   */
  async setAvailable() {
    return await this.updateSaunaStatus('available');
  }

  /**
   * Set sauna as busy with a specific booking
   * @param {number} bookingId - The ID of the booking using the sauna
   * @returns {Promise<Object>} Update result
   */
  async setBusy(bookingId) {
    return await this.updateSaunaStatus('busy', null, bookingId);
  }

  /**
   * Set sauna as out of order with a reason
   * @param {string} reason - Reason for the sauna being out of order
   * @returns {Promise<Object>} Update result
   */
  async setOutOfOrder(reason) {
    return await this.updateSaunaStatus('out_of_order', reason);
  }
}

export default new SaunaService();