<?php

namespace App\Repositories;

use App\Models\Model;
use PDO;

class ProductRepository extends Model
{
    /**
     * Get all products with pagination
     * 
     * @param int $offset Pagination offset
     * @param int $limit Number of products to return
     * @return array List of products
     */
    public function getAll($offset = 0, $limit = 10)
    {
        $stmt = self::$pdo->prepare("
            SELECT id, name, description, price, stock, image_url, created_at, updated_at
            FROM products
            ORDER BY id
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = $stmt->fetchAll();
        
        // Get total count for pagination
        $countStmt = self::$pdo->query("SELECT COUNT(*) as total FROM products");
        $total = $countStmt->fetch()['total'];
        
        return [
            'products' => $products,
            'pagination' => [
                'total' => (int)$total,
                'offset' => (int)$offset,
                'limit' => (int)$limit
            ]
        ];
    }
    
    /**
     * Get a product by ID
     * 
     * @param int $id Product ID
     * @return array|null Product data or null if not found
     */
    public function getById($id)
    {
        $stmt = self::$pdo->prepare("
            SELECT id, name, description, price, stock, image_url, created_at, updated_at
            FROM products
            WHERE id = :id
        ");
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Create a new product
     * 
     * @param array $data Product data
     * @return int Created product ID
     */
    public function create($data)
    {
        $stmt = self::$pdo->prepare("
            INSERT INTO products (name, description, price, stock, image_url)
            VALUES (:name, :description, :price, :stock, :image_url)
        ");
        
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT);
        $stmt->bindValue(':image_url', $data['image_url'], PDO::PARAM_STR);
        
        $stmt->execute();
        
        return self::$pdo->lastInsertId();
    }
    
    /**
     * Update an existing product
     * 
     * @param int $id Product ID
     * @param array $data Product data to update
     * @return bool True if updated successfully
     */
    public function update($id, $data)
    {
        // Build the SET clause dynamically based on provided fields
        $setClause = [];
        $params = [];
        
        // Add fields to update if they are provided
        if (isset($data['name'])) {
            $setClause[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['description'])) {
            $setClause[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        
        if (isset($data['price'])) {
            $setClause[] = "price = :price";
            $params[':price'] = $data['price'];
        }
        
        if (isset($data['stock'])) {
            $setClause[] = "stock = :stock";
            $params[':stock'] = $data['stock'];
        }
        
        if (isset($data['image_url'])) {
            $setClause[] = "image_url = :image_url";
            $params[':image_url'] = $data['image_url'];
        }
        
        // Add updated_at timestamp
        $setClause[] = "updated_at = CURRENT_TIMESTAMP";
        
        // If nothing to update, return false
        if (empty($setClause)) {
            return false;
        }
        
        // Build the SQL query
        $sql = "UPDATE products SET " . implode(", ", $setClause) . " WHERE id = :id";
        
        // Add the ID parameter
        $params[':id'] = $id;
        
        // Prepare and execute the query
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        
        // Check if any rows were affected
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete a product
     * 
     * @param int $id Product ID
     * @return bool True if deleted successfully
     */
    public function delete($id)
    {
        $stmt = self::$pdo->prepare("
            DELETE FROM products
            WHERE id = :id
        ");
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}