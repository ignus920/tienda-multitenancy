/*
* Sistema de Service Worker Manual - DOSIL ERP
* v30 - Fix fallback mobile solo para URLs mobile
*/
const CACHE_NAME = 'quoter-cache-v30';
const PRECACHE_ASSETS = [
    '/',
    '/manifest.json',
    '/Logo_DosilERPFinal.png',
    '/logo.png',
    '/pwa-icons/icon-192x192.png',
    '/pwa-icons/icon-512x512.png',
    '/js/dexie.min.js',
    '/js/sweetalert2.min.js',
    '/tenant/quoter/mobile',
    '/tenant/quoter/products/mobile',
    '/tenant/remissions',
    '/tenant/tat-quoter',
    '/tenant/tat-sales'
];

const OFFLINE_HTML = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sin conexión - DOSIL ERP</title>
<style>
  body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center;
         justify-content: center; min-height: 100vh; margin: 0; background: #f3f4f6; text-align: center; padding: 1rem; }
  .icon { font-size: 4rem; margin-bottom: 1rem; }
  h1 { color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem; }
  p { color: #6b7280; margin-bottom: 1.5rem; }
  button { background: #2563eb; color: white; border: none; padding: 0.75rem 1.5rem;
           border-radius: 0.5rem; font-size: 1rem; cursor: pointer; }
</style>
</head>
<body>
  <div class="icon">📶</div>
  <h1>Sin conexión</h1>
  <p>No hay internet. La página se cargará cuando recuperes la conexión.</p>
  <button onclick="window.location.reload()">Reintentar</button>
</body>
</html>`;

function offlineFallback() {
    return new Response(OFFLINE_HTML, {
        status: 503,
        headers: { 'Content-Type': 'text/html; charset=utf-8' }
    });
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('📦 Precargando v29...');
            return Promise.allSettled(
                PRECACHE_ASSETS.map(asset =>
                    cache.add(asset).catch(err => console.warn(`⚠️ Error precargando ${asset}:`, err))
                )
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('🧹 Limpiando caché antigua:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const isLivewire = event.request.headers.get('X-Livewire');
    const isNavigation = event.request.mode === 'navigate';

    // 1. RUTAS CRÍTICAS (Cotizador y Productos) - NETWORK FIRST
    if (isNavigation && (url.pathname.includes('/quoter') || url.pathname.includes('/products/mobile'))) {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    try {
                        // Intentar con ignoreSearch (cubre ?clear=1 y otros query params)
                        const cached = await caches.match(event.request, { ignoreSearch: true });
                        if (cached) return cached;

                        // Solo usar fallback mobile si la URL original era para la versión mobile
                        if (url.pathname.includes('/mobile')) {
                            const baseUrl = url.pathname.includes('/products/mobile')
                                ? '/tenant/quoter/products/mobile'
                                : '/tenant/quoter/mobile';
                            const baseCached = await caches.match(baseUrl);
                            if (baseCached) return baseCached;
                        }

                        return offlineFallback();
                    } catch (e) {
                        return offlineFallback();
                    }
                })
        );
        return;
    }

    // 2. OTROS NAVEGACIONES Y LIVEWIRE - Network First
    if (isNavigation || isLivewire) {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    try {
                        const cached = await caches.match(event.request, { ignoreSearch: true });
                        if (cached) return cached;

                        // Solo usar fallback mobile si la URL era para versión mobile
                        if (url.pathname.includes('/mobile')) {
                            if (url.pathname.includes('/products/mobile')) {
                                const c = await caches.match('/tenant/quoter/products/mobile');
                                if (c) return c;
                            }
                            if (url.pathname.includes('/quoter')) {
                                const c = await caches.match('/tenant/quoter/mobile');
                                if (c) return c;
                            }
                        }

                        return offlineFallback();
                    } catch (e) {
                        return offlineFallback();
                    }
                })
        );
        return;
    }

    // 3. ACTIVOS (CSS, JS, Imágenes) - Cache First
    const isAsset =
        url.pathname.includes('/build/') ||
        event.request.destination === 'style' ||
        event.request.destination === 'script' ||
        event.request.destination === 'image' ||
        event.request.destination === 'font';

    if (isAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const networkFetch = fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseToCache));
                    }
                    return networkResponse;
                }).catch(() => null);
                return cachedResponse || networkFetch;
            })
        );
    }
});
