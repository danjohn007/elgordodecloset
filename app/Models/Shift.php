<?php

namespace App\Models;

use App\Core\Model;

class Shift extends Model
{
    protected $table = 'shifts';
    protected $fillable = ['restaurant_id', 'shift_name', 'start_time', 'end_time', 'max_duration', 'days_of_week', 'is_active'];

    public function getByRestaurant($restaurantId, $activeOnly = true)
    {
        $conditions = ['restaurant_id' => $restaurantId];
        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }
        
        return $this->where($conditions, 'start_time');
    }

    public function getAvailableShifts($restaurantId, $date)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND is_active = 1
                AND FIND_IN_SET(?, days_of_week) > 0
                ORDER BY start_time";
        
        return $this->db->fetchAll($sql, [$restaurantId, $dayOfWeek]);
    }

    public function isTimeInShift($shiftId, $time)
    {
        $shift = $this->find($shiftId);
        if (!$shift) {
            return false;
        }

        $requestTime = strtotime($time);
        $startTime = strtotime($shift['start_time']);
        $endTime = strtotime($shift['end_time']);

        return $requestTime >= $startTime && $requestTime <= $endTime;
    }

    public function getShiftForTime($restaurantId, $date, $time)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND is_active = 1
                AND FIND_IN_SET(?, days_of_week) > 0
                AND start_time <= ?
                AND end_time >= ?
                ORDER BY start_time
                LIMIT 1";
        
        return $this->db->fetch($sql, [$restaurantId, $dayOfWeek, $time, $time]);
    }

    public function createDefaultShifts($restaurantId)
    {
        $defaultShifts = [
            [
                'restaurant_id' => $restaurantId,
                'shift_name' => 'Almuerzo',
                'start_time' => '12:00:00',
                'end_time' => '16:00:00',
                'max_duration' => 120,
                'days_of_week' => 'monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'is_active' => 1
            ],
            [
                'restaurant_id' => $restaurantId,
                'shift_name' => 'Cena',
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'max_duration' => 150,
                'days_of_week' => 'monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'is_active' => 1
            ]
        ];

        $created = 0;
        foreach ($defaultShifts as $shiftData) {
            if ($this->create($shiftData)) {
                $created++;
            }
        }
        
        return $created;
    }

    public function updateShift($id, $data)
    {
        // Ensure days_of_week is properly formatted
        if (isset($data['days_of_week']) && is_array($data['days_of_week'])) {
            $data['days_of_week'] = implode(',', $data['days_of_week']);
        }
        
        return $this->update($id, $data);
    }

    public function toggleActive($id)
    {
        $shift = $this->find($id);
        if ($shift) {
            $newStatus = $shift['is_active'] ? 0 : 1;
            return $this->update($id, ['is_active' => $newStatus]);
        }
        return false;
    }

    public function deleteByRestaurant($restaurantId)
    {
        $sql = "DELETE FROM {$this->table} WHERE restaurant_id = ?";
        return $this->db->execute($sql, [$restaurantId]);
    }

    public function getTimeSlots($restaurantId, $date, $interval = 30)
    {
        $shifts = $this->getAvailableShifts($restaurantId, $date);
        $timeSlots = [];
        
        foreach ($shifts as $shift) {
            $startTime = strtotime($shift['start_time']);
            $endTime = strtotime($shift['end_time']);
            $maxDuration = $shift['max_duration'] * 60; // Convert to seconds
            
            // Calculate slots within this shift
            $currentTime = $startTime;
            $lastPossibleStart = $endTime - $maxDuration;
            
            while ($currentTime <= $lastPossibleStart) {
                $timeSlots[] = [
                    'shift_id' => $shift['id'],
                    'shift_name' => $shift['shift_name'],
                    'time' => date('H:i:s', $currentTime),
                    'time_display' => date('H:i', $currentTime),
                    'end_time' => date('H:i:s', $currentTime + $maxDuration)
                ];
                
                $currentTime += ($interval * 60); // Add interval in seconds
            }
        }
        
        return $timeSlots;
    }

    public function getShiftUtilization($restaurantId, $date)
    {
        $shifts = $this->getByRestaurant($restaurantId);
        $utilization = [];
        
        foreach ($shifts as $shift) {
            // Count reservations in this shift
            $sql = "SELECT COUNT(*) as reservation_count, SUM(party_size) as total_guests
                    FROM reservations 
                    WHERE restaurant_id = ?
                    AND shift_id = ?
                    AND reservation_date = ?
                    AND status IN ('confirmed', 'pending')";
            
            $stats = $this->db->fetch($sql, [$restaurantId, $shift['id'], $date]);
            
            $utilization[] = [
                'shift' => $shift,
                'reservation_count' => $stats['reservation_count'] ?? 0,
                'total_guests' => $stats['total_guests'] ?? 0
            ];
        }
        
        return $utilization;
    }

    public function getStats($restaurantId)
    {
        $stats = [];
        
        // Total shifts
        $stats['total_shifts'] = $this->count(['restaurant_id' => $restaurantId]);
        
        // Active shifts
        $stats['active_shifts'] = $this->count([
            'restaurant_id' => $restaurantId,
            'is_active' => 1
        ]);
        
        // Shifts by day coverage
        $sql = "SELECT days_of_week, COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = 1 
                GROUP BY days_of_week";
        $dayStats = $this->db->fetchAll($sql, [$restaurantId]);
        $stats['day_coverage'] = $dayStats;
        
        // Average shift duration
        $sql = "SELECT AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))/3600) as avg_duration_hours
                FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = 1";
        $result = $this->db->fetch($sql, [$restaurantId]);
        $stats['average_duration_hours'] = round($result['avg_duration_hours'] ?? 0, 1);
        
        return $stats;
    }

    public function getDaysOfWeekArray($daysString)
    {
        return explode(',', $daysString);
    }

    public function getDaysOfWeekDisplay($daysString)
    {
        $days = $this->getDaysOfWeekArray($daysString);
        $dayNames = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];
        
        $displayDays = [];
        foreach ($days as $day) {
            if (isset($dayNames[$day])) {
                $displayDays[] = $dayNames[$day];
            }
        }
        
        return implode(', ', $displayDays);
    }
}