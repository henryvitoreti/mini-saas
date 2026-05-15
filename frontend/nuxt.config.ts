import {defineNuxtConfig} from "nuxt/config";

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

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
      watch: {
        usePolling: true,
        interval: 100,
      },
      hmr: {
        protocol: 'ws',
        host: 'app.127.0.0.1.sslip.io',
        clientPort: 80,
      },
    },
  },
});