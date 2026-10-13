<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\SiteSetting;

class PricingService {
    public function calculate(
        float $adultPrice,
        int $adultCount,
        float $childPrice,
        int $childCount,
        float $infantPrice = 0.0,
        int $infantCount = 0,
        ?string $couponCode = null,
        ?int $userId = null
    ): array {
        $adultTotal = round($adultPrice * $adultCount, 2);
        $childTotal = round($childPrice * $childCount, 2);
        $infantTotal = round($infantPrice * $infantCount, 2);
        $subtotal = round($adultTotal + $childTotal + $infantTotal, 2);

        $discount = 0.00;
        $couponData = null;
        $couponMessage = null;

        if (!empty($couponCode)) {
            $couponModel = new Coupon();
            $couponResult = $couponModel->findValidCoupon($couponCode, $subtotal, $userId);
            if ($couponResult['valid']) {
                $discount = (float)$couponResult['discount'];
                $couponData = $couponResult['coupon'];
                $couponMessage = $couponResult['message'];
            } else {
                $couponMessage = $couponResult['message'];
            }
        }

        $taxableAmount = max(0, $subtotal - $discount);

        $settingModel = new SiteSetting();
        $taxRate = (float)$settingModel->get('tax_rate', 5.00); // 5% GST
        $taxAmount = round(($taxableAmount * $taxRate) / 100, 2);

        $total = round($taxableAmount + $taxAmount, 2);

        return [
            'adult_price' => $adultPrice,
            'adult_count' => $adultCount,
            'adult_total' => $adultTotal,
            'child_price' => $childPrice,
            'child_count' => $childCount,
            'child_total' => $childTotal,
            'infant_price' => $infantPrice,
            'infant_count' => $infantCount,
            'infant_total' => $infantTotal,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $couponData,
            'coupon_message' => $couponMessage,
            'taxable_amount' => $taxableAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }
}
