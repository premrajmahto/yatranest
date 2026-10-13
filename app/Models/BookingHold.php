<?php

namespace App\Models;

use App\Core\Model;

class BookingHold extends Model {
    protected string $table = 'booking_holds';

    public function pruneExpired(): int {
        $stmt = $this->getDb()->prepare("DELETE FROM booking_holds WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getActiveHoldsCount(int $departureId): int {
        $stmt = $this->getDb()->prepare("SELECT COALESCE(SUM(seats_held), 0) FROM booking_holds WHERE departure_id = :id AND expires_at > NOW()");
        $stmt->execute([':id' => $departureId]);
        return (int)$stmt->fetchColumn();
    }

    public function releaseSessionHold(string $sessionId): void {
        $stmt = $this->getDb()->prepare("DELETE FROM booking_holds WHERE session_id = :sid");
        $stmt->execute([':sid' => $sessionId]);
    }
}
