<?php

namespace App\Services;

use App\Repositories\SaunaStatusRepository;
use App\Repositories\BookingRepository;

class SaunaStatusService
{
    private $saunaStatusRepository;
    private $bookingRepository;
    
    public function __construct()
    {
        $this->saunaStatusRepository = new SaunaStatusRepository();
        $this->bookingRepository = new BookingRepository();
    }
    
    public function getCurrentStatus()
    {
        try {
            $status = $this->saunaStatusRepository->getCurrentStatus();
            
            // If status has a booking_id, get booking details
            if ($status && $status['booking_id']) {
                $booking = $this->bookingRepository->findById($status['booking_id']);
                if ($booking) {
                    $status['booking'] = $booking;
                }
            }
            
            return $status;
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving sauna status: " . $e->getMessage());
        }
    }
    
    public function updateStatus($status, $reason = null, $booking_id = null)
    {
        try {
            // Validate status
            $validStatuses = ['available', 'busy', 'out_of_order'];
            if (!in_array($status, $validStatuses)) {
                throw new \Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses), 400);
            }
            
            // If status is busy, booking_id is required
            if ($status === 'busy' && !$booking_id) {
                throw new \Exception("Booking ID is required when status is busy", 400);
            }
            
            // If status is out_of_order, reason is required
            if ($status === 'out_of_order' && !$reason) {
                throw new \Exception("Reason is required when status is out_of_order", 400);
            }
            
            // If booking_id is provided, verify it exists
            if ($booking_id) {
                $booking = $this->bookingRepository->findById($booking_id);
                if (!$booking) {
                    throw new \Exception("Booking not found", 404);
                }
                
                // Update booking status to in_use
                $this->bookingRepository->update($booking_id, ['status' => 'in_use']);
            }
            
            return $this->saunaStatusRepository->update($status, $reason, $booking_id);
        } catch (\Exception $e) {
            throw new \Exception("Error updating sauna status: " . $e->getMessage(), $e->getCode() ?: 500);
        }
    }
}