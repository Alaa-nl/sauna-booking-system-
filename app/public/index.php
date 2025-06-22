<?php

/**
 * Setup
 */

// require autoload file to autoload vendor libraries
require_once __DIR__ . '/../vendor/autoload.php';

// require local classes
use App\Services\EnvService;
use App\Services\ErrorReportingService;
use App\Services\ResponseService;
use App\Controllers\BookingController;
use App\Controllers\UserController;
use App\Controllers\SaunaController;

// require vendor libraries
use Steampixel\Route;

// initialize global environment variables
EnvService::Init();

// initialize error reporting (on in local env)
ErrorReportingService::Init();

// set CORS headers
ResponseService::SetCorsHeaders();

/**
 * Main application routes
 */
// top level fail-safe try/catch
try {
    /**
     * User routes
     */
    Route::add('/users/login', function () {
        $userController = new UserController();
        $userController->login();
    }, ["post"]);

    /**
     * Booking routes
     */
    // get all bookings
    Route::add('/bookings', function () {
        $bookingController = new BookingController();
        $bookingController->getAll();
    }, ["get"]);
    
    // get booking by id
    Route::add('/bookings/([0-9]*)', function ($id) {
        $bookingController = new BookingController();
        $bookingController->get($id);
    }, ["get"]);
    
    // create booking
    Route::add('/bookings', function () {
        $bookingController = new BookingController();
        $bookingController->create();
    }, ["post"]);
    
    // update booking
    Route::add('/bookings/([0-9]*)', function ($id) {
        $bookingController = new BookingController();
        $bookingController->update($id);
    }, ["put"]);
    
    // delete booking
    Route::add('/bookings/([0-9]*)', function ($id) {
        $bookingController = new BookingController();
        $bookingController->delete($id);
    }, ["delete"]);
    
    /**
     * Sauna Status routes
     */
    Route::add('/sauna/status', function () {
        $saunaController = new SaunaController();
        $saunaController->getStatus();
    }, ["get"]);
    
    Route::add('/sauna/status', function () {
        $saunaController = new SaunaController();
        $saunaController->updateStatus();
    }, ["put"]);

    /**
     * 404 route handler
     */
    Route::pathNotFound(function () {
        ResponseService::Error("route is not defined", 404);
    });
} catch (\Throwable $error) {
    if ($_ENV["environment"] == "LOCAL") {
        var_dump($error);
    } else {
        error_log($error);
    }
    ResponseService::Error("A server error occurred");
}

Route::run();