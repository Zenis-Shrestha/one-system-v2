/**
 * CAS SSO callback — GET /api/cas/callback
 *
 * This is where the CAS server redirects the browser AFTER the user logs in:
 *   {CAS_CALLBACK_URL}?token=<JWT>
 *
 * `createCallbackHandler` (from the package) does everything for us, SERVER-SIDE:
 *   1. reads the single-use `token` query param
 *   2. validates it via POST {CAS_SERVER_URL}/api/validate-token using the
 *      client_secret (which lives only on the server — never in the browser)
 *   3. on success, creates THIS app's own signed, HttpOnly session cookie
 *   4. redirects to `afterLoginUrl` (or the `?returnUrl=` the middleware set)
 */
import { createCallbackHandler } from '@cas-system/nextjs-cas-client/handlers';

export const GET = createCallbackHandler({
  cas: {
    serverUrl: process.env.CAS_SERVER_URL!,
    clientId: process.env.CAS_CLIENT_ID!,
    clientSecret: process.env.CAS_CLIENT_SECRET!,
  },
  // Where to land after a successful login if no returnUrl was provided.
  afterLoginUrl: '/dashboard',
});
