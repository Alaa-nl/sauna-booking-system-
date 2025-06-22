<template>
  <div class="booking-form">
    <form @submit.prevent="submitBooking">
      <div class="form-group">
        <label for="guestName">{{ isEmployee ? 'Guest Name' : 'Name' }}</label>
        <input type="text" id="guestName" v-model="booking.guestName" required placeholder="Enter your full name">
      </div>
      
      <div class="form-group">
        <label for="date">Date</label>
        <div class="date-input-container">
          <input 
            type="date" 
            id="date" 
            v-model="booking.date" 
            required 
            :min="todayFormatted" 
            class="date-input"
          >
        </div>
      </div>
      
      <!-- Duration selector - only for employees -->
      <div class="form-group" v-if="isEmployee">
        <label for="duration">Duration</label>
        <div class="duration-selector">
          <button 
            type="button" 
            v-for="n in 3" 
            :key="n" 
            :class="{ 'duration-btn': true, 'selected': booking.duration === n }"
            @click="booking.duration = n"
          >
            {{ n }} {{ n === 1 ? 'hour' : 'hours' }}
          </button>
        </div>
      </div>
      
      <div class="form-group">
        <label for="time">Start Time</label>
        <div class="time-slots-container">
          <div class="time-slots-header">
            <div class="availability-indicator">
              <span class="availability-dot available"></span> Available
              <span class="availability-dot unavailable"></span> Unavailable
            </div>
            <div v-if="availableTimes.length > 0" class="time-slots-count">
              {{ availableTimes.length }} slots available
            </div>
          </div>
          
          <div class="time-picker">
            <!-- Available Time Slots -->
            <button 
              type="button"
              v-for="time in availableTimes" 
              :key="time"
              :class="{ 'time-slot': true, 'selected': booking.time === time }"
              @click="selectTime(time)"
            >
              {{ time }}
            </button>
            
            <!-- Unavailable Time Slots -->
            <button 
              type="button"
              v-for="time in unavailableTimes" 
              :key="'unavailable-'+time"
              class="time-slot unavailable"
              disabled
            >
              {{ time }}
            </button>
          </div>
          
          <div v-if="availableTimes.length === 0 && unavailableTimes.length === 0 && booking.date" class="time-loading">
            <div class="loading-spinner"></div>
            <span>Checking availability...</span>
          </div>
          <div v-else-if="availableTimes.length === 0 && unavailableTimes.length === 0" class="select-date-prompt">
            Please select a date to view available times
          </div>
        </div>
      </div>

      <div class="form-group" v-if="booking.time">
        <label>Session Details</label>
        <div class="session-card">
          <div class="session-time">
            <div class="session-icon">⏰</div>
            <div class="session-details">
              <div class="session-label">Time</div>
              <div class="time-range">
                <span>{{ booking.time }}</span>
                <span class="time-separator">→</span>
                <span>{{ calculateEndTime(booking.time, booking.duration) }}</span>
              </div>
            </div>
          </div>
          <div class="session-duration" v-if="isEmployee && booking.duration > 1">
            <div class="session-icon">⌛</div>
            <div class="session-details">
              <div class="session-label">Duration</div>
              <div>{{ booking.duration }} {{ booking.duration === 1 ? 'hour' : 'hours' }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group form-group-half">
          <label for="roomNumber">Room Number</label>
          <input 
            type="text" 
            id="roomNumber" 
            v-model="booking.roomNumber" 
            @input="validateRoomNumber" 
            pattern="[0-9]+" 
            title="Please enter a valid room number (numbers only)"
            placeholder="Your room number"
            required
          >
          <div v-if="roomNumberError" class="error-hint">{{ roomNumberError }}</div>
        </div>
        
        <div class="form-group form-group-half">
          <label for="people">Number of People</label>
          <div class="people-selector">
            <button 
              type="button" 
              v-for="n in isEmployee ? 6 : 4" 
              :key="n" 
              :class="{ 'people-btn': true, 'selected': booking.people === n }"
              @click="booking.people = n"
            >
              {{ n }}
            </button>
          </div>
          <div class="form-hint">Maximum {{ isEmployee ? 6 : 4 }} people</div>
        </div>
      </div>
      
      <div class="error-message" v-if="error">
        <div class="error-icon">⚠️</div>
        <div>{{ error }}</div>
      </div>
      
      <button type="submit" class="btn-primary submit-btn" :disabled="isSubmitting || !isFormValid">
        {{ isSubmitting ? 'Processing...' : 'Book Now' }}
      </button>
    </form>
  </div>
</template>

<script>
import axios from '../axios-auth'

export default {
  name: 'BookingForm',
  props: {
    isEmployee: {
      type: Boolean,
      default: false
    },
    username: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      booking: {
        guestName: '',
        date: '',
        time: '',
        roomNumber: '',
        people: 1,
        duration: 1
      },
      error: '',
      roomNumberError: '',
      isSubmitting: false,
      availableTimes: [],
      unavailableTimes: [],
      bookedSlots: [],
      // Calendar properties removed
    }
  },
  computed: {
    todayFormatted() {
      const today = new Date()
      return today.toISOString().split('T')[0]
    },
    // formattedDate removed
    isFormValid() {
      return this.booking.guestName && 
             this.booking.date && 
             this.booking.time && 
             this.booking.roomNumber && 
             !this.roomNumberError
    },
    // Custom calendar computed properties removed
  },
  methods: {
    generateTimeSlots() {
      const times = []
      const now = new Date()
      const today = now.toISOString().split('T')[0]
      const currentHour = now.getHours()
      const currentMinutes = now.getMinutes()
      
      // Start and end hours depend on user type
      const startHour = this.isEmployee ? 0 : 6
      const endHour = this.isEmployee ? 24 : 24  // Both go up to midnight, but with different start times
      
      // Generate time slots with 30-minute intervals
      for (let hour = startHour; hour <= endHour; hour++) {
        // Skip 24:30 for everyone as it's not a valid time (24:00 = midnight)
        if (hour === 24) {
          // Only add 24:00 (midnight)
          times.push('24:00')
          continue
        }
        
        for (let minute = 0; minute < 60; minute += 30) {
          // For today, only show future time slots
          if (this.booking.date === today && 
              (hour < currentHour || (hour === currentHour && minute <= currentMinutes))) {
            continue
          }
          
          const formattedHour = hour.toString().padStart(2, '0')
          const formattedMinute = minute.toString().padStart(2, '0')
          times.push(`${formattedHour}:${formattedMinute}`)
        }
      }
      
      return times
    },
    loadBookedSlots() {
      if (!this.booking.date) return
      
      axios.get('/bookings')
        .then((res) => {
          // Check if response data is an array, if not use empty array
          const bookingsData = Array.isArray(res.data) ? res.data : []
          
          // Filter bookings for the selected date
          this.bookedSlots = bookingsData.filter(booking => 
            booking && booking.date === this.booking.date && 
            booking.status === 'active'
          )
          this.updateAvailableTimes()
        })
        .catch((error) => {
          // Set empty array on error
          this.bookedSlots = []
          this.updateAvailableTimes()
        })
    },
    updateAvailableTimes() {
      const allTimes = this.generateTimeSlots()
      
      // Get all times, mark some as unavailable
      const availableTimes = []
      const unavailableTimes = []
      
      allTimes.forEach(time => {
        // Check if this time slot overlaps with any existing booking
        const isUnavailable = this.bookedSlots.some(booking => {
          const bookingTime = booking.time.substring(0, 5)
          // Convert times to minutes for easier comparison
          const bookingStartMinutes = this.timeToMinutes(bookingTime)
          const bookingEndMinutes = bookingStartMinutes + (booking.duration * 60)
          
          const timeSlotStartMinutes = this.timeToMinutes(time)
          const timeSlotEndMinutes = timeSlotStartMinutes + (this.booking.duration * 60)
          
          // Check for overlap - any of these conditions means there's a conflict
          return (
            // Case 1: Time slot starts during an existing booking
            (timeSlotStartMinutes >= bookingStartMinutes && timeSlotStartMinutes < bookingEndMinutes) ||
            // Case 2: Time slot ends during an existing booking
            (timeSlotEndMinutes > bookingStartMinutes && timeSlotEndMinutes <= bookingEndMinutes) ||
            // Case 3: Time slot completely contains an existing booking
            (timeSlotStartMinutes <= bookingStartMinutes && timeSlotEndMinutes >= bookingEndMinutes)
          )
        })
        
        if (isUnavailable) {
          unavailableTimes.push(time)
        } else {
          availableTimes.push(time)
        }
      })
      
      this.availableTimes = availableTimes
      this.unavailableTimes = unavailableTimes
    },
    timeToMinutes(timeString) {
      const [hours, minutes] = timeString.split(':').map(Number)
      return (hours * 60) + minutes
    },
    timeToDate(timeString) {
      const [hours, minutes] = timeString.split(':').map(Number)
      const date = new Date()
      date.setHours(hours, minutes, 0, 0)
      return date
    },
    calculateEndTime(startTime, duration = 1) {
      if (!startTime) return ''
      
      // Special case for 24:00 (midnight)
      if (startTime === '24:00') {
        // Duration hours later after midnight
        const endHour = duration % 24
        return `${endHour.toString().padStart(2, '0')}:00`
      }
      
      const [hours, minutes] = startTime.split(':').map(Number)
      
      // Convert to total minutes, add duration in hours, then convert back
      let totalMinutes = hours * 60 + minutes + (duration * 60)
      
      // Calculate hours and minutes
      let endHour = Math.floor(totalMinutes / 60)
      let endMinutes = totalMinutes % 60
      
      // Format midnight as 24:00 if it's the end time
      if (endHour === 24 && endMinutes === 0) {
        return '24:00'
      }
      
      // Handle overflow for times after midnight
      if (endHour >= 24) {
        // Check if this booking ends on the next day
        if (this.isEmployee) {
          // For employees: Calculate the actual time (past midnight)
          endHour = endHour % 24
        } else {
          // For guests: We don't allow bookings that end after 1am
          if (endHour > 25 || (endHour === 25 && endMinutes > 0)) {
            return 'Invalid time - exceeds 1am limit'
          }
          endHour = endHour % 24
        }
      }
      
      return `${endHour.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`
    },
    validateRoomNumber() {
      const roomNumber = this.booking.roomNumber
      if (roomNumber === '') {
        this.roomNumberError = ''
        return
      }
      
      if (!/^\d+$/.test(roomNumber)) {
        this.roomNumberError = 'Room number must contain only digits'
        return
      }
      
      this.roomNumberError = ''
    },
    submitBooking() {
      this.isSubmitting = true
      this.error = ''
      
      // Validate room number
      this.validateRoomNumber()
      if (this.roomNumberError) {
        this.isSubmitting = false
        this.error = this.roomNumberError
        return
      }
      
      // Check if the selected time slot is available
      if (!this.availableTimes.includes(this.booking.time)) {
        this.isSubmitting = false
        this.error = 'This time slot is already booked. Please select a different time.'
        return
      }
      
      const bookingData = {
        guest_name: this.booking.guestName,
        date: this.booking.date,
        time: this.booking.time,
        room_number: this.booking.roomNumber,
        people: parseInt(this.booking.people),
        duration: parseInt(this.booking.duration)
      }
      
      // Add created_by for employee bookings
      if (this.isEmployee && this.username) {
        bookingData.created_by = this.username
      }
      
      axios.post('/bookings', bookingData)
        .then((res) => {
          this.isSubmitting = false
          
          if (this.isEmployee) {
            // For employee, emit an event that the parent component can listen to
            this.$emit('booking-created', res.data)
            this.resetForm()
          } else {
            // For guest, redirect to confirmation page
            this.$router.push({ 
              path: '/confirmation', 
              query: { 
                id: res.data.id,
                name: this.booking.guestName,
                date: this.booking.date,
                time: this.booking.time,
                endTime: this.calculateEndTime(this.booking.time, this.booking.duration),
                roomNumber: this.booking.roomNumber,
                people: this.booking.people,
                duration: this.booking.duration
              }
            })
          }
        })
        .catch((error) => {
          this.isSubmitting = false
          this.error = error.response?.data?.error || 'An error occurred'
        })
    },
    resetForm() {
      this.booking = {
        guestName: '',
        date: this.booking.date, // Keep the date
        time: '',
        roomNumber: '',
        people: 1,
        duration: 1
      }
    },
    selectTime(time) {
      this.booking.time = time
    }
  },
  mounted() {
    // Set default date to today
    this.booking.date = this.todayFormatted
    
    // Generate initial time slots
    this.updateAvailableTimes()
    
    // Load booked slots for default date
    this.loadBookedSlots()
  },
  watch: {
    'booking.date': function() {
      this.loadBookedSlots()
    },
    'booking.duration': function() {
      this.updateAvailableTimes()
    }
  }
}
</script>

<style scoped>
.booking-form {
  width: 100%;
}

form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: flex;
  gap: 1.5rem;
}

.form-group-half {
  flex: 1;
}

.form-hint {
  font-size: 0.8rem;
  color: var(--text-light);
  margin-top: 0.5rem;
}

/* Date Input Styling */
.date-input-container {
  position: relative;
  display: flex;
}

.date-input {
  width: 100%;
  cursor: pointer;
}

/* Calendar styling removed */

/* Time Slots Styling */
.time-slots-container {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
}

.time-slots-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background-color: var(--bg-main);
  border-bottom: 1px solid var(--border-color);
}

.availability-indicator {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--text-medium);
}

.availability-dot {
  display: inline-block;
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  margin-right: 0.25rem;
}

.availability-dot.available {
  background-color: var(--success);
}

.availability-dot.unavailable {
  background-color: var(--text-light);
}

.time-slots-count {
  font-size: 0.875rem;
  color: var(--text-medium);
  font-weight: 500;
}

.time-picker {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.5rem;
  padding: 1rem;
  max-height: 300px;
  overflow-y: auto;
}

.time-slot {
  background-color: var(--bg-card);
  border: 1px solid var(--primary);
  color: var(--primary);
  font-weight: 500;
  padding: 0.75rem 0;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.time-slot:hover:not(.unavailable) {
  background-color: var(--primary-light);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.time-slot.selected {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.time-slot.unavailable {
  background-color: var(--bg-main);
  border-color: var(--border-color);
  color: var(--text-light);
  text-decoration: line-through;
  opacity: 0.7;
  cursor: not-allowed;
}

.time-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: var(--text-medium);
  gap: 0.5rem;
}

.loading-spinner {
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spinner 0.8s linear infinite;
}

@keyframes spinner {
  to {
    transform: rotate(360deg);
  }
}

.select-date-prompt {
  text-align: center;
  padding: 2rem;
  color: var(--text-medium);
}

/* Session Card Styling */
.session-card {
  background-color: var(--bg-main);
  border-radius: 8px;
  padding: 1rem;
  border-left: 4px solid var(--primary);
}

.session-time, .session-duration {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
}

.session-time:last-child, .session-duration:last-child {
  margin-bottom: 0;
}

.session-icon {
  font-size: 1.25rem;
  margin-right: 1rem;
  color: var(--primary);
  width: 1.5rem;
  text-align: center;
}

.session-details {
  flex: 1;
}

.session-label {
  font-size: 0.8rem;
  color: var(--text-medium);
  margin-bottom: 0.25rem;
}

.time-range {
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-separator {
  color: var(--text-medium);
}

/* Duration Selector */
.duration-selector {
  display: flex;
  gap: 0.5rem;
}

.duration-btn {
  flex: 1;
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  color: var(--text-dark);
  font-weight: 500;
  padding: 0.75rem 0;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.duration-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.duration-btn.selected {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary-dark);
}

/* People Selector */
.people-selector {
  display: flex;
  gap: 0.5rem;
}

.people-btn {
  flex: 1;
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  color: var(--text-dark);
  font-weight: 500;
  padding: 0.75rem 0;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.people-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.people-btn.selected {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary-dark);
}

/* Error Message */
.error-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background-color: var(--danger-light);
  border-left: 4px solid var(--danger);
  color: var(--danger);
  border-radius: 6px;
  margin-top: 0.5rem;
}

.error-icon {
  font-size: 1.25rem;
}

.error-hint {
  color: var(--danger);
  font-size: 0.8rem;
  margin-top: 0.5rem;
}

/* Submit Button */
.submit-btn {
  margin-top: 1rem;
  width: 100%;
  padding: 1rem;
  font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 1.25rem;
  }
  
  .time-picker {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 480px) {
  .time-picker {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .time-slots-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>