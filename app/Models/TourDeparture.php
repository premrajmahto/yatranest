<?php

namespace App\Models;

use App\Core\Model;

class TourDeparture extends Model {
    protected string $table = 'tour_departures';

    public function getUpcomingForPackage(int $packageId): array {
        $today = date('Y-m-d');
        $sql = "SELECT d.*,
                       (SELECT COALESCE(SUM(seats_held), 0) FROM booking_holds WHERE departure_id = d.id AND expires_at > NOW()) as current_held_seats
                FROM tour_departures d
                WHERE d.package_id = :pkg_id
                  AND d.departure_date >= :today
                  AND d.status = 'open'
                ORDER BY d.departure_date ASC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':pkg_id' => $packageId, ':today' => $today]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $effectiveAvailable = (int)$row['available_seats'] - (int)$row['current_held_seats'];
            $row['real_available_seats'] = max(0, $effectiveAvailable);
        }
        return $rows;
    }

    public function findWithLock(int $id): ?array {
        $driver = $this->getDb()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $lock = ($driver === 'sqlite') ? '' : ' FOR UPDATE';
        $sql = "SELECT * FROM tour_departures WHERE id = :id" . $lock;
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
