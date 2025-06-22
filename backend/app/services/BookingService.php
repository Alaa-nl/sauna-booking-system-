<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\SaunaStatusRepository;

class BookingService
{
    private $bookingRepository;
    private $saunaStatusRepository;
    
    public function __construct()
    {
        $this->bookingRepository = new BookingRepository();
        $this->saunaStatusRepository = new SaunaStatusRepository();
    }
    
    /**
     * Get all bookings with optional pagination
     * 
     * @param int|null $limit Maximum number of bookings to return
     * @param int|null $offset Number of bookings to skip
     * @return array List of bookings and pagination metadata
     * @throws \Exception If retrieval fails
     */
    public function getAllBookings($limit = null, $offset = null)
    {
        try {
            // Get total count for pagination metadata
            $totalCount = $this->bookingRepository->getCount();
            
            // Get paginated bookings
            $bookings = $this->bookingRepository->getAll($limit, $offset);
            
            // Return data with pagination metadata
            return [
                'data' => $bookings,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $limit ? ($offset + $limit < $totalCount) : false
                ]
            ];
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving bookings: " . $e->getMessage());
        }
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array Booking data
     * @throws \Exception If booking not found or retrieval fails
     */
    public function getBookingById($id)
    {
        try {
            $booking = $this->bookingRepository->findById($id);
            if (!$booking) {
                throw new \Exception("Booking not found", 404);
            }
            return $booking;
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Create a new booking
     * 
     * @param array $bookingData The booking data
     * @return array Created booking
     * @throws \Exception If validation fails or there are conflicts
     */
    public function createBooking($bookingData)
    {
        try {
            // 1. Validate booking data
            $this->validateBooking($bookingData);
            
            // 2. Check for time conflicts
            $this->checkForTimeConflicts($bookingData);
            
            // 3. Create booking in database
            $booking = $this->bookingRepository->create($bookingData);
            
            return $booking;
        } catch (\Exception $e) {
            throw new \Exception("Error creating booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Update a booking
     * 
     * @param int $id Booking ID
     * @param array $data Updated booking data
     * @return bool Success status
     * @throws \Exception If booking not found or update fails
     */
    public function updateBooking($id, $data)
    {
        try {
            // 1. Verify booking exists
            $booking = $this->bookingRepository->findById($id);
            if (!$booking) {
                throw new \Exception("Booking not found", 404);
            }
            
            // 2. Update booking in database
            $success = $this->bookingRepository->update($id, $data);
            
            // 3. Handle business logic for status changes
            $this->processBookingStatusChange($id, $data);
            
            return $success;
        } catch (\Exception $e) {
            throw new \Exception("Error updating booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Process booking status changes and update related systems
     * 
     * @param int $id Booking ID
     * @param array $data Booking data with status
     */
    private function processBookingStatusChange($id, $data)
    {
        // Only process if status is being updated
        if (!isset($data['status'])) {
            return;
        }
        
        // If updating to in_use, update sauna status to busy
        if ($data['status'] === 'in_use') {
            $this->saunaStatusRepository->update('busy', null, $id);
        }
        
        // If updating to completed or cancelled, update sauna status if needed
        if ($data['status'] === 'completed' || $data['status'] === 'cancelled') {
            $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
            if ($saunaStatus && $saunaStatus['status'] === 'busy' && $saunaStatus['booking_id'] == $id) {
                $this->saunaStatusRepository->update('available', null, null);
            }
        }
    }
    
    /**
     * Delete a booking
     * 
     * @param int $id Booking ID
     * @return bool Success status
     * @throws \Exception If booking not found, in use, or delete fails
     */
    public function deleteBooking($id)
    {
        try {
            // 1. Verify booking exists
            $booking = $this->bookingRepository->findById($id);
            if (!$booking) {
                throw new \Exception("Booking not found", 404);
            }
            
            // 2. Check business rules - cannot delete a booking in progress
            $this->verifyBookingCanBeDeleted($id);
            
            // 3. Delete from database
            return $this->bookingRepository->delete($id);
        } catch (\Exception $e) {
            throw new \Exception("Error deleting booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Verify a booking can be deleted according to business rules
     * 
     * @param int $id Booking ID
     * @throws \Exception If booking cannot be deleted
     */
    private function verifyBookingCanBeDeleted($id)
    {
        // Check if sauna is currently in use by this booking
        $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
        if ($saunaStatus && $saunaStatus['status'] === 'busy' && $saunaStatus['booking_id'] == $id) {
            throw new \Exception("Cannot delete booking that is currently in progress", 400);
        }
    }
    
    /**
     * Validate booking data meets all business rules
     * 
     * @param array $bookingData The booking data to validate
     * @throws \Exception If validation fails
     */
    private function validateBooking($bookingData)
    {
        // 1. Check required fields
        $this->validateRequiredFields($bookingData);
        
        // 2. Validate format of fields
        $this->validateFieldFormats($bookingData);
        
        // 3. Validate value ranges
        $this->validateValueRanges($bookingData);
    }
    
    /**
     * Validate all required booking fields are present
     * 
     * @param array $bookingData The booking data
     * @throws \Exception If required fields are missing
     */
    private function validateRequiredFields($bookingData)
    {
        $requiredFields = ['guest_name', 'date', 'time', 'room_number', 'people'];
        foreach ($requiredFields as $field) {
            if (!isset($bookingData[$field]) || empty($bookingData[$field])) {
                throw new \Exception("Missing required field: {$field}", 400);
            }
        }
    }
    
    /**
     * Validate formats of booking fields
     * 
     * @param array $bookingData The booking data
     * @throws \Exception If any field has invalid format
     */
    private function validateFieldFormats($bookingData)
    {
        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingData['date'])) {
            throw new \Exception("Invalid date format. Use YYYY-MM-DD", 400);
        }
        
        // Validate time format (HH:MM)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $bookingData['time'])) {
            throw new \Exception("Invalid time format. Use HH:MM in 24-hour format", 400);
        }
        
        // Validate room_number (numeric)
        if (!is_numeric($bookingData['room_number'])) {
            throw new \Exception("Room number must be numeric", 400);
        }
    }
    
    /**
     * Validate value ranges for booking fields
     * 
     * @param array $bookingData The booking data
     * @throws \Exception If any value is out of allowed range
     */
    private function validateValueRanges($bookingData)
    {
        // Validate people (numeric, 1-6)
        if (!is_numeric($bookingData['people']) || $bookingData['people'] < 1 || $bookingData['people'] > 6) {
            throw new \Exception("Number of people must be between 1 and 6", 400);
        }
        
        // Validate duration if set (numeric, 1-3)
        if (isset($bookingData['duration'])) {
            if (!is_numeric($bookingData['duration']) || $bookingData['duration'] < 1 || $bookingData['duration'] > 3) {
                throw new \Exception("Duration must be between 1 and 3 hours", 400);
            }
        }
    }
    
    /**
     * Check for time conflicts with existing bookings
     * 
     * @param array $bookingData The booking data to check
     * @throws \Exception If time conflict exists or sauna is out of order
     */
    private function checkForTimeConflicts($bookingData)
    {
        // 1. Check sauna availability first
        $this->checkSaunaAvailability();
        
        // 2. Get all bookings for the date
        $bookingsForDate = $this->bookingRepository->getAllForDate($bookingData['date']);
        
        // 3. Calculate booking time range
        $timeRange = $this->calculateBookingTimeRange($bookingData);
        $startTime = $timeRange['start'];
        $endTime = $timeRange['end'];
        
        // 4. Check for overlaps with existing bookings
        $this->checkForBookingOverlaps($bookingsForDate, $startTime, $endTime);
    }
    
    /**
     * Check if sauna is currently available
     * 
     * @throws \Exception If sauna is out of order
     */
    private function checkSaunaAvailability()
    {
        $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
        if ($saunaStatus && $saunaStatus['status'] === 'out_of_order') {
            throw new \Exception("Sauna is currently out of order: " . $saunaStatus['reason'], 400);
        }
    }
    
    /**
     * Calculate start and end time for a booking
     * 
     * @param array $bookingData The booking data
     * @return array Start and end timestamps
     */
    private function calculateBookingTimeRange($bookingData)
    {
        $startTime = strtotime($bookingData['date'] . ' ' . $bookingData['time']);
        $duration = isset($bookingData['duration']) ? $bookingData['duration'] : 1;
        $endTime = $startTime + ($duration * 3600); // duration in hours
        
        return [
            'start' => $startTime,
            'end' => $endTime
        ];
    }
    
    /**
     * Check for overlaps with existing bookings
     * 
     * @param array $existingBookings List of existing bookings
     * @param int $startTime New booking start timestamp
     * @param int $endTime New booking end timestamp
     * @throws \Exception If there is a time conflict
     */
    private function checkForBookingOverlaps($existingBookings, $startTime, $endTime)
    {
        foreach ($existingBookings as $existingBooking) {
            // Skip non-active bookings
            if ($existingBooking['status'] !== 'active') {
                continue;
            }
            
            // Calculate existing booking times
            $existingStart = strtotime($existingBooking['date'] . ' ' . $existingBooking['time']);
            $existingDuration = $existingBooking['duration'];
            $existingEnd = $existingStart + ($existingDuration * 3600);
            
            // Check for overlap
            if ($this->doTimeRangesOverlap($startTime, $endTime, $existingStart, $existingEnd)) {
                throw new \Exception("Time slot conflict with existing booking", 400);
            }
        }
    }
    
    /**
     * Check if two time ranges overlap
     * 
     * @param int $start1 First range start time
     * @param int $end1 First range end time
     * @param int $start2 Second range start time
     * @param int $end2 Second range end time
     * @return bool True if ranges overlap
     */
    private function doTimeRangesOverlap($start1, $end1, $start2, $end2)
    {
        return (
            ($start1 >= $start2 && $start1 < $end2) || // New booking starts during existing booking
            ($end1 > $start2 && $end1 <= $end2) || // New booking ends during existing booking
            ($start1 <= $start2 && $end1 >= $end2) // New booking completely covers existing booking
        );
    }
    
    /**
     * Generate HTML QR code for a booking
     * 
     * @param array $booking The booking data
     * @return string HTML content with QR code
     */
    public function generateBookingQrCode($booking)
    {
        // Generate QR code using QrCodeService
        $qrCode = QrCodeService::CreateHtmlPreviewImage(json_encode([
            'id' => $booking['id'],
            'guest' => $booking['guest_name'],
            'date' => $booking['date'],
            'time' => $booking['time'],
            'duration' => $booking['duration']
        ]));
        
        // Create HTML display for QR code
        $html = '<div style="text-align: center; margin: 20px;">';
        $html .= '<h2>Booking QR Code</h2>';
        $html .= '<div>Booking #' . $booking['id'] . ' - ' . $booking['guest_name'] . '</div>';
        $html .= '<div>' . $booking['date'] . ' at ' . $booking['time'] . '</div>';
        $html .= '<div style="margin: 20px;">' . $qrCode . '</div>';
        $html .= '</div>';
        
        return $html;
    }
}