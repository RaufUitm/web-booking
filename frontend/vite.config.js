import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig(async () => {
  let vueDevToolsPlugin = null
  try {
    const mod = await import('vite-plugin-vue-devtools')
    vueDevToolsPlugin = mod.default || mod
  } catch (e) {
    // plugin not available/resolvable — ignore so dev server can start
  }

  return {
    plugins: [
      vue(),
      vueDevToolsPlugin ? vueDevToolsPlugin() : null,
    ].filter(Boolean),
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        'axios': fileURLToPath(new URL('./node_modules/axios/dist/esm/axios.js', import.meta.url)),
        // Explicitly map devtools packages to their ESM entry points to
        // avoid package exports resolution issues on Windows/Vite.
        '@vue/devtools-api': fileURLToPath(new URL('./node_modules/@vue/devtools-api/dist/index.js', import.meta.url)),
        '@vue/devtools-kit': fileURLToPath(new URL('./node_modules/@vue/devtools-kit/dist/index.js', import.meta.url)),
        '@vue/devtools-core': fileURLToPath(new URL('./node_modules/@vue/devtools-core/dist/index.js', import.meta.url))
      }
    },
    // Avoid Vite/esbuild trying to pre-bundle devtools packages which
    // may have non-standard exports in this environment.
    optimizeDeps: {
      exclude: ['@vue/devtools-api', '@vue/devtools-kit', 'vite-plugin-vue-devtools']
    },
    // Force axios to use its ESM dist build to avoid missing internal files during dependency optimization.
  }
})
