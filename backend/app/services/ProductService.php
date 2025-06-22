<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    private $productRepository;

    function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    /**
     * Get all products with pagination
     * 
     * @param int $offset Pagination offset
     * @param int $limit Number of products to return
     * @return array List of products
     */
    public function getAllProducts($offset = 0, $limit = 10)
    {
        return $this->productRepository->getAll($offset, $limit);
    }

    /**
     * Get a product by ID
     * 
     * @param int $id Product ID
     * @return array|null Product data or null if not found
     */
    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * Create a new product
     * 
     * @param array $data Product data
     * @return int Created product ID
     */
    public function createProduct($data)
    {
        // Validate price is numeric
        if (isset($data['price']) && !is_numeric($data['price'])) {
            throw new \Exception("Price must be a numeric value", 400);
        }

        // Extract required fields
        $name = $data['name'];
        $price = $data['price'];
        
        // Extract optional fields with defaults
        $description = $data['description'] ?? '';
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        $image_url = $data['image_url'] ?? null;
        
        // Create product in repository
        return $this->productRepository->create([
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'stock' => $stock,
            'image_url' => $image_url
        ]);
    }

    /**
     * Update an existing product
     * 
     * @param int $id Product ID
     * @param array $data Product data to update
     * @return bool True if updated successfully
     */
    public function updateProduct($id, $data)
    {
        // Validate price is numeric if provided
        if (isset($data['price']) && !is_numeric($data['price'])) {
            throw new \Exception("Price must be a numeric value", 400);
        }
        
        // Validate stock is numeric if provided
        if (isset($data['stock']) && !is_numeric($data['stock'])) {
            throw new \Exception("Stock must be a numeric value", 400);
        }
        
        // Update product in repository
        return $this->productRepository->update($id, $data);
    }

    /**
     * Delete a product
     * 
     * @param int $id Product ID
     * @return bool True if deleted successfully
     */
    public function deleteProduct($id)
    {
        return $this->productRepository->delete($id);
    }
}