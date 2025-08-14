<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\AvailabilityService;
use App\Services\NotificationService;

class ReservationController extends Controller
{
    private $reservationModel;
    private $restaurantModel;
    private $availabilityService;
    private $notificationService;

    public function __construct()
    {
        $this->reservationModel = new Reservation();
        $this->restaurantModel = new Restaurant();
        $this->availabilityService = new AvailabilityService();
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        $this->auth()->requireAuth();
        
        $page = (int) ($_GET['page'] ?? 1);
        $reservations = $this->reservationModel->getByUser($this->auth()->id(), $page, 10);

        $data = [
            'reservations' => $reservations,
            'page_title' => 'Mis Reservas'
        ];

        $this->view('reservations/index', $data);
    }

    public function create()
    {
        $this->auth()->requireAuth();
        
        $restaurantId = $_GET['restaurant_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        $partySize = $_GET['party_size'] ?? 2;
        $time = $_GET['time'] ?? null;

        if (!$restaurantId) {
            $_SESSION['error'] = 'Debe seleccionar un restaurante';
            $this->redirect('/restaurants');
            return;
        }

        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant || $restaurant['status'] !== 'active') {
            $_SESSION['error'] = 'Restaurante no encontrado';
            $this->redirect('/restaurants');
            return;
        }

        // Get available time slots
        $availableSlots = $this->availabilityService->getAvailableTimeSlots($restaurantId, $date, $partySize);

        $data = [
            'restaurant' => $restaurant,
            'date' => $date,
            'party_size' => $partySize,
            'selected_time' => $time,
            'available_slots' => $availableSlots,
            'page_title' => 'Nueva Reserva - ' . $restaurant['name']
        ];

        $this->view('reservations/create', $data);
    }

    public function store()
    {
        $this->auth()->requireAuth();
        
        $validation = $this->validate([
            'restaurant_id' => 'required|integer',
            'date' => 'required|date',
            'time' => 'required',
            'party_size' => 'required|integer|between:1,20',
            'special_requests' => 'max:500'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        $restaurantId = $this->input('restaurant_id');
        $date = $this->input('date');
        $time = $this->input('time');
        $partySize = $this->input('party_size');

        // Validate that the date is not in the past
        if (strtotime($date) < strtotime('today')) {
            $_SESSION['error'] = 'No se pueden hacer reservas para fechas pasadas';
            $this->back();
            return;
        }

        // Check availability
        if (!$this->availabilityService->hasAvailability($restaurantId, $date, $time, $partySize)) {
            $_SESSION['error'] = 'No hay disponibilidad para el horario seleccionado';
            $this->back();
            return;
        }

        $restaurant = $this->restaurantModel->find($restaurantId);
        $user = $this->auth()->user();

        // Find best table
        $endTime = date('H:i:s', strtotime($time) + (2 * 3600)); // 2 hours default
        $table = $this->availabilityService->findBestTable($restaurantId, $date, $time, $endTime, $partySize);

        $reservationData = [
            'user_id' => $user['id'],
            'restaurant_id' => $restaurantId,
            'table_id' => $table ? $table['id'] : null,
            'reservation_date' => $date,
            'reservation_time' => $time,
            'end_time' => $endTime,
            'party_size' => $partySize,
            'status' => $restaurant['auto_confirm'] ? 'confirmed' : 'pending',
            'special_requests' => $this->input('special_requests'),
            'customer_name' => $user['name'],
            'customer_phone' => $user['phone'],
            'customer_email' => $user['email']
        ];

        $reservationId = $this->reservationModel->createReservation($reservationData);

        if ($reservationId) {
            // Send notifications
            $this->notificationService->sendReservationConfirmation($reservationId);

            $_SESSION['success'] = $restaurant['auto_confirm'] 
                ? 'Reserva confirmada exitosamente' 
                : 'Reserva creada. Esperando confirmación del restaurante';
            
            $this->redirect('/reservations/' . $reservationId);
        } else {
            $_SESSION['error'] = 'Error al crear la reserva';
            $this->back();
        }
    }

    public function show($id)
    {
        $this->auth()->requireAuth();
        
        $reservation = $this->reservationModel->getWithDetails($id);
        
        if (!$reservation || $reservation['user_id'] != $this->auth()->id()) {
            $_SESSION['error'] = 'Reserva no encontrada';
            $this->redirect('/reservations');
            return;
        }

        $data = [
            'reservation' => $reservation,
            'page_title' => 'Reserva ' . $reservation['confirmation_code']
        ];

        $this->view('reservations/show', $data);
    }

    public function edit($id)
    {
        $this->auth()->requireAuth();
        
        $reservation = $this->reservationModel->getWithDetails($id);
        
        if (!$reservation || $reservation['user_id'] != $this->auth()->id()) {
            $_SESSION['error'] = 'Reserva no encontrada';
            $this->redirect('/reservations');
            return;
        }

        if (!in_array($reservation['status'], ['pending', 'confirmed'])) {
            $_SESSION['error'] = 'No se puede modificar esta reserva';
            $this->redirect('/reservations/' . $id);
            return;
        }

        // Check if reservation is not too close (at least 2 hours before)
        $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
        if ($reservationDateTime - time() < 7200) { // 2 hours
            $_SESSION['error'] = 'No se puede modificar la reserva con menos de 2 horas de anticipación';
            $this->redirect('/reservations/' . $id);
            return;
        }

        // Get available time slots for the date
        $availableSlots = $this->availabilityService->getAvailableTimeSlots(
            $reservation['restaurant_id'], 
            $reservation['reservation_date'], 
            $reservation['party_size']
        );

        $data = [
            'reservation' => $reservation,
            'available_slots' => $availableSlots,
            'page_title' => 'Modificar Reserva'
        ];

        $this->view('reservations/edit', $data);
    }

    public function update($id)
    {
        $this->auth()->requireAuth();
        
        $reservation = $this->reservationModel->find($id);
        
        if (!$reservation || $reservation['user_id'] != $this->auth()->id()) {
            $_SESSION['error'] = 'Reserva no encontrada';
            $this->redirect('/reservations');
            return;
        }

        $validation = $this->validate([
            'date' => 'required|date',
            'time' => 'required',
            'party_size' => 'required|integer|between:1,20',
            'special_requests' => 'max:500'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        $newDate = $this->input('date');
        $newTime = $this->input('time');
        $newPartySize = $this->input('party_size');

        // Check if new time has availability
        if (!$this->availabilityService->canModifyReservation($id, $newDate, $newTime, $newPartySize)) {
            $_SESSION['error'] = 'No hay disponibilidad para el nuevo horario seleccionado';
            $this->back();
            return;
        }

        // Find new table
        $endTime = date('H:i:s', strtotime($newTime) + (2 * 3600));
        $table = $this->availabilityService->findBestTable(
            $reservation['restaurant_id'], 
            $newDate, 
            $newTime, 
            $endTime, 
            $newPartySize
        );

        $updateData = [
            'reservation_date' => $newDate,
            'reservation_time' => $newTime,
            'end_time' => $endTime,
            'party_size' => $newPartySize,
            'table_id' => $table ? $table['id'] : null,
            'special_requests' => $this->input('special_requests'),
            'status' => 'pending' // Reset to pending after modification
        ];

        if ($this->reservationModel->update($id, $updateData)) {
            $_SESSION['success'] = 'Reserva modificada exitosamente';
            $this->redirect('/reservations/' . $id);
        } else {
            $_SESSION['error'] = 'Error al modificar la reserva';
            $this->back();
        }
    }

    public function cancel($id)
    {
        $this->auth()->requireAuth();
        
        $reservation = $this->reservationModel->find($id);
        
        if (!$reservation || $reservation['user_id'] != $this->auth()->id()) {
            $_SESSION['error'] = 'Reserva no encontrada';
            $this->redirect('/reservations');
            return;
        }

        if (!in_array($reservation['status'], ['pending', 'confirmed'])) {
            $_SESSION['error'] = 'Esta reserva no se puede cancelar';
            $this->redirect('/reservations/' . $id);
            return;
        }

        $reason = $this->input('reason', 'Cancelado por el cliente');
        
        if ($this->reservationModel->cancelReservation($id, $reason)) {
            // Send cancellation notification
            $this->notificationService->sendReservationCancellation($id, $reason);
            
            $_SESSION['success'] = 'Reserva cancelada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al cancelar la reserva';
        }

        $this->redirect('/reservations');
    }
}