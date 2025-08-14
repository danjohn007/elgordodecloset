<?php

namespace App\Models;

use App\Core\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $fillable = [
        'user_id', 'restaurant_id', 'reservation_id', 'rating', 'comment',
        'food_rating', 'service_rating', 'ambiance_rating', 'response', 'response_date'
    ];

    public function createReview($data)
    {
        $reviewId = $this->create($data);
        
        if ($reviewId) {
            // Update restaurant rating
            $this->updateRestaurantRating($data['restaurant_id']);
        }
        
        return $reviewId;
    }

    public function getByRestaurant($restaurantId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, u.name as user_name, res.confirmation_code
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN reservations res ON r.reservation_id = res.id
                WHERE r.restaurant_id = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        
        $data = $this->db->fetchAll($sql, [$restaurantId, $perPage, $offset]);
        $total = $this->count(['restaurant_id' => $restaurantId]);
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

    public function getByUser($userId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, rest.name as restaurant_name, res.confirmation_code
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                LEFT JOIN reservations res ON r.reservation_id = res.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC
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

    public function getWithDetails($id)
    {
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email,
                       rest.name as restaurant_name, res.confirmation_code
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                LEFT JOIN reservations res ON r.reservation_id = res.id
                WHERE r.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    public function addResponse($reviewId, $response)
    {
        return $this->update($reviewId, [
            'response' => $response,
            'response_date' => date('Y-m-d H:i:s')
        ]);
    }

    public function canUserReview($userId, $restaurantId, $reservationId = null)
    {
        // Check if user has already reviewed this restaurant for this reservation
        $conditions = [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ];
        
        if ($reservationId) {
            $conditions['reservation_id'] = $reservationId;
        }
        
        return $this->count($conditions) === 0;
    }

    public function getRestaurantRatingBreakdown($restaurantId)
    {
        $sql = "SELECT 
                    AVG(rating) as overall_rating,
                    AVG(food_rating) as food_rating,
                    AVG(service_rating) as service_rating,
                    AVG(ambiance_rating) as ambiance_rating,
                    COUNT(*) as total_reviews
                FROM {$this->table} 
                WHERE restaurant_id = ?";
        
        $result = $this->db->fetch($sql, [$restaurantId]);
        
        // Get rating distribution (1-5 stars)
        $distributionSql = "SELECT rating, COUNT(*) as count 
                           FROM {$this->table} 
                           WHERE restaurant_id = ? 
                           GROUP BY rating 
                           ORDER BY rating DESC";
        
        $distribution = $this->db->fetchAll($distributionSql, [$restaurantId]);
        
        $result['rating_distribution'] = [];
        for ($i = 5; $i >= 1; $i--) {
            $result['rating_distribution'][$i] = 0;
        }
        
        foreach ($distribution as $dist) {
            $result['rating_distribution'][$dist['rating']] = $dist['count'];
        }
        
        return $result;
    }

    public function getRecentReviews($limit = 10)
    {
        $sql = "SELECT r.*, u.name as user_name, rest.name as restaurant_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                ORDER BY r.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getFeaturedReviews($restaurantId, $limit = 3)
    {
        $sql = "SELECT r.*, u.name as user_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.restaurant_id = ? AND r.rating >= 4
                ORDER BY r.rating DESC, r.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$restaurantId, $limit]);
    }

    public function getStats($restaurantId = null)
    {
        $stats = [];
        
        $whereClause = $restaurantId ? "WHERE restaurant_id = {$restaurantId}" : "";
        
        // Total reviews
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $stats['total_reviews'] = $this->db->fetch($sql)['count'];
        
        // Average ratings
        $sql = "SELECT 
                    AVG(rating) as avg_overall,
                    AVG(food_rating) as avg_food,
                    AVG(service_rating) as avg_service,
                    AVG(ambiance_rating) as avg_ambiance
                FROM {$this->table} {$whereClause}";
        
        $averages = $this->db->fetch($sql);
        $stats['average_ratings'] = [
            'overall' => round($averages['avg_overall'] ?? 0, 2),
            'food' => round($averages['avg_food'] ?? 0, 2),
            'service' => round($averages['avg_service'] ?? 0, 2),
            'ambiance' => round($averages['avg_ambiance'] ?? 0, 2)
        ];
        
        // Rating distribution
        $sql = "SELECT rating, COUNT(*) as count FROM {$this->table} {$whereClause} GROUP BY rating";
        $distribution = $this->db->fetchAll($sql);
        $stats['rating_distribution'] = [];
        foreach ($distribution as $dist) {
            $stats['rating_distribution'][$dist['rating']] = $dist['count'];
        }
        
        // Reviews with responses
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause} AND response IS NOT NULL";
        $responseWhere = $whereClause ? $whereClause . " AND response IS NOT NULL" : "WHERE response IS NOT NULL";
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$responseWhere}";
        $stats['reviews_with_responses'] = $this->db->fetch($sql)['count'];
        
        // Recent activity (this month)
        $recentWhere = $whereClause ? $whereClause . " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)" 
                                   : "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$recentWhere}";
        $stats['reviews_this_month'] = $this->db->fetch($sql)['count'];
        
        return $stats;
    }

    private function updateRestaurantRating($restaurantId)
    {
        $restaurantModel = new Restaurant();
        return $restaurantModel->updateRating($restaurantId);
    }

    public function getTopRatedRestaurants($limit = 10)
    {
        $sql = "SELECT r.restaurant_id, rest.name, rest.address, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
                WHERE rest.status = 'active'
                GROUP BY r.restaurant_id
                HAVING review_count >= 3
                ORDER BY avg_rating DESC, review_count DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function deleteReview($id)
    {
        $review = $this->find($id);
        if ($review) {
            $deleted = $this->delete($id);
            if ($deleted) {
                $this->updateRestaurantRating($review['restaurant_id']);
            }
            return $deleted;
        }
        return false;
    }
}