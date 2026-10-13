<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class StaticPageController extends Controller {
    public function about(Request $request, Response $response): void {
        $this->render('front/pages/about', ['title' => 'About Us - YatraNest Holidays']);
    }

    public function faq(Request $request, Response $response): void {
        $this->render('front/pages/faq', ['title' => 'FAQ - Frequently Asked Questions']);
    }

    public function privacy(Request $request, Response $response): void {
        $this->render('front/pages/privacy', ['title' => 'Privacy Policy']);
    }

    public function terms(Request $request, Response $response): void {
        $this->render('front/pages/terms', ['title' => 'Terms and Conditions']);
    }

    public function cancellation(Request $request, Response $response): void {
        $this->render('front/pages/cancellation', ['title' => 'Cancellation & Refund Policy']);
    }

    public function hotels(Request $request, Response $response): void {
        $this->render('front/pages/hotels', ['title' => 'Hotels & Luxury Accommodations']);
    }
}
