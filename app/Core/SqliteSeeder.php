<?php

namespace App\Core;

use PDO;

class SqliteSeeder {
    /**
     * Register MySQL-compatible functions into SQLite.
     */
    public static function registerFunctions(PDO $pdo): void {
        $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
        $pdo->sqliteCreateFunction('DATE_FORMAT', function($date, $format) {
            if (!$date) return null;
            $time = is_numeric($date) ? (int)$date : strtotime((string)$date);
            if ($time === false) return null;
            $formatMap = [
                '%b' => 'M', '%B' => 'F', '%Y' => 'Y', '%y' => 'y',
                '%m' => 'm', '%c' => 'n', '%d' => 'd', '%e' => 'j',
                '%H' => 'H', '%h' => 'h', '%I' => 'h', '%i' => 'i',
                '%s' => 's', '%S' => 's', '%p' => 'A', '%a' => 'D',
                '%W' => 'l'
            ];
            return date(strtr($format, $formatMap), $time);
        });
        $pdo->sqliteCreateFunction('YEAR', function($date) {
            if (!$date) return null;
            $time = is_numeric($date) ? (int)$date : strtotime((string)$date);
            return $time !== false ? (int)date('Y', $time) : null;
        });
        $pdo->sqliteCreateFunction('MONTH', function($date) {
            if (!$date) return null;
            $time = is_numeric($date) ? (int)$date : strtotime((string)$date);
            return $time !== false ? (int)date('m', $time) : null;
        });
        $pdo->sqliteCreateFunction('DAY', function($date) {
            if (!$date) return null;
            $time = is_numeric($date) ? (int)$date : strtotime((string)$date);
            return $time !== false ? (int)date('d', $time) : null;
        });
        $pdo->sqliteCreateFunction('CONCAT', fn(...$args) => implode('', $args));
    }

    /**
     * Self-healing migration for existing SQLite databases with mismatched or outdated schemas.
     */
    public static function checkAndRepairSchema(PDO $pdo): void {
        self::registerFunctions($pdo);

        try {
            // 1. Repair `bookings` table
            $bCols = array_column($pdo->query("PRAGMA table_info(bookings)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($bCols) && !in_array('booking_status', $bCols, true)) {
                $pdo->exec("CREATE TABLE bookings_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    booking_reference TEXT NOT NULL UNIQUE,
                    customer_id INTEGER NOT NULL,
                    package_id INTEGER NOT NULL,
                    departure_id INTEGER NOT NULL,
                    adult_count INTEGER NOT NULL DEFAULT 1,
                    child_count INTEGER NOT NULL DEFAULT 0,
                    infant_count INTEGER NOT NULL DEFAULT 0,
                    subtotal NUMERIC NOT NULL,
                    discount_amount NUMERIC NOT NULL DEFAULT 0.00,
                    tax_rate NUMERIC NOT NULL DEFAULT 5.00,
                    tax_amount NUMERIC NOT NULL DEFAULT 0.00,
                    total_amount NUMERIC NOT NULL,
                    payment_status TEXT NOT NULL DEFAULT 'unpaid',
                    booking_status TEXT NOT NULL DEFAULT 'pending',
                    payment_method TEXT NOT NULL DEFAULT 'razorpay',
                    pickup_location TEXT,
                    special_requests TEXT,
                    coupon_id INTEGER,
                    internal_notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // Copy over existing records if possible
                if (in_array('user_id', $bCols, true) && in_array('grand_total', $bCols, true)) {
                    $pdo->exec("INSERT INTO bookings_new (
                        id, booking_reference, customer_id, package_id, departure_id,
                        adult_count, child_count, infant_count, subtotal, discount_amount,
                        tax_amount, total_amount, booking_status, internal_notes, created_at, updated_at
                    ) SELECT
                        id, booking_reference, user_id, package_id, departure_id,
                        adult_count, child_count, infant_count, base_fare, discount_amount,
                        tax_amount, grand_total, status, notes, created_at, updated_at
                    FROM bookings");
                }

                $pdo->exec("DROP TABLE bookings");
                $pdo->exec("ALTER TABLE bookings_new RENAME TO bookings");
            }

            // 2. Repair `tour_departures` columns
            $depCols = array_column($pdo->query("PRAGMA table_info(tour_departures)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($depCols) && !in_array('adult_price', $depCols, true) && in_array('price_adult', $depCols, true)) {
                $pdo->exec("ALTER TABLE tour_departures RENAME COLUMN price_adult TO adult_price");
                $pdo->exec("ALTER TABLE tour_departures RENAME COLUMN price_child TO child_price");
            }

            // 3. Repair `booking_status_history`
            $histCols = array_column($pdo->query("PRAGMA table_info(booking_status_history)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($histCols) && !in_array('from_status', $histCols, true) && in_array('status_from', $histCols, true)) {
                $pdo->exec("ALTER TABLE booking_status_history RENAME COLUMN status_from TO from_status");
                $pdo->exec("ALTER TABLE booking_status_history RENAME COLUMN status_to TO to_status");
                if (in_array('remarks', $histCols, true)) {
                    $pdo->exec("ALTER TABLE booking_status_history RENAME COLUMN remarks TO note");
                }
            }

            // 4. Repair `payments`
            $payCols = array_column($pdo->query("PRAGMA table_info(payments)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($payCols) && !in_array('gateway', $payCols, true) && in_array('payment_method', $payCols, true)) {
                $pdo->exec("CREATE TABLE payments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    booking_id INTEGER NOT NULL,
                    transaction_reference TEXT NOT NULL UNIQUE,
                    gateway TEXT NOT NULL DEFAULT 'razorpay',
                    gateway_order_id TEXT,
                    gateway_payment_id TEXT,
                    gateway_signature TEXT,
                    amount NUMERIC NOT NULL,
                    currency TEXT NOT NULL DEFAULT 'INR',
                    status TEXT NOT NULL DEFAULT 'pending',
                    gateway_response TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                $pdo->exec("INSERT INTO payments_new (
                    id, booking_id, transaction_reference, gateway, gateway_order_id,
                    gateway_payment_id, gateway_signature, amount, currency, status, gateway_response, created_at, updated_at
                ) SELECT
                    id, booking_id, transaction_reference, payment_method, gateway_order_id,
                    gateway_payment_id, gateway_signature, amount, currency, status, raw_payload, created_at, updated_at
                FROM payments");

                $pdo->exec("DROP TABLE payments");
                $pdo->exec("ALTER TABLE payments_new RENAME TO payments");
            }

            // 5. Repair `refunds`
            $refCols = array_column($pdo->query("PRAGMA table_info(refunds)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($refCols) && !in_array('refund_reference', $refCols, true)) {
                $pdo->exec("ALTER TABLE refunds ADD COLUMN refund_reference TEXT");
                $pdo->exec("ALTER TABLE refunds ADD COLUMN gateway_refund_id TEXT");
                $pdo->exec("ALTER TABLE refunds ADD COLUMN created_by_user_id INTEGER");
                $pdo->exec("ALTER TABLE refunds ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP");
            }

            // 6. Repair `coupon_redemptions`
            $crCols = array_column($pdo->query("PRAGMA table_info(coupon_redemptions)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($crCols) && !in_array('discount_applied', $crCols, true) && in_array('discount_amount', $crCols, true)) {
                $pdo->exec("ALTER TABLE coupon_redemptions RENAME COLUMN discount_amount TO discount_applied");
                if (in_array('created_at', $crCols, true) && !in_array('redeemed_at', $crCols, true)) {
                    $pdo->exec("ALTER TABLE coupon_redemptions RENAME COLUMN created_at TO redeemed_at");
                }
            }

            // 7. Repair `tour_packages`
            $pkgCols = array_column($pdo->query("PRAGMA table_info(tour_packages)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($pkgCols) && !in_array('gallery_images', $pkgCols, true)) {
                $pdo->exec("ALTER TABLE tour_packages ADD COLUMN gallery_images TEXT");
            }

            // 8. Repair `booking_travelers`
            $trCols = array_column($pdo->query("PRAGMA table_info(booking_travelers)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($trCols) && !in_array('id_proof_type', $trCols, true)) {
                $pdo->exec("ALTER TABLE booking_travelers ADD COLUMN id_proof_type TEXT");
                $pdo->exec("ALTER TABLE booking_travelers ADD COLUMN id_proof_number TEXT");
            }

            // 9. Repair `reviews`
            $rvCols = array_column($pdo->query("PRAGMA table_info(reviews)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($rvCols) && !in_array('booking_id', $rvCols, true)) {
                $pdo->exec("ALTER TABLE reviews ADD COLUMN booking_id INTEGER");
            }

            // 10. Repair `newsletter_subscribers`
            $nlCols = array_column($pdo->query("PRAGMA table_info(newsletter_subscribers)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!empty($nlCols) && !in_array('is_active', $nlCols, true) && in_array('status', $nlCols, true)) {
                $pdo->exec("ALTER TABLE newsletter_subscribers RENAME COLUMN status TO is_active");
            }
        } catch (\Throwable $e) {
            Logger::error("SQLite auto-repair warning: " . $e->getMessage());
        }
    }

    public static function seed(PDO $pdo): void {
        self::registerFunctions($pdo);

        // 1. Create tables with 100% parity to database/database.sql
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                phone TEXT,
                password_hash TEXT NOT NULL,
                status TEXT DEFAULT 'active',
                avatar TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT
            )",
            "CREATE TABLE IF NOT EXISTS user_roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                role_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (user_id, role_id)
            )",
            "CREATE TABLE IF NOT EXISTS destinations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                country TEXT NOT NULL,
                state TEXT,
                city TEXT,
                description TEXT,
                image_url TEXT,
                is_featured INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS tour_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                icon TEXT DEFAULT 'bi-compass',
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS tour_packages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                destination_id INTEGER,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                summary TEXT,
                description TEXT,
                duration_days INTEGER NOT NULL DEFAULT 1,
                duration_nights INTEGER NOT NULL DEFAULT 0,
                price_adult NUMERIC NOT NULL,
                price_child NUMERIC DEFAULT 0,
                price_infant NUMERIC DEFAULT 0,
                featured_image TEXT,
                gallery_images TEXT,
                group_size_max INTEGER DEFAULT 20,
                inclusions TEXT,
                exclusions TEXT,
                cancellation_policy_text TEXT,
                hotel_details TEXT,
                is_featured INTEGER DEFAULT 0,
                is_domestic INTEGER DEFAULT 1,
                tour_type TEXT DEFAULT 'family',
                status TEXT DEFAULT 'published',
                seo_title TEXT,
                seo_meta_description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS tour_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                image_url TEXT NOT NULL,
                caption TEXT,
                sort_order INTEGER DEFAULT 0
            )",
            "CREATE TABLE IF NOT EXISTS tour_itineraries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                day_number INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT,
                meals_included TEXT,
                accommodation TEXT
            )",
            "CREATE TABLE IF NOT EXISTS tour_departures (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                departure_date DATE NOT NULL,
                return_date DATE NOT NULL,
                total_seats INTEGER NOT NULL DEFAULT 20,
                available_seats INTEGER NOT NULL DEFAULT 20,
                adult_price NUMERIC,
                child_price NUMERIC,
                status TEXT DEFAULT 'open',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS booking_holds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                departure_id INTEGER NOT NULL,
                session_id TEXT NOT NULL,
                user_id INTEGER,
                seats_held INTEGER NOT NULL DEFAULT 1,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS bookings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_reference TEXT NOT NULL UNIQUE,
                customer_id INTEGER NOT NULL,
                package_id INTEGER NOT NULL,
                departure_id INTEGER NOT NULL,
                adult_count INTEGER NOT NULL DEFAULT 1,
                child_count INTEGER NOT NULL DEFAULT 0,
                infant_count INTEGER NOT NULL DEFAULT 0,
                subtotal NUMERIC NOT NULL,
                discount_amount NUMERIC NOT NULL DEFAULT 0.00,
                tax_rate NUMERIC NOT NULL DEFAULT 5.00,
                tax_amount NUMERIC NOT NULL DEFAULT 0.00,
                total_amount NUMERIC NOT NULL,
                payment_status TEXT NOT NULL DEFAULT 'unpaid',
                booking_status TEXT NOT NULL DEFAULT 'pending',
                payment_method TEXT NOT NULL DEFAULT 'razorpay',
                pickup_location TEXT,
                special_requests TEXT,
                coupon_id INTEGER,
                internal_notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS booking_travelers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER NOT NULL,
                full_name TEXT NOT NULL,
                age INTEGER NOT NULL,
                gender TEXT NOT NULL,
                traveler_type TEXT NOT NULL DEFAULT 'adult',
                id_proof_type TEXT,
                id_proof_number TEXT
            )",
            "CREATE TABLE IF NOT EXISTS booking_status_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER NOT NULL,
                from_status TEXT,
                to_status TEXT NOT NULL,
                changed_by_user_id INTEGER,
                note TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                discount_type TEXT NOT NULL DEFAULT 'percentage',
                discount_value NUMERIC NOT NULL,
                min_booking_amount NUMERIC NOT NULL DEFAULT 0.00,
                max_discount NUMERIC,
                start_date DATE NOT NULL,
                expiry_date DATE NOT NULL,
                usage_limit INTEGER DEFAULT 100,
                per_user_limit INTEGER DEFAULT 1,
                used_count INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS coupon_redemptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                coupon_id INTEGER NOT NULL,
                booking_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                discount_applied NUMERIC NOT NULL,
                redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER NOT NULL,
                transaction_reference TEXT NOT NULL UNIQUE,
                gateway TEXT NOT NULL DEFAULT 'razorpay',
                gateway_order_id TEXT,
                gateway_payment_id TEXT,
                gateway_signature TEXT,
                amount NUMERIC NOT NULL,
                currency TEXT NOT NULL DEFAULT 'INR',
                status TEXT NOT NULL DEFAULT 'pending',
                gateway_response TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS payment_webhook_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_id TEXT NOT NULL UNIQUE,
                event_type TEXT NOT NULL,
                payload TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'processed',
                processed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS refunds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                booking_id INTEGER NOT NULL,
                payment_id INTEGER,
                refund_reference TEXT NOT NULL UNIQUE,
                amount NUMERIC NOT NULL,
                reason TEXT,
                status TEXT DEFAULT 'pending',
                gateway_refund_id TEXT,
                created_by_user_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                booking_id INTEGER,
                rating INTEGER NOT NULL DEFAULT 5,
                review_title TEXT,
                review_text TEXT NOT NULL,
                status TEXT DEFAULT 'approved',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS contact_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT,
                subject TEXT NOT NULL,
                message TEXT NOT NULL,
                status TEXT DEFAULT 'new',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                token TEXT NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS site_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT,
                setting_group TEXT DEFAULT 'general',
                description TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action TEXT NOT NULL,
                entity_type TEXT,
                entity_id INTEGER,
                old_values TEXT,
                new_values TEXT,
                ip_address TEXT,
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        ];

        foreach ($tables as $tSql) {
            $pdo->exec($tSql);
        }

        // Run repair in case tables already existed with partial old schemas
        self::checkAndRepairSchema($pdo);

        // Check if already seeded
        $existing = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        if ($existing > 0) {
            return;
        }

        // 2. Seed Roles
        $roles = [
            ['super_admin', 'Full administrative access and settings'],
            ['booking_manager', 'Manage tour departures, seats, and customer bookings'],
            ['content_manager', 'Manage destinations, packages, blogs, and reviews'],
            ['customer', 'Registered traveler customer account'],
        ];
        $rStmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        foreach ($roles as $r) {
            $rStmt->execute($r);
        }

        // 3. Seed Users
        $users = [
            ['Super Administrator', 'admin@yatranest.com', '+91 98765 00001', password_hash('Admin@12345', PASSWORD_DEFAULT), 'super_admin'],
            ['Booking Operations Lead', 'manager@yatranest.com', '+91 98765 00002', password_hash('Manager@12345', PASSWORD_DEFAULT), 'booking_manager'],
            ['Rahul Sharma', 'customer@example.com', '+91 98765 43210', password_hash('Customer@12345', PASSWORD_DEFAULT), 'customer'],
            ['Priya Patel', 'priya@example.com', '+91 98765 43211', password_hash('Customer@12345', PASSWORD_DEFAULT), 'customer'],
        ];
        $uStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, 'active')");
        $urStmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, (SELECT id FROM roles WHERE name = ? LIMIT 1))");
        foreach ($users as $u) {
            $uStmt->execute([$u[0], $u[1], $u[2], $u[3]]);
            $uid = (int)$pdo->lastInsertId();
            $urStmt->execute([$uid, $u[4]]);
        }

        // 4. Seed Categories
        $categories = [
            ['Adventure', 'adventure', 'Trekking, rafting, safaris, and thrill-seeking expeditions.', 'bi-compass'],
            ['Family Holiday', 'family-holiday', 'Comfortable, well-paced tours ideal for travelers of all ages.', 'bi-people'],
            ['Honeymoon Special', 'honeymoon-special', 'Romantic escapes, candle-light dinners, and scenic luxury stays.', 'bi-heart'],
            ['Pilgrimage & Spiritual', 'pilgrimage', 'Sacred shrines, temple tours, and serene spiritual journeys.', 'bi-peace'],
            ['Weekend Getaway', 'weekend-getaway', 'Quick refreshing short escapes from city life.', 'bi-calendar-check'],
            ['Luxury & Heritage', 'luxury-heritage', 'Palaces, five-star resorts, and curated bespoke experiences.', 'bi-gem'],
        ];
        $cStmt = $pdo->prepare("INSERT INTO tour_categories (name, slug, description, icon, is_active) VALUES (?, ?, ?, ?, 1)");
        foreach ($categories as $c) {
            $cStmt->execute($c);
        }

        // 5. Seed Destinations
        $destinations = [
            ['Kashmir', 'kashmir', 'India', 'Jammu & Kashmir', 'Srinagar', 'Paradise on Earth with snow peaks and tranquil Dal Lake houseboats.', 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1200&q=80', 1],
            ['Kerala', 'kerala', 'India', 'Kerala', 'Kochi & Alleppey', "God's Own Country with serene backwaters, tea plantations, and beaches.", 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80', 1],
            ['Himachal Pradesh', 'himachal-pradesh', 'India', 'Himachal Pradesh', 'Manali & Shimla', 'Mighty Himalayan heights, lush pine valleys, and adventure trails.', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80', 1],
            ['Goa', 'goa', 'India', 'Goa', 'Panaji & Calangute', 'Sun-kissed Arabian sea beaches, Portuguese architecture, and nightlife.', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1200&q=80', 1],
            ['Rajasthan', 'rajasthan', 'India', 'Rajasthan', 'Jaipur & Udaipur', 'Land of kings, historic forts, grand royal palaces, and desert safaris.', 'https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1200&q=80', 1],
            ['Dubai', 'dubai', 'United Arab Emirates', 'Dubai', 'Dubai', 'A futuristic global oasis with towering skyscrapers, luxury shopping, and desert dunes.', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80', 1],
            ['Bali', 'bali', 'Indonesia', 'Bali', 'Denpasar & Ubud', 'Tropical paradise with lush emerald rice terraces and sea cliffs.', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80', 1],
            ['Switzerland', 'switzerland', 'Switzerland', 'Zurich & Lucerne', 'Lucerne', 'Alpine grandeur with turquoise lakes, snow-clad Alps, and scenic trains.', 'https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99?auto=format&fit=crop&w=1200&q=80', 1],
        ];
        $dStmt = $pdo->prepare("INSERT INTO destinations (name, slug, country, state, city, description, image_url, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        foreach ($destinations as $d) {
            $dStmt->execute($d);
        }

        // 6. Seed Tour Packages
        $packages = [
            [
                'cat' => 'family-holiday', 'dest' => 'kashmir',
                'title' => 'Kashmir Paradise: Srinagar, Gulmarg & Pahalgam',
                'slug' => 'kashmir-paradise-srinagar-gulmarg-pahalgam',
                'summary' => 'Experience the magic of Dal Lake Shikara ride, Gulmarg snow gondola, and Pahalgam valley.',
                'desc' => "Immerse yourself in Kashmir's natural majesty. 6-day package covers Srinagar Mughal Gardens, Dal Lake houseboat, and Gulmarg Gondola.",
                'days' => 6, 'nights' => 5, 'price' => 24999.00, 'cprice' => 14999.00,
                'img' => 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 5 Nights Premium Accommodation\n• Daily Buffet Breakfast & Dinner\n• All transfers in private AC vehicle\n• 1-hour Shikara ride on Dal Lake",
                'exc' => "• Airfare or Train tickets\n• Gulmarg Gondola cable car tickets\n• Personal expenses & tips",
                'hotel' => 'Srinagar: Grand Mumtaz | Houseboat: Royal Dal | Pahalgam: Pine N Peak Resort',
                'type' => 'family', 'dom' => 1
            ],
            [
                'cat' => 'honeymoon-special', 'dest' => 'kerala',
                'title' => 'Magical Kerala: Backwaters, Munnar & Alleppey Houseboat',
                'slug' => 'magical-kerala-backwaters-munnar-alleppey',
                'summary' => 'Romantic mist-kissed Munnar tea gardens, spice plantations, and private luxury houseboat.',
                'desc' => "Discover the timeless romantic beauty of Kerala. Cascading waterfalls in Munnar to serene backwaters of Alleppey.",
                'days' => 5, 'nights' => 4, 'price' => 18500.00, 'cprice' => 9999.00,
                'img' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 4 Nights Accommodation\n• Daily Breakfast & Meals on Houseboat\n• Private AC vehicle for transfers",
                'exc' => "• Airfare / Train fare\n• Personal expenses",
                'hotel' => 'Munnar: Tea County Resort | Alleppey: Deluxe AC Cruise',
                'type' => 'honeymoon', 'dom' => 1
            ],
            [
                'cat' => 'adventure', 'dest' => 'himachal-pradesh',
                'title' => 'Himachal Highs: Shimla, Manali & Rohtang Pass',
                'slug' => 'himachal-highs-shimla-manali-rohtang',
                'summary' => 'Colonial charm in Shimla, adrenaline adventures in Solang Valley, and Himalayan views.',
                'desc' => "The quintessential mountain journey through Himachal Pradesh. Rohtang Pass, Solang Valley paragliding, Atal Tunnel.",
                'days' => 6, 'nights' => 5, 'price' => 16999.00, 'cprice' => 9500.00,
                'img' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 5 Nights Hotel Accommodation\n• Daily Buffet Breakfast & Dinner\n• Individual AC Cab\n• Solang Valley excursion",
                'exc' => "• Rohtang Pass NGT permits\n• Adventure activity fees",
                'hotel' => 'Shimla: The Grand White | Manali: Apple Country Resort',
                'type' => 'adventure', 'dom' => 1
            ],
            [
                'cat' => 'weekend-getaway', 'dest' => 'goa',
                'title' => 'Goa Beach Bliss: Sun, Sand & Heritage',
                'slug' => 'goa-beach-bliss-sun-sand-heritage',
                'summary' => 'Relax on Baga Beach, explore Old Goa UNESCO churches, and Mandovi river cruise.',
                'desc' => "Unwind in coastal vacation capital. Stay near lively beaches, explore Portuguese cathedrals, and sunset cruise.",
                'days' => 4, 'nights' => 3, 'price' => 12499.00, 'cprice' => 6500.00,
                'img' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 3 Nights Stay in 4-Star Beach Resort\n• Daily Buffet Breakfast\n• Guided sightseeing\n• 1-Hour Mandovi Sunset Cruise",
                'exc' => "• Water sports & nightclub entries",
                'hotel' => 'North Goa: Lemon Tree Amarante Beach Resort',
                'type' => 'weekend', 'dom' => 1
            ],
            [
                'cat' => 'luxury-heritage', 'dest' => 'rajasthan',
                'title' => 'Royal Rajasthan: Jaipur, Jodhpur & Udaipur Heritage',
                'slug' => 'royal-rajasthan-jaipur-jodhpur-udaipur',
                'summary' => 'Live like royalty across Rajasthan palaces, the Pink City, Mehrangarh Fort, and Lake Pichola.',
                'desc' => "Journey through the golden history of the Rajputs. Amer Fort, Blue City of Jodhpur, and City of Lakes Udaipur.",
                'days' => 7, 'nights' => 6, 'price' => 28999.00, 'cprice' => 16500.00,
                'img' => 'https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 6 Nights Heritage Accommodation\n• Daily Royal Breakfast & Dinner\n• Private Chauffeur AC Sedan\n• Boat ride on Lake Pichola",
                'exc' => "• Monument entry tickets & guide fees",
                'hotel' => 'Jaipur: Mandawa Haveli | Jodhpur: Ajit Bhawan | Udaipur: Trident',
                'type' => 'luxury', 'dom' => 1
            ],
            [
                'cat' => 'luxury-heritage', 'dest' => 'dubai',
                'title' => 'Dazzling Dubai: Desert Safari, Marina & Burj Khalifa',
                'slug' => 'dazzling-dubai-desert-safari-burj-khalifa',
                'summary' => '124th floor Burj Khalifa view, 4x4 dune bashing desert safari with BBQ, and Dubai Marina dhow cruise.',
                'desc' => "Experience the glitz and glamour of Dubai. Desert safari, dune bashing, luxury dhow cruise, and Burj Khalifa observation deck.",
                'days' => 5, 'nights' => 4, 'price' => 45000.00, 'cprice' => 28000.00,
                'img' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=80',
                'inc' => "• 4 Nights in 4-Star Downtown Hotel\n• Daily Buffet Breakfast\n• Burj Khalifa 124th Floor Ticket\n• 4x4 Desert Safari with BBQ Dinner\n• Marina Dhow Cruise with Dinner",
                'exc' => "• International Flights & Visa\n• Tourism Dirham Fee",
                'hotel' => 'Dubai: Millennium Central Downtown',
                'type' => 'luxury', 'dom' => 0
            ]
        ];

        $pStmt = $pdo->prepare("
            INSERT INTO tour_packages (
                category_id, destination_id, title, slug, summary, description,
                duration_days, duration_nights, price_adult, price_child, price_infant,
                featured_image, inclusions, exclusions, hotel_details,
                is_featured, is_domestic, tour_type, status, cancellation_policy_text
            ) VALUES (
                (SELECT id FROM tour_categories WHERE slug = ? LIMIT 1),
                (SELECT id FROM destinations WHERE slug = ? LIMIT 1),
                ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 1, ?, ?, 'published',
                'Cancellation 30+ days prior: 90% refund. 15-29 days: 50% refund. Within 14 days: No refund.'
            )
        ");

        $depStmt = $pdo->prepare("
            INSERT INTO tour_departures (package_id, departure_date, return_date, total_seats, available_seats, adult_price, child_price, status)
            VALUES (?, ?, ?, 20, 18, ?, ?, 'open')
        ");

        $itinStmt = $pdo->prepare("
            INSERT INTO tour_itineraries (package_id, day_number, title, description, meals_included, accommodation)
            VALUES (?, ?, ?, ?, 'Breakfast, Dinner', ?)
        ");

        foreach ($packages as $pkg) {
            $pStmt->execute([
                $pkg['cat'], $pkg['dest'], $pkg['title'], $pkg['slug'],
                $pkg['summary'], $pkg['desc'], $pkg['days'], $pkg['nights'],
                $pkg['price'], $pkg['cprice'], $pkg['img'], $pkg['inc'], $pkg['exc'],
                $pkg['hotel'], $pkg['dom'], $pkg['type']
            ]);
            $pid = (int)$pdo->lastInsertId();

            // Seed 3 upcoming departures
            for ($k = 1; $k <= 3; $k++) {
                $depDate = date('Y-m-d', strtotime("+" . ($k * 10) . " days"));
                $retDate = date('Y-m-d', strtotime("+" . ($k * 10 + $pkg['days']) . " days"));
                $depStmt->execute([$pid, $depDate, $retDate, $pkg['price'], $pkg['cprice']]);
            }

            // Seed sample itineraries
            for ($d = 1; $d <= $pkg['days']; $d++) {
                $itinStmt->execute([$pid, $d, "Day $d: Scenic Discovery & Local Sightseeing", "Experience curated highlights, guided monument exploration, and relaxed leisure time.", $pkg['hotel']]);
            }
        }

        // 7. Seed Coupons
        $coupons = [
            ['WELCOME10', 'percentage', 10.00, 10000.00, 2500.00, date('Y-m-d', strtotime('-1 month')), date('Y-m-d', strtotime('+6 months')), 500, 2, 12, 1],
            ['FLAT1000', 'fixed', 1000.00, 15000.00, 1000.00, date('Y-m-d', strtotime('-1 month')), date('Y-m-d', strtotime('+6 months')), 200, 1, 5, 1],
            ['EARLYBIRD', 'percentage', 15.00, 20000.00, 3500.00, date('Y-m-d', strtotime('-1 month')), date('Y-m-d', strtotime('+6 months')), 100, 1, 8, 1],
        ];
        $cpnStmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_booking_amount, max_discount, start_date, expiry_date, usage_limit, per_user_limit, used_count, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($coupons as $cp) {
            $cpnStmt->execute($cp);
        }

        // 8. Seed Site Settings
        $settings = [
            ['site_name', 'YatraNest', 'general', 'Website Brand Name'],
            ['site_tagline', 'Crafting Unforgettable Journeys Across The Globe', 'general', 'Tagline'],
            ['company_name', 'YatraNest Holidays Pvt Ltd', 'general', 'Company Name'],
            ['company_address', 'Suite 402, Travel Towers, MG Road, Bengaluru, Karnataka 560001, India', 'general', 'Address'],
            ['company_phone', '+91 98765 43210', 'general', 'Support Telephone'],
            ['company_email', 'support@yatranest.com', 'general', 'Contact Email'],
            ['company_gstin', '29ABCDE1234F1Z5', 'general', 'GST Number'],
            ['tax_rate', '5.00', 'booking', 'GST Tax Rate Percentage'],
            ['currency_code', 'INR', 'booking', 'Base Currency'],
            ['currency_symbol', '₹', 'booking', 'Currency Symbol'],
            ['hold_timeout_minutes', '15', 'booking', 'Seat Hold Expiration'],
            ['razorpay_mode', 'test', 'payment', 'Gateway Mode'],
            ['razorpay_key_id', 'rzp_test_sampleKey12345', 'payment', 'Razorpay Key ID'],
        ];
        $sStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group, description) VALUES (?, ?, ?, ?)");
        foreach ($settings as $st) {
            $sStmt->execute($st);
        }

        // 9. Seed Reviews
        $reviews = [
            ['kashmir-paradise-srinagar-gulmarg-pahalgam', 'customer@example.com', 5, 'Unbelievable experience in Kashmir!', 'Our trip with YatraNest was seamless from start to finish. The houseboat stay in Dal Lake was like a dream, and our driver was courteous.'],
            ['magical-kerala-backwaters-munnar-alleppey', 'priya@example.com', 5, 'Most romantic Kerala honeymoon trip', 'The Alleppey houseboat was clean and the freshly prepared Karimeen fish dinner was delicious. Munnar tea estates were breathtaking.'],
            ['himachal-highs-shimla-manali-rohtang', 'customer@example.com', 5, 'Thrilling adventure in Solang Valley', 'Loved the paragliding and the trip through Atal Tunnel. Good hotels, friendly guides, and delicious buffet breakfasts throughout.'],
            ['royal-rajasthan-jaipur-jodhpur-udaipur', 'priya@example.com', 5, 'Truly royal treatment across Rajasthan', 'The heritage properties selected by YatraNest made us feel like royalty. The sunset boat ride on Lake Pichola in Udaipur was magical.'],
            ['goa-beach-bliss-sun-sand-heritage', 'customer@example.com', 5, 'Relaxing and vibrant Goa holiday', 'The beach resort was fantastic and the private Mandovi river sunset cruise was unforgettable. Everything was punctual and hassle-free.'],
            ['dazzling-dubai-desert-safari-burj-khalifa', 'priya@example.com', 5, 'Glitz, thrill, and luxury in Dubai', 'Desert safari dune bashing followed by dinner under the stars was an adrenaline rush! The Burj Khalifa 124th-floor views were breathtaking.'],
        ];

        $rvStmt = $pdo->prepare("
            INSERT INTO reviews (package_id, user_id, rating, review_title, review_text, status)
            VALUES (
                (SELECT id FROM tour_packages WHERE slug = ? LIMIT 1),
                (SELECT id FROM users WHERE email = ? LIMIT 1),
                ?, ?, ?, 'approved'
            )
        ");
        foreach ($reviews as $rv) {
            $rvStmt->execute($rv);
        }

        // 10. Seed Demo Bookings & Payments for Dashboard Analytics
        $bStmt = $pdo->prepare("
            INSERT INTO bookings (
                booking_reference, customer_id, package_id, departure_id,
                adult_count, child_count, infant_count, subtotal, discount_amount,
                tax_rate, tax_amount, total_amount, payment_status, booking_status,
                payment_method, pickup_location, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $demoBookings = [
            ['YN-DEMO-001', 3, 1, 1, 2, 1, 0, 64997.00, 2500.00, 5.00, 3124.85, 65621.85, 'paid', 'confirmed', 'razorpay', 'Airport Terminal 2', date('Y-m-d H:i:s', strtotime('-5 days')), date('Y-m-d H:i:s', strtotime('-5 days'))],
            ['YN-DEMO-002', 4, 2, 4, 2, 0, 0, 37000.00, 1000.00, 5.00, 1800.00, 37800.00, 'paid', 'confirmed', 'razorpay', 'Kochi Airport', date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('-2 days'))],
            ['YN-DEMO-003', 3, 3, 7, 2, 0, 0, 33998.00, 0.00, 5.00, 1699.90, 35697.90, 'unpaid', 'pending', 'pay_later', 'Chandigarh Railway Station', date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-1 days'))],
        ];

        foreach ($demoBookings as $dbData) {
            $bStmt->execute($dbData);
            $bId = (int)$pdo->lastInsertId();

            if ($dbData[12] === 'paid') {
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (
                        booking_id, transaction_reference, gateway, gateway_order_id,
                        gateway_payment_id, amount, currency, status, created_at
                    ) VALUES (?, ?, 'razorpay', ?, ?, ?, 'INR', 'paid', ?)
                ");
                $payStmt->execute([
                    $bId,
                    'TXN-' . date('Ymd') . '-' . bin2hex(random_bytes(4)),
                    'order_' . bin2hex(random_bytes(6)),
                    'pay_' . bin2hex(random_bytes(6)),
                    $dbData[11],
                    $dbData[16]
                ]);
            }
        }
    }
}
