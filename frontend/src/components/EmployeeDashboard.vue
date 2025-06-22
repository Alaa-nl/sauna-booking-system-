<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div class="dashboard-title">
        <h2>Employee Dashboard</h2>
        <p class="dashboard-subtitle">Amsterdam ID ApartHotel Sauna Management</p>
      </div>
      <button class="btn-secondary logout-btn" @click="logout">
        <span class="btn-icon">🚪</span>
        Logout
      </button>
    </div>
    
    <!-- Sauna Status Card -->
    <SaunaStatus 
      :status="saunaStatus.status" 
      :reason="saunaStatus.reason"
      :current-booking="currentBooking"
      @set-out-of-order="setOutOfOrder"
      @set-available="setAvailable"
      @complete-session="completeSession"
      @cancel-session="cancelSession"
    />
    
    <!-- New Booking Button -->
    <div class="action-buttons">
      <button class="btn-primary new-booking-btn" @click="showBookingForm = true">
        <span class="btn-icon">➕</span>
        Create New Booking
      </button>
    </div>
    
    <!-- Employee Booking Form Modal -->
    <div v-if="showBookingForm" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Create Employee Booking</h3>
          <button class="modal-close" @click="showBookingForm = false">&times;</button>
        </div>
        <EmployeeBookingForm 
          :username="username" 
          @booking-created="onBookingCreated" 
        />
      </div>
      <div class="modal-overlay" @click="showBookingForm = false"></div>
    </div>
    
    <!-- Out of Order Form Modal -->
    <div v-if="showOutOfOrderForm" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Set Sauna Out of Order</h3>
          <button class="modal-close" @click="showOutOfOrderForm = false">&times;</button>
        </div>
        <form @submit.prevent="submitOutOfOrder" class="out-of-order-form">
          <div class="form-group">
            <label for="reason">Reason:</label>
            <textarea 
              id="reason" 
              v-model="outOfOrderReason" 
              required
              placeholder="Please provide a reason why the sauna is out of order..."
              rows="4"
            ></textarea>
          </div>
          <button type="submit" class="btn-warning confirm-btn">
            <span class="btn-icon">⚠️</span>
            Confirm Out of Order
          </button>
        </form>
      </div>
      <div class="modal-overlay" @click="showOutOfOrderForm = false"></div>
    </div>
    
    <!-- Today's and Future Bookings -->
    <div class="section-heading">
      <h3>Manage Reservations</h3>
      <div class="section-divider"></div>
    </div>
    
    <div class="bookings-container">
      <div class="card bookings-section">
        <div class="bookings-section-header">
          <h3>Today's Bookings</h3>
          <div class="booking-count">{{ todayBookings.length }} bookings</div>
        </div>
        <BookingsList 
          :bookings="todayBookings" 
          :show-actions="true"
          title=""
          @cancel-booking="cancelBooking" 
          @start-session="startSession"
        />
      </div>
      
      <div class="card bookings-section">
        <div class="bookings-section-header">
          <h3>Future Bookings</h3>
          <div class="booking-count">{{ futureBookings.length }} bookings</div>
        </div>
        <BookingsList 
          :bookings="futureBookings" 
          :show-actions="true"
          title=""
          @cancel-booking="cancelBooking" 
        />
      </div>
    </div>
    
    <!-- Recent History Section -->
    <div class="section-heading">
      <h3>Booking History</h3>
      <div class="section-divider"></div>
    </div>
    
    <div class="history-container">
      <!-- Completed Bookings -->
      <div class="card history-section">
        <BookingsList 
          :bookings="completedOnlyBookings" 
          :show-actions="false"
          :show-export="true"
          export-type="completed"
          title="Completed Reservations"
        />
      </div>
      
      <!-- Cancelled Bookings -->
      <div class="card history-section">
        <BookingsList 
          :bookings="cancelledBookings" 
          :show-actions="false"
          :show-export="true"
          export-type="cancelled"
          title="Cancelled Reservations"
        />
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../axios-auth'
import BookingsList from './BookingsList.vue'
import EmployeeBookingForm from './EmployeeBookingForm.vue'
import SaunaStatus from './SaunaStatus.vue'
import { useAuthStore } from '../stores/auth'

export default {
  name: 'EmployeeDashboard',
  components: {
    BookingsList,
    EmployeeBookingForm,
    SaunaStatus
  },
  data() {
    return {
      bookings: [],
      saunaStatus: {
        status: 'available',
        reason: null
      },
      currentBooking: null,
      showBookingForm: false,
      showOutOfOrderForm: false,
      outOfOrderReason: ''
    }
  },
  computed: {
    username() {
      return useAuthStore().username
    },
    todayBookings() {
      const today = new Date().toISOString().split('T')[0]
      return this.bookings.filter(booking => 
        booking.date === today && 
        booking.status === 'active' &&
        booking.id !== (this.currentBooking ? this.currentBooking.id : null)
      ).sort((a, b) => a.time.localeCompare(b.time))
    },
    futureBookings() {
      const today = new Date().toISOString().split('T')[0]
      return this.bookings.filter(booking => 
        booking.date > today && 
        booking.status === 'active'
      ).sort((a, b) => a.date.localeCompare(b.date) || a.time.localeCompare(b.time))
    },
    completedOnlyBookings() {
      const oneWeekAgo = new Date()
      oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
      const oneWeekAgoStr = oneWeekAgo.toISOString().split('T')[0]
      
      return this.bookings.filter(booking => 
        booking.date >= oneWeekAgoStr && 
        booking.status === 'completed'
      ).sort((a, b) => {
        // Sort by date descending, then by time
        if (a.date !== b.date) return b.date.localeCompare(a.date)
        return a.time.localeCompare(b.time)
      })
    },
    cancelledBookings() {
      const oneWeekAgo = new Date()
      oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
      const oneWeekAgoStr = oneWeekAgo.toISOString().split('T')[0]
      
      return this.bookings.filter(booking => 
        booking.date >= oneWeekAgoStr && 
        booking.status === 'cancelled'
      ).sort((a, b) => {
        // Sort by date descending, then by time
        if (a.date !== b.date) return b.date.localeCompare(a.date)
        return a.time.localeCompare(b.time)
      })
    }
  },
  methods: {
    loadBookings() {
      axios.get('/bookings')
        .then((res) => {
          this.bookings = res.data
        })
        .catch((error) => {
          console.log(error)
          if (error.response?.status === 401) {
            this.logout()
          }
        })
    },
    loadSaunaStatus() {
      axios.get('/sauna/status')
        .then((res) => {
          this.saunaStatus = res.data
          
          // If status is busy, find the current booking
          if (this.saunaStatus.status === 'busy' && this.saunaStatus.booking_id) {
            this.loadCurrentBooking(this.saunaStatus.booking_id)
          }
        })
        .catch((error) => console.log(error))
    },
    loadCurrentBooking(bookingId) {
      axios.get(`/bookings/${bookingId}`)
        .then((res) => {
          this.currentBooking = res.data
        })
        .catch((error) => console.log(error))
    },
    startSession(booking) {
      // First update the booking status to "in_use"
      axios.put(`/bookings/${booking.id}`, {
        status: 'in_use'
      })
        .then(() => {
          // Then update the sauna status to "busy"
          return axios.put(`/sauna/status`, {
            status: 'busy',
            booking_id: booking.id
          })
        })
        .then(() => {
          this.loadSaunaStatus()
          this.loadBookings()
        })
        .catch((error) => console.log(error))
    },
    completeSession() {
      axios.put(`/bookings/${this.currentBooking.id}`, {
        status: 'completed'
      })
        .then(() => {
          return axios.put('/sauna/status', { status: 'available' })
        })
        .then(() => {
          this.currentBooking = null
          this.loadSaunaStatus()
          this.loadBookings()
        })
        .catch((error) => console.log(error))
    },
    cancelSession() {
      axios.put(`/bookings/${this.currentBooking.id}`, {
        status: 'cancelled'
      })
        .then(() => {
          return axios.put('/sauna/status', { status: 'available' })
        })
        .then(() => {
          this.currentBooking = null
          this.loadSaunaStatus()
          this.loadBookings()
        })
        .catch((error) => console.log(error))
    },
    cancelBooking(booking) {
      if (confirm(`Are you sure you want to cancel the booking for ${booking.guest_name}?`)) {
        axios.put(`/bookings/${booking.id}`, {
          status: 'cancelled'
        })
          .then(() => {
            this.loadBookings()
          })
          .catch((error) => console.log(error))
      }
    },
    setOutOfOrder() {
      this.showOutOfOrderForm = true
    },
    submitOutOfOrder() {
      axios.put('/sauna/status', {
        status: 'out_of_order',
        reason: this.outOfOrderReason
      })
        .then(() => {
          this.showOutOfOrderForm = false
          this.outOfOrderReason = ''
          this.loadSaunaStatus()
        })
        .catch((error) => console.log(error))
    },
    setAvailable() {
      axios.put('/sauna/status', {
        status: 'available',
        reason: null
      })
        .then(() => {
          this.loadSaunaStatus()
        })
        .catch((error) => console.log(error))
    },
    onBookingCreated(booking) {
      this.showBookingForm = false
      this.loadBookings()
    },
    logout() {
      // Use the auth store for logout
      const authStore = useAuthStore()
      authStore.logout()
      
      // Redirect to login page
      this.$router.push('/employee')
    }
  },
  mounted() {
    // Check authentication using the auth store
    const authStore = useAuthStore()
    authStore.autoLogin()
    
    if (!authStore.loggedIn) {
      this.$router.push('/employee')
      return
    }
    
    // Set auth header
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + authStore.getToken
    
    // Load initial data
    this.loadBookings()
    this.loadSaunaStatus()
  }
}
</script>

<style scoped>
.dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-title {
  text-align: left;
}

.dashboard-subtitle {
  color: var(--text-medium);
  margin-top: 0.25rem;
}

.logout-btn {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-weight: 500;
  border: 1px solid var(--border-color);
  background-color: transparent;
  color: var(--text-medium);
  border-radius: 6px;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background-color: var(--bg-main);
  color: var(--primary);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-buttons {
  margin-bottom: 2rem;
  display: flex;
  justify-content: center;
}

.new-booking-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  font-size: 1.1rem;
  font-weight: 500;
  transition: all 0.2s ease;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(26, 95, 122, 0.15);
}

.new-booking-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(26, 95, 122, 0.2);
}

.btn-icon {
  margin-right: 0.5rem;
}

/* Section Styles */
.section-heading {
  display: flex;
  align-items: center;
  margin: 2.5rem 0 1.5rem;
}

.section-heading h3 {
  font-size: 1.25rem;
  color: var(--text-dark);
  margin: 0;
  padding-right: 1rem;
  white-space: nowrap;
}

.section-divider {
  flex: 1;
  height: 1px;
  background-color: var(--border-color);
}

.bookings-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.bookings-section, .history-section {
  border-top: 4px solid var(--primary);
}

.bookings-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border-color);
}

.bookings-section-header h3 {
  margin: 0;
  font-size: 1.2rem;
}

.booking-count {
  background-color: var(--bg-main);
  color: var(--text-medium);
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.history-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

/* Modal Styling */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
}

.modal-content {
  background-color: var(--bg-card);
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  z-index: 1001;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--border-color);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  margin: 0;
  color: var(--primary);
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-medium);
  cursor: pointer;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background-color: var(--bg-main);
  color: var(--danger);
}

.out-of-order-form {
  padding: 1.5rem;
}

textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  resize: vertical;
  font-family: inherit;
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
}

.confirm-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0.75rem;
  margin-top: 1rem;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1000px) {
  .bookings-container, .history-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .dashboard-title {
    text-align: center;
    width: 100%;
  }
  
  .logout-btn {
    align-self: flex-end;
  }
}

@media (max-width: 600px) {
  .dashboard-header {
    align-items: center;
  }
  
  .logout-btn {
    align-self: center;
    width: 100%;
    justify-content: center;
  }
  
  .modal-content {
    width: 95%;
    max-height: 80vh;
  }
}
</style>