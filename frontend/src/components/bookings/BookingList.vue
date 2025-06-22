<template>
  <div class="bookings-list">
    <div class="bookings-header">
      <h3 class="bookings-title">{{ title }}</h3>
      <button 
        v-if="showExport && bookings.length > 0" 
        @click="exportBookings" 
        class="export-btn"
        :title="`Export ${title} to Excel`"
      >
        <span class="export-icon">📊</span> Export
      </button>
    </div>

    <div v-if="bookings.length === 0" class="no-bookings">
      <div class="no-data-icon">📅</div>
      <p>No bookings found</p>
    </div>
    
    <div v-else class="bookings-grid">
      <booking-item
        v-for="booking in bookings"
        :key="booking.id"
        :booking="booking"
        :show-actions="showActions"
        @start-session="$emit('start-session', $event)"
        @cancel-booking="$emit('cancel-booking', $event)"
      />
    </div>
  </div>
</template>

<script>
import ExportService from '@/services/ExportService'
import BookingItem from './BookingItem.vue'

export default {
  name: 'BookingList',
  components: {
    BookingItem
  },
  props: {
    bookings: {
      type: Array,
      required: true
    },
    showActions: {
      type: Boolean,
      default: false
    },
    showExport: {
      type: Boolean,
      default: false
    },
    exportType: {
      type: String,
      default: 'bookings'
    },
    title: {
      type: String,
      default: 'Bookings'
    }
  },
  methods: {
    exportBookings() {
      ExportService.exportToExcel(this.bookings, this.exportType);
    }
  }
}
</script>

<style scoped>
.bookings-list {
  width: 100%;
}

.bookings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border-color);
}

.bookings-title {
  font-size: 1.2rem;
  margin: 0;
  color: var(--primary);
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: var(--bg-main);
  color: var(--primary);
  border: 1px solid var(--primary);
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 4px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.export-btn:hover {
  background-color: var(--primary-light);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(26, 95, 122, 0.2);
}

.export-icon {
  font-size: 1rem;
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

/* Media Queries */
@media (max-width: 768px) {
  .bookings-grid {
    grid-template-columns: 1fr;
  }
}
</style>