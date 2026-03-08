const CACHE_NAME = 'bhdm-v3';
const STATIC_ASSETS = [
    '/',
    '/login',
    '/css/app.css',
    '/css/mobile.css',
    '/css/pwa.css',
    '/js/app.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Installation
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.error('[SW] Cache addAll failed:', err);
                // Continue même si certains fichiers manquent
                return Promise.resolve();
            });
        }).then(() => {
            console.log('[SW] Install completed');
            self.skipWaiting();
        })
    );
});

// Activation
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

// Fetch avec stratégie Network First (CORRIGÉ)
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ignorer les requêtes non-GET et non-HTTP(S)
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    // Stratégie différente selon le type
    if (request.destination === 'image' || request.destination === 'style' || request.destination === 'script') {
        // Cache First pour les assets statiques
        event.respondWith(cacheFirstStrategy(request));
    } else {
        // Network First pour les pages et API
        event.respondWith(networkFirstStrategy(request));
    }
});

// Stratégie Cache First (pour CSS, JS, images)
async function cacheFirstStrategy(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            // Cloner AVANT de mettre en cache
            await cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.error('[SW] Cache first failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Stratégie Network First (pour pages HTML et API) - CORRIGÉ
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);

        // Si réponse OK, mettre en cache et retourner
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            // Cloner AVANT de consommer le body
            await cache.put(request, networkResponse.clone());
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
            const fallback = await caches.match('/login');
            if (fallback) return fallback;
        }

        return new Response('Vous êtes hors ligne', {
            status: 503,
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

// Message handler
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});
