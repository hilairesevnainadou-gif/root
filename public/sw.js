const CACHE_NAME = 'bhdm-v3';
const STATIC_ASSETS = [
    '/',
    '/login',
    '/css/app.css',
    '/css/mobile.css',
    '/css/pwa.css',
    '/js/app.js',
    '/images/logo.png',
    '/icons/icon-72x72.png',
    '/icons/icon-96x96.png',
    '/icons/icon-128x128.png',
    '/icons/icon-144x144.png',
    '/icons/icon-152x152.png',
    '/icons/icon-192x192.png',
    '/icons/icon-384x384.png',
    '/icons/icon-512x512.png'
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Installation en cours...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Mise en cache des ressources statiques');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Installation terminée');
                return self.skipWaiting();
            })
            .catch((err) => {
                console.error('[SW] Erreur lors de la mise en cache:', err);
                // Continue même si certains fichiers manquent
                return self.skipWaiting();
            })
    );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    console.log('[SW] Activation en cours...');

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => {
                        console.log('[SW] Suppression de l\'ancien cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            console.log('[SW] Activation terminée');
            return self.clients.claim();
        })
    );
});

// Gestion des requêtes fetch
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ignorer les requêtes non-GET et les requêtes non-HTTP(S)
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    // Stratégie différente selon le type de requête
    if (isStaticAsset(request)) {
        // Cache First pour les assets statiques (CSS, JS, images, fonts)
        event.respondWith(cacheFirstStrategy(request));
    } else {
        // Network First pour les pages HTML et API
        event.respondWith(networkFirstStrategy(request));
    }
});

// Vérifie si c'est un asset statique
function isStaticAsset(request) {
    const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot'];
    const url = new URL(request.url);
    return staticExtensions.some(ext => url.pathname.endsWith(ext)) ||
           request.destination === 'image' ||
           request.destination === 'style' ||
           request.destination === 'script' ||
           request.destination === 'font';
}

// Stratégie Cache First pour les assets statiques
async function cacheFirstStrategy(request) {
    const cached = await caches.match(request);

    if (cached) {
        // Rafraîchir le cache en arrière-plan (stale-while-revalidate)
        fetch(request).then(response => {
            if (response.ok) {
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response);
                });
            }
        }).catch(() => {});

        return cached;
    }

    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.error('[SW] Échec réseau et cache pour:', request.url);
        return new Response('Ressource non disponible hors ligne', {
            status: 503,
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

// Stratégie Network First pour les pages et API
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW] Réseau indisponible, utilisation du cache pour:', request.url);

        const cached = await caches.match(request);

        if (cached) {
            return cached;
        }

        // Fallback pour navigation
        if (request.mode === 'navigate' || request.destination === 'document') {
            const fallback = await caches.match('/login');
            if (fallback) return fallback;

            const homeFallback = await caches.match('/');
            if (homeFallback) return homeFallback;
        }

        return new Response('Vous êtes hors ligne. Veuillez vérifier votre connexion.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
        });
    }
}

// Gestion des messages du client
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }

    if (event.data === 'getVersion') {
        event.ports[0].postMessage(CACHE_NAME);
    }
});

// Gestion des notifications push (optionnel)
self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();
        event.waitUntil(
            self.registration.showNotification(data.title, {
                body: data.body,
                icon: '/icons/icon-192x192.png',
                badge: '/icons/icon-72x72.png',
                data: data.data
            })
        );
    }
});

// Gestion du clic sur notification (optionnel)
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data?.url || '/')
    );
});
