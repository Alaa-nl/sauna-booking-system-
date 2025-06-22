<?php

namespace App\Models;

class SaunaStatus
{
    public int $id;
    public string $status = 'available';
    public ?string $reason = null;
    public ?string $updated_at = null;
    public ?int $booking_id = null;
}