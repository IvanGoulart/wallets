import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // Adicionando configuração para resolver problemas comuns
    server: {
        host: 'localhost',
        port: 5173,
        open: false, // Não abre o navegador automaticamente
        hmr: {
            host: 'localhost', // Garante que o hot-reload funcione
        },
    },
    build: {
        manifest: true, // Gera um manifesto para integração com Laravel
        outDir: 'public/build', // Diretório de saída padrão para Laravel
    },
});
