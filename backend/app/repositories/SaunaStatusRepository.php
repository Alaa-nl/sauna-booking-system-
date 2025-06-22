<?php

namespace App\Repositories;

use App\Models\Model;

class SaunaStatusRepository extends Model
{
    private $table = 'sauna_status';

    public function getCurrentStatus()
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting current sauna status: " . $e->getMessage());
        }
    }

    public function update($status, $reason = null, $booking_id = null)
    {
        try {
            $stmt = self::$pdo->prepare("INSERT INTO {$this->table} (status, reason, booking_id) VALUES (?, ?, ?)");
            $stmt->execute([$status, $reason, $booking_id]);
            return $this->getCurrentStatus();
        } catch (\PDOException $e) {
            throw new \Exception("Error updating sauna status: " . $e->getMessage());
        }
    }
}