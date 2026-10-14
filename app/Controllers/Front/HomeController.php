<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Destination;
use App\Models\NewsletterSubscriber;
use App\Models\Review;
use App\Models\TourCategory;
use App\Models\TourPackage;

class HomeController extends Controller {
    public function index(Request $request, Response $response): void {
        $destModel = new Destination();
        $pkgModel = new TourPackage();
        $catModel = new TourCategory();
        $revModel = new Review();

        $destinations = $destModel->getAllActive();
        $featuredDestinations = $destModel->getFeatured(12);
        $featuredPackages = $pkgModel->getFeatured(6);
        $categories = $catModel->getAllActive();
        $reviews = $revModel->getRecentApproved(6);

        $this->render('front/home', [
            'title' => 'Home - Best Tour & Travel Holiday Packages',
            'destinations' => $destinations,
            'featured_destinations' => $featuredDestinations,
            'featured_packages' => $featuredPackages,
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
