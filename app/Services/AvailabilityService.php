<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Shift;
use App\Models\Reservation;

class AvailabilityService
{
    private $restaurantModel;
    private $tableModel;
    private $shiftModel;
    private $reservationModel;

    public function __construct()
    {
        $this->restaurantModel = new Restaurant();
        $this->tableModel = new Table();
        $this->shiftModel = new Shift();
        $this->reservationModel = new Reservation();
    }

    public function hasAvailability($restaurantId, $date, $time, $partySize, $duration = 120)
    {
        // Check if restaurant exists and is active
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant || $restaurant['status'] !== 'active') {
            return false;
        }

        // Check if restaurant is open at requested time
        if (!$this->isRestaurantOpen($restaurant, $time)) {
            return false;
        }

        // Check if there's an appropriate shift
        $shift = $this->shiftModel->getShiftForTime($restaurantId, $date, $time);
        if (!$shift) {
            return false;
        }

        // Calculate end time
        $endTime = $this->calculateEndTime($time, $duration);

        // Check if there are available tables
        $availableTables = $this->tableModel->getAvailableTables(
            $restaurantId, $date, $time, $endTime, $partySize
        );

        return count($availableTables) > 0;
    }

    public function getAvailableTimeSlots($restaurantId, $date, $partySize, $interval = 30)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant || $restaurant['status'] !== 'active') {
            return [];
        }

        $timeSlots = $this->shiftModel->getTimeSlots($restaurantId, $date, $interval);
        $availableSlots = [];

        foreach ($timeSlots as $slot) {
            if ($this->hasAvailability($restaurantId, $date, $slot['time'], $partySize)) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }

    public function findBestTable($restaurantId, $date, $time, $endTime, $partySize)
    {
        $availableTables = $this->tableModel->getAvailableTables(
            $restaurantId, $date, $time, $endTime, $partySize
        );

        if (empty($availableTables)) {
            return null;
        }

        // Sort by capacity (prefer smallest table that fits)
        usort($availableTables, function($a, $b) {
            return $a['capacity'] - $b['capacity'];
        });

        return $availableTables[0];
    }

    public function checkReservationConflicts($restaurantId, $date, $startTime, $endTime, $excludeReservationId = null)
    {
        return $this->reservationModel->getConflictingReservations(
            $restaurantId, $date, $startTime, $endTime, $excludeReservationId
        );
    }

    public function getRestaurantOccupancy($restaurantId, $date)
    {
        return $this->tableModel->getOccupancyRate($restaurantId, $date);
    }

    public function getAvailabilityCalendar($restaurantId, $month, $year, $partySize = 2)
    {
        $calendar = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            
            // Skip past dates
            if (strtotime($date) < strtotime(date('Y-m-d'))) {
                $calendar[$day] = [
                    'date' => $date,
                    'available' => false,
                    'slots' => [],
                    'occupancy' => 0
                ];
                continue;
            }

            $availableSlots = $this->getAvailableTimeSlots($restaurantId, $date, $partySize);
            $occupancy = $this->getRestaurantOccupancy($restaurantId, $date);

            $calendar[$day] = [
                'date' => $date,
                'available' => count($availableSlots) > 0,
                'slots' => $availableSlots,
                'slot_count' => count($availableSlots),
                'occupancy' => $occupancy
            ];
        }

        return $calendar;
    }

    public function canModifyReservation($reservationId, $newDate, $newTime, $newPartySize)
    {
        $reservation = $this->reservationModel->find($reservationId);
        if (!$reservation) {
            return false;
        }

        // Check if new time has availability
        return $this->hasAvailability(
            $reservation['restaurant_id'],
            $newDate,
            $newTime,
            $newPartySize
        );
    }

    public function getAlternativeTimeSlots($restaurantId, $date, $partySize, $preferredTime, $range = 2)
    {
        $preferredTimestamp = strtotime($preferredTime);
        $rangeSeconds = $range * 3600; // Convert hours to seconds

        $startTime = date('H:i:s', $preferredTimestamp - $rangeSeconds);
        $endTime = date('H:i:s', $preferredTimestamp + $rangeSeconds);

        $allSlots = $this->getAvailableTimeSlots($restaurantId, $date, $partySize);
        $alternatives = [];

        foreach ($allSlots as $slot) {
            $slotTimestamp = strtotime($slot['time']);
            if ($slotTimestamp >= strtotime($startTime) && $slotTimestamp <= strtotime($endTime)) {
                $alternatives[] = $slot;
            }
        }

        return $alternatives;
    }

    public function estimateWaitTime($restaurantId, $date, $time, $partySize)
    {
        if ($this->hasAvailability($restaurantId, $date, $time, $partySize)) {
            return 0; // No wait needed
        }

        // Find next available slot
        $nextSlots = $this->getAvailableTimeSlots($restaurantId, $date, $partySize);
        
        foreach ($nextSlots as $slot) {
            if (strtotime($slot['time']) > strtotime($time)) {
                $waitMinutes = (strtotime($slot['time']) - strtotime($time)) / 60;
                return ceil($waitMinutes);
            }
        }

        return null; // No availability for the day
    }

    private function isRestaurantOpen($restaurant, $time)
    {
        $requestTime = strtotime($time);
        $openingTime = strtotime($restaurant['opening_time']);
        $closingTime = strtotime($restaurant['closing_time']);

        return $requestTime >= $openingTime && $requestTime <= $closingTime;
    }

    private function calculateEndTime($startTime, $durationMinutes)
    {
        $endTimestamp = strtotime($startTime) + ($durationMinutes * 60);
        return date('H:i:s', $endTimestamp);
    }

    public function getPopularTimeSlots($restaurantId, $days = 30)
    {
        $sql = "SELECT 
                    HOUR(reservation_time) as hour,
                    COUNT(*) as reservation_count
                FROM reservations 
                WHERE restaurant_id = ? 
                AND reservation_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND status IN ('confirmed', 'completed')
                GROUP BY HOUR(reservation_time)
                ORDER BY reservation_count DESC";
        
        $db = \App\Core\DB::getInstance();
        return $db->fetchAll($sql, [$restaurantId, $days]);
    }

    public function getRecommendedTimes($restaurantId, $date, $partySize, $limit = 5)
    {
        $availableSlots = $this->getAvailableTimeSlots($restaurantId, $date, $partySize);
        $popularTimes = $this->getPopularTimeSlots($restaurantId);

        // Create a map of popular hours
        $popularHours = [];
        foreach ($popularTimes as $time) {
            $popularHours[$time['hour']] = $time['reservation_count'];
        }

        // Score available slots based on popularity
        foreach ($availableSlots as &$slot) {
            $hour = date('G', strtotime($slot['time']));
            $slot['popularity_score'] = $popularHours[$hour] ?? 0;
        }

        // Sort by popularity and return top recommendations
        usort($availableSlots, function($a, $b) {
            return $b['popularity_score'] - $a['popularity_score'];
        });

        return array_slice($availableSlots, 0, $limit);
    }
}