<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\SaunaStatusService;

class SaunaController extends Controller
{
    private $saunaStatusService;

    function __construct()
    {
        parent::__construct();
        $this->saunaStatusService = new SaunaStatusService();
    }

    /**
     * Get current sauna status (public)
     * GET /sauna/status
     */
    public function getStatus()
    {
        try {
            // Get current status
            $status = $this->saunaStatusService->getCurrentStatus();
            
            // Return JSON response
            ResponseService::Send($status);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Update sauna status (authenticated)
     * PUT /sauna/status
     */
    public function updateStatus()
    {
        try {
            // Require authentication
            $this->requireAuth();
            
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["status"], $data);
            
            // Get optional fields
            $reason = $data["reason"] ?? null;
            $bookingId = $data["booking_id"] ?? null;
            
            // Update status
            $status = $this->saunaStatusService->updateStatus(
                $data["status"],
                $reason,
                $bookingId
            );
            
            // Return JSON response
            ResponseService::Send($status);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}