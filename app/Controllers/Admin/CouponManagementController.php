<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Coupon;

class CouponManagementController extends Controller {
    public function index(Request $request, Response $response): void {
        $couponModel = new Coupon();
        $this->render('admin/coupons/index', [
            'title' => 'Coupons & Promos Management',
            'coupons' => $couponModel->all('id DESC')
        ], 'admin');
    }

    public function create(Request $request, Response $response): void {
        $this->render('admin/coupons/form', [
            'title' => 'Create New Coupon',
            'coupon' => []
        ], 'admin');
    }

    public function store(Request $request, Response $response): void {
        $data = $request->getAllPost();
        $rules = [
            'code' => 'required|min:3|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/coupons/create');
        }

        $couponModel = new Coupon();
        $id = $couponModel->create([
            'code' => strtoupper(trim($data['code'])),
            'discount_type' => $data['discount_type'],
            'discount_value' => (float)$data['discount_value'],
            'min_booking_amount' => (float)($data['min_booking_amount'] ?? 0),
            'max_discount' => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'start_date' => $data['start_date'],
            'expiry_date' => $data['expiry_date'],
            'usage_limit' => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit' => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : 1,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        AuditLog::record('coupon_created', 'coupon', $id);
        $this->flash('success', 'Coupon created successfully.');
        $this->redirect('/admin/coupons');
    }

    public function edit(Request $request, Response $response, string $id): void {
        $couponModel = new Coupon();
        $coupon = $couponModel->find((int)$id);

        if (!$coupon) {
            $this->flash('error', 'Coupon not found.');
            $this->redirect('/admin/coupons');
        }

        $this->render('admin/coupons/form', [
            'title' => 'Edit Coupon: ' . $coupon['code'],
            'coupon' => $coupon
        ], 'admin');
    }

    public function update(Request $request, Response $response, string $id): void {
        $couponModel = new Coupon();
        $coupon = $couponModel->find((int)$id);

        if (!$coupon) {
            $this->flash('error', 'Coupon not found.');
            $this->redirect('/admin/coupons');
        }

        $data = $request->getAllPost();
        $rules = [
            'code' => "required|min:3|unique:coupons,code,{$id}",
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date',
        ];

        $errors = $this->validate($data, $rules);
        if ($errors) {
            $this->redirect('/admin/coupons/edit/' . $id);
        }

        $couponModel->update((int)$id, [
            'code' => strtoupper(trim($data['code'])),
            'discount_type' => $data['discount_type'],
            'discount_value' => (float)$data['discount_value'],
            'min_booking_amount' => (float)($data['min_booking_amount'] ?? 0),
            'max_discount' => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'start_date' => $data['start_date'],
            'expiry_date' => $data['expiry_date'],
            'usage_limit' => !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            'per_user_limit' => !empty($data['per_user_limit']) ? (int)$data['per_user_limit'] : 1,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        AuditLog::record('coupon_updated', 'coupon', (int)$id);
        $this->flash('success', 'Coupon updated successfully.');
        $this->redirect('/admin/coupons');
    }

    public function delete(Request $request, Response $response, string $id): void {
        $couponModel = new Coupon();
        $couponModel->delete((int)$id);

        AuditLog::record('coupon_deleted', 'coupon', (int)$id);
        $this->flash('success', 'Coupon deleted.');
        $this->redirect('/admin/coupons');
    }
}
