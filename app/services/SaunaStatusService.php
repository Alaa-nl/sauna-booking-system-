<?php

namespace App\Services;

use App\Models\SaunaStatus;
use App\Repositories\SaunaStatusRepository;

class SaunaStatusService
{
    private $saunaStatusRepository;

    public function __construct()
    {
        $this->saunaStatusRepository = new SaunaStatusRepository();
    }

    public function getStatus()
    {
        return $this->saunaStatusRepository->getStatus();
    }

    public function updateStatus($statusData)
    {
        $status = new SaunaStatus();
        $status->status = $statusData['status'];
        $status->reason = $statusData['reason'] ?? null;
        $status->booking_id = $statusData['booking_id'] ?? null;
        
        return $this->saunaStatusRepository->updateStatus($status);
    }
}