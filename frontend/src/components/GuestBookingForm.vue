<template>
  <div class="guest-booking-container">
    <div class="booking-header">
      <h2>Book Your Sauna Experience</h2>
      <p class="booking-subheading">Relaxation awaits at Amsterdam ID ApartHotel</p>
    </div>
    
    <div class="card booking-card">
      <BookingForm 
        :isEmployee="false"
      />
      
      <div class="booking-info">
        <div class="info-item">
          <div class="info-icon">⏱️</div>
          <div class="info-text">All sessions are 1 hour long</div>
        </div>
        <div class="info-item">
          <div class="info-icon">👥</div>
          <div class="info-text">Maximum 4 people per session</div>
        </div>
        <div class="info-item">
          <div class="info-icon">🕖</div>
          <div class="info-text">Available from 6:00 AM to 1:00 AM</div>
        </div>
      </div>
    </div>
    
    <div class="employee-access-card">
      <span>Hotel Staff?</span>
      <router-link to="/employee" class="employee-link">Employee Login</router-link>
    </div>
  </div>
</template>

<script>
import BookingForm from './BookingForm.vue'

export default {
  name: 'GuestBookingForm',
  components: {
    BookingForm
  }
}
</script>

<style scoped>
.guest-booking-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 1rem;
}

.booking-header {
  text-align: center;
  margin-bottom: 2rem;
}

.booking-subheading {
  color: var(--text-medium);
  font-size: 1.1rem;
  margin-top: 0.5rem;
}

.booking-card {
  border-top: 4px solid var(--primary);
  padding: 2rem;
}

.booking-info {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.info-icon {
  font-size: 1.25rem;
  margin-right: 1rem;
  color: var(--primary);
}

.info-text {
  color: var(--text-medium);
}

.employee-access-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 2rem;
  padding: 1rem;
  background-color: var(--bg-card);
  border-radius: 8px;
  box-shadow: var(--shadow);
  color: var(--text-medium);
}

.employee-link {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s ease;
  border-bottom: 1px dashed var(--primary);
  padding-bottom: 2px;
}

.employee-link:hover {
  color: var(--primary-dark);
  border-bottom: 1px solid var(--primary-dark);
}
</style>