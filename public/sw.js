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
    // Pass requests through to network.
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
