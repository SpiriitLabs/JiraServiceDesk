self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    self.clients.claim();
    console.log(`PWA Running`)
});

self.addEventListener('fetch', (event) => {
});
