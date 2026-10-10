import tailwindcss from '@tailwindcss/vite';
import frankenUi from 'franken-ui/plugin-vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';
import * as fs from 'fs';

export default defineConfig({
    plugins: lazyPlugins(() => [
        frankenUi({
            preflight: false,
            layer: true,
            layerExceptions: ['chart'],
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ]),
    server: {
        cors: {
            origin: ['https://midas.lndo.site', 'http://midas.lndo.site'],
            credentials: true,
        },
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'midas.lndo.site',
        },
        https: {
            cert: fs.readFileSync('/certs/cert.crt'),
            key: fs.readFileSync('/certs/cert.key'),
        },
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
