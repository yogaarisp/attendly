// Attendly Service Worker
const CACHE_NAME = 'attendly-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Offline attendance is strictly prohibited per PRD section 48.
    // Non-GET requests (e.g. attendance POST) must pass through untouched so
    // network errors reach the page and the auto-retry logic can kick in.
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => {
            return new Response(
                JSON.stringify({
                    error: true,
                    message: 'Anda sedang offline. Absensi membutuhkan koneksi internet aktif.'
                }),
                {
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        })
    );
});
