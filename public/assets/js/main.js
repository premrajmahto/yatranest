/**
 * YatraNest Main Frontend JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Enable Bootstrap tooltips if any
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function (alert) {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Dynamic traveler add/remove fields if booking form exists
    initTravelerForm();

    // Customer reviews sliding carousel
    initReviewsSlider();
});

function initTravelerForm() {
    const adultInput = document.getElementById('adult_count');
    const childInput = document.getElementById('child_count');
    const travelersContainer = document.getElementById('travelers-list');

    if (!adultInput || !travelersContainer) return;

    function renderTravelerInputs() {
        const adults = parseInt(adultInput.value) || 1;
        const children = parseInt(childInput ? childInput.value : 0) || 0;
        const total = adults + children;

        // Current count of forms
        const existingForms = travelersContainer.querySelectorAll('.traveler-card');
        const currentCount = existingForms.length;

        if (currentCount < total) {
            for (let i = currentCount; i < total; i++) {
                const isChild = i >= adults;
                const typeText = isChild ? 'Child' : 'Adult';
                const card = document.createElement('div');
                card.className = 'card p-3 mb-3 traveler-card border-slate-200';
                card.innerHTML = `
                    <h6 class="fw-bold mb-3 text-secondary">
                        <i class="bi bi-person-badge me-1"></i> Traveler #${i + 1} (${typeText})
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" name="travelers[${i}][full_name]" class="form-control form-control-sm" required placeholder="As on Passport/Govt ID">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Age *</label>
                            <input type="number" name="travelers[${i}][age]" class="form-control form-control-sm" required min="${isChild ? 1 : 12}" max="${isChild ? 11 : 100}" value="${isChild ? 8 : 28}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Gender *</label>
                            <select name="travelers[${i}][gender]" class="form-select form-select-sm" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="hidden" name="travelers[${i}][traveler_type]" value="${isChild ? 'child' : 'adult'}">
                        </div>
                    </div>
                `;
                travelersContainer.appendChild(card);
            }
        } else if (currentCount > total) {
            for (let i = currentCount - 1; i >= total; i--) {
                if (existingForms[i]) {
                    existingForms[i].remove();
                }
            }
        }
    }

    adultInput.addEventListener('change', renderTravelerInputs);
    if (childInput) {
        childInput.addEventListener('change', renderTravelerInputs);
    }
}

function initReviewsSlider() {
    const track = document.getElementById('reviewsSliderTrack');
    if (!track) return;

    const slides = track.querySelectorAll('.yn-reviews-slide');
    if (!slides.length) return;

    const prevBtn = document.querySelector('.yn-slider-prev');
    const nextBtn = document.querySelector('.yn-slider-next');
    const dotsContainer = document.getElementById('reviewsSliderDots');

    let currentIndex = 0;
    let autoplayTimer = null;

    function getVisibleCount() {
        if (window.innerWidth < 576) return 1;
        if (window.innerWidth < 992) return 2;
        return 3;
    }

    function getMaxIndex() {
        return Math.max(0, slides.length - getVisibleCount());
    }

    function updateDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        const maxIdx = getMaxIndex();
        for (let i = 0; i <= maxIdx; i++) {
            const dot = document.createElement('button');
            dot.className = 'yn-slider-dot' + (i === currentIndex ? ' active' : '');
            dot.setAttribute('aria-label', 'Go to review slide ' + (i + 1));
            dot.addEventListener('click', () => {
                goToSlide(i);
                restartAutoplay();
            });
            dotsContainer.appendChild(dot);
        }
    }

    function goToSlide(index) {
        const maxIdx = getMaxIndex();
        if (index < 0) {
            currentIndex = maxIdx;
        } else if (index > maxIdx) {
            currentIndex = 0;
        } else {
            currentIndex = index;
        }

        const visibleCount = getVisibleCount();
        const percentPerSlide = 100 / visibleCount;
        track.style.transform = `translateX(-${currentIndex * percentPerSlide}%)`;

        if (dotsContainer) {
            const dots = dotsContainer.querySelectorAll('.yn-slider-dot');
            dots.forEach((d, idx) => {
                d.classList.toggle('active', idx === currentIndex);
            });
        }
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            restartAutoplay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            restartAutoplay();
        });
    }

    // Touch support (swipe)
    let startX = 0;
    let endX = 0;

    track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        stopAutoplay();
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        if (Math.abs(diff) > 40) {
            if (diff > 0) nextSlide();
            else prevSlide();
        }
        restartAutoplay();
    }, { passive: true });

    // Autoplay with hover pause
    function startAutoplay() {
        if (!autoplayTimer) {
            autoplayTimer = setInterval(nextSlide, 4500);
        }
    }

    function stopAutoplay() {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
    }

    const wrapper = track.closest('.yn-reviews-carousel-wrapper');
    if (wrapper) {
        wrapper.addEventListener('mouseenter', stopAutoplay);
        wrapper.addEventListener('mouseleave', startAutoplay);
    }

    window.addEventListener('resize', () => {
        const maxIdx = getMaxIndex();
        if (currentIndex > maxIdx) currentIndex = maxIdx;
        updateDots();
        goToSlide(currentIndex);
    });

    updateDots();
    goToSlide(0);
    startAutoplay();
}

