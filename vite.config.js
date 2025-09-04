import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['images/icon-192x192.png', 'images/icon-512x512.png', 'offline.html'],
            manifest: {
                name: 'PIS-PK Kaben',
                short_name: 'PIS-PK',
                description: 'Aplikasi Program Indonesia Sehat dengan Pendekatan Keluarga',
                start_url: '/',
                display: 'standalone',
                background_color: '#ffffff',
                theme_color: '#3b82f6',
                icons: [
                    {
                        src: '/images/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
            workbox: {
                navigateFallback: '/offline.html',
                runtimeCaching: [
                    {
                        // OSM tiles (a,b,c)
                        urlPattern: /https:\/\/(a|b|c)\.tile\.openstreetmap\.org\/.*$/, 
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'osm-tiles',
                            expiration: {
                                maxEntries: 5000,
                                maxAgeSeconds: 60 * 60 * 24 * 30, // 30 days
                            },
                        },
                    },
                    {
                        // Map API bbox and stats
                        urlPattern: ({ url }) => url.pathname.startsWith('/api/map/') || url.pathname.startsWith('/api/buildings') || url.pathname.startsWith('/api/health-statistics'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            networkTimeoutSeconds: 4,
                            expiration: {
                                maxEntries: 200,
                                maxAgeSeconds: 60 * 60 * 24, // 1 day
                            },
                        },
                    },
                ],
            },
        }),
    ],
});
