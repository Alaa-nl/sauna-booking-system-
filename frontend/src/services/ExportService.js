import * as XLSX from 'xlsx';

class ExportService {
  /**
   * Generate Excel file from bookings data
   * 
   * @param {Array} bookings - Array of booking objects
   * @param {String} type - Type of bookings (completed or cancelled)
   * @returns {void}
   */
  exportToExcel(bookings, type) {
    if (!bookings || bookings.length === 0) {
      return;
    }

    // Format the current date for the filename
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0];
    
    // Prepare data for Excel
    const data = bookings.map(booking => {
      // Calculate end time
      const endTime = this.calculateEndTime(booking.time, booking.duration);
      
      return {
        'Date': booking.date,
        'Start Time': booking.time.substring(0, 5),
        'End Time': endTime,
        'Guest Name': booking.guest_name,
        'Room Number': booking.room_number,
        'Number of People': booking.people,
        'Status': booking.status
      };
    });
    
    // Create worksheet
    const worksheet = XLSX.utils.json_to_sheet(data);
    
    // Create workbook
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Bookings');
    
    // Generate filename
    const filename = `sauna_${type}_${dateStr}.xlsx`;
    
    // Export to file
    XLSX.writeFile(workbook, filename);
  }
  
  /**
   * Calculate end time based on start time and duration
   * 
   * @param {String} startTime - Start time in format HH:MM:SS
   * @param {Number} duration - Duration in hours
   * @returns {String} - End time in format HH:MM
   */
  calculateEndTime(startTime, duration = 1) {
    if (!startTime) return '';
    
    // Special case for 24:00 (midnight)
    if (startTime === '24:00:00' || startTime === '24:00') {
      // Duration hours later after midnight
      const endHour = duration % 24;
      return `${endHour.toString().padStart(2, '0')}:00`;
    }
    
    const [hours, minutes] = startTime.substring(0, 5).split(':').map(Number);
    
    // Convert to total minutes, add duration in hours, then convert back
    let totalMinutes = hours * 60 + minutes + (duration * 60);
    
    // Calculate hours and minutes
    let endHour = Math.floor(totalMinutes / 60);
    let endMinutes = totalMinutes % 60;
    
    // Format midnight as 24:00 if it's the end time
    if (endHour === 24 && endMinutes === 0) {
      return '24:00';
    }
    
    // Handle overflow for times after midnight
    if (endHour >= 24) {
      endHour = endHour % 24;
    }
    
    return `${endHour.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`;
  }
}

export default new ExportService();