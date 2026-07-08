/**
 * @module @cas-system/nextjs-cas-client/types
 * @description Core TypeScript type definitions for the CAS Client SDK.
 */

// ---------------------------------------------------------------------------
// CAS Configuration
// ---------------------------------------------------------------------------

/**
 * Base configuration for connecting to the CAS server.
 *
 * @example
 * ```ts
 * const config: CasConfig = {
 *   serverUrl: 'https://cas.example.com',
 *   clientId: 'my-app',
 *   callbackUrl: 'https://myapp.com/api/cas/callback',
 * };
 * ```
 */
export interface CasConfig {
  /** Base URL of the CAS server (no trailing slash). */
  serverUrl: string;

  /** OAuth / SSO client identifier registered on the CAS server. */
  clientId: string;

  /**
   * URL the CAS server redirects to after login.
   * Defaults to `{origin}/api/cas/callback` when omitted.
   */
  callbackUrl?: string;
}

/**
 * Server-side CAS configuration — extends {@link CasConfig} with the
 * client secret that **must never be exposed to the browser**.
 *
 * @example
 * ```ts
 * const serverConfig: CasServerConfig = {
 *   serverUrl: process.env.CAS_SERVER_URL!,
 *   clientId: process.env.CAS_CLIENT_ID!,
 *   clientSecret: process.env.CAS_CLIENT_SECRET!,
 * };
 * ```
 */
export interface CasServerConfig extends CasConfig {
  /** Secret key used for server-to-server token validation & generation. */
  clientSecret: string;
}

// ---------------------------------------------------------------------------
// CAS User
// ---------------------------------------------------------------------------

/**
 * Authenticated user object returned by the CAS server after token validation.
 *
 * @example
 * ```ts
 * const user: CasUser = {
 *   id: '550e8400-e29b-41d4-a716-446655440000',
 *   username: 'jdoe',
 *   email: 'jdoe@example.com',
 *   roles: ['admin', 'editor'],
 * };
 * ```
 */
export interface CasUser {
  /** Unique identifier of the user. */
  id: string;

  /** Login username. */
  username: string;

  /** E-mail address. */
  email: string;

  /** Optional list of role names assigned to the user. */
  roles?: string[];
}

// ---------------------------------------------------------------------------
// Session / Cookie
// ---------------------------------------------------------------------------

/**
 * Shape of the data persisted in the encrypted session cookie.
 * @internal
 */
export interface CasSessionData {
  /** The authenticated user. */
  user: CasUser;

  /** Raw JWT token issued by the CAS server. */
  token: string;

  /** Unix-ms timestamp when the session was created. */
  createdAt: number;
}

// ---------------------------------------------------------------------------
// Middleware
// ---------------------------------------------------------------------------

/**
 * Configuration for the Next.js CAS authentication middleware.
 *
 * @example
 * ```ts
 * const mwConfig: CasMiddlewareConfig = {
 *   protectedPaths: ['/dashboard', '/admin'],
 *   publicPaths: ['/', '/about', '/api/health'],
 *   loginPath: '/login',
 * };
 * ```
 */
export interface CasMiddlewareConfig {
  /**
   * Path prefixes that require authentication.
   * A request whose `pathname` starts with any of these strings will be
   * checked for a valid CAS session cookie.
   */
  protectedPaths: string[];

  /**
   * Custom path for the login page within the app.
   * When provided, unauthenticated users are redirected here instead of
   * directly to the CAS server login URL.
   * @default undefined — redirects straight to CAS server
   */
  loginPath?: string;

  /**
   * Paths that are explicitly public even if they match a protected prefix.
   * Useful for health-check endpoints nested under a protected prefix.
   */
  publicPaths?: string[];
}

// ---------------------------------------------------------------------------
// Auth Context (client-side)
// ---------------------------------------------------------------------------

/**
 * Shape of the value provided by `CasProvider` through React Context.
 */
export interface CasAuthContext {
  /** The current user, or `null` when not authenticated. */
  user: CasUser | null;

  /** `true` while the initial session fetch is in progress. */
  isLoading: boolean;

  /** `true` when a valid session exists. */
  isAuthenticated: boolean;

  /** Error from the last session fetch, if any. */
  error: Error | null;

  /** Redirect the browser to the CAS login page. */
  login: (returnUrl?: string) => void;

  /** Clear the session and redirect to CAS logout. */
  logout: () => Promise<void>;

  /** Re-fetch the session from the server. */
  refresh: () => Promise<void>;
}

// ---------------------------------------------------------------------------
// Handler helpers
// ---------------------------------------------------------------------------

/**
 * Configuration for the API route handlers.
 */
export interface CasHandlerConfig {
  /** Server-side CAS configuration. */
  cas: CasServerConfig;

  /**
   * URL to redirect to after successful login callback.
   * @default '/'
   */
  afterLoginUrl?: string;

  /**
   * URL to redirect to after logout.
   * @default '/'
   */
  afterLogoutUrl?: string;
}

// ---------------------------------------------------------------------------
// Internal fetch helpers
// ---------------------------------------------------------------------------

/**
 * Response shape returned by `POST {CAS_BASE}/api/validate-token`.
 * Success: `{ valid: true, user: { id, username, email }, expires_at }`.
 * Failure: HTTP 401 with `{ error }`.
 * @internal
 */
export interface CasValidateResponse {
  valid?: boolean;
  user?: CasUser;
  expires_at?: string;
  error?: string;
}

/**
 * Response shape returned by `POST {CAS_BASE}/api/sso/token`.
 * Success: `{ redirect_url, token }`.
 * @internal
 */
export interface CasTokenResponse {
  redirect_url?: string;
  token?: string;
  error?: string;
}
