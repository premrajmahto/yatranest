<?php

namespace App\Models;

use App\Core\Model;

class TourImage extends Model {
    protected string $table = 'tour_images';

    public function getForPackage(int $packageId): array {
        return $this->where('package_id = :id', [':id' => $packageId], 'sort_order ASC, id ASC');
    }
}
