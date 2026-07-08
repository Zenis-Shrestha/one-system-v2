/**
 * CAS authentication middleware.
 *
 * `createCasMiddleware` guards the configured `protectedPaths`. If a request
 * hits a protected path with no valid CAS session cookie, the middleware
 * redirects the browser straight to the CAS SSO login page:
 *   {CAS_SERVER_URL}/sso/login?client_id=...&redirect_uri={CAS_CALLBACK_URL}?returnUrl=...
 *
 * It reads CAS_SERVER_URL, CAS_CLIENT_ID and CAS_CALLBACK_URL from the
 * environment (we do NOT set `loginPath`, so it goes directly to CAS).
 */
import { createCasMiddleware } from '@cas-system/nextjs-cas-client/middleware';

export default createCasMiddleware({
  protectedPaths: ['/dashboard'],
  publicPaths: ['/api/health'],
});

export const config = {
  // Run on everything except Next internals & static assets.
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
