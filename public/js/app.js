// ============================================
// BHDM App - JavaScript Principal
// ============================================

// Variables globales PWA (déclarées UNE SEULE FOIS ici)
let deferredPrompt = null;
let isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
let isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[BHDM] App initializing...');

    // Initialiser la PWA
    initPWA();

    // Navigation mobile
    initMobileNav();

    // Gestion hors ligne
    initOfflineDetection();

    // Flash messages auto-hide
    initFlashMessages();

    // Confirmations
    initConfirmations();

    // Loading states
    initLoadingStates();

    // Déconnexion
    initLogout();

    // Installation PWA
    initInstallPrompts();

    console.log('[BHDM] App initialized');
});

// ============================================
// PWA - Service Worker & Install Prompts
// ============================================

function initPWA() {
    // Enregistrer le Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('[BHDM] SW registered:', registration.scope);

                // Mise à jour du SW
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            showUpdateNotification();
                        }
                    });
                });
            })
            .catch((error) => {
                console.error('[BHDM] SW registration failed:', error);
            });
    }

    // Capturer l'événement beforeinstallprompt (Chrome/Android)
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('[BHDM] beforeinstallprompt captured');
        e.preventDefault();
        deferredPrompt = e;

        // Afficher le bouton d'installation si disponible
        showInstallButton();
    });

    // Détecter si déjà installé
    window.addEventListener('appinstalled', () => {
        console.log('[BHDM] App installed');
        deferredPrompt = null;
        hideAllInstallPrompts();
        showToast('Application installée avec succès !', 'success');
        localStorage.setItem('pwaInstalled', 'true');
    });
}

function initInstallPrompts() {
    // Ne rien faire si déjà installé
    if (isStandalone || localStorage.getItem('pwaInstalled') === 'true') {
        hideAllInstallPrompts();
        return;
    }

    // iOS: Afficher instructions après 3 secondes
    if (isIos) {
        const iosPromptDismissed = localStorage.getItem('iosPromptDismissed');
        if (!iosPromptDismissed) {
            setTimeout(() => {
                showIosPrompt();
            }, 3000);
        }
        return;
    }

    // Android/Chrome: Afficher si deferredPrompt disponible
    if (deferredPrompt) {
        showInstallButton();
    }
}

function showInstallButton() {
    // Créer un bouton flottant d'installation si non existant
    if (document.getElementById('pwa-install-btn')) return;

    const btn = document.createElement('button');
    btn.id = 'pwa-install-btn';
    btn.innerHTML = `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Installer l'app</span>
    `;
    btn.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 16px;
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        z-index: 999;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    `;

    btn.addEventListener('click', installPWA);
    document.body.appendChild(btn);
}

async function installPWA() {
    if (!deferredPrompt) {
        showToast('Installation non disponible sur cet appareil', 'error');
        return;
    }

    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
        btn.innerHTML = '<span>Installation...</span>';
        btn.disabled = true;
    }

    deferredPrompt.prompt();

    const { outcome } = await deferredPrompt.userChoice;
    console.log('[BHDM] Install outcome:', outcome);

    if (outcome === 'accepted') {
        showToast('Installation en cours...', 'success');
        hideAllInstallPrompts();
    } else {
        showToast('Installation annulée', 'info');
        if (btn) {
            btn.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Installer l'app</span>
            `;
            btn.disabled = false;
        }
    }

    deferredPrompt = null;
}

function showIosPrompt() {
    const modal = document.getElementById('ios-prompt');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function hideIosPrompt() {
    const modal = document.getElementById('ios-prompt');
    if (modal) {
        modal.style.display = 'none';
    }
    localStorage.setItem('iosPromptDismissed', 'true');
}

function hideAllInstallPrompts() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) btn.remove();

    const iosModal = document.getElementById('ios-prompt');
    if (iosModal) iosModal.style.display = 'none';
}

function showUpdateNotification() {
    showToast('Nouvelle version disponible ! Rafraîchissez la page.', 'info', 0);
}

// ============================================
// Navigation Mobile
// ============================================

function initMobileNav() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Active state
    const currentPath = window.location.pathname;
    document.querySelectorAll('.mobile-nav-item, .admin-nav-item').forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href)) {
            item.classList.add('active');
        }
    });
}

// ============================================
// Déconnexion
// ============================================

function initLogout() {
    const logoutBtn = document.getElementById('btn-logout-trigger');
    const logoutForm = document.getElementById('logout-form');
    const cancelBtn = document.getElementById('btn-cancel-logout');
    const modalOverlay = document.querySelector('.modal-overlay');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openLogoutModal();
        });
    }

    if (cancelBtn) cancelBtn.addEventListener('click', closeLogoutModal);
    if (modalOverlay) modalOverlay.addEventListener('click', closeLogoutModal);

    if (logoutForm) {
        logoutForm.addEventListener('submit', () => {
            const confirmBtn = document.getElementById('btn-confirm-logout');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                const btnText = confirmBtn.querySelector('.btn-text');
                const btnLoader = confirmBtn.querySelector('.btn-loader');
                if (btnText) btnText.style.display = 'none';
                if (btnLoader) btnLoader.style.display = 'inline-flex';
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLogoutModal();
    });
}

function openLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ============================================
// Détection Hors Ligne
// ============================================

function initOfflineDetection() {
    const offlineBanner = document.getElementById('offline-banner');
    if (!offlineBanner) return;

    function updateStatus() {
        if (navigator.onLine) {
            offlineBanner.classList.add('hidden');
            document.body.classList.remove('offline');
        } else {
            offlineBanner.classList.remove('hidden');
            document.body.classList.add('offline');
        }
    }

    updateStatus();

    window.addEventListener('online', () => {
        updateStatus();
        showToast('Connexion rétablie', 'success');
    });

    window.addEventListener('offline', () => {
        updateStatus();
        showToast('Vous êtes hors ligne', 'warning');
    });
}

// ============================================
// Flash Messages
// ============================================

function initFlashMessages() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => hideAlert(alert), 5000);

        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => hideAlert(alert));
        }
    });
}

function hideAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    alert.style.transition = 'all 0.3s ease';
    setTimeout(() => alert.remove(), 300);
}

// ============================================
// Confirmations
// ============================================

function initConfirmations() {
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.dataset.confirm || 'Êtes-vous sûr ?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

// ============================================
// Loading States
// ============================================

function initLoadingStates() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="animate-spin" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Chargement...
                `;

                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText;
                    }
                }, 10000);
            }
        });
    });
}

// ============================================
// Toast Notifications
// ============================================

function showToast(message, type = 'info', duration = 3000) {
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };

    toast.innerHTML = `
        <svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${icons[type] || icons.info}
        </svg>
        <span>${message}</span>
    `;

    // Styles CSS inline pour le toast
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        transition: transform 0.3s ease;
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    if (duration > 0) {
        setTimeout(() => {
            toast.style.transform = 'translateX(-50%) translateY(-100px)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    return toast;
}

// ============================================
// Utilitaires Globaux
// ============================================

window.BHDM = {
    showToast,
    formatCurrency: (amount, currency = 'XOF') => {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0
        }).format(amount || 0);
    },
    formatDate: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return isNaN(date.getTime()) ? dateString :
            new Intl.DateTimeFormat('fr-FR', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            }).format(date);
    },
    debounce: (func, wait = 300) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    },
    installPWA,
    showIosPrompt,
    hideIosPrompt
};
