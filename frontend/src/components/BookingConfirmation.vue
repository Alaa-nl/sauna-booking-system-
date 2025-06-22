<template>
  <div class="confirmation">
    <h2>Booking Confirmed!</h2>
    
    <div class="confirmation-details">
      <h3>Your Reservation Details</h3>
      <table class="details-table">
        <tbody>
          <tr>
            <td><strong>Name:</strong></td>
            <td>{{ booking.name }}</td>
          </tr>
          <tr>
            <td><strong>Date:</strong></td>
            <td>{{ formattedDate }}</td>
          </tr>
          <tr>
            <td><strong>Time:</strong></td>
            <td>{{ booking.time }} - {{ booking.endTime }}</td>
          </tr>
          <tr>
            <td><strong>Room Number:</strong></td>
            <td>{{ booking.roomNumber }}</td>
          </tr>
          <tr>
            <td><strong>Number of People:</strong></td>
            <td>{{ booking.people }}</td>
          </tr>
          <tr>
            <td><strong>Duration:</strong></td>
            <td>1 hour</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="instructions">
      <h3>Instructions</h3>
      <ol>
        <li>Come to reception at your appointment time.</li>
        <li>Towels and shower provided.</li>
        <li>No alcohol or drugs allowed.</li>
      </ol>
    </div>
    
    <div class="buttons">
      <button @click="backToHome">Make Another Booking</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BookingConfirmation',
  data() {
    return {
      booking: {
        id: this.$route.query.id,
        name: this.$route.query.name,
        date: this.$route.query.date,
        time: this.$route.query.time,
        endTime: this.$route.query.endTime,
        roomNumber: this.$route.query.roomNumber,
        people: this.$route.query.people
      }
    }
  },
  computed: {
    formattedDate() {
      if (!this.booking.date) return ''
      
      const dateObj = new Date(this.booking.date)
      return dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  },
  methods: {
    backToHome() {
      this.$router.push('/')
    }
  },
  mounted() {
    // Check if we have the booking data, if not redirect back to the booking form
    if (!this.booking.id || !this.booking.name || !this.booking.date || !this.booking.time || !this.booking.endTime) {
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
.confirmation {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.confirmation-details, .instructions {
  margin-bottom: 30px;
  text-align: left;
}

h3 {
  color: #2c3e50;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 15px;
}

.details-table {
  width: 100%;
  border-collapse: collapse;
}

.details-table td {
  padding: 8px;
  vertical-align: top;
}

.details-table td:first-child {
  width: 140px;
}

ol {
  padding-left: 20px;
}

li {
  margin-bottom: 10px;
}

button {
  background-color: #4CAF50;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

button:hover {
  background-color: #45a049;
}
</style>