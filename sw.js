const CACHE_NAME = 'quran-pwa-cache-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/style.css',
  '/script.js',
  '/manifest.json',
  'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700;900&family=Kalam:wght@700&display=swap',
  'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(ASSETS_TO_CACHE))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
      );
    })
  );
});

self.addEventListener('fetch', event => {
  if(event.request.url.includes('/api.php') || event.request.url.includes('.mp3')) {
    // For API and MP3, use Network First, then fallback to cache
    event.respondWith(
      fetch(event.request).catch(() => caches.match(event.request))
    );
  } else {
    // For static assets, Cache First, then Network
    event.respondWith(
      caches.match(event.request)
        .then(response => {
          if (response) return response;
          return fetch(event.request).then(netRes => {
            return caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, netRes.clone());
              return netRes;
            });
          });
        })
    );
  }
});
