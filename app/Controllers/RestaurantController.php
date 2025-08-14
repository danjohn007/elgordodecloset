<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Restaurant;
use App\Models\Review;
use App\Services\AvailabilityService;

class RestaurantController extends Controller
{
    private $restaurantModel;
    private $reviewModel;
    private $availabilityService;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
        $this->reviewModel = new Review();
        $this->availabilityService = new AvailabilityService();
    }

    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 12;
        
        $filters = [
            'name' => $_GET['name'] ?? '',
            'cuisine_type' => $_GET['cuisine_type'] ?? '',
            'price_range' => $_GET['price_range'] ?? '',
            'min_rating' => $_GET['min_rating'] ?? '',
            'location' => $_GET['location'] ?? '',
            'sort' => $_GET['sort'] ?? 'rating',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        $restaurants = $this->restaurantModel->search($filters, $page, $perPage);
        $cuisineTypes = $this->restaurantModel->getCuisineTypes();

        $data = [
            'restaurants' => $restaurants,
            'cuisine_types' => $cuisineTypes,
            'filters' => $filters,
            'page_title' => 'Restaurantes'
        ];

        $this->view('restaurants/index', $data);
    }

    public function show($id)
    {
        $restaurant = $this->restaurantModel->getWithOwner($id);
        
        if (!$restaurant || $restaurant['status'] !== 'active') {
            $_SESSION['error'] = 'Restaurante no encontrado';
            $this->redirect('/restaurants');
            return;
        }

        // Get restaurant reviews
        $reviews = $this->reviewModel->getByRestaurant($id, 1, 10);
        $ratingBreakdown = $this->reviewModel->getRestaurantRatingBreakdown($id);

        // Get available time slots for today and tomorrow
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $todaySlots = $this->availabilityService->getAvailableTimeSlots($id, $today, 2);
        $tomorrowSlots = $this->availabilityService->getAvailableTimeSlots($id, $tomorrow, 2);

        $data = [
            'restaurant' => $restaurant,
            'reviews' => $reviews,
            'rating_breakdown' => $ratingBreakdown,
            'today_slots' => $todaySlots,
            'tomorrow_slots' => $tomorrowSlots,
            'page_title' => $restaurant['name']
        ];

        $this->view('restaurants/show', $data);
    }

    public function search()
    {
        $filters = [
            'name' => $this->input('name'),
            'location' => $this->input('location'),
            'cuisine_type' => $this->input('cuisine_type'),
            'price_range' => $this->input('price_range'),
            'min_rating' => $this->input('min_rating'),
            'date' => $this->input('date'),
            'party_size' => $this->input('party_size'),
            'sort' => $this->input('sort', 'rating'),
            'order' => $this->input('order', 'DESC')
        ];

        // Filter out empty values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $queryString = http_build_query($filters);
        $this->redirect('/restaurants?' . $queryString);
    }

    public function availability($id)
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $partySize = (int) ($_GET['party_size'] ?? 2);
        
        $restaurant = $this->restaurantModel->find($id);
        
        if (!$restaurant || $restaurant['status'] !== 'active') {
            $this->json(['error' => 'Restaurante no encontrado'], 404);
            return;
        }

        $availableSlots = $this->availabilityService->getAvailableTimeSlots($id, $date, $partySize);
        $occupancyRate = $this->availabilityService->getRestaurantOccupancy($id, $date);

        $data = [
            'restaurant_id' => $id,
            'date' => $date,
            'party_size' => $partySize,
            'available_slots' => $availableSlots,
            'occupancy_rate' => $occupancyRate,
            'has_availability' => count($availableSlots) > 0
        ];

        $this->json($data);
    }
}