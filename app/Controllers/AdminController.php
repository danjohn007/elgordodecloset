<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\Shift;
use App\Models\Review;

class AdminController extends Controller
{
    private $restaurantModel;
    private $reservationModel;
    private $tableModel;
    private $shiftModel;
    private $reviewModel;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
        $this->reservationModel = new Reservation();
        $this->tableModel = new Table();
        $this->shiftModel = new Shift();
        $this->reviewModel = new Review();
    }

    public function dashboard()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $userId = $this->auth()->id();
        $isSuperAdmin = $this->auth()->isSuperAdmin();
        
        // Get user's restaurants or all if superadmin
        if ($isSuperAdmin) {
            $restaurants = $this->restaurantModel->getAllWithOwner(1, 10);
        } else {
            $restaurants = $this->restaurantModel->getByOwner($userId);
        }

        // Get stats for the first restaurant if admin, or system stats if superadmin
        $stats = [];
        if (!empty($restaurants['data'] ?? $restaurants)) {
            $restaurantId = $isSuperAdmin ? null : ($restaurants[0]['id'] ?? null);
            
            $stats = [
                'reservations' => $this->reservationModel->getStats($restaurantId, 'month'),
                'reviews' => $this->reviewModel->getStats($restaurantId),
                'restaurants' => $this->restaurantModel->getStats($restaurantId)
            ];

            if ($restaurantId) {
                $stats['tables'] = $this->tableModel->getStats($restaurantId);
                $stats['shifts'] = $this->shiftModel->getStats($restaurantId);
            }
        }

        // Get recent reservations
        $recentReservations = [];
        if (!empty($restaurants['data'] ?? $restaurants)) {
            $restaurantId = $isSuperAdmin ? null : ($restaurants[0]['id'] ?? null);
            if ($restaurantId) {
                $recentReservations = $this->reservationModel->getByRestaurant($restaurantId, null, 1, 5);
            } else {
                $recentReservations = $this->reservationModel->getUpcoming(10);
            }
        }

        $data = [
            'restaurants' => $restaurants,
            'stats' => $stats,
            'recent_reservations' => $recentReservations,
            'page_title' => 'Panel de Administración'
        ];

        $this->view('admin/dashboard', $data);
    }

    public function restaurant()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $userId = $this->auth()->id();
        $restaurants = $this->restaurantModel->getByOwner($userId);
        
        $data = [
            'restaurants' => $restaurants,
            'page_title' => 'Mi Restaurante'
        ];

        $this->view('admin/restaurant', $data);
    }

    public function updateRestaurant()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        // Implementation would go here
        $_SESSION['success'] = 'Restaurante actualizado exitosamente';
        $this->redirect('/admin/restaurant');
    }

    public function reservations()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $userId = $this->auth()->id();
        $restaurants = $this->restaurantModel->getByOwner($userId);
        
        if (empty($restaurants)) {
            $_SESSION['error'] = 'No tienes restaurantes registrados';
            $this->redirect('/admin');
            return;
        }

        $restaurantId = $_GET['restaurant_id'] ?? $restaurants[0]['id'];
        $date = $_GET['date'] ?? null;
        $page = (int) ($_GET['page'] ?? 1);

        $reservations = $this->reservationModel->getByRestaurant($restaurantId, $date, $page, 20);

        $data = [
            'restaurants' => $restaurants,
            'current_restaurant_id' => $restaurantId,
            'reservations' => $reservations,
            'selected_date' => $date,
            'page_title' => 'Gestión de Reservas'
        ];

        $this->view('admin/reservations', $data);
    }

    public function confirmReservation($id)
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        if ($this->reservationModel->confirmReservation($id)) {
            $_SESSION['success'] = 'Reserva confirmada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al confirmar la reserva';
        }

        $this->back();
    }

    public function cancelReservation($id)
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $reason = $this->input('reason', 'Cancelado por el restaurante');
        
        if ($this->reservationModel->cancelReservation($id, $reason)) {
            $_SESSION['success'] = 'Reserva cancelada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al cancelar la reserva';
        }

        $this->back();
    }

    public function tables()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $data = [
            'page_title' => 'Gestión de Mesas'
        ];

        $this->view('admin/tables', $data);
    }

    public function storeTables()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $_SESSION['success'] = 'Mesas actualizadas exitosamente';
        $this->redirect('/admin/tables');
    }

    public function shifts()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $data = [
            'page_title' => 'Gestión de Turnos'
        ];

        $this->view('admin/shifts', $data);
    }

    public function storeShifts()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $_SESSION['success'] = 'Turnos actualizados exitosamente';
        $this->redirect('/admin/shifts');
    }

    public function reviews()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $data = [
            'page_title' => 'Gestión de Reseñas'
        ];

        $this->view('admin/reviews', $data);
    }

    public function respondToReview($id)
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $response = $this->input('response');
        
        if ($this->reviewModel->addResponse($id, $response)) {
            $_SESSION['success'] = 'Respuesta añadida exitosamente';
        } else {
            $_SESSION['error'] = 'Error al añadir la respuesta';
        }

        $this->back();
    }

    public function reports()
    {
        $this->auth()->requireRole(['admin_restaurante', 'superadmin']);
        
        $data = [
            'page_title' => 'Reportes y Estadísticas'
        ];

        $this->view('admin/reports', $data);
    }
}