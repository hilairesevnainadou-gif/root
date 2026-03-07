// ============================================
// BHDM App - JavaScript Principal
// ============================================

document.addEventListener('DOMContentLoaded', function() {

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
});

// ============================================
// PWA - Service Worker & Install Prompts
// ============================================

let deferredPrompt = null;

function initPWA() {
  // Enregistrer le Service Worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered:', registration);
      })
      .catch((error) => {
        console.log('SW registration failed:', error);
      });
  }

  // Capturer l'événement beforeinstallprompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    console.log('beforeinstallprompt captured');
  });

  // Masquer les prompts si déjà installé
  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    hideAllInstallPrompts();
    showToast('Application installée avec succès !', 'success');
  });

  // Détecter si déjà en mode standalone
  if (window.matchMedia('(display-mode: standalone)').matches ||
      (window.navigator.standalone === true)) {
    hideAllInstallPrompts();
  }
}

// ============================================
// Navigation Mobile
// ============================================

function initMobileNav() {
  // Gestion du menu hamburger si présent (admin)
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.querySelector('.admin-sidebar');

  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Fermer en cliquant à l'extérieur
    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // Active state sur la nav mobile
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
  const logoutBtn = document.querySelector('.btn-logout');
  const logoutForm = document.querySelector('.logout-form');

  if (logoutBtn && logoutForm) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();

      if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
        // Animation de déconnexion
        logoutBtn.innerHTML = `
          <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        `;

        // Soumettre le formulaire après un court délai
        setTimeout(() => {
          logoutForm.submit();
        }, 300);
      }
    });
  }
}

// ============================================
// Détection Hors Ligne
// ============================================

function initOfflineDetection() {
  // Le banner est maintenant dans le HTML, juste le gérer ici
  const offlineBanner = document.getElementById('offline-banner');

  if (!offlineBanner) return;

  function updateOnlineStatus() {
    if (navigator.onLine) {
      offlineBanner.classList.add('hidden');
      document.body.classList.remove('offline');
    } else {
      offlineBanner.classList.remove('hidden');
      document.body.classList.add('offline');
    }
  }

  // État initial
  updateOnlineStatus();

  window.addEventListener('online', () => {
    updateOnlineStatus();
    showToast('Connexion rétablie', 'success');
    syncOfflineData();
  });

  window.addEventListener('offline', () => {
    updateOnlineStatus();
    showToast('Vous êtes hors ligne', 'warning');
  });
}

// ============================================
// Flash Messages
// ============================================

function initFlashMessages() {
  const alerts = document.querySelectorAll('.alert');

  alerts.forEach(alert => {
    // Auto-hide après 5 secondes
    setTimeout(() => {
      hideAlert(alert);
    }, 5000);

    // Bouton fermeture manuelle
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

        // Réactiver après 10s en cas d'erreur
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
// Gestion des Prompts PWA
// ============================================

function showIosPrompt() {
  const prompt = document.getElementById('ios-install-prompt');
  if (prompt) {
    prompt.classList.add('active');
  }
}

function hideIosPrompt() {
  const prompt = document.getElementById('ios-install-prompt');
  if (prompt) {
    prompt.classList.remove('active');
  }
  localStorage.setItem('iosPromptDismissed', 'true');
}

function showAndroidPrompt() {
  const prompt = document.getElementById('pwa-install-prompt');
  if (prompt && deferredPrompt) {
    prompt.classList.add('active');
  }
}

function hideAndroidPrompt() {
  const prompt = document.getElementById('pwa-install-prompt');
  if (prompt) {
    prompt.classList.remove('active');
  }
  localStorage.setItem('pwaPromptDismissed', 'true');
}

function hideAllInstallPrompts() {
  hideIosPrompt();
  hideAndroidPrompt();
}

async function installPWA() {
  if (!deferredPrompt) {
    showToast('Installation non disponible', 'error');
    return;
  }

  deferredPrompt.prompt();

  const { outcome } = await deferredPrompt.userChoice;

  if (outcome === 'accepted') {
    showToast('Installation en cours...', 'success');
  } else {
    showToast('Installation annulée', 'info');
  }

  deferredPrompt = null;
  hideAndroidPrompt();
}

// ============================================
// Synchronisation Offline
// ============================================

async function syncOfflineData() {
  const queue = JSON.parse(localStorage.getItem('syncQueue') || '[]');
  if (queue.length === 0) return;

  showToast('Synchronisation en cours...', 'info');

  const newQueue = [];

  for (const item of queue) {
    try {
      const response = await fetch(item.url, {
        ...item.options,
        headers: {
          ...item.options.headers,
          'X-Sync-Request': 'true'
        }
      });

      if (!response.ok) throw new Error('Sync failed');
    } catch (e) {
      // Garder dans la queue si échec
      newQueue.push(item);
    }
  }

  if (newQueue.length === 0) {
    localStorage.removeItem('syncQueue');
    showToast('Données synchronisées !', 'success');
  } else {
    localStorage.setItem('syncQueue', JSON.stringify(newQueue));
    showToast(`${newQueue.length} éléments en attente`, 'warning');
  }
}

function queueForSync(url, options) {
  const queue = JSON.parse(localStorage.getItem('syncQueue') || '[]');
  queue.push({
    url,
    options,
    timestamp: Date.now(),
    retries: 0
  });
  localStorage.setItem('syncQueue', JSON.stringify(queue));
  showToast('Action enregistrée pour synchronisation', 'info');
}

// ============================================
// Utilitaires
// ============================================

function showToast(message, type = 'info', duration = 3000) {
  // Supprimer les toasts existants
  const existingToasts = document.querySelectorAll('.toast');
  existingToasts.forEach(t => t.remove());

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

  document.body.appendChild(toast);

  // Animation d'entrée
  requestAnimationFrame(() => {
    toast.classList.add('show');
  });

  // Auto-hide
  if (duration > 0) {
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }

  return toast;
}

// Formatage montant
function formatCurrency(amount, currency = 'XOF') {
  if (typeof amount !== 'number') amount = parseFloat(amount) || 0;

  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
}

// Formatage date
function formatDate(dateString, options = {}) {
  if (!dateString) return '-';

  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString;

  const defaultOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    ...options
  };

  return new Intl.DateTimeFormat('fr-FR', defaultOptions).format(date);
}

// Formatage relatif (il y a X minutes)
function formatRelativeTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return 'À l\'instant';
  if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)} min`;
  if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)} h`;
  if (diffInSeconds < 604800) return `Il y a ${Math.floor(diffInSeconds / 86400)} j`;

  return formatDate(dateString);
}

// Debounce
function debounce(func, wait = 300) {
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

// Throttle
function throttle(func, limit = 300) {
  let inThrottle;
  return function(...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

// Requête API avec gestion offline
async function apiRequest(url, options = {}) {
  const defaultOptions = {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...options.headers
    }
  };

  try {
    const response = await fetch(url, { ...defaultOptions, ...options });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Erreur réseau' }));
      throw new Error(error.message || `HTTP ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    if (!navigator.onLine) {
      // Stocker pour sync ultérieure si c'est une mutation
      if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method)) {
        queueForSync(url, { ...defaultOptions, ...options });
        return { offline: true, queued: true, message: 'Enregistré pour synchronisation' };
      }
    }

    showToast(error.message || 'Erreur de connexion', 'error');
    throw error;
  }
}

// Copier dans le presse-papiers
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    showToast('Copié !', 'success');
  } catch (err) {
    // Fallback
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showToast('Copié !', 'success');
  }
}

// Export pour utilisation globale
window.BHDM = {
  showToast,
  formatCurrency,
  formatDate,
  formatRelativeTime,
  apiRequest,
  copyToClipboard,
  debounce,
  throttle,
  installPWA,
  showIosPrompt,
  hideIosPrompt,
  showAndroidPrompt,
  hideAndroidPrompt
};
