/**
 * @module @cas-system/nextjs-cas-client/middleware
 * @description Factory for creating a Next.js middleware that guards routes
 * behind CAS authentication.
 *
 * @example
 * ```ts
 * // middleware.ts (project root)
 * import { createCasMiddleware } from '@cas-system/nextjs-cas-client/middleware';
 *
 * export default createCasMiddleware({
 *   protectedPaths: ['/dashboard', '/admin', '/settings'],
 *   publicPaths: ['/api/health'],
 *   loginPath: '/login',
 * });
 *
 * export const config = {
 *   matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
 * };
 * ```
 */

import { NextRequest, NextResponse } from 'next/server';
import type { CasMiddlewareConfig } from './types';
import { getCasSessionFromRequest } from './server/auth';

/**
 * Check whether `pathname` starts with any of the given prefixes.
 * @internal
 */
function matchesAny(pathname: string, prefixes: string[]): boolean {
  return prefixes.some(
    (p) => pathname === p || pathname.startsWith(p.endsWith('/') ? p : `${p}/`),
  );
}

/**
 * Create a Next.js middleware function that enforces CAS authentication on
 * the configured protected paths.
 *
 * @param config - Middleware configuration.
 * @returns A Next.js-compatible middleware function.
 *
 * @remarks
 * - If the request targets a **public path** it is always allowed through.
 * - If the request targets a **protected path** and there is no valid CAS
 *   session cookie, the user is redirected to either the `loginPath` (if
 *   set) or the CAS server SSO login page.
 * - The original URL is forwarded as a `?returnUrl=…` query parameter so
 *   the login page can redirect back after authentication.
 */
export function createCasMiddleware(config: CasMiddlewareConfig) {
  const {
    protectedPaths,
    publicPaths = [],
    loginPath,
  } = config;

  return async function casMiddleware(
    request: NextRequest,
  ): Promise<NextResponse> {
    const { pathname } = request.nextUrl;

    // 1. Skip static assets and internal Next.js routes
    if (
      pathname.startsWith('/_next') ||
      pathname.startsWith('/favicon') ||
      pathname.includes('.')
    ) {
      return NextResponse.next();
    }

    // 2. Always allow explicitly public paths
    if (matchesAny(pathname, publicPaths)) {
      return NextResponse.next();
    }

    // 3. Check whether this path requires authentication
    const isProtected = matchesAny(pathname, protectedPaths);
    if (!isProtected) {
      return NextResponse.next();
    }

    // 4. Attempt to read the session from cookies
    const session = await getCasSessionFromRequest(request);
    if (session) {
      // Authenticated — attach user info as request headers so downstream
      // Server Components / Route Handlers can read them without re-parsing
      // the cookie.
      const requestHeaders = new Headers(request.headers);
      requestHeaders.set('x-cas-user', JSON.stringify(session.user));
      requestHeaders.set('x-cas-authenticated', 'true');

      return NextResponse.next({
        request: { headers: requestHeaders },
      });
    }

    // 5. Not authenticated — redirect to login
    const returnUrl = request.nextUrl.pathname + request.nextUrl.search;

    if (loginPath) {
      // Redirect to the app's own login page
      const loginUrl = new URL(loginPath, request.url);
      loginUrl.searchParams.set('returnUrl', returnUrl);
      return NextResponse.redirect(loginUrl);
    }

    // Redirect directly to the CAS SSO login page.
    // The browser must reach CAS at its PUBLIC base url, which may differ from
    // the internal back-channel url used for server-to-server validation.
    // Prefer CAS_PUBLIC_URL; fall back to CAS_SERVER_URL for single-url setups.
    const casServerUrl = process.env.CAS_PUBLIC_URL || process.env.CAS_SERVER_URL;
    const casClientId = process.env.CAS_CLIENT_ID;
    const casCallbackUrl =
      process.env.CAS_CALLBACK_URL ??
      `${request.nextUrl.origin}/api/cas/callback`;

    if (!casServerUrl || !casClientId) {
      console.error(
        '[CasMiddleware] CAS_SERVER_URL and CAS_CLIENT_ID env vars are required ' +
          'when no loginPath is configured.',
      );
      return NextResponse.next();
    }

    // Encode the return URL into the callback so we can redirect after login
    const callbackWithReturn = new URL(casCallbackUrl);
    callbackWithReturn.searchParams.set('returnUrl', returnUrl);

    const params = new URLSearchParams({
      client_id: casClientId,
      response_type: 'token',
      redirect_uri: callbackWithReturn.toString(),
    });

    const casLoginUrl = `${casServerUrl.replace(/\/+$/, '')}/sso/login?${params.toString()}`;
    return NextResponse.redirect(casLoginUrl);
  };
}
