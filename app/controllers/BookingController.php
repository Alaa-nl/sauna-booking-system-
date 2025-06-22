<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\BookingService;

class BookingController extends Controller
{
    private $bookingService;

    function __construct()
    {
        parent::__construct();
        $this->bookingService = new BookingService();
    }

    function getAll()
    {
        try {
            // Get offset and limit from query parameters
            $offset = $_GET['offset'] ?? 0;
            $limit = $_GET['limit'] ?? 100;
            
            // For employee view, require authentication and show all bookings
            if (isset($_GET['employee']) && $_GET['employee'] === 'true') {
                $user = $this->checkForJwt();
                if (!$user) {
                    return;
                }
            }
            
            // Call service method
            $bookings = $this->bookingService->getAll($offset, $limit);
            
            // Return JSON response
            ResponseService::Send($bookings);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to get bookings: " . $e->getMessage(), 500);
        }
    }

    function get($id)
    {
        try {
            // Call service method
            $booking = $this->bookingService->get($id);
            
            if (!$booking) {
                ResponseService::Error("Booking not found", 404);
                return;
            }
            
            // Return JSON response
            ResponseService::Send($booking);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to get booking: " . $e->getMessage(), 500);
        }
    }

    function create()
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["guest_name", "date", "time", "room_number", "people"], $data);
            
            // For employee bookings with extended options, require authentication
            if (isset($data['created_by']) || (isset($data['duration']) && $data['duration'] > 1) || (isset($data['people']) && $data['people'] > 4)) {
                $user = $this->checkForJwt();
                if (!$user) {
                    return;
                }
                
                // Set created_by to authenticated username
                $data['created_by'] = $user->data->username;
            }
            
            // Call service method
            $newBooking = $this->bookingService->create($data);
            
            // Return JSON response
            ResponseService::Send($newBooking, 201);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to create booking: " . $e->getMessage(), 500);
        }
    }

    function update($id)
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            
            // For booking status updates, authentication is required
            if (isset($data['status']) && $data['status'] !== 'active') {
                $user = $this->checkForJwt();
                if (!$user) {
                    return;
                }
            }
            
            // Call service method
            $updatedBooking = $this->bookingService->update($id, $data);
            
            // Return JSON response
            ResponseService::Send($updatedBooking);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to update booking: " . $e->getMessage(), 500);
        }
    }

    function delete($id)
    {
        try {
            // Require authentication for deleting bookings
            $user = $this->checkForJwt();
            if (!$user) {
                return;
            }
            
            // Call service method
            $this->bookingService->delete($id);
            
            // Return JSON response
            ResponseService::Send(["success" => true], 204);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to delete booking: " . $e->getMessage(), 500);
        }
    }
}