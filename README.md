# YatraNest - Production-Ready Tour & Travel Booking Platform

A full-stack, enterprise-grade Tour & Travel Booking Website built with **Core PHP 8.4+ MVC**, **MySQL 8+ / MariaDB**, **Bootstrap 5.3**, **PHPMailer**, and **Razorpay Payment Gateway**.

Designed for high conversion, responsive mobile-first UX, concurrent seat reservation protection with temporary holds, dynamic tax/coupon computation, cryptographic payment signature validation, and automated tax invoice generation.

---

## 🌟 Key Features

### 1. Modern Travel Discovery Frontend
- **Hero Search Widget**: Filter tours by destination, travel style/category, duration, and guest count.
- **Top Destinations Showcase**: Grid of popular Indian (Kashmir, Kerala, Himachal, Goa, Rajasthan) and International destinations (Dubai, Bali, Switzerland) with tour counters and rich imagery.
- **Tour Packages Catalogue**: Filter by budget slider, duration (weekend, popular, grand tour), travel categories (Adventure, Honeymoon, Family, Pilgrimage, Luxury), and sorting by popularity, price, rating, or newest.
- **Detailed Package Itineraries**: Day-wise interactive accordions, inclusions/exclusions, accommodation details, cancellation policies, photo gallery, verified customer reviews, and a sticky booking widget.

### 2. Robust Seat Inventory & Concurrency Management
- **15-Minute Temporary Holds**: Locks seat inventory while a customer enters traveler details and completes checkout, preventing concurrent race conditions.
- **Row-Level Transaction Locking**: Utilizes PDO transactions with `SELECT ... FOR UPDATE` on `tour_departures` to prevent overbooking.
- **Automatic Expired Hold Pruning**: Prunes abandoned holds during availability checks.
- **Overbooking Exception Workflow**: In the rare event a payment callback arrives after hold expiration and seats were exhausted, the booking is flagged for administrative review without overselling.

### 3. Transparent Dynamic Pricing & Coupons
- Server-side formula: `Subtotal = (Adult Price × Adults) + (Child Price × Children) + (Infant Price × Infants)`.
- Promotional Coupons: Percentage and flat discounts (e.g. `WELCOME10`, `FLAT1000`, `EARLYBIRD`) with minimum spend rules, max discount caps, and per-user redemption tracking.
- Configurable GST/VAT Tax Rate (e.g., 5.00% GST) loaded dynamically from database settings.

### 4. Dual Payment Options & Razorpay Integration
- **Razorpay Online Checkout**: Real test-mode integration with server-side order generation and client-side modal.
- **Cryptographic Signature Verification**: Validates signatures on the server using `hash_hmac('sha256', order_id . '|' . payment_id, key_secret)`.
- **Webhook Endpoint**: Verifies raw payload webhook signatures idempotently to avoid duplicate captures.
- **Pay Later / At Office**: Creates an unconfirmed reservation allowing customers to pay via bank transfer or at branch offices.

### 5. Secure Admin Operations Dashboard (`/admin`)
- **Visual Analytics**: Interactive Chart.js graphs for 6-month revenue trends and booking status breakdown.
- **Real-Time Counters**: Total revenue, confirmed bookings, pending bookings, active tour packages, customer counts, and refunds.
- **Tour Package Management**: Full CRUD, image uploads, day-by-day itinerary builder, and SEO metadata.
- **Departure & Seat Inventory**: Create departure dates, set capacity, manage seat availability, block dates.
- **Booking Lifecycle**: Filter by status, confirm pay-later reservations, cancel with reasons, process refunds, and export bookings to CSV.
- **Coupon Manager**: Create discount codes with date limits and usage caps.
- **Customer Manager**: Manage customer accounts and toggle active/suspended statuses without exposing sensitive credentials.
- **Site Settings**: Tax rates, company details, Razorpay credentials, and SMTP settings.
- **Audit Logs**: Immutable log of security and administrative actions.

### 6. Customer Dashboard & Invoicing
- Manage profile and change password.
- View upcoming and past trips with status badges.
- Download or print computer-generated **Tax Invoices** with GSTIN, traveler roster, and payment stamps.
- Cancel bookings in accordance with cancellation policy rules.
- Submit ratings and reviews for completed tours.

---

## 🚀 Installation & Local Setup

### Prerequisites
- PHP 8.2 or higher (PHP 8.4 recommended) with `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`.
- MySQL 8.0+ or MariaDB 10.4+.
- Composer (or bundled `vendor/` autoloading).

### 1. Database Setup
Ensure MySQL is running (e.g., in XAMPP or native service).
Create the database and import the schema:
```bash
# Using MySQL CLI
mysql -u root -e "CREATE DATABASE IF NOT EXISTS yatranest_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root yatranest_db < database/database.sql
```

### 2. Populate Demo Data
Run the database seeder to create sample destinations, packages, itineraries, departures, coupons, and demo accounts:
```bash
php database/seed.php
```

### 3. Configure Environment Variables
Copy `.env.example` to `.env` and configure your credentials:
```bash
cp .env.example .env
```
Key `.env` variables:
```ini
APP_NAME="YatraNest - Tour & Travel Booking"
APP_ENV=development
APP_DEBUG=true
APP_URL="http://localhost:8000"
APP_TIMEZONE="Asia/Kolkata"

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=yatranest_db
DB_USER=root
DB_PASS=

# Razorpay Test Mode Credentials
RAZORPAY_KEY_ID=rzp_test_sampleKey12345
RAZORPAY_KEY_SECRET=rzp_test_secretSample67890
RAZORPAY_WEBHOOK_SECRET=whsec_sampleRazorpayWebhookSecret
RAZORPAY_MODE=test

# Email Setup (SMTP)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

### 4. Start Development Web Server
Run PHP's built-in development server pointing to the `public/` directory:
```bash
php -S localhost:8000 -t public
```
Visit `http://localhost:8000` in your web browser.

---

## 🔑 Default Demo Accounts

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@yatranest.com` | `Admin@12345` | Full administrative & financial access |
| **Booking Manager** | `manager@yatranest.com` | `Manager@12345` | Manage departures, seats, and bookings |
| **Customer** | `customer@example.com` | `Customer@12345` | View trips, cancel, download invoices |

---

## 🧪 Automated QA Test Suite

The project includes an end-to-end automated CLI test suite covering 32 validation scenarios:
```bash
php tests/run_tests.php
```

Tested modules:
1. Database row population & foreign key integrity.
2. Pricing calculations (Adults, Children, Infants, GST 5%).
3. Coupon validation (Percentage vs Flat, minimum booking amount, usage caps).
4. Concurrency: 15-minute seat holds and multi-session inventory decrement.
5. Overbooking prevention (Attempting to book beyond capacity is rejected).
6. Cryptographic Razorpay payment signature & webhook HMAC-SHA256 verification.
7. Cancellation and automatic seat inventory restitution.
8. Tax Invoice HTML generation and GSTIN validation.
9. Security audits: XSS escaping `e()` and CSRF token validation.

---

## 🌐 Deployment Instructions (Hostinger, cPanel & Apache)

### Hostinger / cPanel Shared Hosting
1. **Upload Files**: Upload the project folder to your hosting account.
2. **Web Root Configuration**:
   - In cPanel or Hostinger htdocs, point the domain Document Root to the `/public` folder.
   - Alternatively, if your shared hosting restricts pointing Document Root outside `public_html`, the repository includes a root `index.php` and root `.htaccess` which automatically bridges requests to `/public/`.
3. **Database Configuration**:
   - Create a MySQL database and user via cPanel / Hostinger MySQL Wizard.
   - Import `database/database.sql`.
   - Update `DB_NAME`, `DB_USER`, and `DB_PASS` in your `.env` file.
4. **Permissions**:
   - Ensure `storage/logs/`, `storage/invoices/`, and `public/uploads/` are writable (`chmod 755` or `775`).
5. **SSL & HTTPS**:
   - Enable Free Let's Encrypt SSL in your hosting dashboard.
6. **Razorpay Live Configuration**:
   - Enter your Live Razorpay Key ID and Secret in `/admin/settings` or in `.env`.
   - Add your Webhook URL in Razorpay Dashboard: `https://yourdomain.com/api/webhook/razorpay`.

---

## 🛡️ Security Architecture
- **Prepared Statements**: 100% of database queries use PDO prepared statements with parameter binding.
- **CSRF Protection**: Cryptographic CSRF tokens embedded in all state-changing forms and validated via `CsrfMiddleware`.
- **Output Escaping**: All dynamic user inputs are sanitized with `e()` (HTML entities UTF-8).
- **Session Security**: Session fixation defense via `session_regenerate_id(true)`, `httponly`, and `SameSite=Lax`.
- **Rate Limiting**: Failed login attempts are throttled after 5 consecutive failures.
- **Access Control**: Role-based access control protecting administrative routes (`super_admin`, `booking_manager`, `content_manager`).
