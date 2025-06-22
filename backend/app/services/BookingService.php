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
    
    public function getAllBookings()
    {
        try {
            return $this->bookingRepository->getAll();
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving bookings: " . $e->getMessage());
        }
    }
    
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
    
    public function createBooking($bookingData)
    {
        try {
            // Validate booking data
            $this->validateBooking($bookingData);
            
            // Check for time conflicts
            $this->checkForTimeConflicts($bookingData);
            
            // Create booking
            $booking = $this->bookingRepository->create($bookingData);
            
            return $booking;
        } catch (\Exception $e) {
            throw new \Exception("Error creating booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function updateBooking($id, $data)
    {
        try {
            // Check if booking exists
            $booking = $this->bookingRepository->findById($id);
            if (!$booking) {
                throw new \Exception("Booking not found", 404);
            }
            
            // Update booking status
            $success = $this->bookingRepository->update($id, $data);
            
            // If updating to in_use, update sauna status
            if (isset($data['status']) && $data['status'] === 'in_use') {
                $this->saunaStatusRepository->update('busy', null, $id);
            }
            
            // If updating to completed or cancelled, and sauna status is busy with this booking,
            // update sauna status to available
            if (isset($data['status']) && ($data['status'] === 'completed' || $data['status'] === 'cancelled')) {
                $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
                if ($saunaStatus && $saunaStatus['status'] === 'busy' && $saunaStatus['booking_id'] == $id) {
                    $this->saunaStatusRepository->update('available', null, null);
                }
            }
            
            return $success;
        } catch (\Exception $e) {
            throw new \Exception("Error updating booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    public function deleteBooking($id)
    {
        try {
            // Check if booking exists
            $booking = $this->bookingRepository->findById($id);
            if (!$booking) {
                throw new \Exception("Booking not found", 404);
            }
            
            // Check if sauna is currently in use by this booking
            $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
            if ($saunaStatus && $saunaStatus['status'] === 'busy' && $saunaStatus['booking_id'] == $id) {
                throw new \Exception("Cannot delete booking that is currently in progress", 400);
            }
            
            return $this->bookingRepository->delete($id);
        } catch (\Exception $e) {
            throw new \Exception("Error deleting booking: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    private function validateBooking($bookingData)
    {
        // Required fields
        $requiredFields = ['guest_name', 'date', 'time', 'room_number', 'people'];
        foreach ($requiredFields as $field) {
            if (!isset($bookingData[$field]) || empty($bookingData[$field])) {
                throw new \Exception("Missing required field: {$field}", 400);
            }
        }
        
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
    
    private function checkForTimeConflicts($bookingData)
    {
        // Get all bookings for the date
        $bookingsForDate = $this->bookingRepository->getAllForDate($bookingData['date']);
        
        // Current booking details
        $startTime = strtotime($bookingData['date'] . ' ' . $bookingData['time']);
        $duration = isset($bookingData['duration']) ? $bookingData['duration'] : 1;
        $endTime = $startTime + ($duration * 3600); // duration in hours
        
        // Check for conflicts with existing active bookings
        foreach ($bookingsForDate as $existingBooking) {
            // Skip non-active bookings
            if ($existingBooking['status'] !== 'active') {
                continue;
            }
            
            // Calculate existing booking times
            $existingStart = strtotime($existingBooking['date'] . ' ' . $existingBooking['time']);
            $existingDuration = $existingBooking['duration'];
            $existingEnd = $existingStart + ($existingDuration * 3600);
            
            // Check for overlap
            if (
                ($startTime >= $existingStart && $startTime < $existingEnd) || // New booking starts during existing booking
                ($endTime > $existingStart && $endTime <= $existingEnd) || // New booking ends during existing booking
                ($startTime <= $existingStart && $endTime >= $existingEnd) // New booking completely covers existing booking
            ) {
                throw new \Exception("Time slot conflict with existing booking", 400);
            }
        }
        
        // Check sauna status
        $saunaStatus = $this->saunaStatusRepository->getCurrentStatus();
        if ($saunaStatus && $saunaStatus['status'] === 'out_of_order') {
            throw new \Exception("Sauna is currently out of order: " . $saunaStatus['reason'], 400);
        }
    }
}