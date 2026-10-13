<?php

namespace App\Models;

use App\Core\Model;

class Destination extends Model {
    protected string $table = 'destinations';

    public function getFeatured(int $limit = 6): array {
        return $this->where("status = 'active' AND is_featured = 1", [], 'name ASC', $limit);
    }

    public function getAllActive(): array {
        return $this->where("status = 'active'", [], 'name ASC');
    }

    public function getPackageCount(int $destinationId): int {
        $stmt = $this->query("SELECT COUNT(*) FROM tour_packages WHERE destination_id = :id AND status = 'published'", [':id' => $destinationId]);
        return (int)$stmt->fetchColumn();
    }
}
