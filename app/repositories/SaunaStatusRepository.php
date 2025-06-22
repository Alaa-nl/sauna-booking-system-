<?php

namespace App\Repositories;

use App\Models\SaunaStatus;

class SaunaStatusRepository
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

    public function getStatus()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sauna_status WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateStatus(SaunaStatus $status)
    {
        $query = "UPDATE sauna_status SET 
                 status = ?, 
                 reason = ?,
                 booking_id = ?,
                 updated_at = NOW() 
                 WHERE id = 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $status->status,
            $status->reason,
            $status->booking_id
        ]);
        
        return $this->getStatus();
    }
}