/**
 * @module @cas-system/nextjs-cas-client/handlers/callback
 * @description API route handler for the CAS SSO callback endpoint.
 *
 * After the CAS server authenticates the user it redirects to
 * `{callbackUrl}?token=JWT_TOKEN`.  This handler:
 *
 * 1. Extracts the `token` query parameter.
 * 2. Validates it server-side via the CAS `/api/validate-token` endpoint.
 * 3. Sets an encrypted session cookie.
 * 4. Redirects the user to the intended URL (or `/`).
 *
 * @example
 * ```ts
 * // app/api/cas/callback/route.ts
 * import { createCallbackHandler } from '@cas-system/nextjs-cas-client/handlers';
 *
 * const handler = createCallbackHandler({
 *   cas: {
 *     serverUrl: process.env.CAS_SERVER_URL!,
 *     clientId: process.env.CAS_CLIENT_ID!,
 *     clientSecret: process.env.CAS_CLIENT_SECRET!,
 *   },
 *   afterLoginUrl: '/dashboard',
 * });
 *
 * export { handler as GET };
 * ```
 */

import { NextRequest, NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import type { CasHandlerConfig } from '../types';
import { CasClient } from '../server/cas-client';
import { setCasSession } from '../server/auth';

/**
 * Create a GET handler for `/api/cas/callback`.
 *
 * @param config - Handler configuration including CAS server details.
 * @returns A Next.js App Router GET handler.
 */
export function createCallbackHandler(config: CasHandlerConfig) {
  const cas = new CasClient(config.cas);
  const afterLoginUrl = config.afterLoginUrl ?? '/';

  return async function GET(request: NextRequest): Promise<NextResponse> {
    const { searchParams } = request.nextUrl;
    const token = searchParams.get('token');
    const returnUrl = searchParams.get('returnUrl') ?? afterLoginUrl;

    if (!token) {
      console.error('[CasCallback] No token query parameter received.');
      return NextResponse.redirect(new URL('/login?error=no_token', request.url));
    }

    // Validate the token server-side
    const user = await cas.validateToken(token);

    if (!user) {
      console.error('[CasCallback] Token validation failed.');
      return NextResponse.redirect(
        new URL('/login?error=invalid_token', request.url),
      );
    }

    // Set session cookie
    const cookieStore = await cookies();
    await setCasSession(cookieStore, user, token);

    // Redirect to the intended URL
    const redirectUrl = new URL(returnUrl, request.url);
    return NextResponse.redirect(redirectUrl);
  };
}
