import type { CasConfig, CasUser } from './types';

/** Key used to persist user data in sessionStorage. */
const STORAGE_KEY = 'cas_user';

/** Key used to persist the JWT token in sessionStorage. */
const TOKEN_STORAGE_KEY = 'cas_token';

/**
 * Core CAS (Central Authentication System) client for browser-side operations.
 *
 * This class handles the SSO redirect flow, token extraction from callback URLs,
 * session management via `sessionStorage`, and role-based access checks.
 *
 * **Security note:** Token validation is ALWAYS delegated to your backend.
 * This client never sends `client_secret` or validates JWTs in the browser.
 *
 * @example
 * ```ts
 * import { CasClient } from '@cas-system/react-cas-client';
 *
 * const client = new CasClient({
 *   serverUrl: 'https://cas.example.com',
 *   clientId: 'my-app',
 *   callbackUrl: 'https://myapp.com/auth/callback',
 *   backendValidateUrl: '/api/auth/validate',
 * });
 *
 * // Redirect to CAS login
 * client.login();
 *
 * // Handle the callback (after redirect back)
 * const user = await client.handleCallback();
 * ```
 */
export class CasClient {
  private readonly config: CasConfig;

  /**
   * Create a new CAS client instance.
   * @param config - The CAS configuration object.
   */
  constructor(config: CasConfig) {
    this.config = config;
  }

  // ---------------------------------------------------------------------------
  // Login / Redirect
  // ---------------------------------------------------------------------------

  /**
   * Build the full CAS SSO login URL.
   *
   * @param returnUrl - Optional URL to return to after authentication.
   *   Falls back to `config.callbackUrl`, then to `window.location.href`.
   * @returns The complete login URL including query parameters.
   *
   * @example
   * ```ts
   * const url = client.getLoginUrl('/dashboard');
   * // => "https://cas.example.com/sso/login?client_id=my-app&response_type=token&redirect_uri=https://myapp.com/dashboard"
   * ```
   */
  getLoginUrl(returnUrl?: string): string {
    const redirectUri =
      returnUrl ??
      this.config.callbackUrl ??
      (typeof window !== 'undefined'
        ? window.location.href
        : '');

    const params = new URLSearchParams({
      client_id: this.config.clientId,
      response_type: 'token',
      redirect_uri: redirectUri,
    });

    return `${this.config.serverUrl}/sso/login?${params.toString()}`;
  }

  /**
   * Redirect the browser to the CAS SSO login page.
   *
   * @param returnUrl - Optional URL to return to after authentication.
   */
  login(returnUrl?: string): void {
    if (typeof window !== 'undefined') {
      window.location.href = this.getLoginUrl(returnUrl);
    }
  }

  // ---------------------------------------------------------------------------
  // Token Handling
  // ---------------------------------------------------------------------------

  /**
   * Extract the JWT token from the current URL's `?token=` query parameter.
   *
   * After the CAS server authenticates the user, it redirects back to
   * `callbackUrl?token=JWT_TOKEN`. This method reads that parameter.
   *
   * @returns The JWT token string, or `null` if not present.
   */
  extractTokenFromUrl(): string | null {
    if (typeof window === 'undefined') return null;

    const params = new URLSearchParams(window.location.search);
    return params.get('token');
  }

  /**
   * Send the token to your backend for validation.
   *
   * Your backend should forward the token to
   * `POST {casServerUrl}/api/validate-token` along with `client_id` and
   * `client_secret` — values that must **never** be exposed to the browser.
   *
   * @param token - The JWT token to validate.
   * @returns The validated {@link CasUser} object.
   * @throws {Error} If `backendValidateUrl` is not configured or the request fails.
   *
   * @example
   * ```ts
   * const user = await client.validateTokenViaBackend(token);
   * console.log(user.username); // 'john_doe'
   * ```
   */
  async validateTokenViaBackend(token: string): Promise<CasUser> {
    const { backendValidateUrl } = this.config;

    if (!backendValidateUrl) {
      throw new Error(
        '[CAS Client] backendValidateUrl is not configured. ' +
          'Token validation must be performed by your backend server.',
      );
    }

    const response = await fetch(backendValidateUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token }),
      credentials: 'include',
    });

    if (!response.ok) {
      const errorText = await response.text().catch(() => 'Unknown error');
      throw new Error(
        `[CAS Client] Token validation failed (${response.status}): ${errorText}`,
      );
    }

    const data: CasUser = await response.json();
    return data;
  }

  /**
   * Full callback handler: extract the token from the URL, validate it via the
   * backend, persist the user in `sessionStorage`, and clean the URL.
   *
   * Call this on the page the CAS server redirects to after login.
   *
   * @returns The validated {@link CasUser}, or `null` if no `?token=` param is present.
   * @throws {Error} If token validation fails.
   */
  async handleCallback(): Promise<CasUser | null> {
    const token = this.extractTokenFromUrl();
    if (!token) return null;

    const user = await this.validateTokenViaBackend(token);

    // Persist user and token in sessionStorage
    this.setUser(user);
    this.setToken(token);

    // Remove the token from the URL to prevent accidental leakage
    this.cleanUrl();

    return user;
  }

  // ---------------------------------------------------------------------------
  // Session Management
  // ---------------------------------------------------------------------------

  /**
   * Retrieve the currently stored user from `sessionStorage`.
   *
   * @returns The stored {@link CasUser}, or `null` if no session exists.
   */
  getUser(): CasUser | null {
    if (typeof window === 'undefined') return null;

    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      return raw ? (JSON.parse(raw) as CasUser) : null;
    } catch {
      return null;
    }
  }

  /**
   * Retrieve the currently stored JWT token from `sessionStorage`.
   *
   * @returns The stored token string, or `null` if no session exists.
   */
  getToken(): string | null {
    if (typeof window === 'undefined') return null;

    try {
      return sessionStorage.getItem(TOKEN_STORAGE_KEY);
    } catch {
      return null;
    }
  }

  /**
   * Check whether a user session currently exists.
   *
   * @returns `true` if a user is stored in `sessionStorage`.
   */
  isAuthenticated(): boolean {
    return this.getUser() !== null;
  }

  /**
   * Persist a {@link CasUser} in `sessionStorage`.
   * @internal
   */
  private setUser(user: CasUser): void {
    if (typeof window === 'undefined') return;
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(user));
  }

  /**
   * Persist a JWT token in `sessionStorage`.
   * @internal
   */
  private setToken(token: string): void {
    if (typeof window === 'undefined') return;
    sessionStorage.setItem(TOKEN_STORAGE_KEY, token);
  }

  /**
   * Clear all CAS-related data from `sessionStorage`.
   * @internal
   */
  private clearSession(): void {
    if (typeof window === 'undefined') return;
    sessionStorage.removeItem(STORAGE_KEY);
    sessionStorage.removeItem(TOKEN_STORAGE_KEY);
  }

  // ---------------------------------------------------------------------------
  // Logout
  // ---------------------------------------------------------------------------

  /**
   * Log the user out by clearing the local session, calling the CAS server's
   * logout endpoint, and optionally redirecting.
   *
   * @param redirectUrl - URL to navigate to after logout. If omitted, the
   *   page is reloaded at the current location.
   */
  async logout(redirectUrl?: string): Promise<void> {
    // Clear local session first
    this.clearSession();

    // Call the CAS server logout endpoint (fire-and-forget)
    try {
      await fetch(`${this.config.serverUrl}/api/logout`, {
        method: 'POST',
        credentials: 'include',
      });
    } catch {
      // Swallow network errors — the local session is already cleared
    }

    // Redirect
    if (typeof window !== 'undefined') {
      if (redirectUrl) {
        window.location.href = redirectUrl;
      } else {
        window.location.reload();
      }
    }
  }

  // ---------------------------------------------------------------------------
  // Role Helpers
  // ---------------------------------------------------------------------------

  /**
   * Check whether the current user has a specific role.
   *
   * @param role - The role to check (case-sensitive).
   * @returns `true` if the stored user has the given role.
   */
  userHasRole(role: string): boolean {
    const user = this.getUser();
    return user?.roles?.includes(role) ?? false;
  }

  /**
   * Check whether the current user has **at least one** of the given roles.
   *
   * @param roles - The roles to check.
   * @returns `true` if the user has any of the specified roles.
   */
  userHasAnyRole(roles: string[]): boolean {
    const user = this.getUser();
    if (!user?.roles) return false;
    return roles.some((role) => user.roles!.includes(role));
  }

  /**
   * Check whether the current user has **all** of the given roles.
   *
   * @param roles - The roles to check.
   * @returns `true` if the user has every specified role.
   */
  userHasAllRoles(roles: string[]): boolean {
    const user = this.getUser();
    if (!user?.roles) return false;
    return roles.every((role) => user.roles!.includes(role));
  }

  // ---------------------------------------------------------------------------
  // Internal Helpers
  // ---------------------------------------------------------------------------

  /**
   * Remove the `token` query parameter from the browser URL without reloading.
   * @internal
   */
  private cleanUrl(): void {
    if (typeof window === 'undefined') return;

    const url = new URL(window.location.href);
    url.searchParams.delete('token');

    const cleanedUrl = url.pathname + (url.search || '') + url.hash;
    window.history.replaceState({}, document.title, cleanedUrl);
  }
}
