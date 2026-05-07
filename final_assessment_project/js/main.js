/**
 * Uganda Martyrs University Event Management System
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Form validation
    initFormValidation();

    // Initialize event handlers based on page - with guards
    initEventsPage();
    initRSVPPage();

    // Reports removed
    if (document.querySelector('.admin-page, [data-page="admin"]')) {
        initAdminPage();
    }

    initEventsSlider();

    // Resize charts on window resize
    window.addEventListener('resize', debounce(function() {
        if (window.recentRsvpChart) window.recentRsvpChart.resize();
        if (window.upcomingEventsChart) window.upcomingEventsChart.resize();
    }, 250));
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Form Validation
 */
function initFormValidation() {
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const eventForm = document.getElementById('eventForm');

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let valid = true;

            const fullName = document.getElementById('full_name');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            clearErrors();

            if (!fullName.value.trim() || fullName.value.trim().length < 3) {
                showError('full_name_error', 'Full name must be at least 3 characters.');
                valid = false;
            }

            if (!email.value.trim() || !isValidEmail(email.value)) {
                showError('email_error', 'Please enter a valid email address.');
                valid = false;
            }

            if (!password.value || password.value.length < 6) {
                showError('password_error', 'Password must be at least 6 characters.');
                valid = false;
            }

            if (password.value !== confirmPassword.value) {
                showError('confirm_password_error', 'Passwords do not match.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let valid = true;
            clearErrors();

            const email = document.getElementById('email');
            const password = document.getElementById('password');

            if (!email.value.trim() || !isValidEmail(email.value)) {
                showError('email_error', 'Please enter a valid email.');
                valid = false;
            }

            if (!password.value) {
                showError('password_error', 'Password is required.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEventFormSubmit(this);
        });
    }
}

function showError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) el.textContent = message;
}

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Events Page - Search, Filter, Load
 */
function initEventsPage() {
    const eventsContainer = document.getElementById('eventsContainer');
    if (!eventsContainer) return;

    loadEvents();

    // Search and filter handlers - safe checks
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const applyFilters = document.getElementById('applyFilters');

    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadEvents(1), 300);
        });
    }

    if (applyFilters) {
        applyFilters.addEventListener('click', function() {
            loadEvents(1);
        });
    }
}

function loadEvents(page = 1) {
    const container = document.getElementById('eventsContainer');
     if (!container) return; 
    const pagination = document.getElementById('pagination');

    const search = document.getElementById('searchInput')?.value || '';
    const category = document.getElementById('categoryFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';

    container.innerHTML = '<div class="text-center"><div class="loading"></div><p>Loading events...</p></div>';

    const params = new URLSearchParams({ page, search, category, date_from: dateFrom, date_to: dateTo });

    fetch(`get_events.php?${params}`)
        .then(res => res.json().catch(() => ({ success: false, message: 'Server returned invalid JSON.' })))
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                return;
            }

            if (data.events.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">&#128197;</div>
                        <h3>No events found</h3>
                        <p>Try adjusting your search or filters.</p>
                    </div>`;
                if (pagination) pagination.innerHTML = '';
                return;
            }

            container.innerHTML = '<div class="events-grid">' +
                data.events.map(event => renderEventCard(event)).join('') +
                '</div>';

            // Render pagination
            if (pagination && data.pages > 1) {
                let html = '';
                for (let i = 1; i <= data.pages; i++) {
                    html += `<button class="${i === page ? 'active' : ''}" onclick="loadEvents(${i})">${i}</button>`;
                }
                pagination.innerHTML = html;
            }
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div class="alert alert-error">Failed to load events.</div>';
        });
}

function renderEventCard(event) {
    const date = new Date(event.event_date);
    const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    let actions = `<a href="event_details.php?id=${event.id}" class="btn btn-secondary btn-sm">View Details</a>`;

    if (event.has_rsvped) {
        actions += ` <button class="btn btn-outline btn-sm" onclick="handleRSVP(${event.id}, 'cancel')">Cancel RSVP</button>`;
    } else if (event.has_rsvped !== undefined) {
        actions += ` <button class="btn btn-primary btn-sm" onclick="handleRSVP(${event.id}, 'rsvp')">RSVP</button>`;
    }

    const imageStyle = event.image_url ? `style="background: url('${event.image_url}') center/cover no-repeat;"` : '';

    return `
        <div class="event-card">
            <div class="event-image" ${imageStyle}>
                <span>&#9733;</span>
                <span class="event-category">${event.category}</span>
            </div>
            <div class="event-body">
                <h3 class="event-title">${escapeHtml(event.title)}</h3>
                <div class="event-meta">
                    <span>&#128197; ${dateStr} at ${timeStr}</span>
                    <span>&#128205; ${escapeHtml(event.location)}</span>
                </div>
                <p class="event-description">${escapeHtml(event.description.substring(0, 120))}...</p>
                <div class="event-actions">
                    ${actions}
                </div>
            </div>
        </div>
    `;
}

/**
 * RSVP Handling
 */
function handleRSVP(eventId, action) {
    // Prefer meta tag, but also support hidden input used by my_rsvps.php
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    if (!csrfToken) {
        csrfToken = document.getElementById('csrfToken')?.value || '';
    }

    fetch('rsvp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `event_id=${eventId}&action=${action}&csrf_token=${encodeURIComponent(csrfToken)}`
    })
        .then(res => res.json())
        .then(data => {
            // Only show alert(s) when present to avoid duplicate/empty messages
            if (data && data.message) alert(data.message);

            if (data && data.success) {
                // Guard refreshes so cancelling on My RSVPs doesn't break other pages
                // (also prevents triggering loadEvents() -> TypeError when container is missing)
                if (document.getElementById('eventsContainer') && typeof loadEvents === 'function') {
                    loadEvents();
                }
                if (document.getElementById('rsvpsContainer') && typeof loadRSVPs === 'function') {
                    loadRSVPs();
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to process RSVP. Please try again.');
        });
}

/**
 * RSVP History Page
 */
function initRSVPPage() {
    const rsvpsContainer = document.getElementById('rsvpsContainer');
    if (!rsvpsContainer) return;

    loadRSVPs();

    const searchInput = document.getElementById('rsvpSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadRSVPs(), 300);
        });
    }
}

function loadRSVPs() {
    const container = document.getElementById('rsvpsContainer');
    const search = document.getElementById('rsvpSearch')?.value || '';
    const category = document.getElementById('rsvpCategory')?.value || '';

    container.innerHTML = '<div class="text-center"><div class="loading"></div><p>Loading your RSVPs...</p></div>';

    const params = new URLSearchParams({ search, category });

    fetch(`get_rsvps.php?${params}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                return;
            }

            if (data.rsvps.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">&#128197;</div>
                        <h3>No RSVPs yet</h3>
                        <p>Browse events and RSVP to see them here.</p>
                        <a href="events.php" class="btn btn-primary mt-2">Browse Events</a>
                    </div>`;
                return;
            }

            container.innerHTML = '<div class="events-grid">' +
                data.rsvps.map(rsvp => renderRSVPCard(rsvp)).join('') +
                '</div>';
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div class="alert alert-error">Failed to load RSVPs.</div>';
        });
}

function renderRSVPCard(rsvp) {
    const date = new Date(rsvp.event_date);
    const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    const imageStyle = rsvp.image_url ? `style="background: url('${rsvp.image_url}') center/cover no-repeat;"` : '';

    return `
        <div class="event-card">
            <div class="event-image" ${imageStyle}>
                <span>&#9733;</span>
                <span class="event-category">${rsvp.category}</span>
            </div>
            <div class="event-body">
                <h3 class="event-title">${escapeHtml(rsvp.title)}</h3>
                <div class="event-meta">
                    <span>&#128197; ${dateStr} at ${timeStr}</span>
                    <span>&#128205; ${escapeHtml(rsvp.location)}</span>
                </div>
                <p class="event-description">${escapeHtml(rsvp.description.substring(0, 120))}...</p>
                <div class="event-actions">
                    <a href="event_details.php?id=${rsvp.event_id}" class="btn btn-secondary btn-sm">View Details</a>
                    <button class="btn btn-outline btn-sm" onclick="handleRSVP(${rsvp.event_id}, 'cancel')">Cancel RSVP</button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Admin Reports - Charts
 */
/* removed initAdminReports */
function initAdminReports_removed() {
    // Reports UI removed.
    // Intentionally left blank to avoid fetching/JSON parsing issues.
}

function initCharts(data) {
    const recentCtx = document.getElementById('recentRsvpsChart')?.getContext('2d');
    const upcomingCtx = document.getElementById('upcomingEventsChart')?.getContext('2d');

    if (!recentCtx || !upcomingCtx) return;

    // Recent RSVPs Line Chart
    window.recentRsvpChart = new Chart(recentCtx, {
        type: 'line',
        data: {
            labels: data.recent_rsvps.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'New RSVPs',
                data: data.recent_rsvps.map(item => item.count),
                borderColor: 'rgb(212, 175, 55)',
                backgroundColor: 'rgba(212, 175, 55, 0.2)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(212, 175, 55)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(212, 175, 55)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Upcoming Events Bar Chart
    window.upcomingEventsChart = new Chart(upcomingCtx, {
        type: 'bar',
        data: {
            labels: data.upcoming_events.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Events',
                data: data.upcoming_events.map(item => item.count),
                backgroundColor: 'rgba(7, 7, 7, 0.8)',
                borderColor: 'rgb(212, 175, 55)',
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

/**
 * Admin Page - Event Management
 */
function initAdminPage() {
    const adminEventsTable = document.getElementById('adminEventsTable');
    if (!adminEventsTable) return;

    loadAdminEvents();

    // Modal handling
    const modal = document.getElementById('eventModal');
    const modalClose = document.querySelector('.modal-close');
    const createEventBtn = document.getElementById('createEventBtn');

    if (createEventBtn) {
        createEventBtn.addEventListener('click', function() {
            openModal('create');
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }
}

function loadAdminEvents() {
    const tbody = document.querySelector('#adminEventsTable tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';

    fetch('get_events.php?page=1&per_page=100')
        .then(res => res.json())
        .then(data => {
            if (!data.success || data.events.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No events found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.events.map(event => `
                <tr>
                    <td>${escapeHtml(event.title)}</td>
                    <td>${event.category}</td>
                    <td>${new Date(event.event_date).toLocaleDateString()}</td>
                    <td>${escapeHtml(event.location)}</td>
                    <td>${escapeHtml(event.creator_name || 'Unknown')}</td>
                    <td class="actions">
                        <button class="btn btn-secondary btn-sm" onclick="editEvent(${event.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteEvent(${event.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-error">Failed to load events.</td></tr>';
        });
}

function openModal(mode, eventId = null) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const title = document.getElementById('modalTitle');

    if (!modal || !form) return;

    form.reset();
    document.getElementById('event_id').value = eventId || '';

    if (mode === 'edit' && eventId) {
        title.textContent = 'Edit Event';

        // Load event data
        fetch(`get_events.php`)
            .then(res => res.json())
            .then(data => {
                const event = data.events.find(e => e.id == eventId);
                if (event) {
                    document.getElementById('eventTitle').value = event.title;
                    document.getElementById('eventDescription').value = event.description;
                    document.getElementById('eventCategory').value = event.category;
                    document.getElementById('eventDate').value = event.event_date.replace(' ', 'T');
                    document.getElementById('eventLocation').value = event.location;
                }
            });
    } else {
        title.textContent = 'Create New Event';
    }

    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.remove('active');
}

function handleEventFormSubmit(form) {
    const formData = new FormData(form);
    const eventIdEl = document.getElementById('event_id');
    const eventId = eventIdEl ? eventIdEl.value : '';
    const url = eventId ? 'update_event.php' : 'create_event.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeModal();
                loadAdminEvents();
            } else {
                alert(data.message || 'Failed to save event.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to save event. Please try again.');
        });
}

function editEvent(eventId) {
    openModal('edit', eventId);
}

function slideEvents(direction) {
    const slider = document.getElementById('eventsSlider');
    if (!slider) return;

    const cards = Array.from(slider.querySelectorAll('.slider-card'));
    if (cards.length === 0) return;

    const cardWidth = cards[0].offsetWidth || 320;
    const gap = 24;
    const scrollAmount = cardWidth + gap;

    // Add sliding class for CSS transition effects
    slider.classList.add('sliding');

    // Determine animation direction for cards
    cards.forEach(card => {
        card.classList.remove('slide-in-right', 'slide-in-left', 'slide-in-up', 'active-slide');
        if (direction > 0) {
            card.classList.add('slide-in-right');
        } else {
            card.classList.add('slide-in-left');
        }
    });

    // Peform scroll with smooth behavior
    slider.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });

    // Remove sliding class and highlight active slide after animation
    setTimeout(() => {
        slider.classList.remove('sliding');
        cards.forEach(card => card.classList.remove('slide-in-right', 'slide-in-left'));
        highlightActiveSlide(slider);
    }, 550);
}

/**
 * Highlight the card closest to the center of the slider viewport
 */
function highlightActiveSlide(slider) {
    if (!slider) return;
    const cards = slider.querySelectorAll('.slider-card');
    const sliderRect = slider.getBoundingClientRect();
    const sliderCenter = sliderRect.left + sliderRect.width / 2;
    let closestCard = null;
    let minDistance = Infinity;

    cards.forEach(card => {
        card.classList.remove('active-slide');
        const cardRect = card.getBoundingClientRect();
        const cardCenter = cardRect.left + cardRect.width / 2;
        const distance = Math.abs(sliderCenter - cardCenter);
        if (distance < minDistance) {
            minDistance = distance;
            closestCard = card;
        }
    });

    if (closestCard) {
        closestCard.classList.add('active-slide');
    }
}

function initEventsSlider() {
    const slider = document.getElementById('eventsSlider');
    if (!slider) return;

    const wrapper = slider.closest('.events-slider-wrapper');
    const cards = slider.querySelectorAll('.slider-card');
    if (cards.length === 0) return;

    let autoSlideInterval;
    let currentDirection = 1;
    let progressInterval;
    const autoSlideDelay = 4500;
    let isHovering = false;

    // Staggered entrance animation on load
    function triggerEntranceAnimation() {
        cards.forEach(card => {
            card.classList.remove('slide-in-up', 'slide-in-right', 'slide-in-left');
            void card.offsetWidth; // force reflow
            card.classList.add('slide-in-up');
        });
    }

    // Trigger entrance animation after a short delay
    setTimeout(triggerEntranceAnimation, 100);

    // Highlight the initially visible card(s)
    setTimeout(() => highlightActiveSlide(slider), 600);

    function updateProgressBar(elapsed) {
        if (!wrapper) return;
        let progressWrapper = wrapper.querySelector('.slider-progress-wrapper');
        let progressBar = wrapper.querySelector('.slider-progress-bar');
        if (!progressWrapper) {
            progressWrapper = document.createElement('div');
            progressWrapper.className = 'slider-progress-wrapper';
            progressBar = document.createElement('div');
            progressBar.className = 'slider-progress-bar';
            progressWrapper.appendChild(progressBar);
            wrapper.style.position = 'relative';
            wrapper.appendChild(progressWrapper);
        }
        const pct = Math.min(100, (elapsed / autoSlideDelay) * 100);
        if (progressBar) progressBar.style.width = pct + '%';
    }

    function clearProgressBar() {
        if (!wrapper) return;
        const progressBar = wrapper.querySelector('.slider-progress-bar');
        if (progressBar) progressBar.style.width = '0%';
    }

    function autoSlide() {
        const cardWidth = slider.querySelector('.slider-card')?.offsetWidth || 320;
        const gap = 24;
        const scrollAmount = cardWidth + gap;
        const maxScroll = slider.scrollWidth - slider.clientWidth;

        // Reverse direction at edges
        if (slider.scrollLeft >= maxScroll - 5) {
            currentDirection = -1;
        } else if (slider.scrollLeft <= 5) {
            currentDirection = 1;
        }

        // Add animation classes
        slider.classList.add('sliding');
        cards.forEach(card => {
            card.classList.remove('slide-in-right', 'slide-in-left');
            if (currentDirection > 0) {
                card.classList.add('slide-in-right');
            } else {
                card.classList.add('slide-in-left');
            }
        });

        slider.scrollBy({ left: currentDirection * scrollAmount, behavior: 'smooth' });

        setTimeout(() => {
            slider.classList.remove('sliding');
            cards.forEach(card => card.classList.remove('slide-in-right', 'slide-in-left'));
            highlightActiveSlide(slider);
        }, 550);
    }

    function startAutoSlide() {
        clearInterval(autoSlideInterval);
        clearInterval(progressInterval);
        clearProgressBar();

        let startTime = Date.now();

        progressInterval = setInterval(() => {
            if (!isHovering) {
                updateProgressBar(Date.now() - startTime);
            }
        }, 100);

        autoSlideInterval = setInterval(() => {
            if (!isHovering) {
                startTime = Date.now();
                clearProgressBar();
                autoSlide();
            }
        }, autoSlideDelay);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
        clearInterval(progressInterval);
        clearProgressBar();
    }

    // Start auto-slide
    startAutoSlide();

    // Hover pause/resume
    if (wrapper) {
        wrapper.addEventListener('mouseenter', () => { isHovering = true; stopAutoSlide(); });
        wrapper.addEventListener('mouseleave', () => { isHovering = false; startAutoSlide(); });
    }

    // IntersectionObserver: only auto-slide when in viewport
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (!isHovering) startAutoSlide();
                } else {
                    stopAutoSlide();
                }
            });
        }, { threshold: 0.3 });
        observer.observe(wrapper || slider);
    }

    // Pause on manual button click, resume after
    document.querySelectorAll('.slider-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            stopAutoSlide();
            setTimeout(() => { if (!isHovering) startAutoSlide(); }, 5500);
        });
    });

    // Update active slide on scroll end
    let scrollTimeout;
    slider.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => highlightActiveSlide(slider), 150);
    });
}

function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event? This will also remove all RSVPs.')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch('delete_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `event_id=${eventId}&csrf_token=${encodeURIComponent(csrfToken)}`
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                loadAdminEvents();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to delete event.');
        });
}

/**
 * Utility Functions
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

