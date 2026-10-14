<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <h1 class="fw-extrabold display-5 text-dark mb-2">Get in Touch With Us</h1>
        <p class="text-muted lead mx-auto" style="max-width: 650px;">
            Have questions about a holiday package, custom itinerary, or group booking? Our holiday planners are ready to assist.
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-light h-100">
                <h4 class="fw-bold text-dark mb-4">Contact Information</h4>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="p-3 bg-white text-primary rounded-circle shadow-sm fs-4">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Our Headquarters:</strong>
                        <span class="text-muted small">Suite 402, Travel Towers, MG Road, Bengaluru, Karnataka 560001, India</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="p-3 bg-white text-secondary rounded-circle shadow-sm fs-4">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Telephone Support:</strong>
                        <span class="text-muted small"><a href="tel:+916000595647" class="text-decoration-none text-dark fw-semibold">+91 60005 95647</a> (24/7 Helpline)</span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="p-3 bg-white text-success rounded-circle shadow-sm fs-4">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">WhatsApp Direct:</strong>
                        <a href="<?= whatsapp_url('Hi YatraNest, I would like to inquire about your travel packages and services.') ?>" target="_blank" rel="noopener" class="text-success text-decoration-none fw-semibold small">
                            +91 60005 95647 &bull; Chat on WhatsApp <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="p-3 bg-white text-warning rounded-circle shadow-sm fs-4">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Email Inquiries:</strong>
                        <span class="text-muted small">
                            <a href="mailto:yatranest18@gmail.com" class="text-decoration-none text-dark fw-semibold">yatranest18@gmail.com</a> / 
                            <a href="mailto:yatranest.bookings@gmail.com" class="text-decoration-none text-dark fw-semibold">yatranest.bookings@gmail.com</a>
                        </span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="p-3 bg-white text-info rounded-circle shadow-sm fs-4">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Office Hours:</strong>
                        <span class="text-muted small">Monday – Saturday: 9:00 AM – 8:00 PM IST</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validated Contact Form -->
        <div class="col-lg-7">
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">Send Us a Message</h4>

                <form action="<?= url('/contact/submit') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Your Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="John Doe" value="<?= e(old('name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Email Address *</label>
                            <input type="email" name="email" class="form-control" required placeholder="name@example.com" value="<?= e(old('email')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 60005 95647" value="<?= e(old('phone')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Subject *</label>
                            <input type="text" name="subject" class="form-control" required placeholder="Custom holiday inquiry, feedback..." value="<?= e(old('subject')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Your Message *</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Provide details about your desired travel dates, destination, number of guests..."><?= e(old('message')) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn yn-btn-primary px-5 py-2 fw-semibold">
                            <i class="bi bi-send me-1"></i> Send Inquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
