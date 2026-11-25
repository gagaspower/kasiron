import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

const pwaRevision = Date.now().toString();

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/pages/categories.js', 'resources/js/pages/units.js', 'resources/js/pages/products.js'],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            base: '/',
            buildBase: '/build/',
            scope: '/',
            registerType: 'autoUpdate',
            injectRegister: false,
            manifest: false,
            devOptions: {
                enabled: false,
            },
            workbox: {
                globPatterns: ['**/*.{css,html,ico,js,png,svg,woff,woff2}'],
                additionalManifestEntries: [
                    { url: '/', revision: pwaRevision },
                    { url: '/manifest.webmanifest', revision: pwaRevision },
                    { url: '/pwa-192x192.png', revision: pwaRevision },
                    { url: '/pwa-512x512.png', revision: pwaRevision },
                    { url: '/pwa-maskable-512x512.png', revision: pwaRevision },
                ],
                navigateFallback: '/',
                navigateFallbackDenylist: [/^\/(build\/|storage\/|_boost\/)/],
                maximumFileSizeToCacheInBytes: 3_000_000,
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
