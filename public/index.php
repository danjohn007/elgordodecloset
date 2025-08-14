<?php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Start session
session_start();

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';

// Error reporting
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City');

// Initialize router
$router = new \App\Core\Router();

// Define routes
// Home and public routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');

// Authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', ['CSRF']);
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register', ['CSRF']);
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword', ['CSRF']);
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword', ['CSRF']);

// Restaurant search and browsing
$router->get('/restaurants', 'RestaurantController@index');
$router->get('/restaurants/{id}', 'RestaurantController@show');
$router->get('/restaurants/{id}/availability', 'RestaurantController@availability');
$router->post('/restaurants/search', 'RestaurantController@search', ['CSRF']);

// Reservation routes (require authentication)
$router->get('/reservations', 'ReservationController@index', ['Auth']);
$router->get('/reservations/create', 'ReservationController@create', ['Auth']);
$router->post('/reservations', 'ReservationController@store', ['Auth', 'CSRF']);
$router->get('/reservations/{id}', 'ReservationController@show', ['Auth']);
$router->get('/reservations/{id}/edit', 'ReservationController@edit', ['Auth']);
$router->put('/reservations/{id}', 'ReservationController@update', ['Auth', 'CSRF']);
$router->delete('/reservations/{id}', 'ReservationController@cancel', ['Auth', 'CSRF']);

// Review routes
$router->get('/reviews', 'ReviewController@index', ['Auth']);
$router->get('/restaurants/{restaurantId}/reviews', 'ReviewController@restaurantReviews');
$router->post('/reviews', 'ReviewController@store', ['Auth', 'CSRF']);
$router->get('/reviews/{id}/edit', 'ReviewController@edit', ['Auth']);
$router->put('/reviews/{id}', 'ReviewController@update', ['Auth', 'CSRF']);

// Admin routes (restaurant owners)
$router->get('/admin', 'AdminController@dashboard', ['RestaurantAdmin']);
$router->get('/admin/restaurant', 'AdminController@restaurant', ['RestaurantAdmin']);
$router->post('/admin/restaurant', 'AdminController@updateRestaurant', ['RestaurantAdmin', 'CSRF']);
$router->get('/admin/tables', 'AdminController@tables', ['RestaurantAdmin']);
$router->post('/admin/tables', 'AdminController@storeTables', ['RestaurantAdmin', 'CSRF']);
$router->get('/admin/shifts', 'AdminController@shifts', ['RestaurantAdmin']);
$router->post('/admin/shifts', 'AdminController@storeShifts', ['RestaurantAdmin', 'CSRF']);
$router->get('/admin/reservations', 'AdminController@reservations', ['RestaurantAdmin']);
$router->post('/admin/reservations/{id}/confirm', 'AdminController@confirmReservation', ['RestaurantAdmin', 'CSRF']);
$router->post('/admin/reservations/{id}/cancel', 'AdminController@cancelReservation', ['RestaurantAdmin', 'CSRF']);
$router->get('/admin/reviews', 'AdminController@reviews', ['RestaurantAdmin']);
$router->post('/admin/reviews/{id}/response', 'AdminController@respondToReview', ['RestaurantAdmin', 'CSRF']);
$router->get('/admin/reports', 'AdminController@reports', ['RestaurantAdmin']);

// Super admin routes
$router->get('/superadmin', 'SuperAdminController@dashboard', ['SuperAdmin']);
$router->get('/superadmin/restaurants', 'SuperAdminController@restaurants', ['SuperAdmin']);
$router->post('/superadmin/restaurants', 'SuperAdminController@createRestaurant', ['SuperAdmin', 'CSRF']);
$router->get('/superadmin/restaurants/{id}/edit', 'SuperAdminController@editRestaurant', ['SuperAdmin']);
$router->put('/superadmin/restaurants/{id}', 'SuperAdminController@updateRestaurant', ['SuperAdmin', 'CSRF']);
$router->delete('/superadmin/restaurants/{id}', 'SuperAdminController@deleteRestaurant', ['SuperAdmin', 'CSRF']);
$router->get('/superadmin/users', 'SuperAdminController@users', ['SuperAdmin']);
$router->get('/superadmin/reports', 'SuperAdminController@reports', ['SuperAdmin']);

// API routes
$router->get('/api/restaurants/search', 'ApiController@searchRestaurants');
$router->get('/api/restaurants/{id}/availability', 'ApiController@checkAvailability');
$router->post('/api/reservations', 'ApiController@createReservation', ['Auth', 'CSRF']);

// Resolve the route
try {
    $router->resolve();
} catch (Exception $e) {
    // Handle errors
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo "Error: " . $e->getMessage();
    } else {
        http_response_code(500);
        require_once __DIR__ . '/../app/Views/errors/500.php';
    }
}