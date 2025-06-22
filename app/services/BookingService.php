<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\BookingRepository;

class BookingService
{
    private $bookingRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository();
    }

    public function getAll($offset = 0, $limit = 100)
    {
        return $this->bookingRepository->getAll($offset, $limit);
    }

    public function get($id)
    {
        return $this->bookingRepository->get($id);
    }

    public function create($data)
    {
        $booking = new Booking();
        $booking->guest_name = $data["guest_name"];
        $booking->room_number = $data["room_number"];
        $booking->date = $data["date"];
        $booking->time = $data["time"];
        $booking->people = $data["people"];
        
        if (isset($data["duration"])) {
            $booking->duration = $data["duration"];
        }
        
        if (isset($data["created_by"])) {
            $booking->created_by = $data["created_by"];
        }
        
        $isAvailable = $this->bookingRepository->checkAvailability(
            $booking->date, 
            $booking->time, 
            $booking->duration
        );
        
        if (!$isAvailable) {
            throw new \Exception("This time slot is already booked. Please select a different time.");
        }
        
        return $this->bookingRepository->create($booking);
    }

    public function update($id, $data)
    {
        $currentBooking = $this->bookingRepository->get($id);
        
        if (!$currentBooking) {
            throw new \Exception("Booking not found");
        }
        
        $booking = new Booking();
        $booking->guest_name = $data["guest_name"] ?? $currentBooking["guest_name"];
        $booking->room_number = $data["room_number"] ?? $currentBooking["room_number"];
        $booking->date = $data["date"] ?? $currentBooking["date"];
        $booking->time = $data["time"] ?? $currentBooking["time"];
        $booking->duration = $data["duration"] ?? $currentBooking["duration"];
        $booking->people = $data["people"] ?? $currentBooking["people"];
        $booking->status = $data["status"] ?? $currentBooking["status"];
        
        if ($booking->date != $currentBooking["date"] || 
            $booking->time != $currentBooking["time"] || 
            $booking->duration != $currentBooking["duration"]) {
            
            $isAvailable = $this->bookingRepository->checkAvailability(
                $booking->date, 
                $booking->time, 
                $booking->duration
            );
            
            if (!$isAvailable) {
                throw new \Exception("This time slot is already booked. Please select a different time.");
            }
        }
        
        return $this->bookingRepository->update($id, $booking);
    }

    public function delete($id)
    {
        $booking = $this->bookingRepository->get($id);
        
        if (!$booking) {
            throw new \Exception("Booking not found");
        }
        
        $this->bookingRepository->delete($id);
        return true;
    }
}