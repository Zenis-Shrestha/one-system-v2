/**
 * CAS logout — POST /api/cas/logout
 *
 * `createLogoutHandler` reads the current session to obtain the JWT, best-effort
 * notifies the CAS server (POST {CAS_SERVER_URL}/api/logout), then clears THIS
 * app's session cookies. The client-side <CasProvider>.logout() calls this.
 */
import { createLogoutHandler } from '@cas-system/nextjs-cas-client/handlers';

export const POST = createLogoutHandler({
  cas: {
    serverUrl: process.env.CAS_SERVER_URL!,
    clientId: process.env.CAS_CLIENT_ID!,
    clientSecret: process.env.CAS_CLIENT_SECRET!,
  },
  afterLogoutUrl: '/',
});
