<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <h1 class="fw-extrabold display-5 text-dark mb-2">Hotels & Luxury Accommodations</h1>
        <p class="text-muted lead mx-auto" style="max-width: 650px;">
            Partner resorts, boutique heritage stays, and deluxe houseboats curated across our destination network.
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
            All hotel accommodations are included when you book our complete <a href="<?= url('/packages') ?>" class="fw-bold text-dark">Tour Packages</a>. For standalone hotel bookings or customized stays, submit an enquiry below.
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80" alt="Resort" class="w-100 object-fit-cover" style="height: 220px;">
                <div class="card-body p-4">
                    <span class="badge bg-secondary mb-2">5-Star Luxury</span>
                    <h5 class="fw-bold text-dark">Heritage Palaces & Resorts</h5>
                    <p class="text-muted small">Royal Rajputana palaces in Udaipur & Jaipur, luxury lakeview rooms, and private courtyards.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80" alt="Houseboat" class="w-100 object-fit-cover" style="height: 220px;">
                <div class="card-body p-4">
                    <span class="badge bg-secondary mb-2">Alleppey & Srinagar</span>
                    <h5 class="fw-bold text-dark">Deluxe Backwater Houseboats</h5>
                    <p class="text-muted small">Handcrafted cedarwood houseboats with private bedrooms, personal chef, and sun decks.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80" alt="Hill Resort" class="w-100 object-fit-cover" style="height: 220px;">
                <div class="card-body p-4">
                    <span class="badge bg-secondary mb-2">Manali & Munnar</span>
                    <h5 class="fw-bold text-dark">Alpine & Tea Valley Retreats</h5>
                    <p class="text-muted small">Panoramic mountain views, fireplace lounges, organic gardens, and bonfire evenings.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Standalone Hotel Enquiry Form -->
    <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 bg-white">
        <h4 class="fw-bold text-dark mb-2">Inquire for Custom Hotel Reservation</h4>
        <p class="text-muted small mb-4">Looking for a specific hotel, airport transfer, or flight add-on? Let our concierge team arrange it for you.</p>

        <form action="<?= url('/contact/submit') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="subject" value="Hotel & Add-on Booking Enquiry">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Your Name *</label>
                    <input type="text" name="name" class="form-control form-control-sm" required placeholder="John Doe" value="<?= e(auth()->user()['name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Email Address *</label>
                    <input type="email" name="email" class="form-control form-control-sm" required placeholder="name@example.com" value="<?= e(auth()->user()['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Mobile Number *</label>
                    <input type="tel" name="phone" class="form-control form-control-sm" required placeholder="+91 98765 43210" value="<?= e(auth()->user()['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-dark">Your Requirement *</label>
                    <textarea name="message" class="form-control" rows="3" required placeholder="Specify preferred destination, check-in/out dates, hotel category (3-star, 4-star, 5-star), and number of rooms..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn yn-btn-primary px-4">Submit Hotel Concierge Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
