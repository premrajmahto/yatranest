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
