const CACHE_NAME = 'cafe-hagi-v3';
const ASSETS = [
  '/manifest.webmanifest',
  '/assets/pwa/icon-192.png',
  '/assets/pwa/icon-512.png',
  '/assets/logo.svg',
  '/favicon.ico',
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS)).catch(() => {})
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;

  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/dashboard') || url.pathname.startsWith('/login')) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => response)
        .catch(() => caches.match(request).then((cached) => cached || caches.match('/')))
    );
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      const networkFetch = fetch(request)
        .then((response) => {
          if (
            response &&
            response.status === 200 &&
            (
              url.pathname.includes('/assets/') ||
              url.pathname.includes('/storage/') ||
              url.pathname.endsWith('.css') ||
              url.pathname.endsWith('.js') ||
              url.pathname.endsWith('.png') ||
              url.pathname.endsWith('.jpg') ||
              url.pathname.endsWith('.jpeg') ||
              url.pathname.endsWith('.svg') ||
              url.pathname.endsWith('.webp') ||
              url.pathname.endsWith('.ico')
            )
          ) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return response;
        })
        .catch(() => cached);

      return cached || networkFetch;
    })
  );
});