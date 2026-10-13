<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\SiteSetting;

class SettingsController extends Controller {
    public function index(Request $request, Response $response): void {
        $settingModel = new SiteSetting();
        $settings = $settingModel->loadAll();

        $this->render('admin/settings/index', [
            'title' => 'Platform Settings',
            'settings' => $settings
        ], 'admin');
    }

    public function update(Request $request, Response $response): void {
        $settingModel = new SiteSetting();
        $fields = [
            'company_name' => 'general',
            'company_phone' => 'general',
            'company_email' => 'general',
            'company_address' => 'general',
            'company_gstin' => 'general',
            'tax_rate' => 'booking',
            'currency_symbol' => 'booking',
            'hold_timeout_minutes' => 'booking',
            'razorpay_mode' => 'payment',
            'razorpay_key_id' => 'payment',
            'razorpay_key_secret' => 'payment',
            'razorpay_webhook_secret' => 'payment',
            'mail_host' => 'mail',
            'mail_port' => 'mail',
            'mail_username' => 'mail',
            'mail_password' => 'mail',
        ];

        foreach ($fields as $field => $group) {
            $val = $request->input($field);
            if ($val !== null && $val !== '') {
                $settingModel->set($field, trim((string)$val), $group);
            }
        }

        AuditLog::record('settings_updated', 'site_setting');
        $this->flash('success', 'Platform settings saved successfully.');
        $this->redirect('/admin/settings');
    }
}
