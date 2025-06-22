<?php
// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Import and use the ResponseService for CORS
use App\Services\ResponseService;

// Handle CORS with the ResponseService
ResponseService::SetCorsHeaders();

// Import environment and error reporting services
use App\Services\EnvService;
use App\Services\ErrorReportingService;

// Initialize environment variables
EnvService::Init();

// Set up error reporting based on environment
ErrorReportingService::Init();

// Import necessary classes
use Steampixel\Route;
use App\Controllers\UserController;
use App\Controllers\BookingController;
use App\Controllers\SaunaController;
use App\Controllers\ProductController;

// Initialize controllers
$userController = new UserController();
$bookingController = new BookingController();
$saunaController = new SaunaController();
$productController = new ProductController();

// User routes - following the exact pattern from lectures
Route::add('/users/login', function() use ($userController) {
    $userController->login();
}, 'post');

Route::add('/users', function() use ($userController) {
    $userController->getAll();
}, 'get');

Route::add('/users', function() use ($userController) {
    $userController->create();
}, 'post');

Route::add('/users/([0-9]+)', function($id) use ($userController) {
    $userController->update($id);
}, 'put');

Route::add('/users/([0-9]+)', function($id) use ($userController) {
    $userController->delete($id);
}, 'delete');

Route::add('/users/change-password', function() use ($userController) {
    $userController->changePassword();
}, 'put');

Route::add('/users/([0-9]+)/reset-password', function($id) use ($userController) {
    $userController->resetPassword($id);
}, 'put');

// Booking routes
Route::add('/bookings', function() use ($bookingController) {
    $bookingController->getAll();
}, 'get');

Route::add('/bookings/([0-9]+)', function($id) use ($bookingController) {
    $bookingController->getOne($id);
}, 'get');

Route::add('/bookings', function() use ($bookingController) {
    $bookingController->create();
}, 'post');

Route::add('/bookings/([0-9]+)', function($id) use ($bookingController) {
    $bookingController->update($id);
}, 'put');

Route::add('/bookings/([0-9]+)', function($id) use ($bookingController) {
    $bookingController->delete($id);
}, 'delete');

// Sauna status routes
Route::add('/sauna/status', function() use ($saunaController) {
    $saunaController->getStatus();
}, 'get');

Route::add('/sauna/status', function() use ($saunaController) {
    $saunaController->updateStatus();
}, 'put');

// QR Code route for bookings (optional)
Route::add('/bookings/([0-9]+)/qrcode', function($id) use ($bookingController) {
    $bookingController->getQrCode($id);
}, 'get');

// Product routes - implementing the required endpoints
Route::add('/products', function() use ($productController) {
    $productController->getAll();
}, 'get');

Route::add('/products/([0-9]+)', function($id) use ($productController) {
    $productController->getOne($id);
}, 'get');

Route::add('/products', function() use ($productController) {
    $productController->create();
}, 'post');

Route::add('/products/([0-9]+)', function($id) use ($productController) {
    $productController->update($id);
}, 'put');

Route::add('/products/([0-9]+)', function($id) use ($productController) {
    $productController->delete($id);
}, 'delete');

// Handle 404 errors
Route::pathNotFound(function() {
    http_response_code(404);
    ResponseService::Error("Endpoint not found", 404);
});

// Handle method not allowed
Route::methodNotAllowed(function() {
    http_response_code(405);
    ResponseService::Error("Method not allowed", 405);
});

// Run the router
Route::run('/');