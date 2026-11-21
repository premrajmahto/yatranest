<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Destination;
use App\Models\NewsletterSubscriber;
use App\Models\Review;
use App\Models\TourCategory;
use App\Models\TourDeparture;
use App\Models\TourPackage;

class HomeController extends Controller {
    public function index(Request $request, Response $response): void {
        $destModel = new Destination();
        $pkgModel = new TourPackage();
        $catModel = new TourCategory();
        $revModel = new Review();
        $depModel = new TourDeparture();

        $destinations = $destModel->getAllActive();
        $featuredDestinations = $destModel->getFeatured(12);
        $featuredPackages = $pkgModel->getFeatured(6);
        $categories = $catModel->getAllActive();
        $reviews = $revModel->getRecentApproved(6);

        // Fetch all active packages for booking modal
        $searchResult = $pkgModel->search([], 'popular', 50);
        $allPackages = !empty($searchResult['packages']) ? $searchResult['packages'] : $featuredPackages;

        $today = date('Y-m-d');
        $depSql = "SELECT d.id, d.package_id, d.departure_date, d.available_seats, d.adult_price, d.child_price,
                          (SELECT COALESCE(SUM(seats_held), 0) FROM booking_holds WHERE departure_id = d.id AND expires_at > NOW()) as current_held_seats
                   FROM tour_departures d
                   JOIN tour_packages p ON d.package_id = p.id
                   WHERE p.status = 'published'
                     AND d.departure_date >= :today
                     AND d.status = 'open'
                   ORDER BY d.departure_date ASC";
        $depStmt = $depModel->getDb()->prepare($depSql);
        $depStmt->execute([':today' => $today]);
        $rawDepartures = $depStmt->fetchAll();

        $departuresByPackage = [];
        foreach ($rawDepartures as $dep) {
            $realSeats = max(0, (int)$dep['available_seats'] - (int)$dep['current_held_seats']);
            $departuresByPackage[$dep['package_id']][] = [
                'id' => (int)$dep['id'],
                'package_id' => (int)$dep['package_id'],
                'departure_date' => $dep['departure_date'],
                'formatted_date' => date('d M Y (D)', strtotime($dep['departure_date'])),
                'available_seats' => $realSeats,
                'adult_price' => $dep['adult_price'] !== null ? (float)$dep['adult_price'] : null,
                'child_price' => $dep['child_price'] !== null ? (float)$dep['child_price'] : null,
            ];
        }

        $packagesMap = [];
        foreach ($allPackages as $pkg) {
            $packagesMap[$pkg['id']] = [
                'id' => (int)$pkg['id'],
                'title' => $pkg['title'],
                'slug' => $pkg['slug'],
                'destination_name' => $pkg['destination_name'] ?? '',
                'duration_days' => (int)$pkg['duration_days'],
                'duration_nights' => (int)$pkg['duration_nights'],
                'price_adult' => (float)$pkg['price_adult'],
                'price_child' => (float)$pkg['price_child'],
                'featured_image' => $pkg['featured_image'],
                'is_domestic' => (bool)($pkg['is_domestic'] ?? true),
            ];
        }

        $this->render('front/home', [
            'title' => 'Home - Best Tour & Travel Holiday Packages',
            'destinations' => $destinations,
            'featured_destinations' => $featuredDestinations,
            'featured_packages' => $featuredPackages,
            'all_packages' => $allPackages,
            'packages_map' => $packagesMap,
            'departures_by_package' => $departuresByPackage,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    }

    public function newsletter(Request $request, Response $response): void {
        $email = strtolower(trim((string)$request->input('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please provide a valid email address.');
            $this->redirect('/');
        }

        $newsModel = new NewsletterSubscriber();
        $existing = $newsModel->findBy('email', $email);
        if (!$existing) {
            $newsModel->create(['email' => $email]);
            $this->flash('success', 'Thank you for subscribing to YatraNest holiday deals!');
        } else {
            $this->flash('info', 'You are already subscribed to our newsletter.');
        }

        $this->redirect('/');
    }
}
