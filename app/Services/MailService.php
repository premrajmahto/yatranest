<?php

namespace App\Services;

use App\Core\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService {
    private array $config;

    public function __construct() {
        $this->config = config('mail', []);
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody = ''): bool {
        $mail = new PHPMailer(true);

        try {
            $host = $this->config['host'] ?? '';
            $username = $this->config['username'] ?? '';
            $password = $this->config['password'] ?? '';

            // If SMTP credentials not provided, log email content to storage/logs and return true
            if (empty($host) || empty($username)) {
                Logger::info("SMTP credentials not configured. Logging outbound email to {$toEmail}: Subject: {$subject}");
                $logFile = dirname(__DIR__, 2) . '/storage/logs/emails-' . date('Y-m-d') . '.log';
                $emailLog = sprintf(
                    "[%s] TO: %s <%s>\nSUBJECT: %s\nBODY:\n%s\n%s\n\n",
                    date('Y-m-d H:i:s'),
                    $toName,
                    $toEmail,
                    $subject,
                    strip_tags($htmlBody),
                    str_repeat('-', 60)
                );
                @file_put_contents($logFile, $emailLog, FILE_APPEND | LOCK_EX);
                return true;
            }

            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = $this->config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)($this->config['port'] ?? 587);

            $mail->setFrom($this->config['from_address'] ?? 'no-reply@yatranest.com', $this->config['from_name'] ?? 'YatraNest Holidays');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            Logger::error("PHPMailer error: {$mail->ErrorInfo} | Exception: " . $e->getMessage());
            return false;
        }
    }

    public function sendBookingConfirmation(array $booking): bool {
        $subject = "Booking Confirmed: {$booking['booking_reference']} - YatraNest";
        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
            <div style='text-align: center; padding-bottom: 20px; border-bottom: 2px solid #008080;'>
                <h1 style='color: #0d233a; margin: 0;'>YatraNest</h1>
                <p style='color: #008080; font-weight: bold; margin: 5px 0;'>Your Journey Begins With Us</p>
            </div>
            <div style='padding: 20px 0;'>
                <h2 style='color: #10b981;'>Booking Confirmed!</h2>
                <p>Dear <strong>" . htmlspecialchars($booking['customer_name'] ?? 'Traveler') . "</strong>,</p>
                <p>Thank you for booking with YatraNest. Your tour package reservation has been confirmed.</p>
                <div style='background: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Booking Reference:</strong> " . htmlspecialchars($booking['booking_reference']) . "</p>
                    <p style='margin: 5px 0;'><strong>Tour Package:</strong> " . htmlspecialchars($booking['package_title']) . "</p>
                    <p style='margin: 5px 0;'><strong>Departure Date:</strong> " . htmlspecialchars($booking['departure_date']) . "</p>
                    <p style='margin: 5px 0;'><strong>Travelers:</strong> " . ($booking['adult_count'] + $booking['child_count']) . " person(s)</p>
                    <p style='margin: 5px 0;'><strong>Total Paid:</strong> " . format_currency($booking['total_amount']) . "</p>
                </div>
                <p>You can access your complete trip details and download your invoice at any time from your customer dashboard.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . url('/my-bookings/' . $booking['booking_reference']) . "' style='background: #ff6b35; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Booking Details</a>
                </div>
            </div>
            <div style='border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; font-size: 12px; color: #64748b;'>
                <p>Need assistance? Contact our 24/7 support at support@yatranest.com or call +91 98765 43210.</p>
                <p>&copy; " . date('Y') . " YatraNest Holidays Pvt Ltd. All rights reserved.</p>
            </div>
        </div>";

        return $this->send($booking['customer_email'], $booking['customer_name'], $subject, $html);
    }

    public function sendPasswordReset(string $email, string $name, string $resetLink): bool {
        $subject = "Password Reset Request - YatraNest";
        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
            <h2 style='color: #0d233a;'>Reset Your Password</h2>
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>We received a request to reset your YatraNest account password. Click the button below to set a new password. This link is valid for 1 hour.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . htmlspecialchars($resetLink) . "' style='background: #008080; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a>
            </div>
            <p style='font-size: 13px; color: #64748b;'>If you did not request this password reset, please ignore this email.</p>
        </div>";

        return $this->send($email, $name, $subject, $html);
    }
}
