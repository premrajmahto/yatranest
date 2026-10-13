<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Destination;
use App\Models\TourPackage;

class DestinationController extends Controller {
    public function index(Request $request, Response $response): void {
        $destModel = new Destination();

        $search = trim((string)$request->getQuery('search', ''));
        $selectedCountry = trim((string)$request->getQuery('country', ''));

        $where = ["status = 'active'"];
        $params = [];

        if ($search !== '') {
            $where[] = "(name LIKE :s OR country LIKE :s OR state LIKE :s OR city LIKE :s)";
            $params[':s'] = "%$search%";
        }

        if ($selectedCountry !== '') {
            $where[] = "country = :c";
            $params[':c'] = $selectedCountry;
        }

        $destinations = $destModel->where(implode(' AND ', $where), $params, 'name ASC');

        // Add package counts
        foreach ($destinations as &$d) {
            $d['package_count'] = $destModel->getPackageCount($d['id']);
        }

        // Get distinct countries
        $allDests = $destModel->all('country ASC');
        $countries = array_unique(array_column($allDests, 'country'));

        $this->render('front/destinations/index', [
            'title' => 'Destinations - Explore India & International Vacations',
            'destinations' => $destinations,
            'countries' => $countries,
            'search' => $search,
            'selectedCountry' => $selectedCountry
        ]);
    }

    public function detail(Request $request, Response $response, string $slug): void {
        $destModel = new Destination();
        $dest = $destModel->findBy('slug', $slug);

        if (!$dest || $dest['status'] !== 'active') {
            $response->setStatusCode(404);
            $this->render('front/errors/404', ['title' => 'Destination Not Found']);
            return;
        }

        $pkgModel = new TourPackage();
        $packages = $pkgModel->where("destination_id = :did AND status = 'published'", [':did' => $dest['id']], 'id DESC');

        $this->render('front/destinations/detail', [
            'title' => $dest['name'] . ' - Tour Packages & Travel Guide',
            'destination' => $dest,
            'packages' => $packages
        ]);
    }
}
