/**
 * @module @cas-system/nextjs-cas-client/handlers/logout
 * @description API route handler for the CAS logout endpoint.
 *
 * 1. Reads the session to obtain the JWT token.
 * 2. Notifies the CAS server of the logout.
 * 3. Clears the session cookies.
 * 4. Redirects the user (or returns 200 for API calls).
 *
 * @example
 * ```ts
 * // app/api/cas/logout/route.ts
 * import { createLogoutHandler } from '@cas-system/nextjs-cas-client/handlers';
 *
 * const handler = createLogoutHandler({
 *   cas: {
 *     serverUrl: process.env.CAS_SERVER_URL!,
 *     clientId: process.env.CAS_CLIENT_ID!,
 *     clientSecret: process.env.CAS_CLIENT_SECRET!,
 *   },
 *   afterLogoutUrl: '/',
 * });
 *
 * export { handler as POST };
 * ```
 */

import { NextRequest, NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import type { CasHandlerConfig } from '../types';
import { CasClient } from '../server/cas-client';
import { getCasSession, clearCasSession } from '../server/auth';

/**
 * Create a POST handler for `/api/cas/logout`.
 *
 * @param config - Handler configuration.
 * @returns A Next.js App Router POST handler.
 */
export function createLogoutHandler(config: CasHandlerConfig) {
  const cas = new CasClient(config.cas);
  const afterLogoutUrl = config.afterLogoutUrl ?? '/';

  return async function POST(request: NextRequest): Promise<NextResponse> {
    const cookieStore = await cookies();

    // Attempt to read the current session for the token
    const session = await getCasSession(cookieStore);

    // Notify the CAS server (best-effort)
    if (session?.token) {
      await cas.logout(session.token).catch(() => {
        // Swallow — the local session will be cleared regardless
      });
    }

    // Clear local session cookies
    clearCasSession(cookieStore);

    // If the request expects JSON (e.g. fetch from client), return 200
    const accept = request.headers.get('accept') ?? '';
    if (accept.includes('application/json')) {
      return NextResponse.json({ success: true });
    }

    // Otherwise redirect
    return NextResponse.redirect(new URL(afterLogoutUrl, request.url));
  };
}
