// ============================================
// Offline Detection & Management
// ============================================

const OfflineManager = {
  isOnline: navigator.onLine,
  syncQueue: [],

  init() {
    this.loadQueue();
    this.setupListeners();
    this.updateUI();
  },

  setupListeners() {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.updateUI();
      this.syncData();
      showToast('Connexion rétablie', 'success');
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.updateUI();
      showToast('Vous êtes hors ligne', 'warning');
    });

    // Intercept form submissions
    document.addEventListener('submit', (e) => {
      if (!this.isOnline) {
        e.preventDefault();
        this.queueForm(e.target);
      }
    });
  },

  updateUI() {
    const indicator = document.querySelector('.network-status');
    if (indicator) {
      indicator.classList.toggle('online', this.isOnline);
      indicator.classList.toggle('offline', !this.isOnline);
    }

    document.body.classList.toggle('is-offline', !this.isOnline);
  },

  queueForm(form) {
    const data = new FormData(form);
    const entry = {
      url: form.action,
      method: form.method,
      data: Object.fromEntries(data),
      timestamp: Date.now()
    };

    this.syncQueue.push(entry);
    this.saveQueue();

    showToast('Données enregistrées. Synchronisation automatique au retour en ligne.', 'info');

    // Store files separately if any
    const files = form.querySelectorAll('input[type="file"]');
    files.forEach(input => {
      if (input.files.length > 0) {
        // Store file references for later upload
        this.storeFileForSync(input.name, input.files[0]);
      }
    });
  },

  async syncData() {
    if (this.syncQueue.length === 0) return;

    const indicator = document.querySelector('.sync-indicator');
    indicator?.classList.add('active');

    const queue = [...this.syncQueue];
    const failed = [];

    for (const entry of queue) {
      try {
        const response = await fetch(entry.url, {
          method: entry.method,
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(entry.data)
        });

        if (!response.ok) throw new Error('Sync failed');

      } catch (error) {
        failed.push(entry);
      }
    }

    this.syncQueue = failed;
    this.saveQueue();

    indicator?.classList.remove('active');

    if (failed.length === 0) {
      showToast('Toutes les données ont été synchronisées', 'success');
    } else {
      showToast(`${failed.length} éléments n'ont pas pu être synchronisés`, 'warning');
    }
  },

  storeFileForSync(name, file) {
    // In a real app, use IndexedDB to store files
    // For now, we'll store metadata
    const fileEntry = {
      name: name,
      fileName: file.name,
      size: file.size,
      type: file.type,
      lastModified: file.lastModified
    };
    localStorage.setItem(`pending_file_${name}`, JSON.stringify(fileEntry));
  },

  saveQueue() {
    localStorage.setItem('syncQueue', JSON.stringify(this.syncQueue));
    this.updateBadge();
  },

  loadQueue() {
    const stored = localStorage.getItem('syncQueue');
    if (stored) {
      this.syncQueue = JSON.parse(stored);
      this.updateBadge();
    }
  },

  updateBadge() {
    const badge = document.querySelector('.sync-badge');
    if (badge) {
      badge.textContent = this.syncQueue.length;
      badge.style.display = this.syncQueue.length > 0 ? 'flex' : 'none';
    }
  }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  OfflineManager.init();
});

// Background sync registration
async function registerBackgroundSync() {
  if ('serviceWorker' in navigator && 'SyncManager' in window) {
    const registration = await navigator.serviceWorker.ready;
    try {
      await registration.sync.register('sync-documents');
    } catch (error) {
      console.log('Background sync failed:', error);
    }
  }
}
