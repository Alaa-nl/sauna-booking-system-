<template>
  <div class="booking-list">
    <div v-if="bookings.length === 0" class="no-bookings">
      <div class="no-data-icon">📅</div>
      <p>No bookings found</p>
    </div>
    
    <div v-else class="bookings-grid">
      <div 
        v-for="booking in bookings" 
        :key="booking.id" 
        class="booking-card"
      >
        <div class="booking-header">
          <div class="booking-date">{{ formatDate(booking.date) }}</div>
          <div class="booking-status status-active">Active</div>
        </div>
        
        <div class="booking-details">
          <div class="booking-info">
            <div class="info-row">
              <div class="info-icon">⏰</div>
              <div class="info-content">
                <div class="info-label">Time</div>
                <div class="info-value">
                  {{ formatTime(booking.time) }} - {{ calculateEndTime(booking.time, booking.duration) }}
                </div>
              </div>
            </div>
            
            <div class="info-row">
              <div class="info-icon">👤</div>
              <div class="info-content">
                <div class="info-label">Guest</div>
                <div class="info-value">{{ booking.guest_name }}</div>
              </div>
            </div>
            
            <div class="info-flex">
              <div class="info-col">
                <div class="info-label">Room</div>
                <div class="info-value">{{ booking.room_number }}</div>
              </div>
              
              <div class="info-col">
                <div class="info-label">People</div>
                <div class="info-value">{{ booking.people }}</div>
              </div>
              
              <div class="info-col">
                <div class="info-label">Duration</div>
                <div class="info-value">{{ booking.duration }}h</div>
              </div>
            </div>
          </div>
          
          <div v-if="showActions" class="booking-actions">
            <button 
              v-if="isToday(booking.date)" 
              @click="$emit('start-session', booking)" 
              class="action-btn start-btn"
              title="Start this session"
            >
              Start
            </button>
            <button 
              @click="$emit('cancel-booking', booking)" 
              class="action-btn cancel-btn"
              title="Cancel this booking"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BookingList',
  props: {
    bookings: {
      type: Array,
      required: true
    },
    showActions: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    formatTime(time) {
      if (!time) return '';
      return time.substring(0, 5);
    },
    calculateEndTime(startTime, duration = 1) {
      if (!startTime) return '';
      
      // Special case for 24:00 (midnight)
      if (startTime === '24:00') {
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
    },
    isToday(date) {
      if (!date) return false;
      const today = new Date().toISOString().split('T')[0];
      return date === today;
    },
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const options = { weekday: 'short', month: 'short', day: 'numeric' };
      return date.toLocaleDateString('en-US', options);
    }
  }
}
</script>

<style scoped>
.booking-list {
  width: 100%;
}

.no-bookings {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: var(--bg-main);
  padding: 3rem 1rem;
  border-radius: 8px;
  color: var(--text-light);
  text-align: center;
}

.no-data-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.6;
}

.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.booking-card {
  background-color: var(--bg-card);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background-color: var(--bg-main);
  border-bottom: 1px solid var(--border-color);
}

.booking-date {
  font-weight: 600;
  color: var(--text-dark);
}

.booking-status {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.status-active {
  background-color: var(--success-light);
  color: var(--success);
}

.booking-details {
  padding: 1rem;
}

.booking-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-row {
  display: flex;
  gap: 0.75rem;
}

.info-icon {
  font-size: 1.25rem;
  color: var(--primary);
  width: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-content {
  flex: 1;
}

.info-flex {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
  background-color: var(--bg-main);
  padding: 0.75rem;
  border-radius: 6px;
}

.info-col {
  flex: 1;
  text-align: center;
}

.info-label {
  font-size: 0.75rem;
  color: var(--text-medium);
  margin-bottom: 0.25rem;
}

.info-value {
  font-weight: 500;
  color: var(--text-dark);
}

.booking-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.action-btn {
  flex: 1;
  padding: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.start-btn {
  background-color: var(--primary);
  color: white;
  border: none;
}

.start-btn:hover {
  background-color: var(--primary-light);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(26, 95, 122, 0.2);
}

.cancel-btn {
  background-color: var(--danger);
  color: white;
  border: none;
}

.cancel-btn:hover {
  background-color: #b34d52;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(201, 93, 99, 0.2);
}

/* Media Queries */
@media (max-width: 768px) {
  .bookings-grid {
    grid-template-columns: 1fr;
  }
}
</style>