<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\ProductService;

class ProductController extends Controller
{
    private $productService;

    function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
    }

    /**
     * Get all products with optional pagination
     * GET /products?offset=0&limit=10
     */
    public function getAll()
    {
        try {
            // Get query parameters for pagination
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            // Get products with pagination
            $products = $this->productService->getAllProducts($offset, $limit);
            
            // Return JSON response
            ResponseService::Send($products);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Get a single product by ID
     * GET /products/{id}
     */
    public function getOne($id)
    {
        try {
            // Get product by ID
            $product = $this->productService->getProductById($id);
            
            if (!$product) {
                ResponseService::Error("Product not found", 404);
                return;
            }
            
            // Return JSON response
            ResponseService::Send($product);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Create a new product
     * POST /products
     */
    public function create()
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["name", "price"], $data);
            
            // Create product
            $productId = $this->productService->createProduct($data);
            
            // Get the created product
            $product = $this->productService->getProductById($productId);
            
            // Return success response with the created product
            ResponseService::Send($product, 201);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Update an existing product
     * PUT /products/{id}
     */
    public function update($id)
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get data from request
            $data = $this->decodePostData();
            
            // Update product
            $success = $this->productService->updateProduct($id, $data);
            
            if (!$success) {
                ResponseService::Error("Product not found or no changes made", 404);
                return;
            }
            
            // Get the updated product
            $product = $this->productService->getProductById($id);
            
            // Return success response with the updated product
            ResponseService::Send($product);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Delete a product
     * DELETE /products/{id}
     */
    public function delete($id)
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Delete product
            $success = $this->productService->deleteProduct($id);
            
            if (!$success) {
                ResponseService::Error("Product not found", 404);
                return;
            }
            
            // Return success response
            ResponseService::Send(["message" => "Product deleted successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}