self.addEventListener('install', (event) => {
    self.skipWaiting();

    console.log(`[PWA/ServiceWorker] Install`);
    console.log(event);
});

self.addEventListener('activate', (event) => {
    self.clients.claim();

    console.log(`[PWA/ServiceWorker] activate`);
    console.log(event);
});

self.addEventListener('push', async (event) => {
    console.log(`[PWA/ServiceWorker] push`);
    console.log(event);

    const data = event.data.json();
    let options = {
        icon: './icons/icon.png',
    };

    options = {...options, ...data.options};

    await self.registration.showNotification(data.title, options)
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();

  console.log('[SW] notification click - action:', event.action);

  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow('/')
    );
  } else if (event.action === 'close') {
  } else {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});
