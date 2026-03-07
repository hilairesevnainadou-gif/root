const CACHE_NAME = 'bhdm-v2';
const STATIC_ASSETS = [
    '/',
    '/login',
    '/css/app.css',
    '/css/mobile.css',
    '/css/pwa.css',
    '/css/transitions.css',
    '/js/app.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/images/logo.png'
];

// Installation avec gestion d'erreurs
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.all(
                STATIC_ASSETS.map(url => {
                    return fetch(url, { mode: 'no-cors' })
                        .then(response => {
                            if (response.ok || response.type === 'opaque') {
                                return cache.put(url, response);
                            }
                            console.warn('[SW] Failed to cache:', url);
                            return Promise.resolve();
                        })
                        .catch(err => {
                            console.warn('[SW] Error caching', url, ':', err);
                            return Promise.resolve();
                        });
                })
            );
        }).then(() => {
            console.log('[SW] Install completed');
            self.skipWaiting();
        })
    );
});

// Activation - Nettoyage des anciens caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            console.log('[SW] Activation completed');
            return self.clients.claim();
        })
    );
});

// Fetch avec stratégie Network First
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ignorer les requêtes non-GET et les chrome-extensions
    if (request.method !== 'GET' || request.url.startsWith('chrome-extension://')) {
        return;
    }

    // Stratégie différente selon le type de requête
    if (request.destination === 'image' || request.destination === 'style' || request.destination === 'script') {
        // Cache First pour les assets statiques
        event.respondWith(cacheFirst(request));
    } else {
        // Network First pour les pages et API
        event.respondWith(networkFirst(request));
    }
});

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Cache first failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);
        const cached = await caches.match(request);

        if (cached) {
            return cached;
        }

        // Fallback pour navigation
        if (request.mode === 'navigate') {
            return caches.match('/login');
        }

        return new Response('Vous êtes hors ligne', {
            status: 503,
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

// Gestion des messages depuis la page
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});
