import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Port the whole sample is served on (dev server AND the Express backend).
const PORT = Number(process.env.PORT || 9107);

export default defineConfig({
  plugins: [react()],

  resolve: {
    alias: {
      // Resolve @cas-system/react-cas-client to its TypeScript SOURCE.
      //
      // The package's package.json "exports" point at dist/, which is only
      // present after you run `npm run build` inside the package. Vite happily
      // compiles the package's TS + JSX on the fly, so aliasing straight to the
      // source makes this sample runnable with ZERO build step in the package
      // -- while still depending on it locally via "file:" in package.json.
      //
      // (If you prefer to consume the built artifact instead, build the package
      //  -- `cd ../../packages/react-cas-client && npm i && npm run build` --
      //  and delete this alias.)
      '@cas-system/react-cas-client': path.resolve(
        __dirname,
        '../../packages/react-cas-client/src/index.ts',
      ),
    },
  },

  server: {
    port: PORT,
    // In dev, the SPA runs on Vite (this port) and the Express API runs on a
    // separate internal port; proxy the backend routes to it so the browser only
    // ever talks to one origin. See server.js + the dev script. We point the
    // proxy at the backend's own port via API_PORT (defaults to PORT+1 in dev).
    //
    //   /api    -> CAS config + token validation + /api/me (local session)
    //   /login  -> local login form (GET) + local/CAS-validation login (POST)
    //   /logout -> clear the local session
    proxy: {
      '/api': {
        target: `http://localhost:${Number(process.env.API_PORT || PORT + 1)}`,
        changeOrigin: true,
      },
      '/login': {
        target: `http://localhost:${Number(process.env.API_PORT || PORT + 1)}`,
        changeOrigin: true,
      },
      '/logout': {
        target: `http://localhost:${Number(process.env.API_PORT || PORT + 1)}`,
        changeOrigin: true,
      },
    },
  },

  build: {
    outDir: 'dist',
  },
});
