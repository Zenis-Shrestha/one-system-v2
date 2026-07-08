// -----------------------------------------------------------------------------
// App bootstrap.
//
// Demonstrates the full @cas-system/vue-cas-client install pattern:
//   1. app.use(CasPlugin, config)           -> provides reactive auth context
//   2. createCasAuthGuard(casContext, opts)  -> protects routes via meta
//
// SPLIT-HORIZON CAS URL
// ---------------------
// The browser SDK uses `serverUrl` to build the /sso/login redirect, so it must
// point at the PUBLIC CAS host the browser can reach. We get that value from the
// backend at runtime via GET /api/auth/config, which the Express server
// populates from CAS_PUBLIC_URL (falling back to the internal CAS_BASE_URL).
// This is the authoritative source because the Docker image is built without the
// VITE_* values; the build-time vars below are only a local-dev fallback when
// running `vite` directly. Server-to-server token validation stays internal and
// is handled entirely by the backend behind `backendValidateUrl`.
//
// Only PUBLIC values (CAS server URL, client_id, callback URL) ever live in the
// front-end. The client_secret is NEVER here — it stays in the Express backend,
// which the SDK reaches through `backendValidateUrl`.
// -----------------------------------------------------------------------------
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import {
  CasPlugin,
  createCasAuthGuard,
  CAS_AUTH_KEY,
} from '@cas-system/vue-cas-client';

import App from './App.vue';
import { router } from './router';

// Resolve the PUBLIC, browser-facing CAS config. Prefer the runtime value the
// backend exposes at /api/auth/config (sourced from CAS_PUBLIC_URL); fall back to
// the build-time VITE_* vars for standalone `vite` dev.
async function resolveCasConfig() {
  const fallback = {
    serverUrl: import.meta.env.VITE_CAS_BASE_URL,
    clientId: import.meta.env.VITE_CAS_CLIENT_ID,
  };
  try {
    const res = await fetch('/api/auth/config', {
      headers: { Accept: 'application/json' },
    });
    if (!res.ok) return fallback;
    const cfg = await res.json();
    return {
      serverUrl: cfg.serverUrl || fallback.serverUrl,
      clientId: cfg.clientId || fallback.clientId,
    };
  } catch {
    return fallback;
  }
}

async function bootstrap() {
  const cas = await resolveCasConfig();

  const app = createApp(App);

  // Pinia is optional for this SDK, but enabling it lets the sample also expose
  // the useCasStore() path if desired. The composables (useCasAuth/useCasUser)
  // used in this sample work without it.
  app.use(createPinia());

  // 1) Install the CAS plugin. The plugin auto-handles `?token=` callbacks by
  //    default (autoHandleCallback: true), but our /auth/callback view calls
  //    handleCallback() explicitly so we can route afterwards — we therefore
  //    disable the auto behaviour to validate the single-use token exactly once.
  app.use(CasPlugin, {
    // PUBLIC CAS base — the browser SDK builds {serverUrl}/sso/login from this.
    serverUrl: cas.serverUrl,
    clientId: cas.clientId,
    callbackUrl: import.meta.env.VITE_CAS_CALLBACK_URL,
    // The backend endpoint (same origin) that performs the server-to-server
    // validation with the client_secret against the INTERNAL CAS base.
    backendValidateUrl: '/api/auth/validate',
    autoHandleCallback: false,
  });

  // 2) Attach the router guard. Guards run outside the component tree, so we read
  //    the CAS context from the app's provides (per the package README).
  const casContext = app._context.provides[CAS_AUTH_KEY];
  router.beforeEach(
    createCasAuthGuard(casContext, {
      redirectToLogin: true,
    }),
  );

  app.use(router);
  app.mount('#app');
}

bootstrap();
