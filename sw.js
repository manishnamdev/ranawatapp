self.addEventListener('push', (event) => {
    let data = {};
    const scopeUrl = new URL(self.registration.scope);
    const basePath = scopeUrl.pathname === '/' ? '' : scopeUrl.pathname.replace(/\/$/, '');

    try {
        data = event.data ? event.data.json() : {};
    } catch (error) {
        data = {
            title: 'New notification',
            body: event.data ? event.data.text() : '',
        };
    }

    const title = data.title || 'New notification';
    const options = {
        body: data.body || '',
        icon: data.icon || `${basePath}/assets/images/banner.jpeg`,
        badge: data.badge || `${basePath}/assets/images/banner.jpeg`,
        data: {
            url: data.url || `${basePath}/dashboard.php`,
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const scopeUrl = new URL(self.registration.scope);
    const basePath = scopeUrl.pathname === '/' ? '' : scopeUrl.pathname.replace(/\/$/, '');

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : `${basePath}/dashboard.php`;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }

            return null;
        })
    );
});
