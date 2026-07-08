import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// The local package (@cas-system/vue-cas-client) is linked via
// `file:../../packages/vue-cas-client` and ships raw TypeScript + .vue source
// (its package.json `main` points at ./src/index.ts). Vite compiles it on the
// fly, so we must NOT pre-bundle it — `optimizeDeps.exclude` keeps esbuild from
// choking on the .vue single-file component inside the dependency.
//
// Because the package is symlinked, its bare imports (vue, vue-router, pinia)
// would otherwise resolve relative to the package's own (empty) folder. We
// `dedupe` + alias those peer deps to THIS app's node_modules so there is a
// single copy of each and Rollup/Vite can resolve them during build.
const r = (p) => fileURLToPath(new URL(p, import.meta.url));

export default defineConfig({
  plugins: [vue()],
  resolve: {
    dedupe: ['vue', 'vue-router', 'pinia'],
    alias: {
      vue: r('./node_modules/vue'),
      'vue-router': r('./node_modules/vue-router'),
      pinia: r('./node_modules/pinia'),
    },
  },
  optimizeDeps: {
    exclude: ['@cas-system/vue-cas-client'],
  },
  server: {
    port: 9109,
  },
});
