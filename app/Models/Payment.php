<?php

namespace App\Models;

use App\Core\Model;

class Payment extends Model {
    protected string $table = 'payments';

    public function findByTransactionReference(string $ref): ?array {
        return $this->findBy('transaction_reference', $ref);
    }

    public function findByOrderId(string $orderId): ?array {
        return $this->findBy('gateway_order_id', $orderId);
    }
}
