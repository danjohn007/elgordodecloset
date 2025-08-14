<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;

class NotificationService
{
    private $mailerService;
    private $reservationModel;
    private $restaurantModel;

    public function __construct()
    {
        $this->mailerService = new MailerService();
        $this->reservationModel = new Reservation();
        $this->restaurantModel = new Restaurant();
    }

    public function sendReservationConfirmation($reservationId)
    {
        $reservation = $this->reservationModel->getWithDetails($reservationId);
        if (!$reservation) {
            return false;
        }

        $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
        
        // Send confirmation to customer
        $customerSuccess = $this->mailerService->sendReservationConfirmation($reservation, $restaurant);
        
        // Notify restaurant
        $restaurantSubject = "Nueva Reserva - {$reservation['confirmation_code']}";
        $restaurantMessage = $this->buildNewReservationMessage($reservation);
        $restaurantSuccess = $this->mailerService->sendRestaurantNotification(
            $restaurant['email'],
            $restaurant['name'],
            $restaurantSubject,
            $restaurantMessage
        );

        return $customerSuccess && $restaurantSuccess;
    }

    public function sendReservationCancellation($reservationId, $reason = null)
    {
        $reservation = $this->reservationModel->getWithDetails($reservationId);
        if (!$reservation) {
            return false;
        }

        $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
        
        // Send cancellation to customer
        $customerSuccess = $this->mailerService->sendReservationCancellation($reservation, $restaurant, $reason);
        
        // Notify restaurant
        $restaurantSubject = "Reserva Cancelada - {$reservation['confirmation_code']}";
        $restaurantMessage = $this->buildCancellationMessage($reservation, $reason);
        $restaurantSuccess = $this->mailerService->sendRestaurantNotification(
            $restaurant['email'],
            $restaurant['name'],
            $restaurantSubject,
            $restaurantMessage
        );

        return $customerSuccess && $restaurantSuccess;
    }

    public function sendDailyReminders()
    {
        // Get reservations for tomorrow
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $reservations = $this->reservationModel->where([
            'reservation_date' => $tomorrow,
            'status' => 'confirmed'
        ]);

        $sent = 0;
        foreach ($reservations as $reservation) {
            $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
            if ($restaurant && $this->mailerService->sendReservationReminder($reservation, $restaurant)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendDailyRestaurantSummary($restaurantId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return false;
        }

        $today = date('Y-m-d');
        $todayReservations = $this->reservationModel->getTodayReservations($restaurantId);
        
        $subject = "Resumen del Día - {$today}";
        $message = $this->buildDailySummaryMessage($restaurant, $todayReservations);
        
        return $this->mailerService->sendRestaurantNotification(
            $restaurant['email'],
            $restaurant['name'],
            $subject,
            $message
        );
    }

    public function sendWeeklyReport($restaurantId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return false;
        }

        // Get week's statistics
        $stats = $this->getWeeklyStats($restaurantId);
        
        $subject = "Reporte Semanal - Semana del " . date('d/m/Y', strtotime('-7 days'));
        $message = $this->buildWeeklyReportMessage($restaurant, $stats);
        
        return $this->mailerService->sendRestaurantNotification(
            $restaurant['email'],
            $restaurant['name'],
            $subject,
            $message
        );
    }

    public function notifyUpcomingReservations($hours = 2)
    {
        $targetTime = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
        $targetDate = date('Y-m-d', strtotime($targetTime));
        $targetHour = date('H:i:s', strtotime($targetTime));
        
        $sql = "SELECT r.*, rest.name as restaurant_name, rest.phone as restaurant_phone
                FROM reservations r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                WHERE r.reservation_date = ?
                AND r.reservation_time BETWEEN ? AND DATE_ADD(?, INTERVAL 30 MINUTE)
                AND r.status = 'confirmed'";
        
        $db = \App\Core\DB::getInstance();
        $upcomingReservations = $db->fetchAll($sql, [$targetDate, $targetHour, $targetHour]);
        
        $notified = 0;
        foreach ($upcomingReservations as $reservation) {
            // You could send SMS notifications here
            // For now, we'll just log or send internal notifications
            $this->logUpcomingReservation($reservation);
            $notified++;
        }
        
        return $notified;
    }

    public function sendPasswordResetNotification($userEmail, $userName, $resetToken)
    {
        return $this->mailerService->sendPasswordReset($userEmail, $userName, $resetToken);
    }

    public function sendWelcomeNotification($userEmail, $userName, $userRole)
    {
        return $this->mailerService->sendWelcomeEmail($userEmail, $userName, $userRole);
    }

    public function sendReviewResponseNotification($reviewId)
    {
        // Implementation for notifying users when restaurant responds to their review
        // This would fetch the review and user details and send appropriate notification
        return true;
    }

    private function buildNewReservationMessage($reservation)
    {
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = date('H:i', strtotime($reservation['reservation_time']));
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Nueva Reserva Recibida</h2>
            <p><strong>Código:</strong> {$reservation['confirmation_code']}</p>
            <p><strong>Cliente:</strong> {$reservation['customer_name']}</p>
            <p><strong>Teléfono:</strong> {$reservation['customer_phone']}</p>
            <p><strong>Email:</strong> {$reservation['customer_email']}</p>
            <p><strong>Fecha:</strong> {$date}</p>
            <p><strong>Hora:</strong> {$time}</p>
            <p><strong>Personas:</strong> {$reservation['party_size']}</p>
            " . ($reservation['special_requests'] ? "<p><strong>Solicitudes Especiales:</strong> {$reservation['special_requests']}</p>" : "") . "
            <p><strong>Estado:</strong> {$reservation['status']}</p>
        </body>
        </html>";
    }

    private function buildCancellationMessage($reservation, $reason)
    {
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = date('H:i', strtotime($reservation['reservation_time']));
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Reserva Cancelada</h2>
            <p><strong>Código:</strong> {$reservation['confirmation_code']}</p>
            <p><strong>Cliente:</strong> {$reservation['customer_name']}</p>
            <p><strong>Fecha:</strong> {$date}</p>
            <p><strong>Hora:</strong> {$time}</p>
            <p><strong>Personas:</strong> {$reservation['party_size']}</p>
            " . ($reason ? "<p><strong>Motivo:</strong> {$reason}</p>" : "") . "
        </body>
        </html>";
    }

    private function buildDailySummaryMessage($restaurant, $reservations)
    {
        $today = date('d/m/Y');
        $totalReservations = count($reservations);
        $totalGuests = array_sum(array_column($reservations, 'party_size'));
        
        $reservationsList = "";
        foreach ($reservations as $reservation) {
            $time = date('H:i', strtotime($reservation['reservation_time']));
            $reservationsList .= "<li>{$time} - {$reservation['customer_name']} ({$reservation['party_size']} personas) - {$reservation['status']}</li>";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Resumen del Día - {$today}</h2>
            <h3>Estadísticas</h3>
            <p><strong>Total de Reservas:</strong> {$totalReservations}</p>
            <p><strong>Total de Huéspedes:</strong> {$totalGuests}</p>
            
            <h3>Reservas de Hoy</h3>
            <ul>
                {$reservationsList}
            </ul>
        </body>
        </html>";
    }

    private function buildWeeklyReportMessage($restaurant, $stats)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Reporte Semanal</h2>
            <p><strong>Total de Reservas:</strong> {$stats['total_reservations']}</p>
            <p><strong>Total de Huéspedes:</strong> {$stats['total_guests']}</p>
            <p><strong>Reservas Confirmadas:</strong> {$stats['confirmed_reservations']}</p>
            <p><strong>Reservas Canceladas:</strong> {$stats['cancelled_reservations']}</p>
            <p><strong>Tasa de Ocupación Promedio:</strong> {$stats['avg_occupancy']}%</p>
            <p><strong>Ingreso Total:</strong> $" . number_format($stats['total_revenue'], 2) . "</p>
        </body>
        </html>";
    }

    private function getWeeklyStats($restaurantId)
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_reservations,
                    SUM(party_size) as total_guests,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_reservations,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_reservations,
                    SUM(COALESCE(payment_amount, 0)) as total_revenue
                FROM reservations 
                WHERE restaurant_id = ? 
                AND reservation_date BETWEEN ? AND ?";
        
        $db = \App\Core\DB::getInstance();
        $stats = $db->fetch($sql, [$restaurantId, $startDate, $endDate]);
        
        // Calculate average occupancy
        $tableModel = new \App\Models\Table();
        $occupancySum = 0;
        $days = 0;
        
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $occupancy = $tableModel->getOccupancyRate($restaurantId, $date);
            $occupancySum += $occupancy;
            $days++;
        }
        
        $stats['avg_occupancy'] = $days > 0 ? round($occupancySum / $days, 1) : 0;
        
        return $stats;
    }

    private function logUpcomingReservation($reservation)
    {
        $logFile = __DIR__ . '/../../storage/logs/upcoming_reservations.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " - Upcoming: {$reservation['confirmation_code']} - {$reservation['customer_name']} at {$reservation['reservation_time']}" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function processNotificationQueue()
    {
        // This method could be called by a cron job to process queued notifications
        // For now, it's a placeholder for future queue implementation
        return true;
    }
}