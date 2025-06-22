<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\BookingService;
use App\Services\QrCodeService;

class BookingController extends Controller
{
    private $bookingService;

    function __construct()
    {
        parent::__construct();
        $this->bookingService = new BookingService();
    }

    /**
     * Get all bookings (authenticated)
     * GET /bookings
     */
    public function getAll()
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Get all bookings
            $bookings = $this->bookingService->getAllBookings();
            
            // Return JSON response
            ResponseService::Send($bookings);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Get a booking by ID (authenticated)
     * GET /bookings/{id}
     */
    public function getOne($id)
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Get booking by ID
            $booking = $this->bookingService->getBookingById($id);
            
            // Return JSON response
            ResponseService::Send($booking);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Get QR code for a booking (authenticated)
     * GET /bookings/{id}/qrcode
     */
    public function getQrCode($id)
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Get booking by ID
            $booking = $this->bookingService->getBookingById($id);
            
            // Generate QR code HTML
            $qrCode = QrCodeService::CreateHtmlPreviewImage(json_encode([
                'id' => $booking['id'],
                'guest' => $booking['guest_name'],
                'date' => $booking['date'],
                'time' => $booking['time'],
                'duration' => $booking['duration']
            ]));
            
            // Send QR code HTML (not JSON)
            header('Content-Type: text/html; charset=utf-8');
            echo '<div style="text-align: center; margin: 20px;">';
            echo '<h2>Booking QR Code</h2>';
            echo '<div>Booking #' . $booking['id'] . ' - ' . $booking['guest_name'] . '</div>';
            echo '<div>' . $booking['date'] . ' at ' . $booking['time'] . '</div>';
            echo '<div style="margin: 20px;">' . $qrCode . '</div>';
            echo '</div>';
            exit();
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Create a new booking (authenticated)
     * POST /bookings
     */
    public function create()
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            
            // Validate required fields
            $requiredFields = ['guest_name', 'date', 'time', 'room_number', 'people'];
            $this->validateInput($requiredFields, $data);
            
            // For employee bookings, add the created_by field
            $user = $this->getAuthenticatedUser();
            if ($user) {
                $data['created_by'] = $user->username;
            }
            
            // Create booking
            $booking = $this->bookingService->createBooking($data);
            
            // Return success response
            ResponseService::Send($booking, 201);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Update a booking (authenticated)
     * PUT /bookings/{id}
     */
    public function update($id)
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Get data from request
            $data = $this->decodePostData();
            
            // Update booking
            $success = $this->bookingService->updateBooking($id, $data);
            
            // Return success response
            ResponseService::Send(["message" => "Booking updated successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Delete a booking (authenticated)
     * DELETE /bookings/{id}
     */
    public function delete($id)
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Delete booking
            $success = $this->bookingService->deleteBooking($id);
            
            // Return success response
            ResponseService::Send(["message" => "Booking deleted successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}