import { Injectable } from '@angular/core';
import { CasAuthService, CasUser } from '@cas-system/angular-cas-client';

/**
 * Bridges LOCAL username/password login into the app's OWN session — the very
 * same browser session the CAS SDK uses.
 *
 * The SDK persists its session in `sessionStorage` under `cas_token` +
 * `cas_user`, and `CasAuthService.checkAuth()` re-hydrates its reactive
 * `user$` / `isAuthenticated$` streams from those keys. So to make a local
 * login indistinguishable to the rest of the app (nav bar, /profile guard,
 * home page), we write those same keys and then ask `CasAuthService` to
 * re-read them.
 *
 * This keeps the SDK's public API untouched: we only use its documented
 * `checkAuth()` entry point plus the storage keys it already owns.
 */
const TOKEN_STORAGE_KEY = 'cas_token';
const USER_STORAGE_KEY = 'cas_user';

@Injectable({ providedIn: 'root' })
export class LocalSessionService {
  constructor(private readonly auth: CasAuthService) {}

  /**
   * Establish the app's own session for a locally-authenticated user.
   * Mirrors the SDK's storage shape so every CAS-driven view "just works".
   */
  signInLocal(user: CasUser): void {
    // A synthetic, clearly-local token. There is no CAS JWT for a local login;
    // this satisfies the SDK's `getToken()` (used by the guard/interceptor)
    // while making the origin obvious.
    sessionStorage.setItem(TOKEN_STORAGE_KEY, `local:${user.username}`);
    sessionStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));

    // Mark this session as locally-originated so logout knows NOT to bounce
    // through the CAS server (a local user has no CAS session to clear).
    sessionStorage.setItem('app_local_session', 'true');

    // Re-hydrate the SDK's reactive streams from what we just stored.
    this.auth.checkAuth();
  }

  /** True when the current session came from local username/password login. */
  isLocalSession(): boolean {
    return sessionStorage.getItem('app_local_session') === 'true';
  }

  /**
   * Clear the app's own session for a local user WITHOUT redirecting to the
   * CAS server's logout endpoint (there is no CAS session to end).
   */
  signOutLocal(): void {
    sessionStorage.removeItem(TOKEN_STORAGE_KEY);
    sessionStorage.removeItem(USER_STORAGE_KEY);
    sessionStorage.removeItem('app_local_session');
    sessionStorage.removeItem('cas_return_url');
    this.auth.checkAuth();
  }
}
