<?php

namespace App\Models;

use App\Core\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'user_id', 'restaurant_id', 'table_id', 'shift_id', 'reservation_date',
        'reservation_time', 'end_time', 'party_size', 'status', 'confirmation_code',
        'special_requests', 'customer_name', 'customer_phone', 'customer_email',
        'payment_status', 'payment_amount'
    ];

    public function createReservation($data)
    {
        // Generate confirmation code
        $data['confirmation_code'] = $this->generateConfirmationCode();
        
        // Calculate end time if not provided
        if (!isset($data['end_time'])) {
            $data['end_time'] = $this->calculateEndTime($data['reservation_time'], $data['restaurant_id']);
        }

        return $this->create($data);
    }

    public function getWithDetails($id)
    {
        $sql = "SELECT r.*, rest.name as restaurant_name, rest.address as restaurant_address,
                       rest.phone as restaurant_phone, u.name as user_name, u.email as user_email,
                       t.table_number, t.zone, s.shift_name
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN tables t ON r.table_id = t.id
                LEFT JOIN shifts s ON r.shift_id = s.id
                WHERE r.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    public function getByUser($userId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, rest.name as restaurant_name, rest.address as restaurant_address
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                WHERE r.user_id = ?
                ORDER BY r.reservation_date DESC, r.reservation_time DESC
                LIMIT ? OFFSET ?";
        
        $data = $this->db->fetchAll($sql, [$userId, $perPage, $offset]);
        $total = $this->count(['user_id' => $userId]);
        $totalPages = ceil($total / $perPage);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }

    public function getByRestaurant($restaurantId, $date = null, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                       t.table_number, t.zone, s.shift_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN tables t ON r.table_id = t.id
                LEFT JOIN shifts s ON r.shift_id = s.id
                WHERE r.restaurant_id = ?";
        
        $params = [$restaurantId];
        
        if ($date) {
            $sql .= " AND r.reservation_date = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time ASC
                  LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        
        $conditions = ['restaurant_id' => $restaurantId];
        if ($date) {
            $conditions['reservation_date'] = $date;
        }
        
        $total = $this->count($conditions);
        $totalPages = ceil($total / $perPage);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }

    public function getUpcoming($limit = 10)
    {
        $sql = "SELECT r.*, rest.name as restaurant_name, u.name as user_name
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.reservation_date >= CURDATE() AND r.status IN ('pending', 'confirmed')
                ORDER BY r.reservation_date ASC, r.reservation_time ASC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getTodayReservations($restaurantId = null)
    {
        $sql = "SELECT r.*, rest.name as restaurant_name, u.name as user_name,
                       t.table_number, t.zone
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN tables t ON r.table_id = t.id
                WHERE r.reservation_date = CURDATE()";
        
        $params = [];
        
        if ($restaurantId) {
            $sql .= " AND r.restaurant_id = ?";
            $params[] = $restaurantId;
        }
        
        $sql .= " ORDER BY r.reservation_time ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function confirmReservation($id)
    {
        return $this->update($id, ['status' => 'confirmed']);
    }

    public function cancelReservation($id, $reason = null)
    {
        $data = ['status' => 'cancelled'];
        if ($reason) {
            $data['special_requests'] = ($this->find($id)['special_requests'] ?? '') . "\nCancellation reason: " . $reason;
        }
        return $this->update($id, $data);
    }

    public function markAsCompleted($id)
    {
        return $this->update($id, ['status' => 'completed']);
    }

    public function markAsNoShow($id)
    {
        return $this->update($id, ['status' => 'no_show']);
    }

    public function findByConfirmationCode($code)
    {
        return $this->findWhere('confirmation_code', $code);
    }

    public function getStats($restaurantId = null, $period = 'month')
    {
        $stats = [];
        
        $whereClause = $restaurantId ? "WHERE restaurant_id = {$restaurantId}" : "";
        
        // Period filter
        $periodFilter = "";
        switch ($period) {
            case 'today':
                $periodFilter = "AND reservation_date = CURDATE()";
                break;
            case 'week':
                $periodFilter = "AND reservation_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $periodFilter = "AND reservation_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $periodFilter = "AND reservation_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }

        if ($whereClause) {
            $whereClause .= " " . $periodFilter;
        } else {
            $whereClause = "WHERE " . ltrim($periodFilter, "AND ");
        }

        // Total reservations
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $stats['total_reservations'] = $this->db->fetch($sql)['count'];

        // Reservations by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} {$whereClause} GROUP BY status";
        $statusStats = $this->db->fetchAll($sql);
        $stats['by_status'] = [];
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }

        // Average party size
        $sql = "SELECT AVG(party_size) as avg_party_size FROM {$this->table} {$whereClause}";
        $stats['average_party_size'] = round($this->db->fetch($sql)['avg_party_size'] ?? 0, 1);

        // Revenue (if payment amounts are tracked)
        $sql = "SELECT SUM(payment_amount) as total_revenue FROM {$this->table} {$whereClause} AND payment_status = 'paid'";
        $stats['total_revenue'] = $this->db->fetch($sql)['total_revenue'] ?? 0;

        return $stats;
    }

    private function generateConfirmationCode()
    {
        do {
            $code = 'RES' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while ($this->findByConfirmationCode($code));
        
        return $code;
    }

    private function calculateEndTime($startTime, $restaurantId)
    {
        // Default duration: 2 hours
        $duration = 120; // minutes
        
        // You could fetch this from restaurant settings or shift settings
        $endTimestamp = strtotime($startTime) + ($duration * 60);
        return date('H:i:s', $endTimestamp);
    }

    public function getConflictingReservations($restaurantId, $date, $startTime, $endTime, $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND reservation_date = ? 
                AND status IN ('confirmed', 'pending')
                AND ((reservation_time <= ? AND end_time > ?) 
                     OR (reservation_time < ? AND end_time >= ?))";
        
        $params = [$restaurantId, $date, $startTime, $startTime, $endTime, $endTime];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
}