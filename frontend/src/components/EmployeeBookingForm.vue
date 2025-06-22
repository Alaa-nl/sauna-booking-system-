<template>
  <div class="employee-booking-container">
    <h3>Create New Booking</h3>
    <BookingForm 
      :isEmployee="true"
      :username="username"
      @booking-created="onBookingCreated"
    />
  </div>
</template>

<script>
import BookingForm from './BookingForm.vue'

export default {
  name: 'EmployeeBookingForm',
  components: {
    BookingForm
  },
  props: {
    username: {
      type: String,
      required: true
    }
  },
  methods: {
    onBookingCreated(booking) {
      this.$emit('booking-created', booking)
    }
  }
}
</script>

<style scoped>
.employee-booking-container {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  background-color: #f9f9f9;
  margin-bottom: 20px;
}

h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}
</style>