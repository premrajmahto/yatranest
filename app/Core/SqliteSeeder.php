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

        self::seedNorthEastPackages($pdo);
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
            ['company_address', 'Guwahati, Assam, India', 'general', 'Address'],
            ['company_phone', '+91 60005 95647', 'general', 'Support Telephone'],
            ['company_whatsapp', '+91 60005 95647', 'general', 'WhatsApp Support Phone'],
            ['company_email', 'yatranest18@gmail.com', 'general', 'Contact Email'],
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

    /**
     * Ensure North East tour packages and destinations are seeded into SQLite.
     */
    public static function seedNorthEastPackages(PDO $pdo): void {
        try {
            $destCheck = $pdo->prepare("SELECT id FROM destinations WHERE slug = ? LIMIT 1");
            $destInsert = $pdo->prepare("
                INSERT INTO destinations (name, slug, country, state, city, description, image_url, is_featured, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");

            $dests = [
                ['Meghalaya', 'meghalaya', 'India', 'Meghalaya', 'Shillong & Cherrapunji', 'Known as the Abode of the Clouds, Meghalaya mesmerizes with cascading waterfalls like Nohkalikai, bio-engineered living root bridges, and crystal-clear Dawki waters.', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80', 1],
                ['Assam', 'assam', 'India', 'Assam', 'Guwahati & Kaziranga', 'Land of the mighty Brahmaputra river, sacred Kamakhya Temple, sprawling aromatic tea gardens, and the world-renowned UNESCO Kaziranga National Park.', 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80', 1],
                ['Arunachal Pradesh', 'arunachal-pradesh', 'India', 'Arunachal Pradesh', 'Tawang, Dirang & Bomdila', 'The Land of Dawn-Lit Mountains captivates with high Himalayan mountain passes like Sela Pass (13,700 ft), Buddhist monasteries, and pristine alpine lakes.', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80', 1]
            ];

            foreach ($dests as $d) {
                $destCheck->execute([$d[1]]);
                if (!$destCheck->fetchColumn()) {
                    $destInsert->execute($d);
                }
            }

            $pkgCheck = $pdo->prepare("SELECT id FROM tour_packages WHERE slug = ? LIMIT 1");
            $pkgInsert = $pdo->prepare("
                INSERT INTO tour_packages (
                    category_id, destination_id, title, slug, summary, description,
                    duration_days, duration_nights, price_adult, price_child, price_infant,
                    featured_image, group_size_max, inclusions, exclusions,
                    cancellation_policy_text, hotel_details, is_featured, is_domestic,
                    tour_type, status
                ) VALUES (
                    (SELECT id FROM tour_categories WHERE slug = ? LIMIT 1),
                    (SELECT id FROM destinations WHERE slug = ? LIMIT 1),
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published'
                )
            ");

            $itinInsert = $pdo->prepare("
                INSERT INTO tour_itineraries (package_id, day_number, title, description, meals_included, accommodation)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $depInsert = $pdo->prepare("
                INSERT INTO tour_departures (package_id, departure_date, return_date, total_seats, available_seats, adult_price, child_price, status)
                VALUES (?, ?, ?, 16, 16, ?, ?, 'open')
            ");

            $nePackages = [
                [
                    'cat' => 'weekend-getaway',
                    'dest' => 'meghalaya',
                    'title' => '3 Nights 4 Days Shillong & Meghalaya Express (PH - NE 1)',
                    'slug' => 'ph-ne-1-3-nights-4-days-shillong',
                    'summary' => 'Explore the Scotland of the East with picturesque Umiam Lake, breathtaking Nohkalikai Falls in Cherrapunjee, Asia cleanest village Mawlynnong, and crystal-clear Dawki waters.',
                    'desc' => 'Explore the Scotland of the East with picturesque Umiam Lake, breathtaking Nohkalikai Falls in Cherrapunjee, Asia cleanest village Mawlynnong, and crystal-clear Dawki waters.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 4,
                    'nights' => 3,
                    'price' => 14999.0,
                    'cprice' => 8999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 3 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Shillong: Hotel Polo Towers / Center Point (3 Nights)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'family',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati Airport / Railway Station - Shillong (100 kms / 3 hrs.)',
                            'desc' => 'Welcome to Awesome Assam. Upon reaching Guwahati Airport or Railway Station, you will be transferred to Shillong, which is often referred to as the "Scotland of the East." The distance between Guwahati and Shillong is approximately 100 kilometers, and the journey takes around 3 hours. On the way, you will have the opportunity to visit Umiam Lake, a beautiful and serene lake surrounded by picturesque hills. Check in to your hotel and Overnight stay at Shillong',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Shillong - Cherrapunjee - Shillong (60 kms / 2 hrs per way.)',
                            'desc' => 'After having breakfast early in the morning, you will proceed towards Cherrapunjee, which holds the title of being the wettest place on Earth. During the visit, you will have the opportunity to witness the magnificent Nohkalikai waterfall. Visit  Eco Park, Nohkalikai Falls, Nohsngithiang Falls (Seven Sisters Falls), Mawsmai Cave, Thangkharang Park. Evening return to Shillong. Visit Elephanta Falls. Overnight stay in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Shillong - Mawlynnong - Dawki - Shillong (120 kms / 4 hrs per way)',
                            'desc' => 'After having breakfast early in the morning, you will embark on a drive to Mawlynnong, which is famously known as "Asia\'s Cleanest Village." Mawlynnong boasts various fascinating attractions, including the living root bridge and a peculiar natural phenomenon of a boulder delicately balanced on another small rock. Afterwards, you will proceed to Dawki, a small town situated near the India-Bangladesh border. Here, you can enjoy the awe-inspiring view of the Umangot River. In the evening, you will drive back to Shillong and spend the night at your hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong  -  Guwahati (100 kms / 3 hrs.)',
                            'desc' => 'After breakfast, drive back to Guwahati. Overnight stay at Guwahati or transfer to airport or railway station',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'family-holiday',
                    'dest' => 'meghalaya',
                    'title' => '4 Nights 5 Days Shillong & Guwahati Highlights (PH - NE 2)',
                    'slug' => 'ph-ne-2-4-nights-5-days-shillong-guwahati',
                    'summary' => 'Experience Shillong (3N) and Guwahati (1N): Cherrapunjee waterfalls, Asia cleanest village Mawlynnong, Dawki Umngot river, and Brahmaputra sunset cruise.',
                    'desc' => 'Experience Shillong (3N) and Guwahati (1N): Cherrapunjee waterfalls, Asia cleanest village Mawlynnong, Dawki Umngot river, and Brahmaputra sunset cruise.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 5,
                    'nights' => 4,
                    'price' => 18499.0,
                    'cprice' => 10999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 4 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Shillong: Hotel Polo Towers (3 Nights) | Guwahati: Hotel Dynasty / Vivanta (1 Night)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'family',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati Airport / Railway Station - Shillong (100 kms / 3 hrs)',
                            'desc' => 'Welcome to Awesome Assam. Upon reaching Guwahati Airport or Railway Station, you will be transported to Shillong, which is often referred to as the "Scotland of the East" due to its resemblance to the scenic beauty of Scotland. The distance between Guwahati and Shillong is approximately 100 kilometers, and the journey takes around 3 hours. On the way, you will have the opportunity to visit Umiam Lake, a magnificent and peaceful lake surrounded by lush green hills. Upon arrival in Shillong, you will check in to your hotel and spend the night there.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Shillong - Cherrapunjee - Shillong  (60 kms / 2 hrs per way.)',
                            'desc' => 'Following an early morning meal, you will proceed on a drive to Cherrapunjee, known as the wettest place on Earth, situated at an altitude of 4,400 feet. There, you will have the opportunity to witness the captivating Nohkalikai waterfall. Visit  Eco Park, Nohkalikai Falls, Nohsngithiang Falls (Seven Sisters Falls), Mawsmai Cave, Thangkharang Park. Evening return to Shillong. Visit Elephanta Falls. Overnight stay in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Shillong - Mawlynnong - Dawki - Shillong (120 kms / 4 hrs per way)',
                            'desc' => 'After enjoying an early breakfast, you will embark on a journey to Mawlynnong, known as "Asia\'s Cleanest Village." Mawlynnong is renowned for its remarkable attractions, including the living root bridge and an intriguing natural phenomenon where a boulder precariously balances on a small rock. Afterward, you will continue your trip to Dawki, a small town located near the India-Bangladesh border. Here, you can revel in the breathtaking view of the Umangot River. In the evening, you will drive back to Shillong and spend the night at your hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong -  Guwahati (100 kms / 3 hrs.)',
                            'desc' => 'After breakfast visit Don Bosco Museum & Ward\'s Lake. Proceed to Guwahati. In the evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Guwahati',
                            'desc' => 'After having breakfast and checking out from the hotel, if time permits, we may opt for a tour of the Kamakhya Temple with cherished memories of our trip, we will then transfer you to Guwahati airport or railway station for your onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'adventure',
                    'dest' => 'meghalaya',
                    'title' => '5 Nights 6 Days Shillong & Cherrapunji Explorer (PH - NE 3)',
                    'slug' => 'ph-ne-3-5-nights-6-days-shillong-cherrapunji',
                    'summary' => 'Venture deep into Meghalaya: Laitlum Grand Canyons, Krang Suri Blue Falls, Mawsynram caves, Mawphlang Sacred Forest, Dawki, and Double Decker Living Root Bridge.',
                    'desc' => 'Venture deep into Meghalaya: Laitlum Grand Canyons, Krang Suri Blue Falls, Mawsynram caves, Mawphlang Sacred Forest, Dawki, and Double Decker Living Root Bridge.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 6,
                    'nights' => 5,
                    'price' => 22999.0,
                    'cprice' => 13499.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 5 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Shillong: Alpine Continental (3 Nights) | Cherrapunji: Polo Orchid Resort (2 Nights)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'adventure',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati - Shillong  (98 KMS/3 HRS)',
                            'desc' => 'Arrival at Guwahati Airport/Station and drive to Shillong. En route witness the Umiam Lake or Barapani - is the biggest artificial lake in Meghalaya in the midst of sylvan hills adorned by Khasi pines & peerless beauty.Check-in Hotel and after refreshment if time permits visit Cathedral of Mary and Ward\'s Lake. Evening free at leisure to explore the region on my own. You may take a walk to the famous Police Bazaar for shopping. Overnight in Shillong.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Shillong - Laitlum Canyons -Krang Suri Falls - Shillong (90 KM | 2 Hrs Per Way)',
                            'desc' => 'Following breakfast, we will make our way to Laitlum Canyons, where you can experience awe-inspiring views. Later, we will visit Krang Suri Falls, which is regarded as one of the finest waterfalls in Meghalaya. Here, you will have the opportunity to swim in a natural pool and witness the waterfall from behind, as the water cascades in front of you. After enjoying lunch, we will return to Shillong. During the remainder of the day, we will visit various attractions including the Don Bosco Centre for Indigenous Cultures, Ward\'s Lake (where boating can be enjoyed), the Cathedral Catholic Church, and Lady Hydari Park. We will spend the night in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Shillong - Mawsynram - Mawphlang Sacred Forest - Shillong ( 60 KM | 1.5 Hrs Per Way)',
                            'desc' => 'After having breakfast, we will head towards Mawsynram, which has recently been identified as the place receiving the highest rainfall in the world according to geological reports. The region is renowned for its numerous caves. Following that, we will visit Mawphlang Sacred Grove, one of the largest sacred groves in Meghalaya. It is a well-preserved forest protected by the local community and governed by the local deities, prohibiting anything from being taken out of the forest. We will also visit a Khasi model village. In the evening, we will return to Shillong and visit Elephanta Falls. We will spend the night in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong - Dawki - Mawlynnong Village - Cherrapunji ( 90 KM | 2 Hrs Per Way)',
                            'desc' => 'After enjoying breakfast, we will visit Mawlynnong Village, which is recognized as the cleanest village in India. This charming and vibrant village is renowned for its exceptional cleanliness. It is located approximately 90 kilometers from Shillong and offers various fascinating attractions, including the Living Root Bridge and the peculiar natural phenomenon of a boulder balancing on another rock. Following that, we will proceed to Dawki, a place situated along the Indo-Bangladesh border. Here, you will have the opportunity to indulge in boating in the crystal clear waters of the Umgnot River. In the evening, we will continue our journey to Cherrapunji. We will spend the night in Cherrapunji.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Cherrapunji',
                            'desc' => 'Rise early today to fully immerse yourself in the captivating mornings of Cherrapunji. Explore various attractions including Eco Park, Nohkalikai Falls, Nohsngithiang Falls (Seven Sisters Falls), Mawsmai Cave, and Thangkharang Park. After a day filled with exploration, spend the night in Cherrapunji.

Optional - You have the option to embark on a full day trek to the Double Decker Living Root Bridge at Nongriat Village. The trek involves descending approximately 3,200 steps each way to reach the Double Decker Living Root Bridge. For those seeking further adventure, you can continue trekking to the Rainbow Falls, which takes about 60 to 90 minutes from the Double Decker Living Root Bridge.

Overnight stay in cherrapunji',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Cherrapunjee'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Cherrapunji to Guwahati (160 KM | 4.5 HRS)',
                            'desc' => 'Following breakfast, we will make our way to Guwahati. Enroute, we will visit the renowned Kamakhya Temple, known for its religious significance. Cherishing the unforgettable memories of our journey, we will then transfer you to the airport or railway station for your onward travel arrangements.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'family-holiday',
                    'dest' => 'meghalaya',
                    'title' => '6 Nights 7 Days Shillong, Cherrapunji & Guwahati (PH - NE 4)',
                    'slug' => 'ph-ne-4-6-nights-7-days-shillong-cherrapunji-guwahati',
                    'summary' => 'The complete Meghalaya & Assam holiday: Shillong (3N), Cherrapunjee (2N), and Guwahati (1N) covering Laitlum, Krang Suri, Double Decker Root Bridge, Dawki, and Kamakhya.',
                    'desc' => 'The complete Meghalaya & Assam holiday: Shillong (3N), Cherrapunjee (2N), and Guwahati (1N) covering Laitlum, Krang Suri, Double Decker Root Bridge, Dawki, and Kamakhya.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 7,
                    'nights' => 6,
                    'price' => 26999.0,
                    'cprice' => 15999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 6 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Shillong: Center Point (3 Nights) | Cherrapunji: Cherrapunjee Holiday Resort (2 Nights) | Guwahati: Hotel Kiranshree (1 Night)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'family',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati arrival and transfers to Shillong  (98 KMS/3 HRS)',
                            'desc' => 'Arrival at Guwahati Airport/Station and drive to Shillong. En route witness the Umiam Lake or Barapani - is the biggest artificial lake in Meghalaya in the midst of sylvan hills adorned by Khasi pines & peerless beauty.Check-in Hotel and after refreshment if time permits visit Cathedral of Mary and Ward\'s Lake. Evening free at leisure to explore the region on my own. You may take a walk to the famous Police Bazaar for shopping. Overnight in Shillong.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Shillong - Laitlum Canyons -Krang Suri Falls - Shillong (90 KM | 2 Hrs Per Way)',
                            'desc' => 'Following breakfast, we will make our way to Laitlum Canyons, where you can experience awe-inspiring views. Later, we will visit Krang Suri Falls, which is regarded as one of the finest waterfalls in Meghalaya. Here, you will have the opportunity to swim in a natural pool and witness the waterfall from behind, as the water cascades in front of you. After enjoying lunch, we will return to Shillong. During the remainder of the day, we will visit various attractions including the Don Bosco Centre for Indigenous Cultures, Ward\'s Lake (where boating can be enjoyed), the Cathedral Catholic Church, and Lady Hydari Park. We will spend the night in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Shillong - Mawsynram - Mawphlang Sacred Forest - Shillong ( 60 KM | 1.5 Hrs Per Way)',
                            'desc' => 'After having breakfast, we will head towards Mawsynram, which has recently been identified as the place receiving the highest rainfall in the world according to geological reports. The region is renowned for its numerous caves. Following that, we will visit Mawphlang Sacred Grove, one of the largest sacred groves in Meghalaya. It is a well-preserved forest protected by the local community and governed by the local deities, prohibiting anything from being taken out of the forest. We will also visit a Khasi model village. In the evening, we will return to Shillong and visit Elephanta Falls. We will spend the night in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong - Dawki - Mawlynnong Village - Cherrapunji ( 90 KM | 2 Hrs Per Way)',
                            'desc' => 'After enjoying breakfast, we will visit Mawlynnong Village, which is recognized as the cleanest village in India. This charming and vibrant village is renowned for its exceptional cleanliness. It is located approximately 90 kilometers from Shillong and offers various fascinating attractions, including the Living Root Bridge and the peculiar natural phenomenon of a boulder balancing on another rock. Following that, we will proceed to Dawki, a place situated along the Indo-Bangladesh border. Here, you will have the opportunity to indulge in boating in the crystal clear waters of the Umgnot River. In the evening, we will continue our journey to Cherrapunji. We will spend the night in Cherrapunji.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Cherrapunji',
                            'desc' => 'Rise early today to fully immerse yourself in the captivating mornings of Cherrapunji. Explore various attractions including Eco Park, Nohkalikai Falls, Nohsngithiang Falls (Seven Sisters Falls), Mawsmai Cave, and Thangkharang Park. After a day filled with exploration, spend the night in Cherrapunji.

Optional - You have the option to embark on a full day trek to the Double Decker Living Root Bridge at Nongriat Village. The trek involves descending approximately 3,500 steps each way to reach the Double Decker Living Root Bridge. For those seeking further adventure, you can continue trekking to the Rainbow Falls, which takes about 60 to 90 minutes from the Double Decker Living Root Bridge.

Overnight stay in cherrapunji',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Cherrapunjee'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Cherrapunji to Guwahati (160 KM | 4.5 HRS)',
                            'desc' => 'Following breakfast, we will make our way to Guwahati. Take rest and in the evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Cherrapunjee'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Guwahati',
                            'desc' => 'After having breakfast and checking out from the hotel, if time permits, we may opt for a tour of the Kamakhya Temple and we will then transfer you to Guwahati airport or railway station for your onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'adventure',
                    'dest' => 'assam',
                    'title' => '5 Nights 6 Days Kaziranga Wildlife & Shillong (PH - NE 5)',
                    'slug' => 'ph-ne-5-5-nights-6-days-kaziranga-shillong',
                    'summary' => 'Thrilling UNESCO Kaziranga Rhinoceros safaris paired with the cascading waterfalls, living root bridges, and picturesque lakes of Shillong and Cherrapunjee.',
                    'desc' => 'Thrilling UNESCO Kaziranga Rhinoceros safaris paired with the cascading waterfalls, living root bridges, and picturesque lakes of Shillong and Cherrapunjee.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 6,
                    'nights' => 5,
                    'price' => 24499.0,
                    'cprice' => 14499.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 5 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• 1 Elephant Safari and 1 Jeep Safari inside Kaziranga National Park',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Kaziranga: IORA - The Retreat / Borgos (2 Nights) | Shillong: Hotel Polo Towers (3 Nights)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'adventure',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati - Kaziranga National Park (230 KM | 4.5 Hrs)',
                            'desc' => 'Welcome to Awesome Assam. Upon arrival at Guwahati Airport/Railway Station, meet your guide/driver. Proceed Kaziranga National Park. Enjoy the scenic drive through the beautiful landscapes of Assam. Reach Kaziranga National Park. Check into your hotel/resort. Evening you may visit Orchid Park and the nearby Tea Plantations. Overnight stay at Kaziranga National Park',
                            'meals' => 'Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Kaziranga National Park',
                            'desc' => 'Experience the wonders of Kaziranga National Park with an early morning elephant safari, allowing you to explore the park\'s diverse wildlife. Besides the endangered One-Horn Indian Rhinoceros, the park boasts a significant population of genetically pure Wild Water Buffaloes, over 1000 Wild elephants, and a dense population of Royal Bengal Tigers. Bird enthusiasts will delight in spotting the park\'s 500 species of birds, including the Crested Serpent Eagle, Palla\'s Fishing Eagle, Greyheaded Fishing Eagle, and many more. After the safari, return to the resort for breakfast. In the afternoon, enjoy a thrilling jeep safari, further immersing yourself in the park\'s natural wonders. As the day concludes, return to the hotel for a comfortable overnight stay within the Kaziranga National Park.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Kaziranga National Park to Shillong (280 Km | 6 Hrs)',
                            'desc' => 'Start your day with breakfast, and then embark on a drive towards Shillong, a city often referred to as the \'Scotland of the East.\' As you make your way, you\'ll reach the magnificent Umium Lake, also known as Barapani. Here, you have the option to participate in water sports activities if you desire. Upon reaching Shillong, check in at your designated hotel. In the evening, take the opportunity to explore the bustling Police Bazaar, the largest local market in the city. Enjoy the vibrant atmosphere and indulge in shopping or sampling local cuisine. Afterward, retire for the night at your accommodation in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong-Cherrapunjee-Shillong (65 km | 1.5 hrs per way)',
                            'desc' => 'After breakfast, embark on a day trip to Cherrapunjee (approximately 2-3 hours\' drive from Shillong). Visit the Seven Sisters Falls, Mawsmai Cave, and the Nohkalikai Falls, the tallest plunge waterfall in India, Eco Park, ,,  Thangkharang Park. Enjoy the breathtaking views of the landscape and the lush green valleys. Return to Shillong in the evening and relax at your hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Shillong-Dawki-Mawlynnong-Shillong ((90 km | 2 hrs per Way)',
                            'desc' => 'Embark on a one-day trip from Shillong to Dawki and Mawlynnong, starting early in the morning. Drive to Dawki and immerse yourself in the beauty of the crystal-clear Umngot River. Take a boat ride to witness the mesmerizing underwater world. After a delicious lunch in Dawki, continue your journey to Mawlynnong, known as the "Cleanest Village in Asia." Explore the village\'s well-maintained streets, visit the Living Root Bridge, and climb the Sky View Point for breathtaking panoramic views. Don\'t miss the fascinating Balancing Rock. After a day filled with natural wonders, return to Shillong in the evening and relax at the hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Shillong to Guwahati (100 km | 3 hrs)',
                            'desc' => 'After having breakfast, as per time permits, begin your day by visiting the Don Bosco Centre for Indigenous Cultures. This cultural center showcases the rich heritage and traditions of the indigenous people. Next, head to Ward\'s Lake, where you can take pleasure in boating activities on the serene lake. Don\'t miss the chance to visit the Cathedral Catholic Church, known for its architectural beauty. Continue your exploration by visiting Lady Hydari Park, a charming green space that offers a tranquil ambiance. Take a leisurely stroll and admire the natural beauty of the park. Later, proceed towards Guwahati, where your unforgettable journey comes to an end. You will be transferred to the airport or railway station for your onward travel, cherishing the everlasting memories created during your trip.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'family-holiday',
                    'dest' => 'assam',
                    'title' => '6 Nights 7 Days Kaziranga, Shillong & Guwahati (PH - NE 6)',
                    'slug' => 'ph-ne-6-6-nights-7-days-kaziranga-shillong-guwahati',
                    'summary' => 'The golden triangle of the North East: Kaziranga Elephant & Jeep Safari, Shillong, Cherrapunjee waterfalls, Dawki Umngot river, and Brahmaputra river cruise.',
                    'desc' => 'The golden triangle of the North East: Kaziranga Elephant & Jeep Safari, Shillong, Cherrapunjee waterfalls, Dawki Umngot river, and Brahmaputra river cruise.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 7,
                    'nights' => 6,
                    'price' => 28999.0,
                    'cprice' => 16999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 6 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• 1 Elephant Safari and 1 Jeep Safari inside Kaziranga National Park',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.',
                    'hotel' => 'Kaziranga: Borgos Resort (2 Nights) | Shillong: Polo Towers (3 Nights) | Guwahati: Vivanta Guwahati (1 Night)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'family',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati - Kaziranga National Park (230 KM | 4.5 Hrs)',
                            'desc' => 'Welcome to Awesome Assam. Upon arrival at Guwahati Airport/Railway Station, meet your guide/driver. Proceed Kaziranga National Park. Enjoy the scenic drive through the beautiful landscapes of Assam. Reach Kaziranga National Park. Check into your hotel/resort. Evening you may visit Orchid Park and the nearby Tea Plantations. Overnight stay at Kaziranga National Park',
                            'meals' => 'Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Kaziranga National Park',
                            'desc' => 'Experience the wonders of Kaziranga National Park with an early morning elephant safari, allowing you to explore the park\'s diverse wildlife. Besides the endangered One-Horn Indian Rhinoceros, the park boasts a significant population of genetically pure Wild Water Buffaloes, over 1000 Wild elephants, and a dense population of Royal Bengal Tigers. Bird enthusiasts will delight in spotting the park\'s 500 species of birds, including the Crested Serpent Eagle, Palla\'s Fishing Eagle, Greyheaded Fishing Eagle, and many more. After the safari, return to the resort for breakfast. In the afternoon, enjoy a thrilling jeep safari, further immersing yourself in the park\'s natural wonders. As the day concludes, return to the hotel for a comfortable overnight stay within the Kaziranga National Park.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Kaziranga National Park to Shillong (280 Km | 6 Hrs)',
                            'desc' => 'Start your day with breakfast, and then embark on a drive towards Shillong, a city often referred to as the \'Scotland of the East.\' As you make your way, you\'ll reach the magnificent Umium Lake, also known as Barapani. Here, you have the option to participate in water sports activities if you desire. Upon reaching Shillong, check in at your designated hotel. In the evening, take the opportunity to explore the bustling Police Bazaar, the largest local market in the city. Enjoy the vibrant atmosphere and indulge in shopping or sampling local cuisine. Afterward, retire for the night at your accommodation in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong-Cherrapunjee-Shillong (65 km | 1.5 hrs per way)',
                            'desc' => 'After breakfast, embark on a day trip to Cherrapunjee (approximately 2-3 hours\' drive from Shillong). Visit the Seven Sisters Falls, Mawsmai Cave, and the Nohkalikai Falls, the tallest plunge waterfall in India, Eco Park, ,,  Thangkharang Park. Enjoy the breathtaking views of the landscape and the lush green valleys. Return to Shillong in the evening and relax at your hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Shillong-Dawki-Mawlynnong-Shillong ((90 km | 2 hrs per Way)',
                            'desc' => 'Embark on a one-day trip from Shillong to Dawki and Mawlynnong, starting early in the morning. Drive to Dawki and immerse yourself in the beauty of the crystal-clear Umngot River. Take a boat ride to witness the mesmerizing underwater world. After a delicious lunch in Dawki, continue your journey to Mawlynnong, known as the "Cleanest Village in Asia." Explore the village\'s well-maintained streets, visit the Living Root Bridge, and climb the Sky View Point for breathtaking panoramic views. Don\'t miss the fascinating Balancing Rock. After a day filled with natural wonders, return to Shillong in the evening and relax at the hotel.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Shillong to Guwahati (100 km | 3 hrs)',
                            'desc' => 'After having breakfast, begin your day by visiting the Don Bosco Centre for Indigenous Cultures. This cultural center showcases the rich heritage and traditions of the indigenous people. Next, head to Ward\'s Lake, where you can take pleasure in boating activities on the serene lake. Don\'t miss the chance to visit the Cathedral Catholic Church, known for its architectural beauty. Continue your exploration by visiting Lady Hydari Park, a charming green space that offers a tranquil ambiance.Later, proceed towards Guwahati. Overnight stay at Guwahati',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Guwahati',
                            'desc' => 'After having breakfast and checking out from the hotel. Visit the Kamakhya Temple and we will then transfer you to Guwahati airport or railway station for your onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'adventure',
                    'dest' => 'arunachal-pradesh',
                    'title' => '6 Nights 7 Days Arunachal: Tezpur, Dirang, Tawang & Bomdila (PH - NE 7)',
                    'slug' => 'ph-ne-7-6-nights-7-days-arunachal-tawang-circuit',
                    'summary' => 'Ascend the Eastern Himalayas across Sela Pass (13,700 ft) to Tawang 400-year-old Monastery, Nuranang Falls, Dirang Kiwi orchards, and Bomdila.',
                    'desc' => 'Ascend the Eastern Himalayas across Sela Pass (13,700 ft) to Tawang 400-year-old Monastery, Nuranang Falls, Dirang Kiwi orchards, and Bomdila.

IMPORTANT: Inner Line Permit (ILP) is mandatory for this tour. Guests must submit 1 passport photograph and government ID proof.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 7,
                    'nights' => 6,
                    'price' => 31999.0,
                    'cprice' => 18999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 6 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• Arunachal Pradesh Inner Line Permit (ILP) documentation assistance',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance
• Government ILP application fees and local DC office permit charges',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.

Mandatory Documents for Arunachal Pradesh (ILP):
Inner Line Permit (ILP) is mandatory for all visitors to Arunachal Pradesh. Please submit 1 passport-size photo and 1 government-authorized photo ID (Passport / Aadhaar / Voter ID) at least 7 days prior to travel.',
                    'hotel' => 'Tezpur: Kanyapur (1N) | Dirang: Pemaling (1N) | Tawang: Hotel Yangzom (2N) | Bomdila: Tsepal Yangjom (1N) | Guwahati: Hotel Dynasty (1N)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'adventure',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati- Tezpur ( 183 km| 3 hr 30 min)',
                            'desc' => 'Welcome to Awesome Assam. Meet and be assisted by our representative at the airport/Railway station. Transfer to Tezpur. After reaching Tezpur, check into your accommodation and take some rest. In the afternoon, you can visit some popular attractions in Tezpur like Agnigarh Hill, Cole Park, Bamuni Hills, and Mahabhairav Temple. Enjoy the evening exploring the local markets and trying out local delicacies.Overnight stay in Tezpur.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Guwahati'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Tezpur - Dirang (187 Km | 4-5 Hrs)',
                            'desc' => 'After Breakfast Proceed to Dirang. On the way visit Orchid Research Centre Tipi, an orchidarium with about 10,000 orchid plants belonging to various genera and species. Arunachal has the largest range of orchids in the country. Continue your drive and do not forget to keep your cameras handy as you cross beautiful waterfalls and get some spectacular views. Enroute visit Nag Mandir. Arrive and check in to your hotel. After lunch visit the regional apple Nursery, kiwi farm, Sangti Valley. Overnight stay in Dirang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Dirang - Tawang (140 Km | 6 Hrs)',
                            'desc' => 'After having breakfast visit Dirang Monestry and proceed towards Tawang. On the way, make a stop at the war cemetery to pay respects. Resume your journey and reach Sela Pass, which stands at an impressive altitude of 4170 meters (13700 feet). This pass is renowned for its breathtaking lakes and scenic landscapes. Continue driving until you arrive at the Jaswant Garh War Memorial, a tribute to the courageous Indian soldier Jaswant Singh Rawat. Take some time to visit the Nuranang Falls, also known as Jang Falls. Finally, reach Tawang and check in at your hotel for an overnight stay.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Tawang',
                            'desc' => 'After breakfast, you have two options for the day. Option one is to visit Bum La Pass, which is the Indo-China border. To do so, you will need to obtain a permit from the DC office in Tawang on your own. You can also visit Sangestar Lake, also known as Madhuri Lake, and Pangateng Tso Lake, also called P.Tso Lake. Please note that for these visits, you\'ll need to hire a local taxi in Tawang and pay directly for the service, as our vehicles are not allowed in that area. Extra cost 5,500 INR per vehicle and 100 INR per ticketper person

Alternatively, you can choose option two, which includes visiting Tawang Monastery, also known as Namgey Lhatse, Singsor Ani Gompa, and Tawang War Memorial. Please note that if you decide to visit Bum La Pass and Madhuri Lake, it may not be possible to cover all the places in a single day. In the evening, return to the hotel for an overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Tawang- Bomdila (190 KM | 7 HRS)',
                            'desc' => 'After enjoying your breakfast, proceed to Bomdila and complete the check-in process at your hotel upon arrival. Take the opportunity to visit the prominent attractions in Bomdila, such as the Bomdila Monastery In the evening, you can explore the local market and immerse yourself in the vibrant atmosphere. Conclude the day with a comfortable overnight stay in Bomdila.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Bomdila- Guwahati (335 KM | 6 - 7 HRS)',
                            'desc' => 'After Breakfast, transfer to Guwahati. Check in to your hotel. Evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Bomdila'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Guwahati',
                            'desc' => 'After breakfast check out from the hotel. Visit the Kamakhya Temple With everlasting memories transfer to Guwahati airport/railway station for onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'adventure',
                    'dest' => 'arunachal-pradesh',
                    'title' => '7 Nights 8 Days Arunachal Grand Circuit with Bum La Pass (PH - NE 8)',
                    'slug' => 'ph-ne-8-7-nights-8-days-arunachal-bumla-tawang',
                    'summary' => 'Comprehensive Arunachal odyssey: Bum La Pass (Indo-China border), pristine Madhuri Lake, Sela Pass, Jaswant Garh, Tawang Monastery, Dirang, and Bomdila.',
                    'desc' => 'Comprehensive Arunachal odyssey: Bum La Pass (Indo-China border), pristine Madhuri Lake, Sela Pass, Jaswant Garh, Tawang Monastery, Dirang, and Bomdila.

IMPORTANT: Inner Line Permit (ILP) is mandatory for this tour. Guests must submit 1 passport photograph and government ID proof.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 8,
                    'nights' => 7,
                    'price' => 35999.0,
                    'cprice' => 21499.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 7 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• Arunachal Pradesh Inner Line Permit (ILP) documentation assistance',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance
• Government ILP application fees and local DC office permit charges',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.

Mandatory Documents for Arunachal Pradesh (ILP):
Inner Line Permit (ILP) is mandatory for all visitors to Arunachal Pradesh. Please submit 1 passport-size photo and 1 government-authorized photo ID (Passport / Aadhaar / Voter ID) at least 7 days prior to travel.',
                    'hotel' => 'Tezpur: Kanyapur (1N) | Dirang: Pemaling (1N) | Tawang: Hotel Yangzom / Denzong (3N) | Bomdila: Tsepal Yangjom (1N) | Guwahati: Vivanta (1N)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'adventure',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati- Tezpur (200 | 4 Hrs)',
                            'desc' => 'Welcome to Awesome Assam. Meet and be assisted by our representative at the airport/Railway station. Transfer to Tezpur. Start your journey from Guwahati in the morning. After reaching Tezpur, check into your accommodation and take some rest. In the afternoon, you can visit some popular attractions in Tezpur like Agnigarh Hill, Cole Park and Mahabhairav Temple. Enjoy the evening exploring the local markets and trying out local delicacies.Overnight stay in Tezpur.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Guwahati'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Tezpur - Dirang (187 Km | 4-5 Hrs)',
                            'desc' => 'After Breakfast Proceed to Dirang. On the way visit Orchid Research Centre Tipi, an orchidarium with about 10,000 orchid plants belonging to various genera and species. Arunachal has the largest range of orchids in the country. Continue your drive and do not forget to keep your cameras handy as you cross beautiful waterfalls and get some spectacular views. Enroute visit Nag Mandir. Arrive and check in to your hotel. After lunch visit the regional apple Nursery, kiwi farm, hot water spring, Sangti Valley. Overnight stay in Dirang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Dirang - Tawang (140 Km | 6 Hrs)',
                            'desc' => 'After having breakfast visit Dirang Monestry and  proceed towards Tawang. On the way, make a stop at the war cemetery to pay respects. Resume your journey and reach Sela Pass, which stands at an impressive altitude of 4170 meters (13700 feet). This pass is renowned for its breathtaking lakes and scenic landscapes. Continue driving until you arrive at the Jaswant Garh War Memorial, a tribute to the courageous Indian soldier Jaswant Singh Rawat. Take some time to visit the Nuranang Falls, also known as Jang Falls. Finally, reach Tawang and check in at your hotel for an overnight stay.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Tawang Local Sightseeing',
                            'desc' => 'After breakfast visit Tawang Monastery (Namgey Lhatse), Singsor Ani Gompa, and Tawang War Memorial. Evening return back to the hotel. Overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Tawang - Bumla Pass (China Border) - Madhuri Lake - Tawang',
                            'desc' => 'After breakfast, visit Bum La Pass (Indo-China Border) for which you will have to take a permit from the DC office at Tawang on your own, Sangestar Lake (Madhuri Lake) and Pangateng Tso Lake (P.Tso Lake). Please note that for these visits, you\'ll need to hire a local taxi in Tawang and pay directly for the service, as our vehicles are not allowed in that area. Extra cost 5,500 INR per vehicle and 100 INR per ticketper person',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Tawang- Bomdila (190 KM | 7-8 HRS)',
                            'desc' => 'After enjoying your breakfast, proceed to Bomdila and complete the check-in process at your hotel upon arrival. Take the opportunity to visit the prominent attractions in Bomdila, such as the Bomdila Monastery.In the evening, you can explore the local market and immerse yourself in the vibrant atmosphere. Conclude the day with a comfortable overnight stay in Bomdila.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Bomdila- Guwahati (335 KM | 6 - 7 HRS)',
                            'desc' => 'After Breakfast transfer to Guwahati. Check in to your hotel. Evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Bomdila'
                        ],
                        [
                            'day' => 8,
                            'title' => 'Guwahati',
                            'desc' => 'After breakfast check out from the hotel. Visit the Kamakhya Temple With everlasting memories transfer to Guwahati airport/railway station for onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'adventure',
                    'dest' => 'arunachal-pradesh',
                    'title' => '7 Nights 8 Days Kaziranga Wildlife & Arunachal Tawang (PH - NE 09)',
                    'slug' => 'ph-ne-09-7-nights-8-days-kaziranga-tawang-arunachal',
                    'summary' => 'The ultimate fusion of Kaziranga Rhino jungle safaris and high Himalayan peaks, Tawang Buddhist Monasteries, Dirang Valley, and Sela Pass.',
                    'desc' => 'The ultimate fusion of Kaziranga Rhino jungle safaris and high Himalayan peaks, Tawang Buddhist Monasteries, Dirang Valley, and Sela Pass.

IMPORTANT: Inner Line Permit (ILP) is mandatory for this tour. Guests must submit 1 passport photograph and government ID proof.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 8,
                    'nights' => 7,
                    'price' => 36999.0,
                    'cprice' => 22000.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 7 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• 1 Elephant Safari and 1 Jeep Safari inside Kaziranga National Park
• Arunachal Pradesh Inner Line Permit (ILP) documentation assistance',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance
• Government ILP application fees and local DC office permit charges',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.

Mandatory Documents for Arunachal Pradesh (ILP):
Inner Line Permit (ILP) is mandatory for all visitors to Arunachal Pradesh. Please submit 1 passport-size photo and 1 government-authorized photo ID (Passport / Aadhaar / Voter ID) at least 7 days prior to travel.',
                    'hotel' => 'Kaziranga: Borgos (2N) | Dirang: Pemaling (1N) | Tawang: Yangzom (2N) | Bomdila: Tsepal (1N) | Guwahati: Dynasty (1N)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'adventure',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati - Kaziranga National Park (230 KM | 4.5 Hrs)',
                            'desc' => 'Welcome to Awesome Assam. Upon arrival at Guwahati Airport/Railway Station, meet your guide/driver. Proceed Kaziranga National Park. Enjoy the scenic drive through the beautiful landscapes of Assam. Reach Kaziranga National Park. Check into your hotel/resort. Evening you may visit Orchid Park and the nearby Tea Plantations. Overnight stay at Kaziranga National Park',
                            'meals' => 'Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Kaziranga National Park',
                            'desc' => 'Experience the wonders of Kaziranga National Park with an early morning elephant safari, allowing you to explore the park\'s diverse wildlife. Besides the endangered One-Horn Indian Rhinoceros, the park boasts a significant population of genetically pure Wild Water Buffaloes, over 1000 Wild elephants, and a dense population of Royal Bengal Tigers. Bird enthusiasts will delight in spotting the park\'s 500 species of birds, including the Crested Serpent Eagle, Palla\'s Fishing Eagle, Greyheaded Fishing Eagle, and many more. After the safari, return to the resort for breakfast. In the afternoon, enjoy a thrilling jeep safari, further immersing yourself in the park\'s natural wonders. As the day concludes, return to the hotel for a comfortable overnight stay within the Kaziranga National Park.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Kaziranga National Park - Dirang (260 Km | 6 to 7 Hrs)',
                            'desc' => 'Start early in the morning for Dirang. On the way visit Orchid Research Centre Tipi, an orchidarium with about 10,000 orchid plants belonging to various genera and species. Arunachal has the largest range of orchids in the country. Continue your drive and do not forget to keep your cameras handy as you cross beautiful waterfalls and get some spectacular views. Enroute visit Nag Mandir. Arrive and check in to your hotel. After lunch visit the regional apple Nursery, kiwi farm, hot water spring, Sangti Valley.Overnight stay in Dirang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Dirang - Tawang (140 Km | 6 Hrs)',
                            'desc' => 'After breakfast visit Dirang Monestry and transfer to Tawang. Enroute visit war cemetery. Continue with the drive till you reach Sela Pass at an altitude of 4170 m / 13700 ft. It is famous for high altitude lakes & scenic beauty. Continue the drive to Jaswant Garh War Memorial a memorial of the brave Indian soldier Jaswant Singh Rawat. Visit Nuranang Falls (Jang Falls). Arrive and check in the hotel. Overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Tawang',
                            'desc' => 'After breakfast we have 2 options for the day. We can visit Bum La Pass (Indo-China Border) for which you will have to take a permit from the DC office at Tawang on your own, Sangestar Lake (Madhuri Lake) and Pangateng Tso Lake (P.Tso Lake). Please Note you will also have to take a local taxi in Tawang on direct payment basis to cover these places as our vehicles are not allowed. OR you may visit Tawang Monastery (Namgey Lhatse), Singsor Ani Gompa, and Tawang War Memorial. Please Note It may not be possible to cover all the places in a day in case you visit Bumla Pass and Madhuri lake. Evening return back to the hotel. Overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Tawang- Bomdila (190 KM | 7-8 HRS)',
                            'desc' => 'After enjoying your breakfast, proceed to Bomdila and complete the check-in process at your hotel upon arrival. Take the opportunity to visit the prominent attractions in Bomdila, such as the Bomdila Monastery. In the evening, you can explore the local market and immerse yourself in the vibrant atmosphere. Conclude the day with a comfortable overnight stay in Bomdila.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Bomdila- Guwahati (335 KM | 6 - 7 HRS)',
                            'desc' => 'After Breakfast transfer to Guwahati. Check in to your hotel. Evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Bomdila'
                        ],
                        [
                            'day' => 8,
                            'title' => 'Guwahati',
                            'desc' => 'After breakfast check out from the hotel. Visit the Kamakhya Temple With everlasting memories transfer to Guwahati airport/railway station for onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
                [
                    'cat' => 'luxury-heritage',
                    'dest' => 'arunachal-pradesh',
                    'title' => '10 Nights 11 Days Ultimate North East Explorer: Meghalaya, Kaziranga & Arunachal (PH - NE 10)',
                    'slug' => 'ph-ne-10-10-nights-11-days-ultimate-north-east-explorer',
                    'summary' => 'The grand expedition across the North East: Shillong, Cherrapunjee, Dawki, Kaziranga National Park safaris, Dirang, Sela Pass, Tawang, and Bomdila.',
                    'desc' => 'The grand expedition across the North East: Shillong, Cherrapunjee, Dawki, Kaziranga National Park safaris, Dirang, Sela Pass, Tawang, and Bomdila.

IMPORTANT: Inner Line Permit (ILP) is mandatory for this tour. Guests must submit 1 passport photograph and government ID proof.

Destination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM',
                    'days' => 11,
                    'nights' => 10,
                    'price' => 48999.0,
                    'cprice' => 28999.0,
                    'iprice' => 0.0,
                    'img' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                    'grp' => 16,
                    'inc' => '• 10 Nights Hotel / Resort Accommodation as per itinerary
• Daily Buffet Breakfast and Dinner at all hotels
• All sightseeing, inter-city transfers, and excursions in Private Dedicated Vehicle
• Experienced chauffeur with all toll taxes, parking fees, and driver allowances included
• 24x7 local on-ground tour coordinator support throughout the journey
• Arunachal Pradesh Inner Line Permit (ILP) documentation assistance',
                    'exc' => '• Airfare / Train tickets to and from Guwahati
• Monument entry fees, camera permits, and personal boating tickets
• Optional excursions (River cruise on Brahmaputra, local taxi for Bum La Pass / Madhuri Lake)
• Personal expenses such as laundry, telephone, bottled water, and tips
• Travel and medical insurance
• Government ILP application fees and local DC office permit charges',
                    'cancel' => '• Before 30 Days from Tour Start Date: Full Refund (less 3% service fee)
• 30 - 21 Days from Tour Start Date: 75% refund
• 21 - 14 Days from Tour Start Date: 50% refund
• 14 - 7 Days from Tour Start Date: 25% refund
• Within 7 days from Tour Start Date: No Refund

Please note: A 3% service fee of the total booking amount is applicable on all cancellations.

Payment Terms:
• 30% of total Tour Cost within 3 Days after confirmation.
• 70% of total Tour Cost 7 Days before tour start date.

Mandatory Documents for Arunachal Pradesh (ILP):
Inner Line Permit (ILP) is mandatory for all visitors to Arunachal Pradesh. Please submit 1 passport-size photo and 1 government-authorized photo ID (Passport / Aadhaar / Voter ID) at least 7 days prior to travel.',
                    'hotel' => 'Shillong: Polo Towers (3N) | Kaziranga: Borgos (2N) | Dirang: Pemaling (1N) | Tawang: Yangzom (2N) | Bomdila: Tsepal (1N) | Guwahati: Vivanta (1N)',
                    'feat' => 1,
                    'dom' => 1,
                    'type' => 'family',
                    'itin' => [
                        [
                            'day' => 1,
                            'title' => 'Guwahati - Shillong (100 KM | 3 Hrs)',
                            'desc' => 'Welcome to Awesome Assam. Meet and be assisted by our representative at the airport/Railway Station. Proceed to Shillong, also called \'Scotland of the East". Reach the majestic Umium Lake (Barapani). You may do the water sports here (Optional). On arrival at Shillong, check in at your hotel. Evening you can visit the Police Bazaar which is the biggest local market. Overnight stay in Shillong.',
                            'meals' => 'Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 2,
                            'title' => 'Shillong - Cherrapunji - Shillong (65 KM | 1.5 Hrs Per Way)',
                            'desc' => 'Get up early today to enjoy the mesmerizing mornings of Shillong. After an early breakfast drive to Cherrapunji, this is the wettest place in the world. Visit  Eco Park, ,, Nohkalikai Falls, Nohsngithiang Falls (Seven Sisters Falls), Mawsmai Cave, Thangkharang Park. Evening return to Shillong. Overnight stay in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 3,
                            'title' => 'Shillong - Dawki - Mawlynnong Village - Shillong (90 KM | 2 Hrs Per Way)',
                            'desc' => 'After breakfast visit Mawlynnong Village the cleanest village in India. This cute and colorful little village is known for its cleanliness.  It is situated 90 kms. from Shillong and besides the picturesque village, offers many interesting sights such as the Living Root Bridge and another strange natural phenomenon of a boulder balancing on another rock. Visit Dawki, It is along the Indo-Bangladesh border. You can enjoy boating in the crystal clear waters of the Umgnot River. Evening we will return back to Shillong. Overnight in Shillong.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 4,
                            'title' => 'Shillong - Kaziranga National Park (280 KM | 6 Hrs)',
                            'desc' => 'After breakfast tansfer to Kaziranga National Park, the home of the One Horn Indian Rhinoceros. Check in at your hotel/Lodge/resort. Evening you may visit Orchid Park and the nearby Tea Plantations. Overnight stay at Kaziranga National Park.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Shillong'
                        ],
                        [
                            'day' => 5,
                            'title' => 'Kaziranga National Park',
                            'desc' => 'Experience the wonders of Kaziranga National Park with an early morning elephant safari, allowing you to explore the park\'s diverse wildlife. Besides the endangered One-Horn Indian Rhinoceros, the park boasts a significant population of genetically pure Wild Water Buffaloes, over 1000 Wild elephants, and a dense population of Royal Bengal Tigers. Bird enthusiasts will delight in spotting the park\'s 500 species of birds, including the Crested Serpent Eagle, Palla\'s Fishing Eagle, Greyheaded Fishing Eagle, and many more. After the safari, return to the resort for breakfast. In the afternoon, enjoy a thrilling jeep safari, further immersing yourself in the park\'s natural wonders. As the day concludes, return to the hotel for a comfortable overnight stay within the Kaziranga National Park.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 6,
                            'title' => 'Kaziranga National Park - Dirang (260 Km | 6 to 7 Hrs)',
                            'desc' => 'Start early in the morning for Dirang. On the way visit Orchid Research Centre Tipi, an orchidarium with about 10,000 orchid plants belonging to various genera and species. Arunachal has the largest range of orchids in the country. Continue your drive and do not forget to keep your cameras handy as you cross beautiful waterfalls and get some spectacular views. Enroute visit Nag Mandir. Arrive and check in to your hotel. After lunch visit the regional apple Nursery, kiwi farm, hot water spring, Sangti Valley. Overnight stay in Dirang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Resort in Kaziranga'
                        ],
                        [
                            'day' => 7,
                            'title' => 'Dirang - Tawang (140 Km | 6 Hrs)',
                            'desc' => 'After breakfast visit Dirang Monestry and transfer to Tawang. Enroute visit the war cemetery. Continue with the drive till you reach Sela Pass at an altitude of 4170 m / 13700 ft. It is famous for high altitude lakes & scenic beauty. Continue the drive to JaswantGarh War Memorial, a memorial of the brave Indian soldier Jaswant Singh Rawat. Visit Nuranang Falls (Jang Falls). Arrive and check in the hotel. Overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Dirang'
                        ],
                        [
                            'day' => 8,
                            'title' => 'Tawang',
                            'desc' => 'After breakfast we have 2 options for the day. We can visit Bum La Pass (Indo-China Border) for which you will have to take a permit from the DC office at Tawang on your own, Sangestar Lake (Madhuri Lake) and Pangateng Tso Lake (P.Tso Lake). Please Note you will also have to take a local taxi in Tawang on direct payment basis to cover these places as our vehicles are not allowed. OR you may visit Tawang Monastery (Namgey Lhatse), Singsor Ani Gompa, and Tawang War Memorial. Please Note It may not be possible to cover all the places in a day in case you visit Bumla Pass and Madhuri lake. Evening return back to the hotel. Overnight stay in Tawang.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 9,
                            'title' => 'Tawang- Bomdila (190 KM | 7-8 HRS)',
                            'desc' => 'After enjoying your breakfast, proceed to Bomdila and complete the check-in process at your hotel upon arrival. Take the opportunity to visit the prominent attractions in Bomdila, such as the Bomdila Monastery. In the evening, you can explore the local market and immerse yourself in the vibrant atmosphere. Conclude the day with a comfortable overnight stay in Bomdila.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Tawang'
                        ],
                        [
                            'day' => 10,
                            'title' => 'Bomdila- Guwahati (335 KM | 6 - 7 HRS)',
                            'desc' => 'After Breakfast transfer to Guwahati. Check in to your hotel. Evening you may take a River Cruise (Direct Payment) on the mighty River Brahmaputra. You may also visit the local market. Assam is famous for Assam Silk particularly Golden Muga Silk, Assam Tea, Bamboo and Cane Products. Overnight stay in Guwahati.',
                            'meals' => 'Breakfast, Dinner',
                            'stay' => 'Hotel in Bomdila'
                        ],
                        [
                            'day' => 11,
                            'title' => 'Guwahati',
                            'desc' => 'After breakfast check out from the hotel. Visit the Kamakhya Temple With everlasting memories transfer to Guwahati airport/railway station for onward journey.',
                            'meals' => 'Breakfast',
                            'stay' => 'Tour Concludes / Airport Drop'
                        ],
                    ]
                ],
            ];

            foreach ($nePackages as $pkg) {
                $pkgCheck->execute([$pkg['slug']]);
                $pid = $pkgCheck->fetchColumn();
                if (!$pid) {
                    $pkgInsert->execute([
                        $pkg['cat'], $pkg['dest'], $pkg['title'], $pkg['slug'],
                        $pkg['summary'], $pkg['desc'], $pkg['days'], $pkg['nights'],
                        $pkg['price'], $pkg['cprice'], $pkg['iprice'], $pkg['img'],
                        $pkg['grp'], $pkg['inc'], $pkg['exc'], $pkg['cancel'],
                        $pkg['hotel'], $pkg['feat'], $pkg['dom'], $pkg['type']
                    ]);
                    $pid = (int)$pdo->lastInsertId();

                    foreach ($pkg['itin'] as $it) {
                        $itinInsert->execute([
                            $pid, $it['day'], $it['title'], $it['desc'], $it['meals'], $it['stay']
                        ]);
                    }

                    $baseDates = [7, 14, 21, 30, 45, 60, 75, 90];
                    foreach ($baseDates as $offset) {
                        $depDate = date('Y-m-d', strtotime("+$offset days"));
                        $retDate = date('Y-m-d', strtotime("+$offset days +{$pkg['days']} days"));
                        $depInsert->execute([$pid, $depDate, $retDate, $pkg['price'], $pkg['cprice']]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Logger::error("SQLite North East package seeder error: " . $e->getMessage());
        }
    }

}
