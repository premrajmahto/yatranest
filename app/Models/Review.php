<?php

namespace App\Models;

use App\Core\Model;

class Review extends Model {
    protected string $table = 'reviews';

    public function getForPackage(int $packageId): array {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.package_id = :pkg_id AND r.status = 'approved'
                ORDER BY r.id DESC";
        return $this->fetchAll($sql, [':pkg_id' => $packageId]);
    }

    public function getRecentApproved(int $limit = 6): array {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar, p.title as package_title
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN tour_packages p ON r.package_id = p.id
                WHERE r.status = 'approved'
                ORDER BY r.id DESC
                LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }
}

