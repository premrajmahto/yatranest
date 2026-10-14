<?php

namespace App\Models;

use App\Core\Model;

class Destination extends Model {
    protected string $table = 'destinations';

    public static function getSuitableImage(string $name, string $slug = ''): string {
        $n = strtolower($name . ' ' . $slug);
        if (str_contains($n, 'assam')) {
            return 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'darjeeling')) {
            return 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'manipur')) {
            return 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'meghalaya')) {
            return 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'mizoram')) {
            return 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'nagaland')) {
            return 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'sikkim')) {
            return 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'tripura')) {
            return 'https://images.unsplash.com/photo-1566837945700-30057527ade0?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'arunachal')) {
            return 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80';
        }
        if (str_contains($n, 'bhutan')) {
            return 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=80';
        }
        return 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80';
    }

    public static function isMismatchedImage(string $name, ?string $img): bool {
        if (empty($img)) return true;
        $badKeys = [
            'assam' => ['photo-1537996194471'],      // Bali
            'darjeeling' => ['photo-1602216056096'], // Kerala
            'manipur' => ['photo-1595815771614'],    // Kashmir
            'meghalaya' => ['photo-1512343879784'],  // Goa
            'mizoram' => ['photo-1477587458883'],    // Rajasthan Hawa Mahal
            'sikkim' => ['photo-1512453979798'],     // Dubai Burj Khalifa
            'nagaland' => ['photo-1626621341517'],   // Snow mountain (Himachal)
            'tripura' => ['photo-1530122037265'],    // Swiss Alps
        ];
        $n = strtolower($name);
        foreach ($badKeys as $key => $badPatterns) {
            if (str_contains($n, $key)) {
                foreach ($badPatterns as $pat) {
                    if (str_contains($img, $pat)) return true;
                }
            }
        }
        return false;
    }

    private function sanitizeList(array $list): array {
        foreach ($list as &$d) {
            if (self::isMismatchedImage($d['name'] ?? '', $d['image_url'] ?? '')) {
                $suitable = self::getSuitableImage($d['name'] ?? '', $d['slug'] ?? '');
                $d['image_url'] = $suitable;
                // Auto-sync in background if writable
                try {
                    if (!empty($d['id'])) {
                        $this->query("UPDATE destinations SET image_url = :img WHERE id = :id", [':img' => $suitable, ':id' => $d['id']]);
                    }
                } catch (\Throwable $e) {}
            }
        }
        return $list;
    }

    public function getFeatured(int $limit = 8): array {
        $rows = $this->where("status = 'active' AND is_featured = 1", [], 'name ASC', $limit);
        return $this->sanitizeList($rows);
    }

    public function getAllActive(): array {
        $rows = $this->where("status = 'active'", [], 'name ASC');
        return $this->sanitizeList($rows);
    }

    public function getPackageCount(int $destinationId): int {
        $stmt = $this->query("SELECT COUNT(*) FROM tour_packages WHERE destination_id = :id AND status = 'published'", [':id' => $destinationId]);
        return (int)$stmt->fetchColumn();
    }
}
