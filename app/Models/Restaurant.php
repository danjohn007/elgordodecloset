<?php

namespace App\Models;

use App\Core\Model;

class Restaurant extends Model
{
    protected $table = 'restaurants';
    protected $fillable = [
        'owner_id', 'name', 'description', 'address', 'phone', 'email',
        'cuisine_type', 'price_range', 'opening_time', 'closing_time',
        'max_capacity', 'auto_confirm', 'latitude', 'longitude', 'image_url', 'status'
    ];

    public function getWithOwner($id)
    {
        $sql = "SELECT r.*, u.name as owner_name, u.email as owner_email 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.owner_id = u.id 
                WHERE r.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getAllWithOwner($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, u.name as owner_name, u.email as owner_email 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.owner_id = u.id 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $data = $this->db->fetchAll($sql, [$perPage, $offset]);
        $total = $this->count();
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

    public function getByOwner($ownerId)
    {
        return $this->where(['owner_id' => $ownerId]);
    }

    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active'";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE ?";
            $params[] = "%{$filters['name']}%";
        }

        if (!empty($filters['cuisine_type'])) {
            $sql .= " AND cuisine_type = ?";
            $params[] = $filters['cuisine_type'];
        }

        if (!empty($filters['price_range'])) {
            $sql .= " AND price_range = ?";
            $params[] = $filters['price_range'];
        }

        if (!empty($filters['min_rating'])) {
            $sql .= " AND rating >= ?";
            $params[] = $filters['min_rating'];
        }

        if (!empty($filters['location'])) {
            $sql .= " AND address LIKE ?";
            $params[] = "%{$filters['location']}%";
        }

        // Location radius search if lat/lng provided
        if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['radius'])) {
            $sql .= " AND (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?";
            $params[] = $filters['latitude'];
            $params[] = $filters['longitude'];
            $params[] = $filters['latitude'];
            $params[] = $filters['radius'];
        }

        $orderBy = $filters['sort'] ?? 'rating';
        $order = $filters['order'] ?? 'DESC';
        $sql .= " ORDER BY {$orderBy} {$order}";

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $data = $this->db->fetchAll($sql, $params);

        // Get total count with same filters
        $countSql = str_replace('SELECT * FROM', 'SELECT COUNT(*) as count FROM', $sql);
        $countSql = preg_replace('/ORDER BY.*/', '', $countSql);
        $countSql = preg_replace('/LIMIT.*/', '', $countSql);
        
        $countParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
        $total = $this->db->fetch($countSql, $countParams)['count'];
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

    public function updateRating($restaurantId)
    {
        $sql = "UPDATE {$this->table} SET 
                rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE restaurant_id = ?),
                total_reviews = (SELECT COUNT(*) FROM reviews WHERE restaurant_id = ?)
                WHERE id = ?";
        
        return $this->db->execute($sql, [$restaurantId, $restaurantId, $restaurantId]);
    }

    public function getCuisineTypes()
    {
        $sql = "SELECT DISTINCT cuisine_type FROM {$this->table} WHERE cuisine_type IS NOT NULL AND status = 'active' ORDER BY cuisine_type";
        return $this->db->fetchAll($sql);
    }

    public function getFeatured($limit = 6)
    {
        return $this->where(['status' => 'active'], 'rating', 'DESC', $limit);
    }

    public function getStats($ownerId = null)
    {
        $stats = [];
        
        $whereClause = $ownerId ? "WHERE owner_id = {$ownerId}" : "";
        
        // Total restaurants
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $stats['total_restaurants'] = $this->db->fetch($sql)['count'];

        // Active restaurants
        $activeWhere = $ownerId ? "WHERE owner_id = {$ownerId} AND status = 'active'" : "WHERE status = 'active'";
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$activeWhere}";
        $stats['active_restaurants'] = $this->db->fetch($sql)['count'];

        // Average rating
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} {$whereClause}";
        $stats['average_rating'] = round($this->db->fetch($sql)['avg_rating'] ?? 0, 2);

        // Restaurants by cuisine type
        $sql = "SELECT cuisine_type, COUNT(*) as count FROM {$this->table} {$whereClause} GROUP BY cuisine_type";
        $cuisineStats = $this->db->fetchAll($sql);
        $stats['by_cuisine'] = [];
        foreach ($cuisineStats as $stat) {
            $stats['by_cuisine'][$stat['cuisine_type']] = $stat['count'];
        }

        return $stats;
    }

    public function isAvailable($restaurantId, $date, $time, $partySize)
    {
        // Check if restaurant is open at the requested time
        $restaurant = $this->find($restaurantId);
        if (!$restaurant) {
            return false;
        }

        // Check operating hours
        $requestTime = strtotime($time);
        $openingTime = strtotime($restaurant['opening_time']);
        $closingTime = strtotime($restaurant['closing_time']);

        if ($requestTime < $openingTime || $requestTime > $closingTime) {
            return false;
        }

        // Check if there are available tables
        $availabilityService = new \App\Services\AvailabilityService();
        return $availabilityService->hasAvailability($restaurantId, $date, $time, $partySize);
    }
}