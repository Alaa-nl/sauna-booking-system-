<?php

namespace App\Repositories;

use App\Models\Model;

class BookingRepository extends Model
{
    private $table = 'bookings';

    /**
     * Find a booking by ID
     * 
     * @param int $id The booking ID to find
     * @return array|false The booking data or false if not found
     * @throws \Exception If database query fails
     */
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

    /**
     * Get all bookings ordered by date (newest first) and time with optional pagination
     * 
     * @param int|null $limit Maximum number of bookings to return
     * @param int|null $offset Number of bookings to skip
     * @return array List of bookings
     * @throws \Exception If database query fails
     */
    public function getAll($limit = null, $offset = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY date DESC, time ASC";
            
            // Add pagination if limit is provided
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }
            
            $stmt = self::$pdo->prepare($sql);
            
            // Bind pagination parameters if provided
            $params = [];
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) {
                    $params[] = $offset;
                }
            }
            
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting all bookings: " . $e->getMessage());
        }
    }

    /**
     * Get all bookings for a specific date ordered by time
     * 
     * @param string $date Date in YYYY-MM-DD format
     * @return array List of bookings for the date
     * @throws \Exception If database query fails
     */
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

    /**
     * Create a new booking in the database
     * 
     * @param array $bookingData Data for the new booking
     * @return array The created booking with its ID
     * @throws \Exception If database insert fails
     */
    public function create($bookingData)
    {
        try {
            // Generate SQL for dynamic columns
            $columns = implode(', ', array_keys($bookingData));
            $placeholders = implode(', ', array_fill(0, count($bookingData), '?'));
            
            // Insert booking
            $stmt = self::$pdo->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($bookingData));
            
            // Return the newly created booking
            $id = self::$pdo->lastInsertId();
            return $this->findById($id);
        } catch (\PDOException $e) {
            throw new \Exception("Error creating booking: " . $e->getMessage());
        }
    }

    /**
     * Update a booking in the database
     * 
     * @param int $id Booking ID to update
     * @param array $data Updated booking data
     * @return bool True if update successful (rows affected)
     * @throws \Exception If database update fails
     */
    public function update($id, $data)
    {
        try {
            // Prepare update data
            $fields = array_keys($data);
            $values = array_values($data);
            
            // Build SQL placeholders for SET clause
            $placeholders = implode('=?, ', $fields) . '=?';
            
            // Execute update
            $stmt = self::$pdo->prepare("UPDATE {$this->table} SET {$placeholders} WHERE id = ?");
            $values[] = $id;  // Add ID as last parameter
            
            $stmt->execute($values);
            return $stmt->rowCount() > 0;  // Return true if rows were affected
        } catch (\PDOException $e) {
            throw new \Exception("Error updating booking: " . $e->getMessage());
        }
    }

    /**
     * Delete a booking from the database
     * 
     * @param int $id Booking ID to delete
     * @return bool True if deletion successful (rows affected)
     * @throws \Exception If database delete fails
     */
    public function delete($id)
    {
        try {
            $stmt = self::$pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;  // Return true if rows were affected
        } catch (\PDOException $e) {
            throw new \Exception("Error deleting booking: " . $e->getMessage());
        }
    }
    
    /**
     * Get the total count of bookings
     * 
     * @return int Total number of bookings
     * @throws \Exception If database query fails
     */
    public function getCount()
    {
        try {
            $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting booking count: " . $e->getMessage());
        }
    }
}