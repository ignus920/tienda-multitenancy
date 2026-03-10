/*
* Sistema de Service Worker Manual - DOSIL ERP
* v31 - Caché de páginas persistente + migración de versiones anteriores
*/
const CACHE_NAME = 'quoter-cache-v31';
// Caché separado para páginas visitadas — NO se borra al actualizar el SW
const PAGES_CACHE = 'dosil-pages-cache';

const PRECACHE_ASSETS = [
    '/manifest.json',
    '/Logo_DosilERPFinal.png',
    '/logo.png',
    '/pwa-icons/icon-192x192.png',
    '/pwa-icons/icon-512x512.png',
    '/js/dexie.min.js',
    '/js/sweetalert2.min.js',
];

// Páginas que se intentan cachear en instalación (requieren sesión activa)
const PRECACHE_PAGES = [
    '/tenant/quoter/mobile',
    '/tenant/quoter/products/mobile',
    '/tenant/quoter/products',
    '/tenant/quoter',
    '/tenant/remissions',
    '/tenant/tat-quoter',
    '/tenant/tat-sales',
    '/tenant/dashboard',
    '/',
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
    event.waitUntil(Promise.all([
        // Cachear activos estáticos
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_ASSETS.map(asset =>
                    cache.add(asset).catch(err => console.warn(`⚠️ Error precargando asset ${asset}:`, err))
                )
            );
        }),
        // Cachear páginas en PAGES_CACHE (no se borra en actualizaciones)
        caches.open(PAGES_CACHE).then((cache) => {
            return Promise.allSettled(
                PRECACHE_PAGES.map(page =>
                    cache.add(page).catch(err => console.warn(`⚠️ Error precargando página ${page}:`, err))
                )
            );
        }),
    ]));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(async (cacheNames) => {
            const newCache = await caches.open(PAGES_CACHE);
            for (const cacheName of cacheNames) {
                // Migrar páginas de cachés viejas al PAGES_CACHE antes de borrarlas
                if (cacheName !== CACHE_NAME && cacheName !== PAGES_CACHE) {
                    try {
                        const oldCache = await caches.open(cacheName);
                        const keys = await oldCache.keys();
                        for (const request of keys) {
                            // Solo migrar navegaciones HTML (no assets)
                            if (!request.url.includes('/build/') && !request.url.match(/\.(js|css|png|jpg|svg|ico|woff)$/)) {
                                const response = await oldCache.match(request);
                                if (response) {
                                    const existing = await newCache.match(request, { ignoreSearch: true });
                                    if (!existing) {
                                        await newCache.put(request, response).catch(() => {});
                                    }
                                }
                            }
                        }
                    } catch (e) {}
                    console.log('🧹 Limpiando caché antigua:', cacheName);
                    await caches.delete(cacheName);
                }
            }
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const isLivewire = event.request.headers.get('X-Livewire');
    const isNavigation = event.request.mode === 'navigate';

    // 1. NAVEGACIONES - Network First, fallback a PAGES_CACHE
    if (isNavigation) {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        // Guardar en PAGES_CACHE al visitar online
                        const copy = networkResponse.clone();
                        caches.open(PAGES_CACHE).then((cache) => cache.put(event.request, copy));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    try {
                        // Buscar en PAGES_CACHE ignorando query params (?clear=1, etc.)
                        const pagesCache = await caches.open(PAGES_CACHE);
                        const cached = await pagesCache.match(event.request, { ignoreSearch: true });
                        if (cached) return cached;

                        // Fallback por tipo de URL (solo versiones mobile para URLs mobile)
                        if (url.pathname.includes('/mobile')) {
                            const baseUrl = url.pathname.includes('/products/mobile')
                                ? '/tenant/quoter/products/mobile'
                                : '/tenant/quoter/mobile';
                            const baseCached = await pagesCache.match(baseUrl);
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

    // 2. LIVEWIRE - Network First (no cachear)
    if (isLivewire) {
        event.respondWith(
            fetch(event.request).catch(() =>
                new Response('{"message":"offline"}', {
                    status: 503,
                    headers: { 'Content-Type': 'application/json' }
                })
            )
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
