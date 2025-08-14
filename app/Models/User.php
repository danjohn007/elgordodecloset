<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'email_verified_at'];

    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }

    public function updateUser($id, $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }

    public function getClientUsers($page = 1, $perPage = 10)
    {
        return $this->paginate($page, $perPage, ['role' => 'cliente'], 'created_at', 'DESC');
    }

    public function getRestaurantAdmins($page = 1, $perPage = 10)
    {
        return $this->paginate($page, $perPage, ['role' => 'admin_restaurante'], 'created_at', 'DESC');
    }

    public function searchUsers($query, $role = null, $page = 1, $perPage = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE (name LIKE ? OR email LIKE ?)";
        $params = ["%{$query}%", "%{$query}%"];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $data = $this->db->fetchAll($sql, $params);

        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE (name LIKE ? OR email LIKE ?)";
        $countParams = ["%{$query}%", "%{$query}%"];
        
        if ($role) {
            $countSql .= " AND role = ?";
            $countParams[] = $role;
        }

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

    public function verifyEmail($id)
    {
        return $this->update($id, ['email_verified_at' => date('Y-m-d H:i:s')]);
    }

    public function getStats()
    {
        $stats = [];
        
        // Total users by role
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
        $roleStats = $this->db->fetchAll($sql);
        
        foreach ($roleStats as $stat) {
            $stats['users_by_role'][$stat['role']] = $stat['count'];
        }

        // New users this month
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $stats['new_users_this_month'] = $this->db->fetch($sql)['count'];

        // Total users
        $stats['total_users'] = $this->count();

        return $stats;
    }
}