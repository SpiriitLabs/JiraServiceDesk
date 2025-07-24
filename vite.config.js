import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import { viteStaticCopy } from 'vite-plugin-static-copy'

import { dirname, resolve } from 'path'
import { fileURLToPath } from 'url'
const basicPlaygroundDir = dirname(fileURLToPath(import.meta.url))

export default defineConfig({
    base: '/app/',
    define: {
        global: 'globalThis',
    },
    server: {
        // Required to listen on all interfaces
        host: '0.0.0.0',
        watch: {
            ignored: [
                '**/.idea/**',
                '**/.vscode/**',
                '**/tests/**',
                '**/var/**',
                '**/vendor/**',
            ],
        }
    },
    plugins: [
        symfonyPlugin({
            stimulus: './assets/stimulus/controllers.json',
            viteDevServerHostname: 'localhost',
        }),
        viteStaticCopy({
            targets: [
                {
                    src: './assets/static/',
                    dest: '.',
                },
            ],
        }),
    ],
    build: {
        assetsInlineLimit: 0,
        outDir: 'public/app',
        manifest: true,
        rollupOptions: {
            input: {
                app: './assets/app.ts',
                theme: './assets/theme.scss'
            },
        },
    },

    resolve: {
        alias: {
            '~': resolve(basicPlaygroundDir, 'assets'),
            '../images': resolve(basicPlaygroundDir, 'assets/hyper-theme/scss/images'),
            '../fonts': resolve(basicPlaygroundDir, 'assets/hyper-theme/scss/fonts')
        }
    },

    css: {
        preprocessorOptions: {
        scss: {
            silenceDeprecations: [
                'import',
                'mixed-decls',
                'color-functions',
                'global-builtin'
            ]
        }
        }
    }

});
