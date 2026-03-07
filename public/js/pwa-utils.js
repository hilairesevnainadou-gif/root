// ============================================
// PWA Utilities
// ============================================

// Cache management
const CacheManager = {
  async clear() {
    if ('caches' in window) {
      const cacheNames = await caches.keys();
      await Promise.all(cacheNames.map(name => caches.delete(name)));
    }
  },

  async add(urls) {
    if ('caches' in window) {
      const cache = await caches.open('user-cache');
      await cache.addAll(urls);
    }
  }
};

// Notification manager
const NotificationManager = {
  async requestPermission() {
    if (!('Notification' in window)) return false;

    const permission = await Notification.requestPermission();
    return permission === 'granted';
  },

  async subscribe() {
    if (!('serviceWorker' in navigator)) return null;

    const registration = await navigator.serviceWorker.ready;

    try {
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(
          'YOUR_VAPID_PUBLIC_KEY_HERE'
        )
      });

      // Send to server
      await fetch('/api/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(subscription)
      });

      return subscription;
    } catch (error) {
      console.error('Push subscription failed:', error);
      return null;
    }
  },

  show(title, options = {}) {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.ready.then(registration => {
        registration.showNotification(title, {
          icon: '/icons/icon-192x192.png',
          badge: '/icons/icon-72x72.png',
          ...options
        });
      });
    }
  }
};

// Utility: Convert VAPID key
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }

  return outputArray;
}

// Share API wrapper
async function share(data) {
  if (navigator.share) {
    try {
      await navigator.share({
        title: data.title || 'BHDM',
        text: data.text || '',
        url: data.url || window.location.href
      });
      return true;
    } catch (error) {
      console.log('Share cancelled');
      return false;
    }
  }

  // Fallback: copy to clipboard
  if (data.url) {
    await navigator.clipboard.writeText(data.url);
    showToast('Lien copié dans le presse-papier', 'success');
  }

  return false;
}

// App install prompt
let installPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  installPrompt = e;

  // Show custom install UI
  showInstallUI();
});

function showInstallUI() {
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
  const isAndroid = /Android/.test(navigator.userAgent);

  if (isIOS) {
    showIOSInstallGuide();
  } else if (isAndroid && installPrompt) {
    showAndroidInstallBanner();
  }
}

function showIOSInstallGuide() {
  const guide = document.createElement('div');
  guide.className = 'ath-prompt ios-prompt';
  guide.innerHTML = `
    <div class="ath-prompt-arrow"></div>
    <p>Appuyez sur <strong>Partager</strong> puis <strong>"Sur l'écran d'accueil"</strong> pour installer</p>
    <button onclick="this.parentElement.remove()">J'ai compris</button>
  `;
  document.body.appendChild(guide);

  setTimeout(() => guide.classList.add('show'), 100);
}

function showAndroidInstallBanner() {
  const banner = document.createElement('div');
  banner.className = 'install-banner';
  banner.innerHTML = `
    <span>Installer BHDM pour un accès rapide</span>
    <button onclick="triggerInstall(this)">Installer</button>
  `;
  document.body.appendChild(banner);
}

async function triggerInstall(btn) {
  if (!installPrompt) return;

  btn.textContent = 'Installation...';
  installPrompt.prompt();

  const { outcome } = await installPrompt.userChoice;

  if (outcome === 'accepted') {
    btn.textContent = 'Installé !';
    setTimeout(() => btn.parentElement.remove(), 2000);
  } else {
    btn.textContent = 'Réessayer';
  }

  installPrompt = null;
}

// Check if running as installed PWA
function isStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches ||
         window.navigator.standalone === true;
}

// Get app version from service worker
async function getAppVersion() {
  if ('serviceWorker' in navigator) {
    const registration = await navigator.serviceWorker.ready;
    // This would need to be implemented in the SW
    return registration.active?.scriptURL || 'unknown';
  }
  return 'no-sw';
}

// Force update check
async function checkForUpdate() {
  if ('serviceWorker' in navigator) {
    const registration = await navigator.serviceWorker.ready;
    await registration.update();
    showToast('Vérification des mises à jour...', 'info');
  }
}
