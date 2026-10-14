<?php

require_once 'c:/Users/itmpa/Desktop/YatraNest/vendor/autoload.php';
require_once 'c:/Users/itmpa/Desktop/YatraNest/app/Helpers/Helpers.php';

use App\Core\App;
use App\Core\Database;

$app = new App('c:/Users/itmpa/Desktop/YatraNest');
$db = Database::getConnection();

echo "Harmonizing destinations and featured images for YatraNest...\n";

// Disable foreign keys temporarily
try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
} catch (\Throwable $e) {}

// 1. Canonical Destinations Definitions with Authentic Photography
$canonicalDestinations = [
    'arunachal-pradesh' => [
        'name' => 'Arunachal Pradesh',
        'slug' => 'arunachal-pradesh',
        'country' => 'India',
        'state' => 'Arunachal Pradesh',
        'city' => 'Tawang & Bomdila',
        'description' => 'The Land of Dawn-Lit Mountains captivates with high Himalayan passes like Sela Pass (13,700 ft), ancient Tawang Monastery, and serene alpine lakes.',
        'image_url' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'assam' => [
        'name' => 'Assam',
        'slug' => 'assam',
        'country' => 'India',
        'state' => 'Assam',
        'city' => 'Guwahati & Kaziranga',
        'description' => 'Land of the mighty Brahmaputra river, sacred Kamakhya Temple, sprawling aromatic tea gardens, and the UNESCO Kaziranga National Park.',
        'image_url' => 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'meghalaya' => [
        'name' => 'Meghalaya',
        'slug' => 'meghalaya',
        'country' => 'India',
        'state' => 'Meghalaya',
        'city' => 'Shillong & Cherrapunji',
        'description' => 'Known as the Abode of the Clouds, Meghalaya mesmerizes with cascading waterfalls like Nohkalikai, living root bridges, and crystal-clear Dawki waters.',
        'image_url' => 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'darjeeling' => [
        'name' => 'Darjeeling',
        'slug' => 'darjeeling',
        'country' => 'India',
        'state' => 'West Bengal',
        'city' => 'Darjeeling & Mirik',
        'description' => 'The Queen of the Hills, renowned for world-famous tea estates, the UNESCO Himalayan Toy Train, and magical sunrises over Kanchenjunga from Tiger Hill.',
        'image_url' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'manipur' => [
        'name' => 'Manipur',
        'slug' => 'manipur',
        'country' => 'India',
        'state' => 'Manipur',
        'city' => 'Imphal & Loktak',
        'description' => 'The Jewel of India, famed for the world-famous freshwater Loktak Lake with floating phumdis, Keibul Lamjao national park, and Kangla Fort.',
        'image_url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'mizoram' => [
        'name' => 'Mizoram',
        'slug' => 'mizoram',
        'country' => 'India',
        'state' => 'Mizoram',
        'city' => 'Aizawl & Reiek',
        'description' => 'Land of rolling blue hills, pleasant temperate climate, vibrant tribal culture, and dramatic panoramic vistas from Reiek Peak.',
        'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'nagaland' => [
        'name' => 'Nagaland',
        'slug' => 'nagaland',
        'country' => 'India',
        'state' => 'Nagaland',
        'city' => 'Kohima & Dimapur',
        'description' => 'Land of festivals and rich tribal folklore, renowned for the Hornbill Festival, picturesque Dzukou Valley, and historic Kohima War Memorial.',
        'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'tripura' => [
        'name' => 'Tripura',
        'slug' => 'tripura',
        'country' => 'India',
        'state' => 'Tripura',
        'city' => 'Agartala & Udaipur',
        'description' => 'Rich royal heritage featuring the majestic white marble Ujjayanta Palace, the floating Neermahal Water Palace, and ancient rock carvings at Unakoti.',
        'image_url' => 'https://images.unsplash.com/photo-1566837945700-30057527ade0?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'sikkim' => [
        'name' => 'Sikkim',
        'slug' => 'sikkim',
        'country' => 'India',
        'state' => 'Sikkim',
        'city' => 'Gangtok & Pelling',
        'description' => 'Himalayan wonderland boasting views of Mount Kanchenjunga, sacred Tsomgo Lake at 12,400 ft, Rumtek Monastery, and alpine flower valleys.',
        'image_url' => 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    'bhutan' => [
        'name' => 'Bhutan',
        'slug' => 'bhutan',
        'country' => 'Bhutan',
        'state' => 'Paro & Thimphu',
        'city' => 'Thimphu & Paro',
        'description' => 'The Land of the Thunder Dragon, nestled in high Himalayas with iconic cliff-hanging Tiger Nest Monastery (Paro Taktsang) and peaceful Dzongs.',
        'image_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ]
];

// Ensure all canonical destinations exist
$findStmt = $db->prepare("SELECT id FROM destinations WHERE slug = :slug OR name = :name LIMIT 1");
$insertStmt = $db->prepare("
    INSERT INTO destinations (name, slug, country, state, city, description, image_url, is_featured, status)
    VALUES (:name, :slug, :country, :state, :city, :description, :image_url, :is_featured, 'active')
");
$updateStmt = $db->prepare("
    UPDATE destinations SET
        name = :name,
        slug = :slug,
        country = :country,
        state = :state,
        city = :city,
        description = :description,
        image_url = :image_url,
        is_featured = :is_featured,
        status = 'active'
    WHERE id = :id
");

$destIdMap = [];

foreach ($canonicalDestinations as $slug => $dest) {
    $findStmt->execute([':slug' => $slug, ':name' => $dest['name']]);
    $id = $findStmt->fetchColumn();

    if ($id) {
        $updateStmt->execute([
            ':id' => $id,
            ':name' => $dest['name'],
            ':slug' => $dest['slug'],
            ':country' => $dest['country'],
            ':state' => $dest['state'],
            ':city' => $dest['city'],
            ':description' => $dest['description'],
            ':image_url' => $dest['image_url'],
            ':is_featured' => $dest['is_featured']
        ]);
        $destIdMap[$slug] = (int)$id;
        echo "  [+] Updated destination: {$dest['name']} (ID: $id) with authentic photo.\n";
    } else {
        $insertStmt->execute([
            ':name' => $dest['name'],
            ':slug' => $dest['slug'],
            ':country' => $dest['country'],
            ':state' => $dest['state'],
            ':city' => $dest['city'],
            ':description' => $dest['description'],
            ':image_url' => $dest['image_url'],
            ':is_featured' => $dest['is_featured']
        ]);
        $id = (int)$db->lastInsertId();
        $destIdMap[$slug] = $id;
        echo "  [+] Created destination: {$dest['name']} (ID: $id)\n";
    }
}

// Consolidate duplicates: if there are any destinations whose name matches one of canonical but with an old slug, reassign packages and delete duplicate
foreach ($canonicalDestinations as $slug => $dest) {
    $canonId = $destIdMap[$slug];
    $dups = $db->query("SELECT id FROM destinations WHERE (name = '{$dest['name']}' OR slug = '{$slug}') AND id != $canonId")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dups as $dupId) {
        $db->exec("UPDATE tour_packages SET destination_id = $canonId WHERE destination_id = $dupId");
        $db->exec("DELETE FROM destinations WHERE id = $dupId");
        echo "  [-] Consolidated duplicate destination ID $dupId into canonical ID $canonId ({$dest['name']})\n";
    }
}

// 2. Re-assign and polish packages
// North East packages mapping
$nePackagesMap = [
    'ph-ne-1-3-nights-4-days-shillong' => 'meghalaya',
    'ph-ne-2-4-nights-5-days-shillong-guwahati' => 'meghalaya',
    'ph-ne-3-5-nights-6-days-shillong-cherrapunji' => 'meghalaya',
    'ph-ne-4-6-nights-7-days-shillong-cherrapunji-guwahati' => 'meghalaya',
    'ph-ne-5-5-nights-6-days-kaziranga-shillong' => 'assam',
    'ph-ne-6-6-nights-7-days-kaziranga-shillong-guwahati' => 'assam',
    'ph-ne-7-6-nights-7-days-arunachal-tawang-circuit' => 'arunachal-pradesh',
    'ph-ne-8-7-nights-8-days-arunachal-bumla-tawang' => 'arunachal-pradesh',
    'ph-ne-09-7-nights-8-days-kaziranga-tawang-arunachal' => 'arunachal-pradesh',
    'ph-ne-10-10-nights-11-days-ultimate-north-east-explorer' => 'arunachal-pradesh'
];

foreach ($nePackagesMap as $pSlug => $dSlug) {
    if (isset($destIdMap[$dSlug])) {
        $targetDestId = $destIdMap[$dSlug];
        $db->exec("UPDATE tour_packages SET destination_id = $targetDestId, is_featured = 1 WHERE slug = '$pSlug'");
    }
}

// Convert any leftover old demo packages to authentic Eastern Himalayan packages
$demoTransformations = [
    'kashmir-paradise-srinagar-gulmarg-pahalgam' => [
        'title' => '3 Nights 4 Days Mystical Manipur & Loktak Floating Lake',
        'slug' => 'mystical-manipur-loktak-floating-lake',
        'dest' => 'manipur',
        'cat' => 'adventure',
        'days' => 4, 'nights' => 3, 'price' => 15499.00, 'cprice' => 9499.00,
        'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Discover the jewel of the North East: the freshwater Loktak Lake with floating phumdis, Keibul Lamjao national park, and Kangla Fort.'
    ],
    'magical-kerala-backwaters-munnar-alleppey' => [
        'title' => '3 Nights 4 Days Darjeeling Himalayan Serenity & Tea Gardens',
        'slug' => 'darjeeling-himalayan-serenity-tea-gardens',
        'dest' => 'darjeeling',
        'cat' => 'family-holiday',
        'days' => 4, 'nights' => 3, 'price' => 13999.00, 'cprice' => 8499.00,
        'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Experience sunrise over Kanchenjunga from Tiger Hill, ride the UNESCO Himalayan Toy Train, and stroll through aromatic Darjeeling tea gardens.'
    ],
    'himachal-highs-shimla-manali-rohtang' => [
        'title' => '4 Nights 5 Days Nagaland Discovery: Kohima & Dzukou Valley',
        'slug' => 'nagaland-discovery-kohima-dzukou-valley',
        'dest' => 'nagaland',
        'cat' => 'adventure',
        'days' => 5, 'nights' => 4, 'price' => 19999.00, 'cprice' => 11999.00,
        'image' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Immerse in Naga tribal heritage: Kohima War Cemetery, Khonoma green village, and trekking through the pristine Dzukou Valley.'
    ],
    'goa-beach-bliss-sun-sand-heritage' => [
        'title' => '3 Nights 4 Days Scenic Mizoram: Aizawl & Reiek Peak Explorer',
        'slug' => 'scenic-mizoram-aizawl-reiek-peak',
        'dest' => 'mizoram',
        'cat' => 'weekend-getaway',
        'days' => 4, 'nights' => 3, 'price' => 16999.00, 'cprice' => 9999.00,
        'image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Explore the misty hill capital of Aizawl, dramatic cliff views of Reiek Tlang, traditional Mizo heritage, and Solomon Temple.'
    ],
    'royal-rajasthan-jaipur-jodhpur-udaipur' => [
        'title' => '3 Nights 4 Days Royal Tripura: Palaces & Unakoti Heritage',
        'slug' => 'royal-tripura-palaces-unakoti-heritage',
        'dest' => 'tripura',
        'cat' => 'luxury-heritage',
        'days' => 4, 'nights' => 3, 'price' => 14499.00, 'cprice' => 8999.00,
        'image' => 'https://images.unsplash.com/photo-1566837945700-30057527ade0?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Visit the majestic white marble Ujjayanta Palace, the floating Neermahal Water Palace, and ancient rock sculptures at Unakoti.'
    ],
    'dazzling-dubai-desert-safari-burj-khalifa' => [
        'title' => '4 Nights 5 Days Enchanting Sikkim: Gangtok, Tsomgo Lake & Pelling',
        'slug' => 'enchanting-sikkim-gangtok-tsomgo-pelling',
        'dest' => 'sikkim',
        'cat' => 'family-holiday',
        'days' => 5, 'nights' => 4, 'price' => 18999.00, 'cprice' => 11499.00,
        'image' => 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80',
        'summary' => 'Himalayan grandeur in Sikkim: Gangtok MG Marg, sacred alpine Tsomgo Lake at 12,400 ft, Rumtek Monastery, and Kanchenjunga in Pelling.'
    ]
];

$upPkgStmt = $db->prepare("
    UPDATE tour_packages SET
        title = :title,
        slug = :new_slug,
        destination_id = :did,
        category_id = (SELECT id FROM tour_categories WHERE slug = :cat LIMIT 1),
        duration_days = :days,
        duration_nights = :nights,
        price_adult = :price,
        price_child = :cprice,
        featured_image = :img,
        summary = :summary,
        description = :desc,
        is_featured = 1,
        is_domestic = 1,
        status = 'published',
        seo_title = :seo_title,
        seo_meta_description = :seo_meta
    WHERE slug = :old_slug
");

foreach ($demoTransformations as $oldSlug => $tr) {
    if (isset($destIdMap[$tr['dest']])) {
        $did = $destIdMap[$tr['dest']];
        $upPkgStmt->execute([
            ':old_slug' => $oldSlug,
            ':title' => $tr['title'],
            ':new_slug' => $tr['slug'],
            ':did' => $did,
            ':cat' => $tr['cat'],
            ':days' => $tr['days'],
            ':nights' => $tr['nights'],
            ':price' => $tr['price'],
            ':cprice' => $tr['cprice'],
            ':img' => $tr['image'],
            ':summary' => $tr['summary'],
            ':desc' => $tr['summary'] . "\n\nDestination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM | BHUTAN",
            ':seo_title' => $tr['title'] . ' | YatraNest',
            ':seo_meta' => $tr['summary']
        ]);
        echo "  [+] Transformed demo package $oldSlug -> {$tr['title']}\n";
    }
}

// Ensure Bhutan package exists
$bhutanSlug = 'kingdom-of-bhutan-thimphu-paro-tigers-nest';
$bhutanPkgId = $db->query("SELECT id FROM tour_packages WHERE slug = '$bhutanSlug'")->fetchColumn();
if (!$bhutanPkgId && isset($destIdMap['bhutan'])) {
    $db->prepare("
        INSERT INTO tour_packages (
            category_id, destination_id, title, slug, summary, description,
            duration_days, duration_nights, price_adult, price_child, price_infant,
            featured_image, group_size_max, inclusions, exclusions,
            cancellation_policy_text, hotel_details, is_featured, is_domestic,
            tour_type, status, seo_title, seo_meta_description
        ) VALUES (
            (SELECT id FROM tour_categories WHERE slug = 'luxury-heritage' LIMIT 1),
            :did, '4 Nights 5 Days Kingdom of Bhutan: Thimphu & Tiger Nest Paro',
            '$bhutanSlug',
            'Journey to the Land of Happiness: Thimphu Tashichho Dzong, Buddha Dordenma, Punakha Dzong, and the legendary cliffside Tiger Nest Monastery in Paro.',
            'Experience Bhutan: iconic cliff-hanging Paro Taktsang, majestic fortress dzongs, prayer wheels, and serene Himalayan vistas.\n\nDestination Expert for: ASSAM | ARUNACHAL | MEGHALAYA | MANIPUR | MIZORAM | NAGALAND | TRIPURA | SIKKIM | BHUTAN',
            5, 4, 29999.00, 17999.00, 0.00,
            'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1200&q=80',
            16,
            '• 4 Nights 3-4 Star Accommodation in Thimphu & Paro\n• Daily Buffet Breakfast & Dinner\n• Private Dedicated AC vehicle with experienced driver\n• Entry permits & Bhutan Sustainable Development Fee (SDF) guidance',
            '• Airfare to Paro (PBH)\n• Monument entry charges\n• Personal expenses & tips',
            '• Before 30 Days: Full refund less 3% fee\n• 30-21 Days: 75% refund\n• 21-14 Days: 50% refund\n• 14-7 Days: 25% refund\n• Within 7 days: No refund',
            'Thimphu: Hotel Norbuling / Druk (2N) | Paro: Tashi Namgay Resort (2N)',
            1, 0, 'luxury', 'published',
            '4 Nights 5 Days Kingdom of Bhutan | YatraNest',
            'Journey to the Land of Happiness: Thimphu Tashichho Dzong, Buddha Dordenma, and Tiger Nest Monastery.'
        )
    ")->execute([':did' => $destIdMap['bhutan']]);
    $bhutanPkgId = (int)$db->lastInsertId();

    // Insert 5 itinerary days
    $itinIns = $db->prepare("INSERT INTO tour_itineraries (package_id, day_number, title, description, meals_included, accommodation) VALUES (?, ?, ?, ?, ?, ?)");
    $itinIns->execute([$bhutanPkgId, 1, 'Arrival at Paro & Drive to Thimphu', 'Scenic flight into Paro Valley. Drive to Thimphu capital city. Evening visit Memorial Chorten and Tashichho Dzong.', 'Dinner', 'Hotel Norbuling, Thimphu']);
    $itinIns->execute([$bhutanPkgId, 2, 'Thimphu Sightseeing & Buddha Point', 'Visit Buddha Dordenma statue overlooking valley, National Textile Museum, Simply Bhutan, and local handicraft markets.', 'Breakfast, Dinner', 'Hotel Norbuling, Thimphu']);
    $itinIns->execute([$bhutanPkgId, 3, 'Thimphu to Paro & National Museum', 'Drive from Thimphu to Paro. Visit Rinpung Dzong, National Museum (Ta Dzong), and Kyichu Lhakhang.', 'Breakfast, Dinner', 'Tashi Namgay Resort, Paro']);
    $itinIns->execute([$bhutanPkgId, 4, 'Hike to Taktsang (Tiger Nest Monastery)', 'Memorable trek to the iconic Tiger Nest Monastery clinging to a granite cliff 900m above the valley floor.', 'Breakfast, Dinner', 'Tashi Namgay Resort, Paro']);
    $itinIns->execute([$bhutanPkgId, 5, 'Departure from Paro', 'After breakfast, transfer to Paro International Airport for your onward flight with blessings from the Dragon Kingdom.', 'Breakfast', 'Tour Concludes']);

    // Insert departures
    $depIns = $db->prepare("INSERT INTO tour_departures (package_id, departure_date, return_date, total_seats, available_seats, adult_price, child_price, status) VALUES (?, ?, ?, 16, 16, 29999.00, 17999.00, 'open')");
    foreach ([10, 20, 30, 45, 60] as $offset) {
        $dDate = date('Y-m-d', strtotime("+$offset days"));
        $rDate = date('Y-m-d', strtotime("+$offset days +5 days"));
        $depIns->execute([$bhutanPkgId, $dDate, $rDate]);
    }
    echo "  [+] Added Kingdom of Bhutan package (ID: $bhutanPkgId)\n";
}

// Clean up any stray destinations that are not in canonical list and have no packages
$allDests = $db->query("SELECT id, name, slug FROM destinations")->fetchAll(PDO::FETCH_ASSOC);
foreach ($allDests as $ad) {
    if (!isset($canonicalDestinations[$ad['slug']])) {
        $pkgCount = (int)$db->query("SELECT count(*) FROM tour_packages WHERE destination_id = {$ad['id']}")->fetchColumn();
        if ($pkgCount === 0) {
            $db->exec("DELETE FROM destinations WHERE id = {$ad['id']}");
            echo "  [-] Removed unused destination: {$ad['name']} ({$ad['slug']})\n";
        }
    }
}

// Re-enable foreign keys
try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
} catch (\Throwable $e) {}

echo "\nDestinations and featured pictures harmonized successfully!\n";
