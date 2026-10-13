<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Destination;
use App\Models\Review;
use App\Models\TourCategory;
use App\Models\TourDeparture;
use App\Models\TourItinerary;
use App\Models\TourPackage;

class TourPackageController extends Controller {
    public function index(Request $request, Response $response): void {
        $pkgModel = new TourPackage();
        $destModel = new Destination();
        $catModel = new TourCategory();

        $filters = [
            'keyword' => $request->getQuery('keyword'),
            'destination_id' => $request->getQuery('destination_id'),
            'category_id' => $request->getQuery('category_id'),
            'is_domestic' => $request->getQuery('is_domestic'),
            'duration' => $request->getQuery('duration'),
            'min_price' => $request->getQuery('min_price'),
            'max_price' => $request->getQuery('max_price'),
        ];

        $sort = (string)$request->getQuery('sort', 'popular');
        $page = max(1, (int)$request->getQuery('page', 1));
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $results = $pkgModel->search($filters, $sort, $limit, $offset);

        $destinations = $destModel->getAllActive();
        $categories = $catModel->getAllActive();

        $this->render('front/packages/index', [
            'title' => 'Tour Packages - Browse All Holiday Itineraries',
            'packages' => $results['packages'],
            'total' => $results['total'],
            'page' => $results['page'],
            'pages' => $results['pages'],
            'filters' => $filters,
            'sort' => $sort,
            'destinations' => $destinations,
            'categories' => $categories,
        ]);
    }

    public function detail(Request $request, Response $response, string $slug): void {
        $pkgModel = new TourPackage();
        $package = $pkgModel->findBySlug($slug);

        if (!$package) {
            $response->setStatusCode(404);
            $this->render('front/errors/404', ['title' => 'Tour Package Not Found']);
            return;
        }

        $itinModel = new TourItinerary();
        $itineraries = $itinModel->getForPackage($package['id']);

        $depModel = new TourDeparture();
        $departures = $depModel->getUpcomingForPackage($package['id']);

        $revModel = new Review();
        $reviews = $revModel->getForPackage($package['id']);

        $this->render('front/packages/detail', [
            'title' => $package['title'],
            'meta_description' => $package['seo_meta_description'] ?? $package['summary'],
            'meta_image' => $package['featured_image'],
            'package' => $package,
            'itineraries' => $itineraries,
            'departures' => $departures,
            'reviews' => $reviews,
        ]);
    }
}
