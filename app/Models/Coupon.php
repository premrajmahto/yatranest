<?php

namespace App\Models;

use App\Core\Model;

class Coupon extends Model {
    protected string $table = 'coupons';

    public function findValidCoupon(string $code, float $amount, ?int $userId = null): array {
        $coupon = $this->findBy('code', strtoupper(trim($code)));
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'This coupon has been deactivated.'];
        }

        $today = date('Y-m-d');
        if ($today < $coupon['start_date']) {
            return ['valid' => false, 'message' => 'This coupon is not yet active.'];
        }

        if ($today > $coupon['expiry_date']) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon['usage_limit'] !== null && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if ($amount < (float)$coupon['min_booking_amount']) {
            return ['valid' => false, 'message' => 'Minimum booking amount for this coupon is ' . format_currency($coupon['min_booking_amount']) . '.'];
        }

        if ($userId !== null && $coupon['per_user_limit'] !== null) {
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :cid AND user_id = :uid");
            $stmt->execute([':cid' => $coupon['id'], ':uid' => $userId]);
            $userRedemptions = (int)$stmt->fetchColumn();
            if ($userRedemptions >= (int)$coupon['per_user_limit']) {
                return ['valid' => false, 'message' => 'You have already reached the redemption limit for this coupon.'];
            }
        }

        // Calculate discount
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($amount * (float)$coupon['discount_value']) / 100;
            if ($coupon['max_discount'] !== null && $discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
            if ($discount > $amount) {
                $discount = $amount;
            }
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'message' => 'Coupon applied successfully!'
        ];
    }
}
