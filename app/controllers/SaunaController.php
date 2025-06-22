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

    function getStatus()
    {
        try {
            // Call service method
            $status = $this->saunaStatusService->getStatus();
            
            // Return JSON response
            ResponseService::Send($status);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to get sauna status: " . $e->getMessage(), 500);
        }
    }

    function updateStatus()
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["status"], $data);
            
            // Require authentication for updating status
            $user = $this->checkForJwt();
            if (!$user) {
                return;
            }
            
            // Call service method
            $updatedStatus = $this->saunaStatusService->updateStatus($data);
            
            // Return JSON response
            ResponseService::Send($updatedStatus);
        } catch (\Exception $e) {
            ResponseService::Error("Failed to update sauna status: " . $e->getMessage(), 500);
        }
    }
}