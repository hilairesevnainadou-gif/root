// ============================================
// BHDM App - JavaScript Principal
// ============================================

var deferredPrompt = null;
var isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[BHDM] App initializing...');

    initPWA();
    initMobileNav();
    initOfflineDetection();
    initFlashMessages();
    initConfirmations();
    initLoadingStates();
    initLogout();
    initInstallPrompts();

    console.log('[BHDM] App initialized');
});

// ============================================
// PWA
// ============================================

function initPWA() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('[BHDM] SW registered:', registration.scope);
            })
            .catch(function(error) {
                console.error('[BHDM] SW registration failed:', error);
            });
    }

    window.addEventListener('beforeinstallprompt', function(e) {
        console.log('[BHDM] beforeinstallprompt captured');
        e.preventDefault();
        deferredPrompt = e;
        showInstallButton();
    });

    window.addEventListener('appinstalled', function() {
        console.log('[BHDM] App installed');
        deferredPrompt = null;
        hideAllInstallPrompts();
        showToast('Application installee avec succes !', 'success');
        localStorage.setItem('pwaInstalled', 'true');
    });
}

function initInstallPrompts() {
    if (isStandalone || localStorage.getItem('pwaInstalled') === 'true') {
        hideAllInstallPrompts();
        return;
    }

    if (isIos) {
        if (!localStorage.getItem('iosPromptDismissed')) {
            setTimeout(function() {
                showIosPrompt();
            }, 3000);
        }
        return;
    }

    if (deferredPrompt) {
        showInstallButton();
    }
}

function showInstallButton() {
    if (document.getElementById('pwa-install-btn')) return;

    var btn = document.createElement('button');
    btn.id = 'pwa-install-btn';
    btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg><span>Installer l\'app</span>';

    btn.style.cssText = 'position:fixed;bottom:100px;right:16px;background:linear-gradient(135deg,#1e40af,#3b82f6);color:white;border:none;padding:12px 20px;border-radius:50px;font-weight:600;font-size:0.875rem;cursor:pointer;box-shadow:0 4px 15px rgba(37,99,235,0.4);z-index:999;display:flex;align-items:center;gap:8px;transition:all 0.3s ease;';

    btn.addEventListener('click', installPWA);
    document.body.appendChild(btn);
}

function installPWA() {
    if (!deferredPrompt) {
        showToast('Installation non disponible', 'error');
        return;
    }

    var btn = document.getElementById('pwa-install-btn');
    if (btn) {
        btn.innerHTML = '<span>Installation...</span>';
        btn.disabled = true;
    }

    deferredPrompt.prompt();

    deferredPrompt.userChoice.then(function(choiceResult) {
        console.log('[BHDM] Install outcome:', choiceResult.outcome);

        if (choiceResult.outcome === 'accepted') {
            showToast('Installation en cours...', 'success');
            hideAllInstallPrompts();
        } else {
            showToast('Installation annulee', 'info');
            if (btn) {
                btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg><span>Installer l\'app</span>';
                btn.disabled = false;
            }
        }
        deferredPrompt = null;
    });
}

function showIosPrompt() {
    var modal = document.getElementById('ios-prompt');
    if (modal) modal.style.display = 'flex';
}

function hideIosPrompt() {
    var modal = document.getElementById('ios-prompt');
    if (modal) modal.style.display = 'none';
    localStorage.setItem('iosPromptDismissed', 'true');
}

function hideAllInstallPrompts() {
    var btn = document.getElementById('pwa-install-btn');
    if (btn) btn.remove();

    var iosModal = document.getElementById('ios-prompt');
    if (iosModal) iosModal.style.display = 'none';
}

// ============================================
// Navigation
// ============================================

function initMobileNav() {
    var currentPath = window.location.pathname;
    document.querySelectorAll('.mobile-nav-item').forEach(function(item) {
        var href = item.getAttribute('href');
        if (href) {
            var cleanHref = href.replace('http://', '').replace('https://', '');
            if (currentPath.indexOf(cleanHref) !== -1 || currentPath === href) {
                item.classList.add('active');
            }
        }
    });
}

// ============================================
// Deconnexion
// ============================================

function initLogout() {
    var logoutBtn = document.getElementById('btn-logout-trigger');
    var logoutForm = document.getElementById('logout-form');
    var cancelBtn = document.getElementById('btn-cancel-logout');
    var modalOverlay = document.querySelector('.modal-overlay');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openLogoutModal();
        });
    }

    if (cancelBtn) cancelBtn.addEventListener('click', closeLogoutModal);
    if (modalOverlay) modalOverlay.addEventListener('click', closeLogoutModal);

    if (logoutForm) {
        logoutForm.addEventListener('submit', function() {
            var confirmBtn = document.getElementById('btn-confirm-logout');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                var btnText = confirmBtn.querySelector('.btn-text');
                var btnLoader = confirmBtn.querySelector('.btn-loader');
                if (btnText) btnText.style.display = 'none';
                if (btnLoader) btnLoader.style.display = 'inline-flex';
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogoutModal();
    });
}

function openLogoutModal() {
    var modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeLogoutModal() {
    var modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ============================================
// Hors Ligne
// ============================================

function initOfflineDetection() {
    var offlineBanner = document.getElementById('offline-banner');
    if (!offlineBanner) return;

    function updateStatus() {
        if (navigator.onLine) {
            offlineBanner.classList.add('hidden');
        } else {
            offlineBanner.classList.remove('hidden');
        }
    }

    updateStatus();

    window.addEventListener('online', function() {
        updateStatus();
        showToast('Connexion retablie', 'success');
    });

    window.addEventListener('offline', function() {
        updateStatus();
        showToast('Vous etes hors ligne', 'warning');
    });
}

// ============================================
// Flash Messages
// ============================================

function initFlashMessages() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            hideAlert(alert);
        }, 5000);

        var closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                hideAlert(alert);
            });
        }
    });
}

function hideAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    alert.style.transition = 'all 0.3s ease';
    setTimeout(function() {
        alert.remove();
    }, 300);
}

// ============================================
// Confirmations
// ============================================

function initConfirmations() {
    document.querySelectorAll('[data-confirm]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var message = btn.dataset.confirm || 'Etes-vous sur ?';
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
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.innerHTML;

                var spinnerHtml = '<svg class="animate-spin" style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:8px;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Chargement...';

                submitBtn.innerHTML = spinnerHtml;

                setTimeout(function() {
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

function showToast(message, type, duration) {
    type = type || 'info';
    duration = duration || 3000;

    document.querySelectorAll('.toast').forEach(function(t) {
        t.remove();
    });

    var toast = document.createElement('div');

    var icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };

    var colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };

    var iconSvg = icons[type] || icons.info;
    var bgColor = colors[type] || colors.info;

    toast.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">' + iconSvg + '</svg><span>' + message + '</span>';

    var styleText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%) translateY(-100px);background:' + bgColor + ';color:white;padding:12px 24px;border-radius:12px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:10000;transition:transform 0.3s ease;';

    toast.style.cssText = styleText;

    document.body.appendChild(toast);

    requestAnimationFrame(function() {
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    if (duration > 0) {
        setTimeout(function() {
            toast.style.transform = 'translateX(-50%) translateY(-100px)';
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, duration);
    }

    return toast;
}

// ============================================
// Exports Globaux
// ============================================

window.BHDM = {
    showToast: showToast,
    installPWA: installPWA,
    showIosPrompt: showIosPrompt,
    hideIosPrompt: hideIosPrompt
};
