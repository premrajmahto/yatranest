<footer class="footer-yn">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-airplane-engines fs-2 text-warning"></i>
                    <h4 class="m-0 text-white fw-bold">Yatra<span class="text-info">Nest</span></h4>
                </div>
                <p class="text-secondary small mb-4 pe-lg-4">
                    Your premier tour & travel booking partner. Crafting bespoke journeys, authentic cultural experiences, and unforgettable domestic and international holidays.
                </p>
                <div class="d-flex gap-3 fs-5">
                    <a href="#" class="text-light"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= url('/destinations') ?>">Destinations</a></li>
                    <li><a href="<?= url('/packages') ?>">Tour Packages</a></li>
                    <li><a href="<?= url('/hotels') ?>">Hotels & Stays</a></li>
                    <li><a href="<?= url('/about') ?>">About Us</a></li>
                    <li><a href="<?= url('/contact') ?>">Contact Support</a></li>
                    <li><a href="<?= url('/faq') ?>">Frequently Asked Questions</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Top Destinations</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="<?= url('/packages?keyword=Kashmir') ?>">Kashmir Paradise</a></li>
                    <li><a href="<?= url('/packages?keyword=Kerala') ?>">Kerala Backwaters</a></li>
                    <li><a href="<?= url('/packages?keyword=Himachal') ?>">Himachal & Manali</a></li>
                    <li><a href="<?= url('/packages?keyword=Goa') ?>">Goa Beach Holidays</a></li>
                    <li><a href="<?= url('/packages?keyword=Dubai') ?>">Dazzling Dubai Tours</a></li>
                    <li><a href="<?= url('/packages?keyword=Rajasthan') ?>">Royal Rajasthan</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Newsletter</h5>
                <p class="small text-secondary mb-3">Subscribe to get exclusive secret deals, early-bird holiday discounts, and travel guides.</p>
                <form action="<?= url('/newsletter') ?>" method="POST" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="input-group input-group-sm">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        <button class="btn btn-warning fw-bold text-dark" type="submit">Join</button>
                    </div>
                </form>
                <div class="small text-secondary">
                    <div><i class="bi bi-shield-check text-success me-1"></i> Verified Secure Razorpay Gateway</div>
                    <div><i class="bi bi-headset text-info me-1"></i> 24/7 Traveler Emergency Support</div>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                &copy; <?= date('Y') ?> <strong>YatraNest Holidays Pvt Ltd</strong>. All rights reserved.
            </div>
            <div class="d-flex gap-3 small">
                <a href="<?= url('/privacy') ?>">Privacy Policy</a>
                <a href="<?= url('/terms') ?>">Terms & Conditions</a>
                <a href="<?= url('/cancellation-policy') ?>">Cancellation Policy</a>
            </div>
        </div>
    </div>
</footer>
