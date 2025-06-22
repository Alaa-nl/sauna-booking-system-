<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\UserService;

class UserController extends Controller
{
    private $userService;

    function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    /**
     * Login a user and return JWT token
     * POST /users/login
     */
    public function login()
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["username", "password"], $data);
            
            // Call service method to check username/password
            $user = $this->userService->checkUsernamePassword($data["username"], $data["password"]);
            
            if (!$user) {
                ResponseService::Error("Invalid credentials", 401);
                return;
            }
            
            // Generate JWT token
            $tokenResponse = $this->userService->generateJwt($user);
            
            // Return JSON response
            ResponseService::Send($tokenResponse);
        } catch (\Exception $e) {
            ResponseService::Error("Login failed: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get all users (admin only)
     * GET /users
     */
    public function getAll()
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get all users
            $users = $this->userService->getAllUsers();
            
            // Return JSON response
            ResponseService::Send($users);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Create a new user (admin only)
     * POST /users
     */
    public function create()
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["username", "password"], $data);
            
            // Set role from request or default to employee
            $role = isset($data["role"]) && $data["role"] === "admin" ? "admin" : "employee";
            
            // Create user
            $userId = $this->userService->createUser($data["username"], $data["password"], $role);
            
            // Return success response
            ResponseService::Send(["id" => $userId, "message" => "User created successfully"], 201);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Update a user (admin only)
     * PUT /users/{id}
     */
    public function update($id)
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get data from request
            $data = $this->decodePostData();
            
            // Remove password field if present (use resetPassword for that)
            if (isset($data["password"])) {
                unset($data["password"]);
            }
            
            // Update user
            $success = $this->userService->updateUser($id, $data);
            
            if (!$success) {
                ResponseService::Error("User not found or no changes made", 404);
                return;
            }
            
            // Return success response
            ResponseService::Send(["message" => "User updated successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Reset a user's password (admin only)
     * PUT /users/{id}/reset-password
     */
    public function resetPassword($id)
    {
        try {
            // Require admin access
            $this->requireAdmin();
            
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["password"], $data);
            
            // Reset password
            $success = $this->userService->resetPassword($id, $data["password"]);
            
            if (!$success) {
                ResponseService::Error("User not found", 404);
                return;
            }
            
            // Return success response
            ResponseService::Send(["message" => "Password reset successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Change own password (authenticated)
     * PUT /users/change-password
     */
    public function changePassword()
    {
        try {
            // Require authentication
            $user = $this->requireAuth();
            
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["currentPassword", "newPassword"], $data);
            
            // Change password
            $success = $this->userService->changePassword(
                $user->id, 
                $data["currentPassword"], 
                $data["newPassword"]
            );
            
            if (!$success) {
                ResponseService::Error("Password change failed - current password incorrect", 400);
                return;
            }
            
            // Return success response
            ResponseService::Send(["message" => "Password changed successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Delete a user (admin only)
     * DELETE /users/{id}
     */
    public function delete($id)
    {
        try {
            // Require admin access
            $admin = $this->requireAdmin();
            
            // Prevent deleting own account
            if ($admin->id == $id) {
                ResponseService::Error("Cannot delete your own account", 400);
                return;
            }
            
            // Delete user
            $success = $this->userService->deleteUser($id);
            
            if (!$success) {
                ResponseService::Error("User not found", 404);
                return;
            }
            
            // Return success response
            ResponseService::Send(["message" => "User deleted successfully"]);
        } catch (\Exception $e) {
            ResponseService::Error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}