import { Inject, Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, of, catchError, map } from 'rxjs';

import {
  CAS_CONFIG,
  CasConfig,
  CasUser,
  CasValidateResponse,
} from '../models/cas-config.model';

/** Key used for persisting the JWT token in `sessionStorage`. */
const TOKEN_STORAGE_KEY = 'cas_token';

/** Key used for persisting the serialised `CasUser` in `sessionStorage`. */
const USER_STORAGE_KEY = 'cas_user';

/**
 * Low-level CAS client service.
 *
 * Handles direct interactions with the CAS server and local session
 * management (token storage, user retrieval, role checks).
 *
 * > **Tip:** For most application code prefer the higher-level
 * > {@link CasAuthService} which wraps this service with reactive
 * > `Observable` streams.
 *
 * @example
 * ```typescript
 * constructor(private cas: CasClientService) {}
 *
 * onLogin(): void {
 *   this.cas.login('/dashboard');
 * }
 * ```
 */
@Injectable({ providedIn: 'root' })
export class CasClientService {
  /** Resolved callback URL — falls back to `origin + '/cas/callback'`. */
  private readonly callbackUrl: string;

  constructor(
    @Inject(CAS_CONFIG) private readonly config: CasConfig,
    private readonly http: HttpClient,
  ) {
    this.callbackUrl =
      this.config.callbackUrl ??
      `${typeof window !== 'undefined' ? window.location.origin : ''}/cas/callback`;
  }

  // ---------------------------------------------------------------------------
  //  Login helpers
  // ---------------------------------------------------------------------------

  /**
   * Build the full CAS SSO login URL.
   *
   * @param returnUrl - Optional URL to redirect the user to after the CAS
   *   callback has been processed.  Stored in `sessionStorage` so the
   *   callback component can read it later.
   * @returns The absolute CAS login URL.
   *
   * @example
   * ```typescript
   * const url = this.cas.getLoginUrl('/settings');
   * ```
   */
  getLoginUrl(returnUrl?: string): string {
    if (returnUrl) {
      sessionStorage.setItem('cas_return_url', returnUrl);
    }

    const params = new URLSearchParams({
      client_id: this.config.clientId,
      response_type: 'token',
      redirect_uri: this.callbackUrl,
    });

    return `${this.config.serverUrl}/sso/login?${params.toString()}`;
  }

  /**
   * Redirect the browser to the CAS SSO login page.
   *
   * @param returnUrl - URL to navigate to after successful authentication.
   */
  login(returnUrl?: string): void {
    if (typeof window !== 'undefined') {
      window.location.href = this.getLoginUrl(returnUrl);
    }
  }

  // ---------------------------------------------------------------------------
  //  Token handling
  // ---------------------------------------------------------------------------

  /**
   * Extract the JWT token from the current page URL's query string.
   *
   * The CAS server redirects to `{callbackUrl}?token=JWT_TOKEN` after a
   * successful login.
   *
   * @returns The token string, or `null` if not present.
   */
  extractTokenFromUrl(): string | null {
    if (typeof window === 'undefined') {
      return null;
    }

    const params = new URLSearchParams(window.location.search);
    return params.get('token');
  }

  /**
   * Validate a JWT token by POSTing it to the backend validation endpoint.
   *
   * If `backendValidateUrl` is configured in {@link CasConfig} the request
   * goes there; otherwise it falls back to the CAS server's
   * `POST /api/sso/validate` endpoint directly.
   *
   * On success the returned `CasUser` is automatically persisted in
   * `sessionStorage`.
   *
   * @param token - JWT token obtained from the CAS callback URL.
   * @returns An `Observable` that emits the validated `CasUser` or `null`
   *   if validation fails.
   */
  validateTokenViaBackend(token: string): Observable<CasUser | null> {
    const url =
      this.config.backendValidateUrl ??
      `${this.config.serverUrl}/api/sso/validate`;

    const headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    const body = { token, client_id: this.config.clientId };

    return this.http
      .post<CasUser | CasValidateResponse>(url, body, { headers })
      .pipe(
        map((response) => {
          const user = this.extractUser(response);
          if (!user) {
            return null;
          }
          this.setToken(token);
          this.setUser(user);
          return user;
        }),
        catchError(() => of(null)),
      );
  }

  /**
   * Normalise a validation response into a {@link CasUser}.
   *
   * The CAS server's `/api/sso/validate` (and `/api/validate-token`) endpoint
   * wraps the user in an envelope: `{ valid, user: { id, username, email }, expires_at }`.
   * A custom `backendValidateUrl` may instead return a bare `CasUser`. This helper
   * accepts either shape and returns `null` when validation did not succeed.
   */
  private extractUser(
    response: CasUser | CasValidateResponse | null,
  ): CasUser | null {
    if (!response) {
      return null;
    }

    // CAS envelope shape: { valid, user, expires_at }
    if ('user' in response || 'valid' in response) {
      const envelope = response as CasValidateResponse;
      if (envelope.valid === false || !envelope.user) {
        return null;
      }
      return envelope.user;
    }

    // Bare CasUser returned directly by a custom backend.
    return response as CasUser;
  }

  /**
   * Full callback handler: extracts the token from the URL and validates it
   * via the backend in one step.
   *
   * @returns An `Observable` that emits the validated `CasUser` or `null`.
   */
  handleCallback(): Observable<CasUser | null> {
    const token = this.extractTokenFromUrl();
    if (!token) {
      return of(null);
    }
    return this.validateTokenViaBackend(token);
  }

  // ---------------------------------------------------------------------------
  //  Session helpers
  // ---------------------------------------------------------------------------

  /**
   * Retrieve the currently stored JWT token.
   *
   * @returns The token string or `null` if not logged in.
   */
  getToken(): string | null {
    if (typeof sessionStorage === 'undefined') {
      return null;
    }
    return sessionStorage.getItem(TOKEN_STORAGE_KEY);
  }

  /**
   * Retrieve the stored `CasUser` from `sessionStorage`.
   *
   * @returns The user object or `null` if not authenticated.
   */
  getUser(): CasUser | null {
    if (typeof sessionStorage === 'undefined') {
      return null;
    }
    const raw = sessionStorage.getItem(USER_STORAGE_KEY);
    if (!raw) {
      return null;
    }
    try {
      return JSON.parse(raw) as CasUser;
    } catch {
      return null;
    }
  }

  /**
   * Check whether a user session exists (token + user present).
   *
   * @returns `true` when the user is authenticated.
   */
  isAuthenticated(): boolean {
    return this.getToken() !== null && this.getUser() !== null;
  }

  /**
   * Log out: clears `sessionStorage`, notifies CAS with a credentialed POST,
   * and redirects back to the client application.
   *
   * CAS logout is POST-only to prevent forced logout through a cross-site GET.
   * The local redirect runs in `finally`, so users are never stranded on an
   * API error response if the CAS notification is unavailable.
   *
   * @param redirectUrl - URL to land on after logout completes.
   */
  async logout(redirectUrl?: string): Promise<void> {
    if (typeof sessionStorage !== 'undefined') {
      sessionStorage.removeItem(TOKEN_STORAGE_KEY);
      sessionStorage.removeItem(USER_STORAGE_KEY);
      sessionStorage.removeItem('cas_return_url');
    }

    if (typeof window === 'undefined') {
      return;
    }

    const finalRedirect = new URL(redirectUrl ?? '/', window.location.origin).toString();

    try {
      await fetch(`${this.config.serverUrl}/api/logout`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
    } finally {
      window.location.assign(finalRedirect);
    }
  }

  // ---------------------------------------------------------------------------
  //  Role helpers
  // ---------------------------------------------------------------------------

  /**
   * Check whether the current user has a specific role.
   *
   * @param role - Role name to check (case-sensitive).
   * @returns `true` when the user possesses the role.
   */
  userHasRole(role: string): boolean {
    const user = this.getUser();
    return user?.roles?.includes(role) ?? false;
  }

  /**
   * Check whether the current user has **at least one** of the given roles.
   *
   * @param roles - Array of role names.
   * @returns `true` when the user possesses any of the listed roles.
   */
  userHasAnyRole(roles: string[]): boolean {
    const user = this.getUser();
    if (!user?.roles) {
      return false;
    }
    return roles.some((role) => user.roles!.includes(role));
  }

  /**
   * Check whether the current user has **all** of the given roles.
   *
   * @param roles - Array of role names.
   * @returns `true` only when the user possesses every listed role.
   */
  userHasAllRoles(roles: string[]): boolean {
    const user = this.getUser();
    if (!user?.roles) {
      return false;
    }
    return roles.every((role) => user.roles!.includes(role));
  }

  // ---------------------------------------------------------------------------
  //  Private helpers
  // ---------------------------------------------------------------------------

  /** Persist the JWT token. */
  private setToken(token: string): void {
    if (typeof sessionStorage !== 'undefined') {
      sessionStorage.setItem(TOKEN_STORAGE_KEY, token);
    }
  }

  /** Persist the user object. */
  private setUser(user: CasUser): void {
    if (typeof sessionStorage !== 'undefined') {
      sessionStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));
    }
  }
}
