<?php

namespace App\Models;

use App\Core\Model;

class TourCategory extends Model {
    protected string $table = 'tour_categories';

    public function getAllActive(): array {
        return $this->where("is_active = 1", [], 'name ASC');
    }
}
