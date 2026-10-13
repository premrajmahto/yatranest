<?php

namespace App\Services;

use App\Models\SiteSetting;

class InvoiceService {
    public function renderInvoiceHtml(array $booking): string {
        $settingModel = new SiteSetting();
        $companyName = $settingModel->get('company_name', 'YatraNest Holidays Pvt Ltd');
        $companyAddress = $settingModel->get('company_address', 'Suite 402, Travel Towers, MG Road, Bengaluru, India');
        $companyPhone = $settingModel->get('company_phone', '+91 98765 43210');
        $companyEmail = $settingModel->get('company_email', 'support@yatranest.com');
        $companyGstin = $settingModel->get('company_gstin', '29ABCDE1234F1Z5');

        $invoiceNum = 'INV-' . str_replace('YN-', '', $booking['booking_reference']);
        $invoiceDate = date('d M Y', strtotime($booking['created_at']));

        $adultTotal = (float)$booking['adult_count'] * ((float)$booking['subtotal'] / max(1, (int)$booking['adult_count'] + (int)$booking['child_count']));

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Invoice - <?= htmlspecialchars($booking['booking_reference']) ?> - <?= htmlspecialchars($companyName) ?></title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.5; margin: 0; padding: 30px; font-size: 14px; background: #fff; }
                .invoice-box { max-width: 800px; margin: auto; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                .header-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                .header-table td { vertical-align: top; }
                .logo { font-size: 28px; font-weight: 800; color: #0d233a; }
                .logo span { color: #008080; }
                .inv-title { text-align: right; font-size: 24px; font-weight: bold; color: #0d233a; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .info-table td { width: 50%; vertical-align: top; padding: 10px; background: #f8fafc; border-radius: 6px; }
                .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                .items-table th { background: #0d233a; color: #fff; text-align: left; padding: 10px 14px; font-size: 13px; text-transform: uppercase; }
                .items-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; }
                .total-table { width: 45%; margin-left: auto; border-collapse: collapse; margin-bottom: 30px; }
                .total-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
                .total-table .grand-total { font-size: 16px; font-weight: bold; background: #f1f5f9; color: #0d233a; }
                .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
                .badge-success { background: #dcfce7; color: #166534; }
                .badge-warning { background: #fef9c3; color: #854d0e; }
                .badge-danger { background: #fee2e2; color: #991b1b; }
                .footer { border-top: 2px solid #e2e8f0; padding-top: 15px; text-align: center; font-size: 12px; color: #64748b; margin-top: 20px; }
                .no-print { margin-bottom: 20px; text-align: right; }
                .btn { display: inline-block; padding: 8px 16px; border-radius: 6px; font-weight: 600; text-decoration: none; cursor: pointer; }
                .btn-primary { background: #008080; color: #fff; border: none; }
                @media print { .no-print { display: none; } .invoice-box { border: none; box-shadow: none; padding: 0; } }
            </style>
        </head>
        <body>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Download PDF</button>
            </div>
            <div class="invoice-box">
                <table class="header-table">
                    <tr>
                        <td>
                            <div class="logo">Yatra<span>Nest</span></div>
                            <div style="color: #64748b; margin-top: 4px;"><?= htmlspecialchars($companyAddress) ?></div>
                            <div style="color: #64748b;">Phone: <?= htmlspecialchars($companyPhone) ?> | Email: <?= htmlspecialchars($companyEmail) ?></div>
                            <div style="color: #64748b;">GSTIN: <?= htmlspecialchars($companyGstin) ?></div>
                        </td>
                        <td class="inv-title">
                            <div>TAX INVOICE</div>
                            <div style="font-size: 14px; font-weight: normal; color: #64748b; margin-top: 5px;">Invoice #: <?= htmlspecialchars($invoiceNum) ?></div>
                            <div style="font-size: 14px; font-weight: normal; color: #64748b;">Date: <?= htmlspecialchars($invoiceDate) ?></div>
                            <div style="margin-top: 8px;">
                                <?php if ($booking['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success">PAID</span>
                                <?php elseif ($booking['payment_status'] === 'refunded'): ?>
                                    <span class="badge badge-danger">REFUNDED</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= strtoupper($booking['payment_status']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="info-table">
                    <tr>
                        <td style="border-right: 15px solid #fff;">
                            <strong style="color: #0d233a;">Billed To:</strong><br>
                            <?= htmlspecialchars($booking['customer_name']) ?><br>
                            Email: <?= htmlspecialchars($booking['customer_email']) ?><br>
                            Phone: <?= htmlspecialchars($booking['customer_phone'] ?? 'N/A') ?>
                        </td>
                        <td>
                            <strong style="color: #0d233a;">Trip Details:</strong><br>
                            Booking Ref: <strong><?= htmlspecialchars($booking['booking_reference']) ?></strong><br>
                            Destination: <?= htmlspecialchars($booking['destination_name']) ?>, <?= htmlspecialchars($booking['destination_country']) ?><br>
                            Departure: <?= date('d M Y', strtotime($booking['departure_date'])) ?> | Return: <?= date('d M Y', strtotime($booking['return_date'])) ?>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Pax</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($booking['package_title']) ?></strong><br>
                                <span style="font-size: 12px; color: #64748b;">
                                    Adults: <?= (int)$booking['adult_count'] ?>
                                    <?php if ((int)$booking['child_count'] > 0): ?> | Children: <?= (int)$booking['child_count'] ?><?php endif; ?>
                                    <?php if ((int)$booking['infant_count'] > 0): ?> | Infants: <?= (int)$booking['infant_count'] ?><?php endif; ?>
                                </span>
                            </td>
                            <td><?= (int)$booking['adult_count'] + (int)$booking['child_count'] + (int)$booking['infant_count'] ?></td>
                            <td style="text-align: right;"><?= format_currency($booking['subtotal']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class="total-table">
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;"><?= format_currency($booking['subtotal']) ?></td>
                    </tr>
                    <?php if ((float)$booking['discount_amount'] > 0): ?>
                    <tr>
                        <td style="color: #16a34a;">Discount (Coupon):</td>
                        <td style="text-align: right; color: #16a34a;">-<?= format_currency($booking['discount_amount']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>GST (<?= number_format((float)$booking['tax_rate'], 1) ?>%):</td>
                        <td style="text-align: right;"><?= format_currency($booking['tax_amount']) ?></td>
                    </tr>
                    <tr class="grand-total">
                        <td>Total Amount:</td>
                        <td style="text-align: right;"><?= format_currency($booking['total_amount']) ?></td>
                    </tr>
                </table>

                <?php if (!empty($booking['travelers'])): ?>
                <div style="margin-bottom: 25px;">
                    <strong style="color: #0d233a;">Registered Travelers:</strong>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px;">
                        <tr style="background: #f1f5f9; text-align: left;">
                            <th style="padding: 6px 10px;">#</th>
                            <th style="padding: 6px 10px;">Name</th>
                            <th style="padding: 6px 10px;">Age</th>
                            <th style="padding: 6px 10px;">Gender</th>
                            <th style="padding: 6px 10px;">Type</th>
                        </tr>
                        <?php foreach ($booking['travelers'] as $idx => $tr): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 6px 10px;"><?= $idx + 1 ?></td>
                            <td style="padding: 6px 10px; font-weight: 500;"><?= htmlspecialchars($tr['full_name']) ?></td>
                            <td style="padding: 6px 10px;"><?= (int)$tr['age'] ?> yrs</td>
                            <td style="padding: 6px 10px; text-transform: capitalize;"><?= htmlspecialchars($tr['gender']) ?></td>
                            <td style="padding: 6px 10px; text-transform: capitalize;"><?= htmlspecialchars($tr['traveler_type']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <div class="footer">
                    <p>This is a computer-generated invoice and does not require a physical signature.</p>
                    <p>Thank you for choosing YatraNest Holidays. Wishing you a safe and joyful journey!</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
