<?php

namespace App\Repositories;

use App\Models\Model;

class UserRepository extends Model
{
    private $table = 'users';

    public function findByUsername($username)
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("Error finding user by username: " . $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {
            $stmt = self::$pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("Error finding user by ID: " . $e->getMessage());
        }
    }

    /**
     * Get all users with optional pagination
     * 
     * @param int|null $limit Maximum number of users to return
     * @param int|null $offset Number of users to skip
     * @return array List of users (without passwords)
     * @throws \Exception If database query fails
     */
    public function getAll($limit = null, $offset = null)
    {
        try {
            $sql = "SELECT id, username, role, created_at FROM {$this->table} ORDER BY id ASC";
            
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
            throw new \Exception("Error getting all users: " . $e->getMessage());
        }
    }

    public function create($username, $password, $role = 'employee')
    {
        try {
            $stmt = self::$pdo->prepare("INSERT INTO {$this->table} (username, password, role) VALUES (?, ?, ?)");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$username, $password_hash, $role]);
            return self::$pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
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
            throw new \Exception("Error updating user: " . $e->getMessage());
        }
    }

    public function updatePassword($id, $password)
    {
        try {
            $stmt = self::$pdo->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$password_hash, $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Error updating password: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $stmt = self::$pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new \Exception("Error deleting user: " . $e->getMessage());
        }
    }
    
    /**
     * Get the total count of users
     * 
     * @return int Total number of users
     * @throws \Exception If database query fails
     */
    public function getCount()
    {
        try {
            $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Error getting user count: " . $e->getMessage());
        }
    }
}