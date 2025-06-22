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
     * Support pagination with ?limit=10&offset=0 query parameters
     */
    public function getAll()
    {
        try {
            // 1. Verify authentication
            $this->requireAuth();
            
            // 2. Get pagination parameters
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : null;
            
            // 3. Call service method with pagination
            $result = $this->bookingService->getAllBookings($limit, $offset);
            
            // 4. Return JSON response with pagination metadata
            ResponseService::Send($result);
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
            // 1. Verify authentication
            $this->requireAuth();
            
            // 2. Call service method
            $booking = $this->bookingService->getBookingById($id);
            
            // 3. Return JSON response
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
            // 1. Verify authentication
            $this->requireAuth();
            
            // 2. Call service methods
            $booking = $this->bookingService->getBookingById($id);
            $qrHtml = $this->bookingService->generateBookingQrCode($booking);
            
            // 3. Return HTML response
            header('Content-Type: text/html; charset=utf-8');
            echo $qrHtml;
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
            // 1. Get data from request
            $data = $this->decodePostData();
            
            // 2. Attach authenticated user if available
            $user = $this->getAuthenticatedUser();
            if ($user) {
                $data['created_by'] = $user->username;
            }
            
            // 3. Call service method
            $booking = $this->bookingService->createBooking($data);
            
            // 4. Return JSON response
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
            // 1. Verify authentication
            $this->requireAuth();
            
            // 2. Get data from request
            $data = $this->decodePostData();
            
            // 3. Call service method
            $this->bookingService->updateBooking($id, $data);
            
            // 4. Return JSON response
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
            // 1. Verify authentication
            $this->requireAuth();
            
            // 2. Call service method
            $this->bookingService->deleteBooking($id);
            
            // 3. Return JSON response
            ResponseService::Send(["message" => "Booking deleted successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}