<?php

namespace App\Models;

use App\Core\Model;

class Table extends Model
{
    protected $table = 'tables';
    protected $fillable = ['restaurant_id', 'table_number', 'capacity', 'zone', 'status'];

    public function getByRestaurant($restaurantId)
    {
        return $this->where(['restaurant_id' => $restaurantId], 'zone, table_number');
    }

    public function getAvailableTables($restaurantId, $date, $time, $endTime, $partySize)
    {
        $sql = "SELECT t.* FROM {$this->table} t
                WHERE t.restaurant_id = ? 
                AND t.capacity >= ?
                AND t.status = 'available'
                AND t.id NOT IN (
                    SELECT DISTINCT r.table_id FROM reservations r
                    WHERE r.restaurant_id = ?
                    AND r.reservation_date = ?
                    AND r.status IN ('confirmed', 'pending')
                    AND r.table_id IS NOT NULL
                    AND ((r.reservation_time <= ? AND r.end_time > ?) 
                         OR (r.reservation_time < ? AND r.end_time >= ?))
                )
                ORDER BY t.capacity ASC, t.zone, t.table_number";
        
        $params = [
            $restaurantId, $partySize, $restaurantId, $date,
            $time, $time, $endTime, $endTime
        ];
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTablesByZone($restaurantId)
    {
        $sql = "SELECT zone, COUNT(*) as table_count, SUM(capacity) as total_capacity
                FROM {$this->table}
                WHERE restaurant_id = ?
                GROUP BY zone
                ORDER BY zone";
        
        return $this->db->fetchAll($sql, [$restaurantId]);
    }

    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    public function getTotalCapacity($restaurantId)
    {
        $sql = "SELECT SUM(capacity) as total_capacity FROM {$this->table} 
                WHERE restaurant_id = ? AND status = 'available'";
        
        $result = $this->db->fetch($sql, [$restaurantId]);
        return $result['total_capacity'] ?? 0;
    }

    public function getOccupancyRate($restaurantId, $date)
    {
        // Get total capacity
        $totalCapacity = $this->getTotalCapacity($restaurantId);
        
        if ($totalCapacity == 0) {
            return 0;
        }

        // Get reserved capacity for the date
        $sql = "SELECT SUM(r.party_size) as reserved_capacity
                FROM reservations r
                JOIN {$this->table} t ON r.table_id = t.id
                WHERE r.restaurant_id = ?
                AND r.reservation_date = ?
                AND r.status IN ('confirmed', 'pending')";
        
        $result = $this->db->fetch($sql, [$restaurantId, $date]);
        $reservedCapacity = $result['reserved_capacity'] ?? 0;
        
        return round(($reservedCapacity / $totalCapacity) * 100, 2);
    }

    public function getTableStatus($restaurantId, $date = null, $time = null)
    {
        $date = $date ?? date('Y-m-d');
        $time = $time ?? date('H:i:s');
        
        $sql = "SELECT t.*, 
                CASE 
                    WHEN r.id IS NOT NULL THEN 'reserved'
                    ELSE t.status
                END as current_status,
                r.reservation_time,
                r.end_time,
                r.party_size,
                r.customer_name
                FROM {$this->table} t
                LEFT JOIN reservations r ON t.id = r.table_id 
                    AND r.reservation_date = ?
                    AND r.status IN ('confirmed', 'pending')
                    AND r.reservation_time <= ?
                    AND r.end_time > ?
                WHERE t.restaurant_id = ?
                ORDER BY t.zone, t.table_number";
        
        return $this->db->fetchAll($sql, [$date, $time, $time, $restaurantId]);
    }

    public function createBulkTables($restaurantId, $tables)
    {
        $created = 0;
        foreach ($tables as $tableData) {
            $tableData['restaurant_id'] = $restaurantId;
            if ($this->create($tableData)) {
                $created++;
            }
        }
        return $created;
    }

    public function deleteByRestaurant($restaurantId)
    {
        $sql = "DELETE FROM {$this->table} WHERE restaurant_id = ?";
        return $this->db->execute($sql, [$restaurantId]);
    }

    public function getStats($restaurantId)
    {
        $stats = [];
        
        // Total tables
        $stats['total_tables'] = $this->count(['restaurant_id' => $restaurantId]);
        
        // Tables by zone
        $zoneStats = $this->getTablesByZone($restaurantId);
        $stats['by_zone'] = $zoneStats;
        
        // Tables by capacity
        $sql = "SELECT capacity, COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? GROUP BY capacity ORDER BY capacity";
        $capacityStats = $this->db->fetchAll($sql, [$restaurantId]);
        $stats['by_capacity'] = $capacityStats;
        
        // Tables by status
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? GROUP BY status";
        $statusStats = $this->db->fetchAll($sql, [$restaurantId]);
        $stats['by_status'] = [];
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Total capacity
        $stats['total_capacity'] = $this->getTotalCapacity($restaurantId);
        
        // Current occupancy (for today)
        $stats['occupancy_rate_today'] = $this->getOccupancyRate($restaurantId, date('Y-m-d'));
        
        return $stats;
    }
}