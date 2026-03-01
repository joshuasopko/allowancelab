// AllowanceLab Service Worker
// PWA caching + Web Push notification handling

const CACHE_NAME = 'allowancelab-v2';

// Install event
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installed');
    self.skipWaiting();
});

// Activate event — clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Clearing old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event — network first, cache fallback
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const responseClone = response.clone();
                if (event.request.method === 'GET') {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});

// ─── Push Notification Handler ───────────────────────────────────────────────

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'AllowanceLab', body: event.data.text() };
    }

    const title   = data.title   || 'AllowanceLab';
    const options = {
        body:    data.body    || '',
        icon:    data.icon    || '/icon-192.png',
        badge:   data.badge   || '/icon-192.png',
        tag:     data.tag     || 'allowancelab',
        data: {
            url: data.url || '/',
        },
        requireInteraction: data.requireInteraction || false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ─── Notification Click Handler ──────────────────────────────────────────────

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Focus an existing window if one is open on the target URL
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new window
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
