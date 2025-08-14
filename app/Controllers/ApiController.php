<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Restaurant;
use App\Services\AvailabilityService;

class ApiController extends Controller
{
    private $restaurantModel;
    private $availabilityService;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
        $this->availabilityService = new AvailabilityService();
    }

    public function searchRestaurants()
    {
        header('Content-Type: application/json');
        
        $filters = [
            'name' => $_GET['name'] ?? '',
            'location' => $_GET['location'] ?? '',
            'cuisine_type' => $_GET['cuisine_type'] ?? '',
            'price_range' => $_GET['price_range'] ?? '',
            'min_rating' => $_GET['min_rating'] ?? ''
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = min((int) ($_GET['per_page'] ?? 10), 50); // Max 50 per page

        try {
            $restaurants = $this->restaurantModel->search($filters, $page, $perPage);
            
            $this->json([
                'success' => true,
                'data' => $restaurants['data'],
                'pagination' => [
                    'current_page' => $restaurants['current_page'],
                    'per_page' => $restaurants['per_page'],
                    'total' => $restaurants['total'],
                    'total_pages' => $restaurants['total_pages'],
                    'has_next' => $restaurants['has_next'],
                    'has_prev' => $restaurants['has_prev']
                ]
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Error en la búsqueda'
            ], 500);
        }
    }

    public function checkAvailability($id)
    {
        header('Content-Type: application/json');
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $partySize = (int) ($_GET['party_size'] ?? 2);
        
        if (!$date || $partySize < 1) {
            $this->json([
                'success' => false,
                'error' => 'Parámetros inválidos'
            ], 400);
            return;
        }

        try {
            $restaurant = $this->restaurantModel->find($id);
            
            if (!$restaurant || $restaurant['status'] !== 'active') {
                $this->json([
                    'success' => false,
                    'error' => 'Restaurante no encontrado'
                ], 404);
                return;
            }

            $availableSlots = $this->availabilityService->getAvailableTimeSlots($id, $date, $partySize);
            $occupancyRate = $this->availabilityService->getRestaurantOccupancy($id, $date);

            $this->json([
                'success' => true,
                'restaurant_id' => (int) $id,
                'date' => $date,
                'party_size' => $partySize,
                'available_slots' => $availableSlots,
                'occupancy_rate' => $occupancyRate,
                'has_availability' => count($availableSlots) > 0
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Error al verificar disponibilidad'
            ], 500);
        }
    }

    public function createReservation()
    {
        header('Content-Type: application/json');
        
        if (!$this->auth()->check()) {
            $this->json([
                'success' => false,
                'error' => 'Autenticación requerida'
            ], 401);
            return;
        }

        $validation = $this->validate([
            'restaurant_id' => 'required|integer',
            'date' => 'required|date',
            'time' => 'required',
            'party_size' => 'required|integer|between:1,20'
        ]);

        if (!$validation) {
            $this->json([
                'success' => false,
                'error' => 'Datos inválidos',
                'validation_errors' => $this->getValidationErrors()
            ], 400);
            return;
        }

        try {
            $restaurantId = $this->input('restaurant_id');
            $date = $this->input('date');
            $time = $this->input('time');
            $partySize = $this->input('party_size');

            // Validate availability
            if (!$this->availabilityService->hasAvailability($restaurantId, $date, $time, $partySize)) {
                $this->json([
                    'success' => false,
                    'error' => 'No hay disponibilidad para el horario seleccionado'
                ], 409);
                return;
            }

            // Create reservation logic would go here
            // For API, you might want to create a separate ReservationService
            
            $this->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'reservation_id' => 123 // Placeholder
            ]);

        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Error al crear la reserva'
            ], 500);
        }
    }

    private function getValidationErrors()
    {
        // This would typically come from the validation helper
        return [];
    }
}