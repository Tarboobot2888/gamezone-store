import { defineConfig } from "vite"
import laravel from "laravel-vite-plugin"

export default defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/sass/app.scss",
        "resources/sass/app-rtl.scss",
        "resources/sass/dark.scss",
        "resources/sass/light.scss",
        "resources/js/app.js",
        "resources/js/admin.js",
        "resources/js/firebase.js",
      ],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      "~bootstrap": "node_modules/bootstrap",
      "~@fortawesome": "node_modules/@fortawesome",
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ["bootstrap", "alpinejs"],
          firebase: ["firebase/app", "firebase/auth", "firebase/storage"],
          charts: ["chart.js"],
        },
      },
    },
  },
})
