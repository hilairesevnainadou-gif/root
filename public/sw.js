const CACHE_NAME = 'bhdm-v1';
const STATIC_ASSETS = [
    '/',
    '/login',
    '/register',
    '/css/app.css',
    '/css/mobile.css',
    '/css/pwa.css',
    '/js/app.js',
    '/js/mobile-nav.js',
    '/js/offline.js',
    '/js/pwa-utils.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Installation avec gestion d'erreurs
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            // Cache chaque fichier individuellement pour éviter que tout échoue
            return Promise.all(
                STATIC_ASSETS.map(url => {
                    return fetch(url, { mode: 'no-cors' })
                        .then(response => {
                            if (response.ok || response.type === 'opaque') {
                                return cache.put(url, response);
                            }
                            console.warn('Failed to cache:', url);
                            return Promise.resolve(); // Continue même si échec
                        })
                        .catch(err => {
                            console.warn('Error caching', url, ':', err);
                            return Promise.resolve(); // Continue
                        });
                })
            );
        })
    );
    self.skipWaiting();
});

// Activation
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch avec fallback
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ne pas intercepter les requêtes non-GET
    if (request.method !== 'GET') {
        return;
    }

    // Stratégie: Network first, puis cache
    event.respondWith(
        fetch(request)
            .then(response => {
                // Mettre en cache les réponses réussies
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, clone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Fallback sur le cache
                return caches.match(request).then(cached => {
                    if (cached) {
                        return cached;
                    }
                    // Fallback pour les pages HTML
                    if (request.mode === 'navigate') {
                        return caches.match('/login');
                    }
                    return new Response('Offline', { status: 503 });
                });
            })
    );
});
