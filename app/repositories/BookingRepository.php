<?php

namespace App\Repositories;

use App\Models\Booking;

class BookingRepository
{
    private $pdo;

    public function __construct()
    {
        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset={$_ENV['DB_CHARSET']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $this->pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);
    }

    public function getAll($offset = 0, $limit = 100)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings ORDER BY date, time LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(Booking $booking)
    {
        $query = "INSERT INTO bookings (guest_name, room_number, date, time, duration, people, status, created_by) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $booking->guest_name,
            $booking->room_number,
            $booking->date,
            $booking->time,
            $booking->duration,
            $booking->people,
            $booking->status,
            $booking->created_by
        ]);
        
        $bookingId = $this->pdo->lastInsertId();
        return $this->get($bookingId);
    }

    public function update($id, Booking $booking)
    {
        $query = "UPDATE bookings SET 
                guest_name = ?,
                room_number = ?,
                date = ?,
                time = ?,
                duration = ?,
                people = ?,
                status = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $booking->guest_name,
            $booking->room_number,
            $booking->date,
            $booking->time,
            $booking->duration,
            $booking->people,
            $booking->status,
            $id
        ]);
        
        return $this->get($id);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function checkAvailability($date, $time, $duration)
    {
        $query = "SELECT * FROM bookings 
                 WHERE date = ? 
                 AND ((time <= ? AND ADDTIME(time, SEC_TO_TIME(duration * 3600)) > ?) 
                 OR (time < ADDTIME(?, SEC_TO_TIME(? * 3600)) AND time >= ?))
                 AND status = 'active'";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$date, $time, $time, $time, $duration, $time]);
        
        return $stmt->fetch() === false;
    }
}