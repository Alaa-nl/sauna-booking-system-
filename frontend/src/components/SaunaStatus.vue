<template>
  <div class="status-card" :class="statusClass">
    <div v-if="status === 'available'" class="status-content">
      <div class="status-icon">
        <span class="status-icon-symbol">✓</span>
      </div>
      <div class="status-details">
        <h3 class="status-title">Sauna Available</h3>
        <p class="status-description">The sauna is currently available for bookings</p>
        <button @click="setOutOfOrder" class="btn-warning status-action-btn">
          <span class="btn-icon">⚠️</span>
          Set Out of Order
        </button>
      </div>
    </div>
    
    <div v-if="status === 'busy'" class="status-content">
      <div class="status-icon">
        <span class="status-icon-symbol">🔄</span>
      </div>
      <div class="status-details">
        <h3 class="status-title">Sauna In Use</h3>
        <div class="current-session" v-if="currentBooking">
          <div class="session-info">
            <div class="session-info-item">
              <span class="info-label">Guest</span>
              <span class="info-value">{{ currentBooking.guest_name }}</span>
            </div>
            <div class="session-info-item">
              <span class="info-label">Room</span>
              <span class="info-value">{{ currentBooking.room_number }}</span>
            </div>
            <div class="session-info-item">
              <span class="info-label">Time</span>
              <span class="info-value">{{ formatTime(currentBooking.time) }} - {{ formatEndTime(currentBooking.time, currentBooking.duration) }}</span>
            </div>
            <div class="session-info-item">
              <span class="info-label">Duration</span>
              <span class="info-value">{{ currentBooking.duration }} {{ currentBooking.duration === 1 ? 'hour' : 'hours' }}</span>
            </div>
          </div>
          <div class="session-actions">
            <button @click="completeSession" class="btn-success status-action-btn">
              <span class="btn-icon">✓</span>
              Complete Session
            </button>
            <button @click="cancelSession" class="btn-danger status-action-btn">
              <span class="btn-icon">✕</span>
              Cancel Session
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="status === 'out_of_order'" class="status-content">
      <div class="status-icon">
        <span class="status-icon-symbol">!</span>
      </div>
      <div class="status-details">
        <h3 class="status-title">Sauna Out of Order</h3>
        <div class="reason-box">
          <span class="reason-label">Reason:</span>
          <p class="reason-text">{{ reason || 'Maintenance' }}</p>
        </div>
        <button @click="setAvailable" class="btn-success status-action-btn">
          <span class="btn-icon">✓</span>
          Set Available
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SaunaStatus',
  props: {
    status: {
      type: String,
      required: true,
      default: 'available',
      validator: value => ['available', 'busy', 'out_of_order'].includes(value)
    },
    reason: {
      type: String,
      default: ''
    },
    currentBooking: {
      type: Object,
      default: null
    }
  },
  computed: {
    statusClass() {
      return {
        'status-available': this.status === 'available',
        'status-busy': this.status === 'busy',
        'status-out-of-order': this.status === 'out_of_order'
      }
    }
  },
  methods: {
    formatTime(time) {
      if (!time) return '';
      return time.substring(0, 5);
    },
    formatEndTime(startTime, duration = 1) {
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
    setOutOfOrder() {
      this.$emit('set-out-of-order');
    },
    setAvailable() {
      this.$emit('set-available');
    },
    completeSession() {
      this.$emit('complete-session');
    },
    cancelSession() {
      this.$emit('cancel-session');
    }
  }
}
</script>

<style scoped>
.status-card {
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: var(--shadow);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.status-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.status-content {
  display: flex;
  min-height: 180px;
}

.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 150px;
  flex-shrink: 0;
  position: relative;
}

.status-icon-symbol {
  font-size: 2.5rem;
  width: 70px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.status-available .status-icon {
  background: linear-gradient(135deg, var(--success), #2d7a48);
}

.status-busy .status-icon {
  background: linear-gradient(135deg, var(--warning), #c47e2b);
}

.status-out-of-order .status-icon {
  background: linear-gradient(135deg, var(--danger), #a64046);
}

.status-available .status-icon-symbol {
  background-color: rgba(255, 255, 255, 0.2);
}

.status-busy .status-icon-symbol {
  background-color: rgba(255, 255, 255, 0.2);
}

.status-out-of-order .status-icon-symbol {
  background-color: rgba(255, 255, 255, 0.2);
}

.status-details {
  flex: 1;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  background-color: var(--bg-card);
}

.status-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: var(--text-dark);
}

.status-available .status-title {
  color: var(--success);
}

.status-busy .status-title {
  color: var(--warning);
}

.status-out-of-order .status-title {
  color: var(--danger);
}

.status-description {
  color: var(--text-medium);
  margin-bottom: 1.5rem;
}

.current-session {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.session-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.session-info-item {
  display: flex;
  flex-direction: column;
  background-color: var(--bg-main);
  padding: 0.75rem 1rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.session-info-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.info-label {
  font-size: 0.75rem;
  color: var(--text-medium);
  margin-bottom: 0.25rem;
}

.info-value {
  font-weight: 600;
  color: var(--text-dark);
}

.session-actions {
  display: flex;
  gap: 1rem;
  margin-top: auto;
}

.status-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.25rem;
  font-weight: 500;
  transition: all 0.2s ease;
  margin-top: auto;
  border-radius: 6px;
}

.btn-icon {
  margin-right: 0.5rem;
  font-size: 1.1rem;
}

.btn-success {
  background-color: var(--success);
  color: white;
  border: none;
}

.btn-success:hover {
  background-color: #3d7a57;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(76, 149, 108, 0.3);
}

.btn-danger {
  background-color: var(--danger);
  color: white;
  border: none;
}

.btn-danger:hover {
  background-color: #b34d52;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(201, 93, 99, 0.3);
}

.btn-warning {
  background-color: var(--warning);
  color: white;
  border: none;
}

.btn-warning:hover {
  background-color: #d08c44;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(230, 161, 87, 0.3);
}

.reason-box {
  background-color: var(--bg-main);
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.reason-label {
  font-weight: 600;
  color: var(--text-dark);
  margin-right: 0.5rem;
}

.reason-text {
  margin-top: 0.5rem;
  color: var(--text-medium);
}

/* Responsive design */
@media (max-width: 768px) {
  .status-content {
    flex-direction: column;
  }
  
  .status-icon {
    width: 100%;
    padding: 1.5rem;
  }
  
  .session-info {
    grid-template-columns: 1fr;
  }
  
  .session-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>