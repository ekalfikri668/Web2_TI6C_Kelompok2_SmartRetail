/**
 * SmartRetail - Premium Technology Marketplace
 * Main JavaScript v2.0
 */

'use strict';

/* ─── Global Alert Utility ─────────────────────────────────── */
window.showAlert = function(title, message, icon = 'info', callback = null) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: icon,
            confirmButtonColor: '#2563eb',
            borderRadius: '16px'
        }).then((result) => {
            if (typeof callback === 'function') callback();
        });
    } else {
        alert(title + ': ' + message);
        if (typeof callback === 'function') callback();
    }
};

/* ─── DOMContentLoaded ─────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initBootstrapComponents();
    initNavbarScroll();
    initActiveNavLink();
    initProductCards();
    initQtyButtons();
    initFadeInAnimations();
    initTooltips();
    initFormValidation();
    initSearchEnhancement();
});

/* ─── Bootstrap Components ─────────────────────────────────── */
function initBootstrapComponents() {
    // Tooltips
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => new bootstrap.Tooltip(el));

        // Auto-close alerts after 5 seconds
        document.querySelectorAll('.alert.alert-dismissible').forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 5000);
        });
    }
}

/* ─── Navbar Scroll Effect ─────────────────────────────────── */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar-tech');
    if (!navbar) return;

    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.scrollY;
        if (currentScroll > 60) {
            navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.4)';
        } else {
            navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.25)';
        }
        lastScroll = currentScroll;
    }, { passive: true });
}

/* ─── Active Nav Link ──────────────────────────────────────── */
function initActiveNavLink() {
    const path = window.location.pathname;
    document.querySelectorAll('.navbar-tech .nav-link').forEach(link => {
        if (link.href && link.href !== '#' && path.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
}

/* ─── Product Cards Hover Shimmer ──────────────────────────── */
function initProductCards() {
    // Cards are styled via CSS transitions
    // Add ripple effect on click
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const link = this.querySelector('a[href]');
            if (link && !e.target.closest('button')) {
                window.location.href = link.href;
            }
        });
    });
}

/* ─── Qty Buttons ──────────────────────────────────────────── */
function initQtyButtons() {
    document.querySelectorAll('.qty-group').forEach(group => {
        const minusBtn = group.querySelector('.qty-minus');
        const plusBtn  = group.querySelector('.qty-plus');
        const input    = group.querySelector('.qty-input');
        if (!minusBtn || !plusBtn || !input) return;

        minusBtn.addEventListener('click', () => {
            const val = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            if (val > min) { input.value = val - 1; input.dispatchEvent(new Event('change')); }
        });
        plusBtn.addEventListener('click', () => {
            const val = parseInt(input.value) || 1;
            const max = parseInt(input.max) || 999;
            if (val < max) { input.value = val + 1; input.dispatchEvent(new Event('change')); }
        });
    });
}

/* ─── Fade-In Animations on Scroll ────────────────────────── */
function initFadeInAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.product-card, .card.animate, .section-animate').forEach(el => {
        observer.observe(el);
    });
}

/* ─── Tooltips ─────────────────────────────────────────────── */
function initTooltips() {
    if (typeof bootstrap === 'undefined') return;
    document.querySelectorAll('[title]').forEach(el => {
        if (!el.hasAttribute('data-bs-toggle')) {
            el.setAttribute('data-bs-toggle', 'tooltip');
            new bootstrap.Tooltip(el);
        }
    });
}

/* ─── Form Validation ──────────────────────────────────────── */
function initFormValidation() {
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/* ─── Search Enhancement ───────────────────────────────────── */
function initSearchEnhancement() {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;

    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // Placeholder for autocomplete/suggestions
        }, 300);
    });

    // Clear button
    const clearBtn = document.querySelector('.search-clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.focus();
        });
    }
}

/* ─── Price Formatter ──────────────────────────────────────── */
window.formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

/* ─── Show Toast ───────────────────────────────────────────── */
window.showToast = (message, type = 'success', duration = 3000) => {
    const colors = {
        success: { bg: '#10b981', icon: 'fa-check-circle' },
        error:   { bg: '#ef4444', icon: 'fa-times-circle' },
        warning: { bg: '#f59e0b', icon: 'fa-exclamation-triangle' },
        info:    { bg: '#3b82f6', icon: 'fa-info-circle' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.className = 'position-fixed end-0 p-3 fade-in';
    toast.style.cssText = `bottom: 20px; z-index: 10050; max-width: 340px;`;
    toast.innerHTML = `
        <div class="d-flex align-items-center gap-2 text-white rounded-3 px-4 py-3 shadow-lg"
             style="background:${c.bg}; animation: fadeInUp 0.3s ease;">
            <i class="fa-solid ${c.icon} fs-5"></i>
            <span style="font-size:0.9rem; font-weight:500;">${message}</span>
        </div>`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; setTimeout(() => toast.remove(), 400); }, duration);
};

/* ─── Confirm Action ───────────────────────────────────────── */
window.confirmAction = (message, onConfirm) => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            borderRadius: '16px',
        }).then(result => { if (result.isConfirmed) onConfirm(); });
    } else if (confirm(message)) {
        onConfirm();
    }
};

/* ─── Loading Overlay ──────────────────────────────────────── */
window.showLoading = () => {
    let overlay = document.getElementById('page-loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'page-loading-overlay';
        overlay.className = 'page-spinner';
        overlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <div class="text-muted small">Memuat...</div>
            </div>`;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
};
window.hideLoading = () => {
    const overlay = document.getElementById('page-loading-overlay');
    if (overlay) overlay.style.display = 'none';
};

/* ─── Image Error Fallback ─────────────────────────────────── */
document.addEventListener('error', (e) => {
    if (e.target.tagName === 'IMG') {
        e.target.src = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%2394a3b8' font-family='Inter,sans-serif' font-size='13'%3ENo Image%3C/text%3E%3C/svg%3E`;
    }
}, true);

/* ─── Currency Input Auto-Format ───────────────────────────── */
document.querySelectorAll('.currency-input').forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
});
