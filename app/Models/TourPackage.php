<?php

namespace App\Models;

use App\Core\Model;

class TourPackage extends Model {
    protected string $table = 'tour_packages';

    public function getFeatured(int $limit = 6): array {
        $sql = "SELECT p.*, d.name AS destination_name, d.country, c.name AS category_name,
                       (SELECT AVG(rating) FROM reviews WHERE package_id = p.id AND status = 'approved') AS avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE package_id = p.id AND status = 'approved') AS review_count
                FROM tour_packages p
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_categories c ON p.category_id = c.id
                WHERE p.status = 'published' AND p.is_featured = 1
                ORDER BY p.id DESC
                LIMIT :lim";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array {
        $sql = "SELECT p.*, d.name AS destination_name, d.country, d.state, d.city, c.name AS category_name,
                       (SELECT AVG(rating) FROM reviews WHERE package_id = p.id AND status = 'approved') AS avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE package_id = p.id AND status = 'approved') AS review_count
                FROM tour_packages p
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_categories c ON p.category_id = c.id
                WHERE p.slug = :slug AND p.status = 'published'
                LIMIT 1";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function search(array $filters = [], string $sort = 'popular', int $limit = 12, int $offset = 0): array {
        $where = ["p.status = 'published'"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(p.title LIKE :kw OR p.summary LIKE :kw OR d.name LIKE :kw OR d.country LIKE :kw)";
            $params[':kw'] = '%' . trim($filters['keyword']) . '%';
        }

        if (!empty($filters['destination_id'])) {
            $where[] = "p.destination_id = :dest_id";
            $params[':dest_id'] = (int)$filters['destination_id'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :cat_id";
            $params[':cat_id'] = (int)$filters['category_id'];
        }

        if (isset($filters['is_domestic']) && $filters['is_domestic'] !== '') {
            $where[] = "p.is_domestic = :is_dom";
            $params[':is_dom'] = (int)$filters['is_domestic'];
        }

        if (!empty($filters['tour_type'])) {
            $where[] = "p.tour_type = :tour_type";
            $params[':tour_type'] = $filters['tour_type'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.price_adult >= :min_price";
            $params[':min_price'] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.price_adult <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        if (!empty($filters['duration'])) {
            // e.g. '1-3', '4-7', '8+'
            if ($filters['duration'] === '1-3') {
                $where[] = "p.duration_days BETWEEN 1 AND 3";
            } elseif ($filters['duration'] === '4-7') {
                $where[] = "p.duration_days BETWEEN 4 AND 7";
            } elseif ($filters['duration'] === '8+') {
                $where[] = "p.duration_days >= 8";
            }
        }

        $whereClause = implode(' AND ', $where);

        $orderBy = match ($sort) {
            'price_asc' => 'p.price_adult ASC',
            'price_desc' => 'p.price_adult DESC',
            'duration' => 'p.duration_days ASC',
            'rating' => 'avg_rating DESC',
            'newest' => 'p.id DESC',
            default => 'p.is_featured DESC, p.id DESC',
        };

        $sql = "SELECT p.*, d.name AS destination_name, d.country, c.name AS category_name,
                       COALESCE((SELECT AVG(rating) FROM reviews WHERE package_id = p.id AND status = 'approved'), 5) AS avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE package_id = p.id AND status = 'approved') AS review_count
                FROM tour_packages p
                JOIN destinations d ON p.destination_id = d.id
                JOIN tour_categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        $packages = $stmt->fetchAll();

        // Get total count
        $countSql = "SELECT COUNT(*) FROM tour_packages p
                     JOIN destinations d ON p.destination_id = d.id
                     JOIN tour_categories c ON p.category_id = c.id
                     WHERE $whereClause";
        $countStmt = $this->getDb()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        return [
            'packages' => $packages,
            'total' => $total,
            'page' => ($offset / $limit) + 1,
            'pages' => ceil($total / $limit) ?: 1,
        ];
    }
}
