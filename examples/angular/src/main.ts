/**
 * Bootstrap for the One System CAS Angular sample.
 *
 * Why fetch config first?
 * -----------------------
 * The CAS client SDK needs `serverUrl`, `clientId`, and `callbackUrl` to build
 * the login redirect. Those are NOT secrets, but they ARE environment-specific,
 * so we read them at runtime from the backend (`GET /api/config`) rather than
 * hard-coding them. The client_secret is never exposed — it stays on the
 * backend, which is the only thing that calls {CAS_BASE}/api/validate-token.
 *
 * We provide the SDK using the *standalone* pattern documented in the package
 * README: CAS_CONFIG + the CasTokenInterceptor via HTTP_INTERCEPTORS.
 */
import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import {
  provideHttpClient,
  withInterceptorsFromDi,
  HTTP_INTERCEPTORS,
} from '@angular/common/http';

import {
  CAS_CONFIG,
  CasConfig,
  CasTokenInterceptor,
  CasCallbackComponent,
  CasAuthGuard,
} from '@cas-system/angular-cas-client';

import { AppComponent } from './app/app.component';
import { HomeComponent } from './app/pages/home.component';
import { ProfileComponent } from './app/pages/profile.component';
import { LoginComponent } from './app/pages/login.component';

/** Shape of the public, non-secret config served by GET /api/config. */
interface PublicCasConfig {
  serverUrl: string;
  clientId: string;
  callbackUrl: string;
}

async function loadConfig(): Promise<PublicCasConfig> {
  const res = await fetch('/api/config');
  if (!res.ok) {
    throw new Error(`Failed to load /api/config (HTTP ${res.status})`);
  }
  return res.json();
}

loadConfig()
  .then((cfg) => {
    // Build the SDK config. `backendValidateUrl` points at OUR Express
    // endpoint, which adds the client_secret and proxies to the CAS server.
    const casConfig: CasConfig = {
      serverUrl: cfg.serverUrl,
      clientId: cfg.clientId,
      callbackUrl: cfg.callbackUrl,
      backendValidateUrl: '/api/auth/validate',
    };

    return bootstrapApplication(AppComponent, {
      providers: [
        provideHttpClient(withInterceptorsFromDi()),
        provideRouter([
          { path: '', component: HomeComponent },

          // Local username/password login (the app's OWN accounts). Renders a
          // form that POSTs to the backend's /login; on success it establishes
          // the app's own session (the same one CAS uses) and returns home.
          { path: 'login', component: LoginComponent },

          // CAS callback route. The CasCallbackComponent (shipped by the SDK)
          // extracts ?token=, validates it via /api/auth/validate, stores the
          // session, then navigates to the stored return URL.
          { path: 'cas/callback', component: CasCallbackComponent },

          // Protected route — CasAuthGuard redirects to CAS login if there is
          // no session yet.
          {
            path: 'profile',
            component: ProfileComponent,
            canActivate: [CasAuthGuard],
          },

          { path: '**', redirectTo: '' },
        ]),

        // Provide the SDK config that CasClientService / CasAuthService inject.
        { provide: CAS_CONFIG, useValue: casConfig },

        // Auto-attach `Authorization: Bearer <token>` to outgoing HttpClient
        // requests (the SDK reads the JWT from sessionStorage).
        {
          provide: HTTP_INTERCEPTORS,
          useClass: CasTokenInterceptor,
          multi: true,
        },
      ],
    });
  })
  .catch((err) => {
    // Render a plain error so misconfiguration is obvious during local dev.
    const root = document.querySelector('app-root');
    if (root) {
      root.textContent =
        'Could not start the app — is the backend running and /api/config reachable? ' +
        String(err);
    }
    // eslint-disable-next-line no-console
    console.error(err);
  });
