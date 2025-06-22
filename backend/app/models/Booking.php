<?php

namespace App\Models;

/**
 * Booking model
 */
class Booking extends Model
{
    public int $id;
    public string $guest_name;
    public string $date;
    public string $time;
    public int $duration = 1;
    public int $room_number;
    public int $people = 1;
    public string $status = 'active';
    public ?string $created_by = null;
    public string $created_at;
}