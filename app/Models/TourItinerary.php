<?php

namespace App\Models;

use App\Core\Model;

class TourItinerary extends Model {
    protected string $table = 'tour_itineraries';

    public function getForPackage(int $packageId): array {
        return $this->where('package_id = :id', [':id' => $packageId], 'day_number ASC');
    }
}
