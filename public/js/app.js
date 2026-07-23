/* ========================================
   OLMS - Modern UI/UX JavaScript
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Initialize theme toggle
    setupThemeToggle();
    
    // Setup sidebar navigation
    setupSidebarNav();
    
    // Enhance forms
    enhanceForms();
    
    // Setup animations
    setupAnimations();
    
    // Add ripple effect to buttons
    addRippleEffect();
    
    // Setup tooltips
    setupTooltips();
    
    // Enhance tables
    enhanceTables();
    
    // Setup modals
    setupModals();
});

/* ========================================
   THEME MANAGEMENT (Streamlit-style)
   ======================================== */

const THEME_STORAGE_KEY = 'olms-theme-preference';

function getThemePreference() {
    const saved = localStorage.getItem(THEME_STORAGE_KEY);
    if (saved === 'system' || saved === 'light' || saved === 'dark') {
        return saved;
    }

    const legacy = localStorage.getItem('olms-theme');
    if (legacy === 'light' || legacy === 'dark') {
        return legacy;
    }

    return 'system';
}

function resolveEffectiveTheme(preference) {
    if (preference === 'dark' || preference === 'light') {
        return preference;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function initTheme() {
    const preference = getThemePreference();
    applyTheme(preference);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('no-transition');
        });
    });
}

function applyTheme(preference) {
    const effective = resolveEffectiveTheme(preference);
    const root = document.documentElement;

    if (effective === 'dark') {
        root.setAttribute('data-theme', 'dark');
        root.setAttribute('data-bs-theme', 'dark');
    } else {
        root.removeAttribute('data-theme');
        root.removeAttribute('data-bs-theme');
    }

    root.style.colorScheme = effective;
    localStorage.setItem(THEME_STORAGE_KEY, preference);
    // Clean up any legacy keys from earlier implementations
    localStorage.removeItem('olms-theme');
    localStorage.removeItem('theme');
    updateThemeSwitcher(preference);

    window.dispatchEvent(new CustomEvent('olms-theme-change', {
        detail: { preference, effective }
    }));
}

function setupThemeToggle() {
    const switcher = document.getElementById('theme-switcher');
    if (!switcher) {
        return;
    }

    switcher.querySelectorAll('.theme-option').forEach(button => {
        button.addEventListener('click', function () {
            const preference = this.getAttribute('data-theme-preference');
            if (!preference) {
                return;
            }
            applyTheme(preference);
            this.style.transform = 'scale(1.1)';
            setTimeout(() => {
                this.style.transform = '';
            }, 180);
        });
    });
}

function updateThemeSwitcher(preference) {
    const switcher = document.getElementById('theme-switcher');
    if (!switcher) {
        return;
    }

    switcher.querySelectorAll('.theme-option').forEach(button => {
        const isActive = button.getAttribute('data-theme-preference') === preference;
        button.classList.toggle('active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (getThemePreference() === 'system') {
        applyTheme('system');
    }
});

/* ========================================
   SIDEBAR NAVIGATION
   ======================================== */

function setupSidebarNav() {
    const navLinks = document.querySelectorAll('.sidebar-menu a');
    const currentPath = window.location.pathname;
    
    navLinks.forEach(link => {
        // Set active based on current URL
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
        }
        
        // Add click animation
        link.addEventListener('click', function(e) {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            this.style.transform = 'scale(1.02)';
            setTimeout(() => this.style.transform = 'scale(1)', 150);
        });
        
        // Add hover animation
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
}

/* ========================================
   FORM ENHANCEMENTS
   ======================================== */

function enhanceForms() {
    const inputs = document.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        // Add focus effects
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
            this.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            this.style.transform = 'translateY(0)';
        });
        
        // Validate on input
        if (input.hasAttribute('required')) {
            input.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
    });
    
    // Form submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredInputs = this.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                if (input.value.trim() === '') {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill in all required fields', 'danger');
            }
        });
    });
}

/* ========================================
   ANIMATIONS
   ======================================== */

function setupAnimations() {
    // Fade in elements on load
    const cards = document.querySelectorAll('.card, .stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 75);
    });
    
    // Animate stat values
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        const text = stat.textContent.trim();
        // Handle values like "$123" or "123"
        const prefix = text.replace(/[\d,]+.*$/, '');
        const numMatch = text.match(/([\d,]+)/);
        if (numMatch) {
            const finalValue = parseInt(numMatch[1].replace(/,/g, ''));
            if (!isNaN(finalValue) && finalValue > 0) {
                animateValue(stat, 0, finalValue, 1500, prefix);
            }
        }
    });
    
    // Add observer for elements coming into view
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });
        
        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            observer.observe(el);
        });
    }
}

function animateValue(element, start, end, duration, prefix = '') {
    const range = end - start;
    if (range === 0) return;
    
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + range * eased);
        element.textContent = prefix + current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

/* ========================================
   ALERTS & NOTIFICATIONS
   ======================================== */

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('main .content-wrapper') 
                   || document.querySelector('main') 
                   || document.querySelector('.container') 
                   || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transform = 'translateY(-10px)';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

/* ========================================
   RIPPLE EFFECT
   ======================================== */

function addRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

/* ========================================
   TOOLTIPS
   ======================================== */

function setupTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');
    
    elements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-box';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.position = 'absolute';
            tooltip.style.background = document.documentElement.getAttribute('data-theme') === 'dark' ? '#262730' : '#31333f';
            tooltip.style.color = 'white';
            tooltip.style.padding = '0.5rem 0.75rem';
            tooltip.style.borderRadius = '0.375rem';
            tooltip.style.fontSize = '0.85rem';
            tooltip.style.whiteSpace = 'nowrap';
            tooltip.style.zIndex = '1000';
            tooltip.style.pointerEvents = 'none';
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + window.scrollY + 'px';
        });
        
        el.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip-box');
            if (tooltip) tooltip.remove();
        });
    });
}

/* ========================================
   TABLE ENHANCEMENTS
   ======================================== */

function enhanceTables() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.transition = 'all 0.2s ease';
        });
    });
}

/* ========================================
   MODAL ENHANCEMENTS
   ======================================== */

function setupModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            this.style.animation = 'fadeIn 0.3s ease-in-out';
        });
    });
}

/* ========================================
   NAVBAR SCROLL EFFECTS
   ======================================== */

window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.15)';
            navbar.style.backdropFilter = 'blur(20px)';
        } else {
            navbar.style.boxShadow = '0 8px 20px rgba(91, 33, 182, 0.2)';
            navbar.style.backdropFilter = 'blur(10px)';
        }
    }
});

/* ========================================
   UTILITY FUNCTIONS
   ======================================== */

function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

function throttle(func, delay) {
    let lastCall = 0;
    return function(...args) {
        const now = Date.now();
        if (now - lastCall >= delay) {
            lastCall = now;
            func(...args);
        }
    };
}

/* ========================================
   INJECTED STYLES (Ripple + Tooltip + Transitions)
   ======================================== */

const injectedStyles = document.createElement('style');
injectedStyles.textContent = `
    .btn, button {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: rippleAnimation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes rippleAnimation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .tooltip-box {
        animation: tooltipShow 0.2s ease-in;
    }
    
    @keyframes tooltipShow {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Suppress transitions on initial load */
    .no-transition,
    .no-transition * {
        transition: none !important;
    }
`;
document.head.appendChild(injectedStyles);
