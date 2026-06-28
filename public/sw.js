const CACHE = 'finanzas-static-v1';
const ASSETS = ['/offline.html', '/manifest.webmanifest', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(ASSETS)));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))));
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }
    if (new URL(request.url).origin === location.origin && ASSETS.some((asset) => request.url.endsWith(asset))) {
        event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
    }
});
