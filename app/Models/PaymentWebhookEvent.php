<?php

namespace App\Models;

use App\Core\Model;

class PaymentWebhookEvent extends Model {
    protected string $table = 'payment_webhook_events';

    public function isProcessed(string $eventId): bool {
        $event = $this->findBy('event_id', $eventId);
        return $event !== null;
    }
}
