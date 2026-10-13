<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Helpers/Helpers.php';

use App\Core\App;
use App\Core\Database;

// Boot application
$app = new App(dirname(__DIR__));
$db = Database::getConnection();

echo "Starting database seeding for YatraNest...\n";

// Disable foreign keys temporarily
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

// 1. Roles
echo "Seeding roles...\n";
$db->exec("TRUNCATE TABLE roles");
$roles = [
    ['name' => 'super_admin', 'description' => 'Full administrative access and settings'],
    ['name' => 'booking_manager', 'description' => 'Manage tour departures, seats, and customer bookings'],
    ['name' => 'content_manager', 'description' => 'Manage destinations, packages, blogs, and reviews'],
    ['name' => 'customer', 'description' => 'Registered traveler customer account'],
];
$stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (:name, :description)");
foreach ($roles as $r) {
    $stmt->execute($r);
}

// 2. Users
echo "Seeding users...\n";
$db->exec("TRUNCATE TABLE users");
$db->exec("TRUNCATE TABLE user_roles");

$users = [
    [
        'name' => 'Super Administrator',
        'email' => 'admin@yatranest.com',
        'phone' => '+91 98765 00001',
        'password' => 'Admin@12345',
        'roles' => ['super_admin']
    ],
    [
        'name' => 'Booking Operations Lead',
        'email' => 'manager@yatranest.com',
        'phone' => '+91 98765 00002',
        'password' => 'Manager@12345',
        'roles' => ['booking_manager']
    ],
    [
        'name' => 'Rahul Sharma',
        'email' => 'customer@example.com',
        'phone' => '+91 98765 43210',
        'password' => 'Customer@12345',
        'roles' => ['customer']
    ],
    [
        'name' => 'Priya Patel',
        'email' => 'priya@example.com',
        'phone' => '+91 98765 43211',
        'password' => 'Customer@12345',
        'roles' => ['customer']
    ]
];

$userStmt = $db->prepare("INSERT INTO users (name, email, phone, password_hash, status) VALUES (:name, :email, :phone, :password_hash, 'active')");
$urStmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, (SELECT id FROM roles WHERE name = :rname LIMIT 1))");

foreach ($users as $u) {
    $userStmt->execute([
        ':name' => $u['name'],
        ':email' => strtolower($u['email']),
        ':phone' => $u['phone'],
        ':password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
    ]);
    $uid = (int)$db->lastInsertId();
    foreach ($u['roles'] as $rname) {
        $urStmt->execute([':uid' => $uid, ':rname' => $rname]);
    }
}

// 3. Tour Categories
echo "Seeding tour categories...\n";
$db->exec("TRUNCATE TABLE tour_categories");
$categories = [
    ['name' => 'Adventure', 'slug' => 'adventure', 'description' => 'Trekking, rafting, safaris, and thrill-seeking expeditions.', 'icon' => 'bi-compass'],
    ['name' => 'Family Holiday', 'slug' => 'family-holiday', 'description' => 'Comfortable, well-paced tours ideal for travelers of all ages.', 'icon' => 'bi-people'],
    ['name' => 'Honeymoon Special', 'slug' => 'honeymoon-special', 'description' => 'Romantic escapes, candle-light dinners, and scenic luxury stays.', 'icon' => 'bi-heart'],
    ['name' => 'Pilgrimage & Spiritual', 'slug' => 'pilgrimage', 'description' => 'Sacred shrines, temple tours, and serene spiritual journeys.', 'icon' => 'bi-peace'],
    ['name' => 'Weekend Getaway', 'slug' => 'weekend-getaway', 'description' => 'Quick refreshing short escapes from city life.', 'icon' => 'bi-calendar-check'],
    ['name' => 'Luxury & Heritage', 'slug' => 'luxury-heritage', 'description' => 'Palaces, five-star resorts, and curated bespoke experiences.', 'icon' => 'bi-gem'],
];
$catStmt = $db->prepare("INSERT INTO tour_categories (name, slug, description, icon, is_active) VALUES (:name, :slug, :description, :icon, 1)");
foreach ($categories as $cat) {
    $catStmt->execute($cat);
}

// 4. Destinations
echo "Seeding destinations...\n";
$db->exec("TRUNCATE TABLE destinations");
$destinations = [
    [
        'name' => 'Kashmir',
        'slug' => 'kashmir',
        'country' => 'India',
        'state' => 'Jammu and Kashmir',
        'city' => 'Srinagar',
        'description' => 'Referred to as Heaven on Earth, Kashmir enchants with snow-capped peaks, shikara rides on Dal Lake, and lush meadows in Gulmarg.',
        'image_url' => 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Kerala',
        'slug' => 'kerala',
        'country' => 'India',
        'state' => 'Kerala',
        'city' => 'Kochi & Munnar',
        'description' => 'God\'s Own Country famous for serene backwaters, tea gardens in Munnar, Ayurvedic therapies, and Alleppey houseboats.',
        'image_url' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Himachal Pradesh',
        'slug' => 'himachal-pradesh',
        'country' => 'India',
        'state' => 'Himachal Pradesh',
        'city' => 'Manali & Shimla',
        'description' => 'Picturesque hill stations, pine forests, adventure sports in Solang Valley, and scenic mountain passes.',
        'image_url' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Goa',
        'slug' => 'goa',
        'country' => 'India',
        'state' => 'Goa',
        'city' => 'Panaji',
        'description' => 'Golden sand beaches, vibrant nightlife, Portuguese colonial architecture, and delicious coastal seafood.',
        'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Rajasthan',
        'slug' => 'rajasthan',
        'country' => 'India',
        'state' => 'Rajasthan',
        'city' => 'Jaipur & Udaipur',
        'description' => 'Land of kings, historic forts, grand royal palaces, Thar desert safaris, and vibrant folk cultural celebrations.',
        'image_url' => 'https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Dubai',
        'slug' => 'dubai',
        'country' => 'United Arab Emirates',
        'state' => 'Dubai',
        'city' => 'Dubai',
        'description' => 'A futuristic global oasis with towering skyscrapers, world-class luxury shopping, desert dunes, and futuristic entertainment.',
        'image_url' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Bali',
        'slug' => 'bali',
        'country' => 'Indonesia',
        'state' => 'Bali',
        'city' => 'Denpasar & Ubud',
        'description' => 'Tropical paradise with lush emerald rice terraces, sacred Hindu water temples, dramatic sea cliffs, and surfing beaches.',
        'image_url' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Switzerland',
        'slug' => 'switzerland',
        'country' => 'Switzerland',
        'state' => 'Zurich & Lucerne',
        'city' => 'Lucerne',
        'description' => 'Alpine grandeur with pristine turquoise lakes, snow-clad Alps, scenic cogwheel trains, and charming historic towns.',
        'image_url' => 'https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ]
];

$destStmt = $db->prepare("INSERT INTO destinations (name, slug, country, state, city, description, image_url, is_featured, status) VALUES (:name, :slug, :country, :state, :city, :description, :image_url, :is_featured, 'active')");
foreach ($destinations as $d) {
    $destStmt->execute($d);
}

// 5. Tour Packages, Images, Itineraries, and Departures
echo "Seeding tour packages, departures, itineraries...\n";
$db->exec("TRUNCATE TABLE tour_packages");
$db->exec("TRUNCATE TABLE tour_images");
$db->exec("TRUNCATE TABLE tour_itineraries");
$db->exec("TRUNCATE TABLE tour_departures");

$packages = [
    [
        'cat_slug' => 'family-holiday',
        'dest_slug' => 'kashmir',
        'title' => 'Kashmir Paradise: Srinagar, Gulmarg & Pahalgam',
        'slug' => 'kashmir-paradise-srinagar-gulmarg-pahalgam',
        'summary' => 'Experience the magic of Dal Lake Shikara ride, Gulmarg snow gondola, and the valley of shepherds in Pahalgam.',
        'description' => "Immerse yourself in Kashmir's natural majesty. This comprehensive 6-day package covers Srinagar's serene Mughal Gardens, traditional houseboat stay on Dal Lake, snow-clad slopes and Gondola ride in Gulmarg, and lush pine valleys along the Lidder River in Pahalgam.",
        'duration_days' => 6,
        'duration_nights' => 5,
        'price_adult' => 24999.00,
        'price_child' => 14999.00,
        'price_infant' => 0.00,
        'featured_image' => 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 20,
        'inclusions' => "• 5 Nights Premium Accommodation (including 1 Night Luxury Houseboat)\n• Daily Buffet Breakfast & Dinner\n• All transfers & sightseeing in AC private vehicle\n• 1-hour Shikara ride on Dal Lake\n• Toll taxes, parking, driver allowances\n• 24/7 dedicated tour manager assistance",
        'exclusions' => "• Airfare or Train tickets\n• Gulmarg Gondola cable car tickets (Phase 1 & 2)\n• Pony / Horse rides\n• Personal expenses, laundry, tips\n• Travel insurance",
        'cancellation_policy_text' => "• Cancellation 30+ days prior to departure: 90% refund\n• Cancellation 15-29 days prior: 50% refund\n• Cancellation within 14 days: No refund",
        'hotel_details' => 'Srinagar: Grand Mumtaz (4 Star) | Houseboat: Royal Dal Houseboat | Pahalgam: Pine N Peak Resort',
        'is_featured' => 1,
        'is_domestic' => 1,
        'tour_type' => 'family',
        'itinerary' => [
            ['day' => 1, 'title' => 'Arrival in Srinagar & Dal Lake Shikara Ride', 'desc' => 'Arrive at Srinagar airport. Meet our executive, transfer to your hotel. Evening 1-hour relaxing Shikara ride across Dal Lake witnessing floating vegetable gardens and sunset.', 'meals' => 'Dinner', 'stay' => 'Hotel Grand Mumtaz, Srinagar'],
            ['day' => 2, 'title' => 'Srinagar Mughal Gardens Sightseeing', 'desc' => 'Visit famous Nishat Bagh (Garden of Pleasure), Shalimar Bagh, Chashme Shahi, and Shankaracharya Temple perched on a scenic hill overlooking the valley.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Hotel Grand Mumtaz, Srinagar'],
            ['day' => 3, 'title' => 'Excursion to Gulmarg (Meadow of Flowers)', 'desc' => 'Drive through scenic willow countryside to Gulmarg (2,730m). Enjoy the world-famous Gulmarg Gondola cable car ride up to Apharwat Peak for snow activities.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Hotel Grand Mumtaz, Srinagar'],
            ['day' => 4, 'title' => 'Srinagar to Pahalgam (Valley of Shepherds)', 'desc' => 'Drive through saffron fields in Pampore and Awantipora ruins to picturesque Pahalgam. Stroll along the Lidder riverbank and visit Betaab Valley.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Pine N Peak Resort, Pahalgam'],
            ['day' => 5, 'title' => 'Pahalgam to Srinagar Deluxe Houseboat', 'desc' => 'Morning leisure walk in Aru Valley. Post lunch, drive back to Srinagar and check in to a traditional handcrafted wooden deluxe houseboat on Dal Lake.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Royal Heritage Houseboat, Dal Lake'],
            ['day' => 6, 'title' => 'Departure from Srinagar', 'desc' => 'After breakfast, transfer to Srinagar Airport for your onward flight with unforgettable memories of Kashmir.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ],
    [
        'cat_slug' => 'honeymoon-special',
        'dest_slug' => 'kerala',
        'title' => 'Magical Kerala: Backwaters, Munnar & Alleppey Houseboat',
        'slug' => 'magical-kerala-backwaters-munnar-alleppey',
        'summary' => 'Romantic mist-kissed Munnar tea gardens, spice plantations, and private luxury houseboat cruise in Alleppey.',
        'description' => "Discover the timeless romantic beauty of Kerala. From winding scenic ghats lined with cascading waterfalls in Munnar to serene spice plantations in Thekkady, capped by a magical overnight stay on an authentic private houseboat gliding along the palm-fringed backwaters of Alleppey.",
        'duration_days' => 5,
        'duration_nights' => 4,
        'price_adult' => 18500.00,
        'price_child' => 9999.00,
        'price_infant' => 0.00,
        'featured_image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 15,
        'inclusions' => "• 4 Nights Accommodation (2N Munnar, 1N Thekkady, 1N AC Deluxe Houseboat)\n• Daily Breakfast, Lunch & Dinner on Houseboat\n• Private AC vehicle for transfers and sightseeing\n• Spice plantation guided walking tour\n• Driver allowance, tolls, and inter-state permits",
        'exclusions' => "• Airfare / Train fare to Kochi\n• Periyar boat safari tickets\n• Kathakali / Kalaripayattu performance tickets\n• Personal expenses & tips",
        'cancellation_policy_text' => "• 30+ days: 90% refund\n• 15-29 days: 50% refund\n• Within 14 days: Non-refundable",
        'hotel_details' => 'Munnar: Tea County Resort | Thekkady: Spice Village | Alleppey: Deluxe AC Backwater Cruise',
        'is_featured' => 1,
        'is_domestic' => 1,
        'tour_type' => 'honeymoon',
        'itinerary' => [
            ['day' => 1, 'title' => 'Arrival in Kochi & Drive to Munnar', 'desc' => 'Arrive at Cochin Airport. Drive through the mist-laden Western Ghats, stopping at Cheeyappara and Valara waterfalls. Check-in at Munnar resort.', 'meals' => 'Dinner', 'stay' => 'Tea County Resort, Munnar'],
            ['day' => 2, 'title' => 'Munnar Tea Gardens & Eravikulam National Park', 'desc' => 'Visit Eravikulam National Park (home to the endangered Nilgiri Tahr), Tata Tea Museum, Mattupetty Dam, and Echo Point.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Tea County Resort, Munnar'],
            ['day' => 3, 'title' => 'Munnar to Thekkady (Periyar Wildlife Sanctuary)', 'desc' => 'Drive through cardamom hills to Thekkady. Take a guided walking tour of spice plantations (cardamom, cinnamon, vanilla). Optional boating in Periyar Lake.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Spice Village, Thekkady'],
            ['day' => 4, 'title' => 'Thekkady to Alleppey Houseboat Cruise', 'desc' => 'Drive to Alleppey and board your traditional AC private houseboat. Cruise through narrow canals, paddy fields, and enjoy freshly cooked authentic Kerala meals.', 'meals' => 'Breakfast, Lunch, Dinner', 'stay' => 'Private Houseboat, Alleppey'],
            ['day' => 5, 'title' => 'Departure from Cochin', 'desc' => 'Disembark after breakfast and transfer to Cochin International Airport or Railway Station for departure.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ],
    [
        'cat_slug' => 'adventure',
        'dest_slug' => 'himachal-pradesh',
        'title' => 'Himachal Highs: Shimla, Manali & Rohtang Pass',
        'slug' => 'himachal-highs-shimla-manali-rohtang',
        'summary' => 'Colonial charm in Shimla, adrenaline adventures in Solang Valley, and breathtaking Himalayan views.',
        'description' => "The quintessential mountain journey through Himachal Pradesh. Wander through heritage British colonial streets along Shimla's Mall Road, experience paragliding and zorbing in Solang Valley, marvel at the snowy slopes of Rohtang Pass, and visit ancient wooden temples in Old Manali.",
        'duration_days' => 6,
        'duration_nights' => 5,
        'price_adult' => 16999.00,
        'price_child' => 9500.00,
        'price_infant' => 0.00,
        'featured_image' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 25,
        'inclusions' => "• 5 Nights Hotel Accommodation (2N Shimla, 3N Manali)\n• Daily Buffet Breakfast & Dinner\n• Individual AC Cab from Delhi / Chandigarh pickup\n• Solang Valley and Kufri excursion\n• Driver charges, parking, and toll taxes",
        'exclusions' => "• Rohtang Pass NGT National Green Tribunal permit charges\n• Paragliding, river rafting, and adventure activity fees\n• Entry tickets to monuments",
        'cancellation_policy_text' => "• 30+ days: 85% refund\n• 15-29 days: 50% refund\n• Within 14 days: No refund",
        'hotel_details' => 'Shimla: The Grand White (4 Star) | Manali: Apple Country Resort',
        'is_featured' => 1,
        'is_domestic' => 1,
        'tour_type' => 'adventure',
        'itinerary' => [
            ['day' => 1, 'title' => 'Chandigarh / Delhi to Shimla', 'desc' => 'Pick up and scenic drive uphill to Shimla (2,200m). Evening stroll on Ridge and Mall Road.', 'meals' => 'Dinner', 'stay' => 'The Grand White, Shimla'],
            ['day' => 2, 'title' => 'Shimla & Kufri Excursion', 'desc' => 'Excursion to Kufri for horse riding and panoramic views of Himalayan ranges. Visit Jakhoo Temple.', 'meals' => 'Breakfast, Dinner', 'stay' => 'The Grand White, Shimla'],
            ['day' => 3, 'title' => 'Shimla to Manali via Kullu Valley', 'desc' => 'Drive along the Beas river through Pandoh Dam, Kullu Shawl factories, and river rafting points to Manali.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Apple Country Resort, Manali'],
            ['day' => 4, 'title' => 'Manali Local Sightseeing', 'desc' => 'Visit Hadimba Devi Temple, Vashisht Hot Sulphur Springs, Tibetan Monastery, and Club House.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Apple Country Resort, Manali'],
            ['day' => 5, 'title' => 'Solang Valley & Atal Tunnel Adventure', 'desc' => 'Full-day excursion to Solang Valley for paragliding, snow activities, and transit through the engineering marvel of Atal Tunnel.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Apple Country Resort, Manali'],
            ['day' => 6, 'title' => 'Manali to Chandigarh / Delhi Departure', 'desc' => 'After breakfast, drive down to Chandigarh / Delhi for return departure.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ],
    [
        'cat_slug' => 'weekend-getaway',
        'dest_slug' => 'goa',
        'title' => 'Goa Beach Bliss: Sun, Sand & Heritage',
        'slug' => 'goa-beach-bliss-sun-sand-heritage',
        'summary' => 'Relax on Baga Beach, explore Old Goa UNESCO churches, and experience a vibrant Mandovi river cruise.',
        'description' => "Unwind in India's coastal vacation capital. Stay near lively beaches, explore 16th-century Portuguese cathedrals, relax on golden sands, and sail into sunset on the Mandovi River.",
        'duration_days' => 4,
        'duration_nights' => 3,
        'price_adult' => 12499.00,
        'price_child' => 6500.00,
        'price_infant' => 0.00,
        'featured_image' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 20,
        'inclusions' => "• 3 Nights Stay in 4-Star Beach Resort\n• Daily Buffet Breakfast\n• North & South Goa guided sightseeing by AC coach\n• 1-Hour Mandovi River Sunset Cruise\n• Airport / Thivim Station pick-up & drop",
        'exclusions' => "• Water sports (Jet ski, parasailing, banana ride)\n• Alcoholic beverages and nightclub entries\n• Personal expenses",
        'cancellation_policy_text' => "• 21+ days: 90% refund\n• 10-20 days: 50% refund\n• Under 10 days: Non-refundable",
        'hotel_details' => 'North Goa: Lemon Tree Amarante Beach Resort (4 Star)',
        'is_featured' => 1,
        'is_domestic' => 1,
        'tour_type' => 'weekend',
        'itinerary' => [
            ['day' => 1, 'title' => 'Arrival in Goa & Beach Leisure', 'desc' => 'Arrival transfer from airport/railway station. Evening free to relax on Calangute and Baga beach.', 'meals' => 'Welcome Drink', 'stay' => 'Lemon Tree Resort, Goa'],
            ['day' => 2, 'title' => 'North Goa Forts & Water Sports', 'desc' => 'Visit historic Fort Aguada, Chapora Fort, Anjuna flea market beach, and water sports at Calangute.', 'meals' => 'Breakfast', 'stay' => 'Lemon Tree Resort, Goa'],
            ['day' => 3, 'title' => 'South Goa Heritage & Mandovi Cruise', 'desc' => 'Explore Basilica of Bom Jesus, Se Cathedral, Mangueshi Temple, and enjoy a lively sunset river cruise.', 'meals' => 'Breakfast', 'stay' => 'Lemon Tree Resort, Goa'],
            ['day' => 4, 'title' => 'Departure from Goa', 'desc' => 'Breakfast and check out. Airport transfer for flight back home.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ],
    [
        'cat_slug' => 'luxury-heritage',
        'dest_slug' => 'rajasthan',
        'title' => 'Royal Rajasthan: Jaipur, Jodhpur & Udaipur Heritage',
        'slug' => 'royal-rajasthan-jaipur-jodhpur-udaipur',
        'summary' => 'Live like royalty across Rajasthan palaces, the Pink City, Mehrangarh Fort, and Lake Pichola.',
        'description' => "Journey through the golden history of the Rajputs. Tour the imposing Amer Fort and City Palace in Jaipur, the colossal Mehrangarh Fort overlooking Jodhpur's blue city, and the enchanting city of lakes in Udaipur.",
        'duration_days' => 7,
        'duration_nights' => 6,
        'price_adult' => 28999.00,
        'price_child' => 16500.00,
        'price_infant' => 0.00,
        'featured_image' => 'https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 18,
        'inclusions' => "• 6 Nights Heritage Hotel Accommodation (2N Jaipur, 2N Jodhpur, 2N Udaipur)\n• Daily Royal Breakfast & Dinner\n• Private Chauffeur AC Sedan for entire tour\n• Boat ride on Lake Pichola, Udaipur\n• Elephant / Jeep ride at Amer Fort",
        'exclusions' => "• Monument entry tickets and camera fees\n• Guide charges\n• Air / Train fares",
        'cancellation_policy_text' => "• 30+ days: 90% refund\n• 15-29 days: 50% refund\n• Under 15 days: Non-refundable",
        'hotel_details' => 'Jaipur: Mandawa Haveli | Jodhpur: Ajit Bhawan | Udaipur: Trident Udaipur',
        'is_featured' => 1,
        'is_domestic' => 1,
        'tour_type' => 'luxury',
        'itinerary' => [
            ['day' => 1, 'title' => 'Arrival in Jaipur (The Pink City)', 'desc' => 'Pickup from Jaipur airport. Visit Birla Temple and historic Chokhi Dhani for Rajasthani dinner.', 'meals' => 'Dinner', 'stay' => 'Mandawa Haveli, Jaipur'],
            ['day' => 2, 'title' => 'Jaipur Forts & Palaces', 'desc' => 'Ascend to Amer Fort with jeep ride. Tour Hawa Mahal, City Palace, and Jantar Mantar.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Mandawa Haveli, Jaipur'],
            ['day' => 3, 'title' => 'Jaipur to Jodhpur (The Blue City)', 'desc' => 'Drive to Jodhpur via Ajmer Dargah and Pushkar Brahma Temple. Evening check-in at heritage palace.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Ajit Bhawan, Jodhpur'],
            ['day' => 4, 'title' => 'Jodhpur Mehrangarh Fort & Jaswant Thada', 'desc' => 'Explore the majestic Mehrangarh Fort, Jaswant Thada marble cenotaph, and Umaid Bhawan Palace museum.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Ajit Bhawan, Jodhpur'],
            ['day' => 5, 'title' => 'Jodhpur to Udaipur via Ranakpur', 'desc' => 'Scenic drive to Udaipur stopping at Ranakpur Jain Temple renowned for 1,444 intricately carved pillars.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Trident, Udaipur'],
            ['day' => 6, 'title' => 'Udaipur City of Lakes Tour', 'desc' => 'City Palace, Saheliyon-ki-Bari, Jagdish Temple, and romantic sunset boat ride on Lake Pichola.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Trident, Udaipur'],
            ['day' => 7, 'title' => 'Departure from Udaipur', 'desc' => 'Breakfast and transfer to Udaipur Airport for return flight.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ],
    [
        'cat_slug' => 'luxury-heritage',
        'dest_slug' => 'dubai',
        'title' => 'Dazzling Dubai: Desert Safari, Marina & Burj Khalifa',
        'slug' => 'dazzling-dubai-desert-safari-burj-khalifa',
        'summary' => '124th floor Burj Khalifa view, 4x4 dune bashing desert safari with BBQ, and Dubai Marina dhow cruise.',
        'description' => "Experience the glitz and glamour of Dubai. Marvel at the city skyline from the 124th floor of Burj Khalifa, experience thrilling dune bashing and belly dancing in the Arabian desert, sail the Marina on a glass dhow, and shop at the Gold Souk.",
        'duration_days' => 5,
        'duration_nights' => 4,
        'price_adult' => 45000.00,
        'price_child' => 28000.00,
        'price_infant' => 5000.00,
        'featured_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=80',
        'group_size_max' => 20,
        'inclusions' => "• 4 Nights in 4-Star Downtown Hotel\n• Daily International Buffet Breakfast\n• Burj Khalifa 124th Floor Off-Peak Entry Ticket\n• 4x4 Desert Safari with Dune Bashing, Camel Ride, & BBQ Dinner\n• Dubai Marina Luxury Dhow Cruise with Dinner\n• Return Airport Transfers by Private Vehicle",
        'exclusions' => "• International Flights & UAE Tourist Visa\n• Tourism Dirham Fee payable at hotel\n• Personal expenses & optional activities",
        'cancellation_policy_text' => "• 30+ days: 85% refund\n• 15-29 days: 40% refund\n• Within 14 days: No refund",
        'hotel_details' => 'Dubai: Millennium Central Downtown (4 Star)',
        'is_featured' => 1,
        'is_domestic' => 0,
        'tour_type' => 'luxury',
        'itinerary' => [
            ['day' => 1, 'title' => 'Arrival in Dubai & Marina Dhow Cruise', 'desc' => 'Arrive at DXB Airport. Transfer to hotel. Evening 2-hour luxury Marina Dhow Cruise with international buffet dinner.', 'meals' => 'Dinner', 'stay' => 'Millennium Central, Dubai'],
            ['day' => 2, 'title' => 'Half-Day Dubai City Tour & Burj Khalifa', 'desc' => 'Drive past Dubai Frame, Jumeirah Mosque, and Burj Al Arab. Evening visit to Dubai Mall and 124th floor of Burj Khalifa.', 'meals' => 'Breakfast', 'stay' => 'Millennium Central, Dubai'],
            ['day' => 3, 'title' => 'Morning Leisure & Desert Safari with BBQ', 'desc' => 'Morning free for shopping at Gold and Spice Souk. Afternoon 4x4 desert safari, dune bashing, sand boarding, tanoura dance, and BBQ dinner under the stars.', 'meals' => 'Breakfast, Dinner', 'stay' => 'Millennium Central, Dubai'],
            ['day' => 4, 'title' => 'Miracle Garden & Global Village', 'desc' => 'Explore the millions of blooming flowers at Dubai Miracle Garden, followed by an evening immersing in cultures at Global Village.', 'meals' => 'Breakfast', 'stay' => 'Millennium Central, Dubai'],
            ['day' => 5, 'title' => 'Departure from Dubai', 'desc' => 'Breakfast, check out, and transfer to Dubai International Airport for departure.', 'meals' => 'Breakfast', 'stay' => 'Tour Ends']
        ]
    ]
];

$pkgStmt = $db->prepare("
    INSERT INTO tour_packages (
        category_id, destination_id, title, slug, summary, description,
        duration_days, duration_nights, price_adult, price_child, price_infant,
        featured_image, group_size_max, inclusions, exclusions,
        cancellation_policy_text, hotel_details, is_featured, is_domestic,
        tour_type, status, seo_title, seo_meta_description
    ) VALUES (
        (SELECT id FROM tour_categories WHERE slug = :cat_slug LIMIT 1),
        (SELECT id FROM destinations WHERE slug = :dest_slug LIMIT 1),
        :title, :slug, :summary, :description,
        :duration_days, :duration_nights, :price_adult, :price_child, :price_infant,
        :featured_image, :group_size_max, :inclusions, :exclusions,
        :cancellation_policy_text, :hotel_details, :is_featured, :is_domestic,
        :tour_type, 'published', :seo_title, :seo_meta_desc
    )
");

$itinStmt = $db->prepare("
    INSERT INTO tour_itineraries (package_id, day_number, title, description, meals_included, accommodation)
    VALUES (:pid, :day, :title, :desc, :meals, :stay)
");

$depStmt = $db->prepare("
    INSERT INTO tour_departures (package_id, departure_date, return_date, total_seats, available_seats, adult_price, child_price, status)
    VALUES (:pid, :dep_date, :ret_date, :total, :avail, :aprice, :cprice, 'open')
");

foreach ($packages as $p) {
    $pkgStmt->execute([
        ':cat_slug' => $p['cat_slug'],
        ':dest_slug' => $p['dest_slug'],
        ':title' => $p['title'],
        ':slug' => $p['slug'],
        ':summary' => $p['summary'],
        ':description' => $p['description'],
        ':duration_days' => $p['duration_days'],
        ':duration_nights' => $p['duration_nights'],
        ':price_adult' => $p['price_adult'],
        ':price_child' => $p['price_child'],
        ':price_infant' => $p['price_infant'],
        ':featured_image' => $p['featured_image'],
        ':group_size_max' => $p['group_size_max'],
        ':inclusions' => $p['inclusions'],
        ':exclusions' => $p['exclusions'],
        ':cancellation_policy_text' => $p['cancellation_policy_text'],
        ':hotel_details' => $p['hotel_details'],
        ':is_featured' => $p['is_featured'],
        ':is_domestic' => $p['is_domestic'],
        ':tour_type' => $p['tour_type'],
        ':seo_title' => $p['title'] . ' | YatraNest',
        ':seo_meta_desc' => substr($p['summary'], 0, 160)
    ]);
    $packageId = (int)$db->lastInsertId();

    // Insert itinerary
    foreach ($p['itinerary'] as $it) {
        $itinStmt->execute([
            ':pid' => $packageId,
            ':day' => $it['day'],
            ':title' => $it['title'],
            ':desc' => $it['desc'],
            ':meals' => $it['meals'],
            ':stay' => $it['stay']
        ]);
    }

    // Insert Departures across the next 3 months
    $baseDates = [10, 20, 30, 45, 60, 75];
    foreach ($baseDates as $offsetDays) {
        $depDate = date('Y-m-d', strtotime("+$offsetDays days"));
        $retDate = date('Y-m-d', strtotime("+$offsetDays days +{$p['duration_days']} days"));
        $depStmt->execute([
            ':pid' => $packageId,
            ':dep_date' => $depDate,
            ':ret_date' => $retDate,
            ':total' => 20,
            ':avail' => 18, // 2 already reserved as demo
            ':aprice' => $p['price_adult'],
            ':cprice' => $p['price_child'],
        ]);
    }
}

// 6. Coupons
echo "Seeding coupons...\n";
$db->exec("TRUNCATE TABLE coupons");
$coupons = [
    [
        'code' => 'WELCOME10',
        'discount_type' => 'percentage',
        'discount_value' => 10.00,
        'min_booking_amount' => 10000.00,
        'max_discount' => 2500.00,
        'start_date' => date('Y-m-d', strtotime('-1 month')),
        'expiry_date' => date('Y-m-d', strtotime('+6 months')),
        'usage_limit' => 500,
        'per_user_limit' => 2,
        'used_count' => 12,
        'is_active' => 1
    ],
    [
        'code' => 'FLAT1000',
        'discount_type' => 'fixed',
        'discount_value' => 1000.00,
        'min_booking_amount' => 15000.00,
        'max_discount' => 1000.00,
        'start_date' => date('Y-m-d', strtotime('-1 month')),
        'expiry_date' => date('Y-m-d', strtotime('+6 months')),
        'usage_limit' => 200,
        'per_user_limit' => 1,
        'used_count' => 5,
        'is_active' => 1
    ],
    [
        'code' => 'EARLYBIRD',
        'discount_type' => 'percentage',
        'discount_value' => 15.00,
        'min_booking_amount' => 20000.00,
        'max_discount' => 3500.00,
        'start_date' => date('Y-m-d', strtotime('-1 month')),
        'expiry_date' => date('Y-m-d', strtotime('+6 months')),
        'usage_limit' => 100,
        'per_user_limit' => 1,
        'used_count' => 8,
        'is_active' => 1
    ]
];
$cpnStmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_booking_amount, max_discount, start_date, expiry_date, usage_limit, per_user_limit, used_count, is_active) VALUES (:code, :discount_type, :discount_value, :min_booking_amount, :max_discount, :start_date, :expiry_date, :usage_limit, :per_user_limit, :used_count, :is_active)");
foreach ($coupons as $c) {
    $cpnStmt->execute($c);
}

// 7. Site Settings
echo "Seeding site settings...\n";
$db->exec("TRUNCATE TABLE site_settings");
$settings = [
    ['setting_key' => 'site_name', 'setting_value' => 'YatraNest', 'setting_group' => 'general', 'description' => 'Website Brand Name'],
    ['setting_key' => 'site_tagline', 'setting_value' => 'Crafting Unforgettable Journeys Across The Globe', 'setting_group' => 'general', 'description' => 'Tagline displayed on header/hero'],
    ['setting_key' => 'company_name', 'setting_value' => 'YatraNest Holidays Pvt Ltd', 'setting_group' => 'general', 'description' => 'Legal registered company name'],
    ['setting_key' => 'company_address', 'setting_value' => 'Suite 402, Travel Towers, MG Road, Bengaluru, Karnataka 560001, India', 'setting_group' => 'general', 'description' => 'Official physical address'],
    ['setting_key' => 'company_phone', 'setting_value' => '+91 98765 43210', 'setting_group' => 'general', 'description' => 'Customer support telephone'],
    ['setting_key' => 'company_email', 'setting_value' => 'support@yatranest.com', 'setting_group' => 'general', 'description' => 'Primary public contact email'],
    ['setting_key' => 'company_gstin', 'setting_value' => '29ABCDE1234F1Z5', 'setting_group' => 'general', 'description' => 'GST Registration Number'],
    ['setting_key' => 'tax_rate', 'setting_value' => '5.00', 'setting_group' => 'booking', 'description' => 'Applicable GST / VAT tax rate percentage'],
    ['setting_key' => 'currency_code', 'setting_value' => 'INR', 'setting_group' => 'booking', 'description' => 'Base currency ISO code'],
    ['setting_key' => 'currency_symbol', 'setting_value' => '₹', 'setting_group' => 'booking', 'description' => 'Base currency display symbol'],
    ['setting_key' => 'hold_timeout_minutes', 'setting_value' => '15', 'setting_group' => 'booking', 'description' => 'Seat checkout hold reservation expiration in minutes'],
    ['setting_key' => 'razorpay_mode', 'setting_value' => 'test', 'setting_group' => 'payment', 'description' => 'Payment Gateway Mode (test or live)'],
    ['setting_key' => 'razorpay_key_id', 'setting_value' => 'rzp_test_sampleKey12345', 'setting_group' => 'payment', 'description' => 'Razorpay Public Key ID'],
    ['setting_key' => 'google_maps_api_key', 'setting_value' => '', 'setting_group' => 'integrations', 'description' => 'Google Maps JavaScript API key (optional)'],
];
$setStmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group, description) VALUES (:setting_key, :setting_value, :setting_group, :description)");
foreach ($settings as $s) {
    $setStmt->execute($s);
}

// 8. Sample Reviews
echo "Seeding reviews...\n";
$db->exec("TRUNCATE TABLE reviews");
$reviews = [
    [
        'package_slug' => 'kashmir-paradise-srinagar-gulmarg-pahalgam',
        'email' => 'customer@example.com',
        'rating' => 5,
        'title' => 'Unbelievable experience in Kashmir!',
        'text' => 'Our trip with YatraNest was seamless from start to finish. The houseboat stay in Dal Lake was like a dream, and our driver was courteous and knowledgeable.'
    ],
    [
        'package_slug' => 'magical-kerala-backwaters-munnar-alleppey',
        'email' => 'priya@example.com',
        'rating' => 5,
        'title' => 'Most romantic Kerala honeymoon trip',
        'text' => 'The Alleppey houseboat was clean and the freshly prepared Karimeen fish dinner was delicious. Munnar tea estates were breathtaking.'
    ],
    [
        'package_slug' => 'himachal-highs-shimla-manali-rohtang',
        'email' => 'customer@example.com',
        'rating' => 5,
        'title' => 'Thrilling adventure in Solang Valley',
        'text' => 'Loved the paragliding and the trip through Atal Tunnel. Good hotels, friendly guides, and delicious buffet breakfasts throughout.'
    ],
    [
        'package_slug' => 'royal-rajasthan-jaipur-jodhpur-udaipur',
        'email' => 'priya@example.com',
        'rating' => 5,
        'title' => 'Truly royal treatment across Rajasthan',
        'text' => 'The heritage properties selected by YatraNest made us feel like royalty. The sunset boat ride on Lake Pichola in Udaipur was magical.'
    ],
    [
        'package_slug' => 'goa-beach-bliss-sun-sand-heritage',
        'email' => 'customer@example.com',
        'rating' => 5,
        'title' => 'Relaxing and vibrant Goa holiday',
        'text' => 'The beach resort was fantastic and the private Mandovi river sunset cruise was unforgettable. Everything was punctual and hassle-free.'
    ],
    [
        'package_slug' => 'dazzling-dubai-desert-safari-burj-khalifa',
        'email' => 'priya@example.com',
        'rating' => 5,
        'title' => 'Glitz, thrill, and luxury in Dubai',
        'text' => 'Desert safari dune bashing followed by dinner under the stars was an adrenaline rush! The Burj Khalifa 124th-floor views were breathtaking.'
    ]
];

$revStmt = $db->prepare("
    INSERT INTO reviews (package_id, user_id, rating, review_title, review_text, status)
    VALUES (
        (SELECT id FROM tour_packages WHERE slug = :pslug LIMIT 1),
        (SELECT id FROM users WHERE email = :email LIMIT 1),
        :rating, :title, :text, 'approved'
    )
");
foreach ($reviews as $rv) {
    $revStmt->execute([
        ':pslug' => $rv['package_slug'],
        ':email' => $rv['email'],
        ':rating' => $rv['rating'],
        ':title' => $rv['title'],
        ':text' => $rv['text']
    ]);
}

// Re-enable foreign keys
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\nDatabase seeding completed successfully for YatraNest!\n";
echo "Default Accounts Created:\n";
echo "  1. Super Admin: admin@yatranest.com / Admin@12345\n";
echo "  2. Booking Manager: manager@yatranest.com / Manager@12345\n";
echo "  3. Customer: customer@example.com / Customer@12345\n";
