import { defineNuxtConfig } from 'nuxt/config';

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  pages: true,

  css: [
    '@fortawesome/fontawesome-free/css/all.min.css',
    '@/assets/scss/main.scss',
  ],

  devServer: {
    host: '0.0.0.0',
    port: 3000,
  },

  vite: {
    server: {
      allowedHosts: [
        'app.127.0.0.1.sslip.io',
        '.127.0.0.1.sslip.io',
      ],
      watch: {
        usePolling: true,
        interval: 100,
        ignored: [
          '**/node_modules/**',
          '**/.git/**',
        ],
      },
      hmr: {
        protocol: 'ws',
        host: 'app.127.0.0.1.sslip.io',
        clientPort: 8081,
      },
    },
  },
});
