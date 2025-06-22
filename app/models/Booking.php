<?php

namespace App\Models;

class Booking
{
    public int $id;
    public string $guest_name;
    public string $room_number;
    public string $date;
    public string $time;
    public int $duration = 1;
    public int $people;
    public string $status = 'active';
    public string $created_by = 'guest';
}