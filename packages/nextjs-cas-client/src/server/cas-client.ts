/**
 * @module @cas-system/nextjs-cas-client/server/cas-client
 * @description Server-side CAS client for token validation, generation, and
 * SSO login URL construction.  Uses only the built-in `fetch` API — zero
 * external dependencies.
 *
 * **IMPORTANT:** This module must only run on the server. It requires the
 * `clientSecret` which must never be sent to the browser.
 */

import type {
  CasServerConfig,
  CasUser,
  CasValidateResponse,
  CasTokenResponse,
} from '../types';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Normalise a base URL by stripping a trailing slash.
 * @internal
 */
function normaliseUrl(url: string): string {
  return url.replace(/\/+$/, '');
}

// ---------------------------------------------------------------------------
// CasClient
// ---------------------------------------------------------------------------

/**
 * Server-side CAS client.
 *
 * @example
 * ```ts
 * import { CasClient } from '@cas-system/nextjs-cas-client/server';
 *
 * const cas = new CasClient({
 *   serverUrl: process.env.CAS_SERVER_URL!,
 *   clientId: process.env.CAS_CLIENT_ID!,
 *   clientSecret: process.env.CAS_CLIENT_SECRET!,
 *   callbackUrl: process.env.CAS_CALLBACK_URL,
 * });
 *
 * // Validate a token received from the callback
 * const user = await cas.validateToken(token);
 * ```
 */
export class CasClient {
  private readonly config: CasServerConfig & { serverUrl: string };

  constructor(config: CasServerConfig) {
    if (!config.serverUrl) throw new Error('[CasClient] serverUrl is required');
    if (!config.clientId) throw new Error('[CasClient] clientId is required');
    if (!config.clientSecret)
      throw new Error('[CasClient] clientSecret is required');

    this.config = {
      ...config,
      serverUrl: normaliseUrl(config.serverUrl),
    };
  }

  // -----------------------------------------------------------------------
  // Login URL
  // -----------------------------------------------------------------------

  /**
   * Build the CAS SSO login URL.
   *
   * @param returnUrl - Optional URL to redirect back to after login.
   *                    Falls back to `config.callbackUrl`.
   * @returns Fully-qualified CAS login URL.
   *
   * @example
   * ```ts
   * const loginUrl = cas.getLoginUrl('/dashboard');
   * redirect(loginUrl);
   * ```
   */
  getLoginUrl(returnUrl?: string): string {
    const callbackUrl =
      returnUrl ?? this.config.callbackUrl ?? '/api/cas/callback';
    const params = new URLSearchParams({
      client_id: this.config.clientId,
      response_type: 'token',
      redirect_uri: callbackUrl,
    });
    return `${this.config.serverUrl}/sso/login?${params.toString()}`;
  }

  // -----------------------------------------------------------------------
  // Token Validation
  // -----------------------------------------------------------------------

  /**
   * Validate a JWT token with the CAS server (server-to-server call).
   *
   * @param token - The JWT token received from the CAS callback.
   * @returns The authenticated {@link CasUser} or `null` if invalid.
   *
   * @example
   * ```ts
   * const user = await cas.validateToken(token);
   * if (!user) throw new Error('Invalid token');
   * ```
   */
  async validateToken(token: string): Promise<CasUser | null> {
    try {
      const res = await fetch(
        `${this.config.serverUrl}/api/validate-token`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            token,
            client_id: this.config.clientId,
            client_secret: this.config.clientSecret,
          }),
          cache: 'no-store',
        },
      );

      if (!res.ok) {
        console.error(
          `[CasClient] validateToken failed: ${res.status} ${res.statusText}`,
        );
        return null;
      }

      const data = (await res.json()) as CasValidateResponse;
      if (!data.valid || !data.user) return null;

      return data.user;
    } catch (err) {
      console.error('[CasClient] validateToken error:', err);
      return null;
    }
  }

  // -----------------------------------------------------------------------
  // Token Generation
  // -----------------------------------------------------------------------

  /**
   * Generate a JWT token for the given username (server-to-server).
   *
   * This is useful for machine-to-machine auth flows or impersonation
   * when the calling service already knows the target user.
   *
   * @param username - The username to issue a token for.
   * @returns The JWT string or `null` on failure.
   */
  async generateToken(username: string): Promise<string | null> {
    try {
      const res = await fetch(
        `${this.config.serverUrl}/api/sso/token`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            client_id: this.config.clientId,
            client_secret: this.config.clientSecret,
            username,
          }),
          cache: 'no-store',
        },
      );

      if (!res.ok) {
        console.error(
          `[CasClient] generateToken failed: ${res.status} ${res.statusText}`,
        );
        return null;
      }

      const data = (await res.json()) as CasTokenResponse;
      if (!data.token) return null;

      return data.token;
    } catch (err) {
      console.error('[CasClient] generateToken error:', err);
      return null;
    }
  }

  // -----------------------------------------------------------------------
  // Logout
  // -----------------------------------------------------------------------

  /**
   * Notify the CAS server of a logout event.
   *
   * @param token - Optional token to invalidate on the server.
   * @returns `true` if the server acknowledged the logout.
   */
  async logout(token?: string): Promise<boolean> {
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
      };
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const res = await fetch(
        `${this.config.serverUrl}/api/logout`,
        {
          method: 'POST',
          headers,
          body: JSON.stringify({
            client_id: this.config.clientId,
          }),
          cache: 'no-store',
        },
      );

      return res.ok;
    } catch (err) {
      console.error('[CasClient] logout error:', err);
      return false;
    }
  }

  // -----------------------------------------------------------------------
  // Role helpers
  // -----------------------------------------------------------------------

  /**
   * Check whether a user has **all** of the specified roles.
   *
   * @param user  - The CAS user.
   * @param roles - Required roles.
   * @returns `true` if the user possesses every role.
   */
  static hasRoles(user: CasUser, roles: string[]): boolean {
    if (!user.roles) return roles.length === 0;
    return roles.every((r) => user.roles!.includes(r));
  }

  /**
   * Check whether a user has **at least one** of the specified roles.
   *
   * @param user  - The CAS user.
   * @param roles - Roles to check.
   * @returns `true` if the user possesses any of the roles.
   */
  static hasAnyRole(user: CasUser, roles: string[]): boolean {
    if (!user.roles) return false;
    return roles.some((r) => user.roles!.includes(r));
  }
}
