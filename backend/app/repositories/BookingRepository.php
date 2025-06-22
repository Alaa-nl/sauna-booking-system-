<?php

namespace App\Repositories;

use App\Models\Model;
use App\Models\Booking;

class BookingRepository extends Model
{
    private $table = 'bookings';

    public function findById($id)
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("Error finding booking by ID: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} ORDER BY date DESC, time ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting all bookings: " . $e->getMessage());
        }
    }

    public function getAllForDate($date)
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} WHERE date = ? ORDER BY time ASC");
            $stmt->execute([$date]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting bookings for date: " . $e->getMessage());
        }
    }

    public function create($bookingData)
    {
        try {
            $columns = implode(', ', array_keys($bookingData));
            $placeholders = implode(', ', array_fill(0, count($bookingData), '?'));
            
            $stmt = self::$pdo->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($bookingData));
            
            $id = self::$pdo->lastInsertId();
            return $this->findById($id);
        } catch (\PDOException $e) {
            throw new \Exception("Error creating booking: " . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            
            $placeholders = implode('=?, ', $fields) . '=?';
            
            $stmt = self::$pdo->prepare("UPDATE {$this->table} SET {$placeholders} WHERE id = ?");
            $values[] = $id;
            
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Error updating booking: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $stmt = self::$pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Error deleting booking: " . $e->getMessage());
        }
    }
}